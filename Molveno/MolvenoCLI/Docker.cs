namespace MolvenoCLI;

/// <summary>Helpers for querying the local Docker installation.</summary>
internal static class Docker
{
    public static bool IsInstalled() => ProcessRunner.Exists("docker");

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
