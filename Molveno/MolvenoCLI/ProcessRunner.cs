using System.Diagnostics;
using System.Text;

namespace MolvenoCLI;

internal readonly record struct ProcessResult(int ExitCode, string StdOut, string StdErr)
{
    public bool Success => ExitCode == 0;
}

/// <summary>
/// Thin wrapper around <see cref="Process"/> for invoking external tools
/// (docker, installers, shells).
/// </summary>
internal static class ProcessRunner
{
    /// <summary>Runs a command, streaming its output straight to the console.</summary>
    public static int RunInteractive(string fileName, IEnumerable<string> arguments, string? workingDirectory = null)
    {
        var psi = new ProcessStartInfo
        {
            FileName = fileName,
            UseShellExecute = false,
            WorkingDirectory = workingDirectory ?? string.Empty,
        };
        foreach (var arg in arguments)
        {
            psi.ArgumentList.Add(arg);
        }

        try
        {
            using var process = Process.Start(psi);
            if (process is null)
            {
                return -1;
            }

            process.WaitForExit();
            return process.ExitCode;
        }
        catch (Exception ex)
        {
            Cli.Output.Error($"Failed to start '{fileName}': {ex.Message}");
            return -1;
        }
    }

    /// <summary>Runs a command and captures its output.</summary>
    public static ProcessResult Capture(string fileName, IEnumerable<string> arguments)
    {
        var psi = new ProcessStartInfo
        {
            FileName = fileName,
            UseShellExecute = false,
            RedirectStandardOutput = true,
            RedirectStandardError = true,
        };
        foreach (var arg in arguments)
        {
            psi.ArgumentList.Add(arg);
        }

        try
        {
            using var process = Process.Start(psi);
            if (process is null)
            {
                return new ProcessResult(-1, string.Empty, "process did not start");
            }

            var stdout = new StringBuilder();
            var stderr = new StringBuilder();
            process.OutputDataReceived += (_, e) => { if (e.Data is not null) { stdout.AppendLine(e.Data); } };
            process.ErrorDataReceived += (_, e) => { if (e.Data is not null) { stderr.AppendLine(e.Data); } };
            process.BeginOutputReadLine();
            process.BeginErrorReadLine();
            process.WaitForExit();

            return new ProcessResult(process.ExitCode, stdout.ToString().Trim(), stderr.ToString().Trim());
        }
        catch (Exception ex)
        {
            return new ProcessResult(-1, string.Empty, ex.Message);
        }
    }

    /// <summary>Returns true when an executable can be located and launched.</summary>
    public static bool Exists(string fileName, string versionArgument = "--version")
    {
        try
        {
            var result = Capture(fileName, new[] { versionArgument });
            return result.ExitCode == 0;
        }
        catch
        {
            return false;
        }
    }
}
