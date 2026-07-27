namespace MolvenoCLI;

/// <summary>Helpers for querying the local Docker installation.</summary>
internal static class Docker
{
    /// <summary>Name of the container that runs RestaurantApp.</summary>
    public const string ContainerName = "molveno-restaurantapp";

    public static bool IsInstalled() => ProcessRunner.Exists("docker");

    /// <summary>True when a container named <see cref="ContainerName"/> exists (running or stopped).</summary>
    public static bool ContainerExists()
    {
        var result = ProcessRunner.Capture("docker", new[]
        {
            "ps", "-a", "--filter", $"name=^{ContainerName}$", "--format", "{{.Names}}",
        });
        return result.Success && result.StdOut.Trim().Length > 0;
    }

    public static bool IsDaemonRunning()
    {
        var result = ProcessRunner.Capture("docker", new[] { "info", "--format", "{{.ServerVersion}}" });
        return result.Success && result.StdOut.Length > 0;
    }

    public static string? Version()
    {
        var result = ProcessRunner.Capture("docker", new[] { "version", "--format", "{{.Server.Version}}" });
        return result.Success ? result.StdOut : null;
    }
}
