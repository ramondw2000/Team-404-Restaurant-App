namespace MolvenoCLI;

internal static class Downloader
{
    /// <summary>
    /// Downloads <paramref name="url"/> to <paramref name="destination"/>,
    /// rendering a simple progress indicator.
    /// </summary>
    public static async Task<bool> DownloadAsync(string url, string destination)
    {
        using var http = new HttpClient { Timeout = TimeSpan.FromMinutes(30) };
        http.DefaultRequestHeaders.UserAgent.ParseAdd("molveno-cli/1.0");

        try
        {
            using var response = await http.GetAsync(url, HttpCompletionOption.ResponseHeadersRead);
            response.EnsureSuccessStatusCode();

            var total = response.Content.Headers.ContentLength ?? -1L;
            await using var source = await response.Content.ReadAsStreamAsync();
            await using var target = File.Create(destination);

            var buffer = new byte[81920];
            long downloaded = 0;
            int read;
            var lastReport = -1;

            while ((read = await source.ReadAsync(buffer)) > 0)
            {
                await target.WriteAsync(buffer.AsMemory(0, read));
                downloaded += read;

                if (total > 0)
                {
                    var percent = (int)(downloaded * 100 / total);
                    if (percent != lastReport && percent % 5 == 0)
                    {
                        lastReport = percent;
                        Console.Write($"\r    {percent,3}%  ({downloaded / 1_048_576} MB / {total / 1_048_576} MB)   ");
                    }
                }
            }

            if (total > 0)
            {
                Console.WriteLine();
            }

            return true;
        }
        catch (Exception ex)
        {
            Cli.Output.Error($"Download failed: {ex.Message}");
            return false;
        }
    }
}
