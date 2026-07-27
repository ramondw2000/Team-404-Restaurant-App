using System.Runtime.InteropServices;

namespace MolvenoCLI;

internal enum OsKind
{
    Linux,
    MacOs,
    Windows,
    Unknown,
}

/// <summary>
/// Host operating-system and CPU-architecture detection plus the Docker Desktop /
/// Docker Engine download locations that correspond to each platform.
/// </summary>
internal static class Platform
{
    public static OsKind Os
    {
        get
        {
            if (RuntimeInformation.IsOSPlatform(OSPlatform.Linux)) { return OsKind.Linux; }
            if (RuntimeInformation.IsOSPlatform(OSPlatform.OSX)) { return OsKind.MacOs; }
            if (RuntimeInformation.IsOSPlatform(OSPlatform.Windows)) { return OsKind.Windows; }
            return OsKind.Unknown;
        }
    }

    /// <summary>Docker's architecture slug: <c>amd64</c> or <c>arm64</c>.</summary>
    public static string Arch => RuntimeInformation.ProcessArchitecture switch
    {
        Architecture.Arm64 => "arm64",
        Architecture.X64 => "amd64",
        _ => "amd64",
    };

    public static string OsLabel => Os switch
    {
        OsKind.Linux => "Linux",
        OsKind.MacOs => "macOS",
        OsKind.Windows => "Windows",
        _ => "Unknown",
    };

    public static bool IsWindows => Os == OsKind.Windows;

    /// <summary>
    /// The official Docker installer location for the current OS/arch.
    /// macOS and Windows use the Docker Desktop installer; Linux uses the
    /// official convenience script from get.docker.com (Docker Engine).
    /// </summary>
    public static (string Url, string FileName) DockerInstaller() => Os switch
    {
        OsKind.MacOs => ($"https://desktop.docker.com/mac/main/{Arch}/Docker.dmg", "Docker.dmg"),
        OsKind.Windows => ($"https://desktop.docker.com/win/main/{Arch}/Docker%20Desktop%20Installer.exe",
            "Docker Desktop Installer.exe"),
        OsKind.Linux => ("https://get.docker.com", "get-docker.sh"),
        _ => throw new PlatformNotSupportedException("Unsupported operating system."),
    };
}
