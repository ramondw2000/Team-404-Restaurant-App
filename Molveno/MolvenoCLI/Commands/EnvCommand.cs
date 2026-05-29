using MolvenoCLI.Cli;

namespace MolvenoCLI.Commands;

/// <summary>
/// <c>molveno env</c> — writes an example SQLite environment file (mirroring the
/// one the RestaurantApp container uses) that can be edited and passed to
/// <c>molveno run --env=&lt;path&gt;</c>.
/// </summary>
internal static class EnvCommand
{
    public static int Run(ArgParser args)
    {
        var target = args.GetOption("output")
            ?? (args.Positional.Count > 0 ? args.Positional[0] : ".env.example");
        var path = Path.GetFullPath(target);

        if (File.Exists(path) && !args.HasFlag("force"))
        {
            Output.Warn($"{path} already exists. Re-run with --force to overwrite.");
            return 1;
        }

        try
        {
            var dir = Path.GetDirectoryName(path);
            if (!string.IsNullOrEmpty(dir))
            {
                Directory.CreateDirectory(dir);
            }

            File.WriteAllText(path, EnvTemplate.Sqlite + Environment.NewLine);
            Output.Success($"Wrote example environment file to {path}");
            Output.Info("Edit it as needed, then run:");
            Output.Info($"    molveno run --env={target}");
            return 0;
        }
        catch (Exception ex)
        {
            Output.Error($"Could not write {path}: {ex.Message}");
            return 1;
        }
    }
}
