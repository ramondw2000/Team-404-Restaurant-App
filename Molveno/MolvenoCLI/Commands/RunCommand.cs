using MolvenoCLI.Cli;

namespace MolvenoCLI.Commands;

/// <summary>
/// <c>molveno run [--env=&lt;path&gt;]</c> — builds the RestaurantApp image and runs
/// it as a Docker container. Uses RestaurantApp's bundled <c>.env.docker</c> by
/// default (SQLite), or a custom env file when <c>--env</c> is supplied.
/// </summary>
internal static class RunCommand
{
    private const string ImageTag = "molveno-restaurantapp:local";
    private const string ContainerName = "molveno-restaurantapp";

    public static int Run(ArgParser args)
    {
        if (!Docker.IsInstalled())
        {
            Output.Error("Docker is not installed. Run 'molveno install' first.");
            return 1;
        }

        if (!Docker.IsDaemonRunning())
        {
            Output.Error("The Docker daemon is not running. Start Docker and try again.");
            return 1;
        }

        var appDir = AppLocator.Locate(args.GetOption("path"));
        if (appDir is null)
        {
            Output.Error("Could not locate the RestaurantApp directory (needs Dockerfile + docker-compose.yml).");
            Output.Info("Pass it explicitly:  molveno run --path=/path/to/RestaurantApp");
            return 1;
        }
        Output.Info($"Using RestaurantApp at: {appDir}");

        if (!TryResolveEnvFile(args, appDir, out var envFile, out var envContents))
        {
            return 1;
        }
        Output.Info($"Using environment file: {envFile}");

        var port = args.GetOption("port")
            ?? EnvTemplate.ReadValue(envContents, "APP_PORT")
            ?? "8000";

        // Build the image.
        if (!args.HasFlag("no-build"))
        {
            Output.Step("Building the RestaurantApp image (this can take a few minutes the first time)");
            if (ProcessRunner.RunInteractive("docker", new[] { "build", "-t", ImageTag, appDir }) != 0)
            {
                Output.Error("docker build failed.");
                return 1;
            }
        }

        // Replace any previous container.
        ProcessRunner.Capture("docker", new[] { "rm", "-f", ContainerName });

        // Assemble the run arguments.
        var runArgs = new List<string>
        {
            "run", "-d",
            "--name", ContainerName,
            "-p", $"{port}:8000",
            "--env-file", envFile,
            "-v", "molveno-sqlite:/var/www/html/database/data",
            "-v", "molveno-public-storage:/var/www/html/storage/app/public",
        };

        var docsDir = Path.GetFullPath(Path.Combine(appDir, "..", "docs"));
        if (Directory.Exists(docsDir))
        {
            runArgs.Add("-v");
            runArgs.Add($"{docsDir}:/var/www/docs:ro");
        }
        else
        {
            Output.Warn($"Images directory not found at {docsDir}; dishes will seed without images.");
        }

        runArgs.Add(ImageTag);

        Output.Step("Starting the container");
        if (ProcessRunner.RunInteractive("docker", runArgs) != 0)
        {
            Output.Error("docker run failed.");
            return 1;
        }

        Output.Success($"RestaurantApp is starting at http://localhost:{port}");
        Output.Info("First boot runs migrations and seeding; give it a few seconds.");
        Output.Info($"  Logs:  docker logs -f {ContainerName}");
        Output.Info($"  Stop:  docker rm -f {ContainerName}");

        if (args.HasFlag("follow"))
        {
            Output.Step("Following container logs (Ctrl+C to detach; the container keeps running)");
            ProcessRunner.RunInteractive("docker", new[] { "logs", "-f", ContainerName });
        }

        return 0;
    }

    private static bool TryResolveEnvFile(ArgParser args, string appDir, out string envFile, out string envContents)
    {
        envFile = string.Empty;
        envContents = string.Empty;

        var custom = args.GetOption("env");
        if (!string.IsNullOrWhiteSpace(custom))
        {
            var resolved = Path.GetFullPath(custom);
            if (!File.Exists(resolved))
            {
                Output.Error($"Environment file not found: {resolved}");
                return false;
            }

            envFile = resolved;
            envContents = File.ReadAllText(resolved);
            return true;
        }

        // Default: the env file bundled with RestaurantApp.
        var bundled = Path.Combine(appDir, ".env.docker");
        if (File.Exists(bundled))
        {
            envFile = bundled;
            envContents = File.ReadAllText(bundled);
            return true;
        }

        // Fall back to a generated default so the command always works.
        var generated = Path.Combine(Path.GetTempPath(), "molveno.env");
        envContents = EnvTemplate.Sqlite;
        File.WriteAllText(generated, envContents + Environment.NewLine);
        envFile = generated;
        Output.Warn($"No .env.docker found; generated a default SQLite env at {generated}.");
        return true;
    }
}
