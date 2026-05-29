using System.Diagnostics;
using MolvenoCLI.Cli;

namespace MolvenoCLI.Commands;

/// <summary>
/// <c>molveno install</c> — installs the Docker requirements needed to run
/// RestaurantApp. Downloads and launches the correct installer for the current
/// OS: Docker Desktop on macOS/Windows, the official get.docker.com convenience
/// script on Linux.
/// </summary>
internal static class InstallCommand
{
    public static async Task<int> RunAsync(ArgParser args)
    {
        Output.Step($"Checking Docker requirements for {Platform.OsLabel} ({Platform.Arch})");

        if (Docker.IsInstalled())
        {
            if (Docker.IsDaemonRunning())
            {
                Output.Success($"Docker is already installed and running (server {Docker.Version()}).");
                Output.Info("You're ready to go:  molveno run");
                return 0;
            }

            Output.Warn("Docker is installed but the daemon is not running.");
            Output.Info(Platform.Os switch
            {
                OsKind.Linux => "Start it with:  sudo systemctl start docker",
                _ => "Start Docker Desktop and wait for it to report 'running', then:  molveno run",
            });
            return 0;
        }

        var (url, fileName) = Platform.DockerInstaller();
        var destination = Path.Combine(Path.GetTempPath(), fileName);

        Output.Info($"Docker is not installed. Installer source:");
        Output.Info($"    {url}");

        if (args.HasFlag("dry-run"))
        {
            Output.Info("(dry run) Would download the above and run the installer for this OS.");
            return 0;
        }

        Output.Step($"Downloading {fileName}");
        if (!await Downloader.DownloadAsync(url, destination))
        {
            return 1;
        }
        Output.Success($"Downloaded to {destination}");

        Output.Step("Running installer");
        var code = Platform.Os switch
        {
            OsKind.Linux => InstallLinux(destination),
            OsKind.MacOs => InstallMacOs(destination),
            OsKind.Windows => InstallWindows(destination),
            _ => Fail("Unsupported operating system."),
        };

        if (code != 0)
        {
            Output.Error("Installer did not complete successfully. See the output above.");
            return code;
        }

        Output.Success("Docker installation step finished.");
        Output.Info(Platform.Os == OsKind.Linux
            ? "You may need to log out/in for group changes, then run:  molveno run"
            : "Launch Docker Desktop, wait until it reports 'running', then run:  molveno run");
        return 0;
    }

    private static int InstallLinux(string scriptPath)
    {
        // The official convenience script requires root; it installs Docker
        // Engine, the CLI, containerd, buildx and the compose plugin.
        Output.Info("Running the official get.docker.com script with sudo...");
        return ProcessRunner.RunInteractive("sudo", new[] { "sh", scriptPath });
    }

    private static int InstallMacOs(string dmgPath)
    {
        Output.Info("Mounting Docker.dmg...");
        if (ProcessRunner.RunInteractive("hdiutil", new[] { "attach", dmgPath, "-nobrowse", "-quiet" }) != 0)
        {
            return 1;
        }

        const string app = "/Volumes/Docker/Docker.app";
        const string volume = "/Volumes/Docker";
        try
        {
            Output.Info("Copying Docker.app to /Applications (may prompt for your password)...");
            if (ProcessRunner.RunInteractive("sudo", new[] { "cp", "-R", app, "/Applications/" }) != 0)
            {
                return 1;
            }

            Output.Info("Launching Docker Desktop for first-run setup...");
            ProcessRunner.RunInteractive("open", new[] { "-a", "Docker" });
            return 0;
        }
        finally
        {
            ProcessRunner.RunInteractive("hdiutil", new[] { "detach", volume, "-quiet" });
        }
    }

    private static int InstallWindows(string installerPath)
    {
        // The Docker Desktop installer requires administrator rights; launch it
        // via ShellExecute so Windows prompts for UAC elevation.
        try
        {
            var psi = new ProcessStartInfo
            {
                FileName = installerPath,
                UseShellExecute = true,
                Verb = "runas",
            };
            psi.ArgumentList.Add("install");
            psi.ArgumentList.Add("--accept-license");

            using var process = Process.Start(psi);
            if (process is null)
            {
                return 1;
            }

            process.WaitForExit();
            return process.ExitCode;
        }
        catch (Exception ex)
        {
            Output.Error($"Failed to launch installer: {ex.Message}");
            return 1;
        }
    }

    private static int Fail(string message)
    {
        Output.Error(message);
        return 1;
    }
}
