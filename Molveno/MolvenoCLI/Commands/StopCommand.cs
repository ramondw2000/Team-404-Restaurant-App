using MolvenoCLI.Cli;

namespace MolvenoCLI.Commands;

/// <summary>
/// <c>molveno stop</c> — stops and removes the running RestaurantApp container.
/// Named Docker volumes (database, images) are preserved, so a later
/// <c>molveno run</c> keeps the existing data.
/// </summary>
internal static class StopCommand
{
    public static int Run(ArgParser args)
    {
        if (!Docker.IsInstalled())
        {
            Output.Error("Docker is not installed, so there is nothing to stop.");
            return 1;
        }

        if (!Docker.ContainerExists())
        {
            Output.Info("RestaurantApp is not running.");
            return 0;
        }

        Output.Step("Stopping RestaurantApp");
        var result = ProcessRunner.Capture("docker", new[] { "rm", "-f", Docker.ContainerName });
        if (!result.Success)
        {
            Output.Error($"Failed to stop the container: {result.StdErr}");
            return 1;
        }

        Output.Success("Stopped and removed the RestaurantApp container.");
        Output.Info("Data is kept. To delete it too, run:");
        Output.Info("    docker volume rm molveno-sqlite molveno-public-storage");
        return 0;
    }
}
