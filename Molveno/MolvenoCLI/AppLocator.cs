namespace MolvenoCLI;

/// <summary>
/// Locates the RestaurantApp Docker build context (the directory that contains
/// the <c>Dockerfile</c>) so the CLI can build and run it from anywhere.
/// </summary>
internal static class AppLocator
{
    private const string AppFolderName = "RestaurantApp";

    private static bool IsAppDir(string dir) =>
        File.Exists(Path.Combine(dir, "Dockerfile")) &&
        File.Exists(Path.Combine(dir, "docker-compose.yml"));

    public static string? Locate(string? overridePath)
    {
        if (!string.IsNullOrWhiteSpace(overridePath))
        {
            var resolved = Path.GetFullPath(overridePath);
            return IsAppDir(resolved) ? resolved : null;
        }

        var envOverride = Environment.GetEnvironmentVariable("MOLVENO_APP_DIR");
        if (!string.IsNullOrWhiteSpace(envOverride))
        {
            var resolved = Path.GetFullPath(envOverride);
            if (IsAppDir(resolved))
            {
                return resolved;
            }
        }

        foreach (var start in new[] { Directory.GetCurrentDirectory(), AppContext.BaseDirectory })
        {
            var found = WalkUp(start);
            if (found is not null)
            {
                return found;
            }
        }

        return null;
    }

    private static string? WalkUp(string startDir)
    {
        var dir = new DirectoryInfo(startDir);
        while (dir is not null)
        {
            if (IsAppDir(dir.FullName))
            {
                return dir.FullName;
            }

            var nested = Path.Combine(dir.FullName, AppFolderName);
            if (IsAppDir(nested))
            {
                return nested;
            }

            dir = dir.Parent;
        }

        return null;
    }
}
