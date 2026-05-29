#!/usr/bin/env bash
set -euo pipefail

# Builds a single-file Native AOT `molveno` binary.
# Native AOT cannot cross-compile, so run this script ON each target OS
# (Linux, macOS) to produce that platform's binary. Use build.ps1 on Windows.
#
# Usage:  ./build.sh [RID]
#   e.g.  ./build.sh            # auto-detect current platform
#         ./build.sh linux-arm64

RID="${1:-}"
if [ -z "$RID" ]; then
    case "$(uname -s)-$(uname -m)" in
        Linux-x86_64)   RID=linux-x64 ;;
        Linux-aarch64)  RID=linux-arm64 ;;
        Darwin-arm64)   RID=osx-arm64 ;;
        Darwin-x86_64)  RID=osx-x64 ;;
        *) echo "Unknown platform; pass a RID explicitly, e.g. ./build.sh linux-x64"; exit 1 ;;
    esac
fi

cd "$(dirname "$0")/MolvenoCLI"
echo "Publishing Native AOT binary for $RID ..."
dotnet publish -c Release -r "$RID"

echo
echo "Done. Binary:"
echo "  Molveno/MolvenoCLI/bin/Release/net10.0/$RID/publish/molveno"
