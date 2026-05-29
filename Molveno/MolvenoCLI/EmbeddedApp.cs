using System.Formats.Tar;
using System.IO.Compression;
using System.Reflection;
using MolvenoCLI.Cli;

namespace MolvenoCLI;

/// <summary>
/// Access to the RestaurantApp build context that is embedded inside the CLI
/// binary. When present, the application can be built and run with nothing on
/// disk except the <c>molveno</c> executable itself.
///
/// The archive (a gzip tarball produced at build time) contains:
///   RestaurantApp/...   — the Docker build context (Dockerfile, source, etc.)
///   docs/images/...     — the seed images the database seeders read.
/// It is extracted to a per-version cache directory on first use.
/// </summary>
internal static class EmbeddedApp
{
    private static readonly Assembly Self = typeof(EmbeddedApp).Assembly;

    public static bool IsAvailable =>
        Array.IndexOf(Self.GetManifestResourceNames(), AppInfo.AppArchiveResource) >= 0;

    /// <summary>
    /// Ensures the embedded app is extracted to the cache and returns the path to
    /// the extracted RestaurantApp directory (the Docker build context).
    /// </summary>
    public static string? Ensure(bool refresh)
    {
        using var resource = Self.GetManifestResourceStream(AppInfo.AppArchiveResource);
        if (resource is null)
        {
            return null;
        }

        var cacheRoot = Path.Combine(BaseCacheDir(), AppInfo.Version);
        var appDir = Path.Combine(cacheRoot, "RestaurantApp");
        var marker = Path.Combine(cacheRoot, ".extracted");
        var signature = resource.Length.ToString();

        if (!refresh
            && Directory.Exists(appDir)
            && File.Exists(marker)
            && File.ReadAllText(marker).Trim() == signature)
        {
            return appDir;
        }

        try
        {
            Output.Step("Unpacking bundled RestaurantApp");
            if (Directory.Exists(cacheRoot))
            {
                Directory.Delete(cacheRoot, recursive: true);
            }
            Directory.CreateDirectory(cacheRoot);

            Extract(resource, cacheRoot);
            File.WriteAllText(marker, signature);

            if (!Directory.Exists(appDir))
            {
                Output.Error("Bundled archive did not contain a RestaurantApp directory.");
                return null;
            }

            Output.Success($"Bundled app ready at {appDir}");
            return appDir;
        }
        catch (Exception ex)
        {
            Output.Error($"Failed to unpack the bundled app: {ex.Message}");
            return null;
        }
    }

    private static void Extract(Stream archive, string destinationRoot)
    {
        var rootFull = Path.GetFullPath(destinationRoot) + Path.DirectorySeparatorChar;

        using var gzip = new GZipStream(archive, CompressionMode.Decompress);
        using var tar = new TarReader(gzip);

        while (tar.GetNextEntry() is { } entry)
        {
            var name = entry.Name.Replace('\\', '/').TrimStart('/');
            if (name.Length == 0)
            {
                continue;
            }

            var target = Path.GetFullPath(Path.Combine(destinationRoot, name));
            if (!target.StartsWith(rootFull, StringComparison.Ordinal))
            {
                // Guard against path traversal in a malformed archive.
                continue;
            }

            if (entry.EntryType is TarEntryType.Directory)
            {
                Directory.CreateDirectory(target);
                continue;
            }

            if (entry.EntryType is TarEntryType.RegularFile or TarEntryType.V7RegularFile)
            {
                var parent = Path.GetDirectoryName(target);
                if (!string.IsNullOrEmpty(parent))
                {
                    Directory.CreateDirectory(parent);
                }

                entry.ExtractToFile(target, overwrite: true);
            }
        }
    }

    private static string BaseCacheDir()
    {
        var overrideHome = Environment.GetEnvironmentVariable("MOLVENO_HOME");
        if (!string.IsNullOrWhiteSpace(overrideHome))
        {
            return Path.Combine(overrideHome, "app");
        }

        var localData = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData);
        if (string.IsNullOrWhiteSpace(localData))
        {
            localData = Path.GetTempPath();
        }

        return Path.Combine(localData, "molveno", "app");
    }
}
