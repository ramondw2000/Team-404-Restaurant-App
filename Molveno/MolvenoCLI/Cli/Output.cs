namespace MolvenoCLI.Cli;

/// <summary>
/// Minimal coloured console helpers. Colours are disabled automatically when
/// output is redirected or when the NO_COLOR environment variable is set.
/// </summary>
internal static class Output
{
    private static readonly bool ColorEnabled =
        !Console.IsOutputRedirected &&
        string.IsNullOrEmpty(Environment.GetEnvironmentVariable("NO_COLOR"));

    private static void Write(string prefix, ConsoleColor color, string message)
    {
        if (ColorEnabled)
        {
            Console.ForegroundColor = color;
            Console.Write(prefix);
            Console.ResetColor();
            Console.WriteLine(message);
        }
        else
        {
            Console.WriteLine($"{prefix}{message}");
        }
    }

    public static void Step(string message) => Write("==> ", ConsoleColor.Cyan, message);

    public static void Info(string message) => Console.WriteLine(message);

    public static void Success(string message) => Write("✓ ", ConsoleColor.Green, message);

    public static void Warn(string message) => Write("! ", ConsoleColor.Yellow, message);

    public static void Error(string message) => Write("✗ ", ConsoleColor.Red, message);
}
