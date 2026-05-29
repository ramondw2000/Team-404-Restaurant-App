# Builds a single-file Native AOT `molveno.exe` binary on Windows.
# Native AOT cannot cross-compile; run this on Windows. Use build.sh on Linux/macOS.
#
# Usage:  ./build.ps1 [RID]
#   e.g.  ./build.ps1            # auto-detect current architecture
#         ./build.ps1 win-arm64

$ErrorActionPreference = "Stop"

if ($args.Count -ge 1) {
    $rid = $args[0]
} elseif ([System.Runtime.InteropServices.RuntimeInformation]::ProcessArchitecture -eq 'Arm64') {
    $rid = 'win-arm64'
} else {
    $rid = 'win-x64'
}

Push-Location "$PSScriptRoot/MolvenoCLI"
try {
    Write-Host "Publishing Native AOT binary for $rid ..."
    dotnet publish -c Release -r $rid
}
finally {
    Pop-Location
}

Write-Host ""
Write-Host "Done. Binary:"
Write-Host "  Molveno/MolvenoCLI/bin/Release/net10.0/$rid/publish/molveno.exe"
