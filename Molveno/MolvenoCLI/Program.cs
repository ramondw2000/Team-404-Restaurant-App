using MolvenoCLI.Cli;
using MolvenoCLI.Commands;

namespace MolvenoCLI;

internal static class Program
{
    private const string Version = "1.0.0";

    private static async Task<int> Main(string[] args)
    {
        if (args.Length == 0)
        {
            PrintHelp();
            return 0;
        }

        var command = args[0].ToLowerInvariant();
        var rest = new ArgParser(args.Skip(1));

        switch (command)
        {
            case "install":
                return await InstallCommand.RunAsync(rest);

            case "run":
                return RunCommand.Run(rest);

            case "env":
                return EnvCommand.Run(rest);

            case "-h":
            case "--help":
            case "help":
                PrintHelp();
                return 0;

            case "-v":
            case "--version":
            case "version":
                Console.WriteLine($"molveno {Version}");
                return 0;

            default:
                Output.Error($"Unknown command: {command}");
                PrintHelp();
                return 1;
        }
    }

    private static void PrintHelp()
    {
        Console.WriteLine(
            $"""
            molveno {Version} — run RestaurantApp in Docker, anywhere.

            USAGE
                molveno <command> [options]

            COMMANDS
                install            Install the Docker requirements for the current OS
                                   (Docker Desktop on macOS/Windows, get.docker.com on Linux).
                                     --dry-run    Show what would be downloaded, do nothing.

                run                Build and run the RestaurantApp container.
                                     --env=<path> Use a custom environment file (optional).
                                     --port=<n>   Publish on a different host port.
                                     --path=<dir> Path to the RestaurantApp directory.
                                     --no-build   Skip rebuilding the image.
                                     --follow     Stream container logs after starting.

                env                Write an example SQLite environment file.
                                     --output=<path>  Destination (default: ./.env.example).
                                     --force          Overwrite an existing file.

                help               Show this help.
                version            Show the version.

            EXAMPLES
                molveno install
                molveno env
                molveno run
                molveno run --env=./my.env --port=9000
            """);
    }
}
