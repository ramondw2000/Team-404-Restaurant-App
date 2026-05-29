namespace MolvenoCLI.Cli;

/// <summary>
/// Tiny, dependency-free argument parser (Native AOT friendly).
/// Supports <c>--flag</c>, <c>--key=value</c> and <c>--key value</c> forms,
/// plus positional arguments.
/// </summary>
internal sealed class ArgParser
{
    private readonly Dictionary<string, string?> _options = new(StringComparer.OrdinalIgnoreCase);
    private readonly List<string> _positional = new();

    public ArgParser(IEnumerable<string> args)
    {
        var pending = new Queue<string>(args);
        while (pending.Count > 0)
        {
            var token = pending.Dequeue();

            if (token.StartsWith("--", StringComparison.Ordinal))
            {
                var body = token[2..];
                var eq = body.IndexOf('=');
                if (eq >= 0)
                {
                    _options[body[..eq]] = body[(eq + 1)..];
                }
                else if (pending.Count > 0 && !pending.Peek().StartsWith("--", StringComparison.Ordinal))
                {
                    _options[body] = pending.Dequeue();
                }
                else
                {
                    _options[body] = null; // boolean flag
                }
            }
            else
            {
                _positional.Add(token);
            }
        }
    }

    public IReadOnlyList<string> Positional => _positional;

    public bool HasFlag(string name) => _options.ContainsKey(name);

    public string? GetOption(string name) => _options.TryGetValue(name, out var value) ? value : null;
}
