#!/usr/bin/env bash
#
# obf.sh — Quick wrapper for wp_obfuscator_inplace.php
#
# Usage:
#   ./obf.sh here              Obfuscate in-place (backup + obfuscate original)
#   ./obf.sh export             Export obfuscated copy to default path
#   ./obf.sh export /custom/path  Export to a custom path
#   ./obf.sh dry                Dry-run preview (no files changed)
#   ./obf.sh restore            Restore clean files after in-place obfuscation
#

set -euo pipefail

# Convert any path to Windows-native C:/ format for PHP.exe
to_win() {
    local p="$1"
    if [[ "$p" == /mnt/[a-zA-Z]/* ]]; then
        local d="${p:5:1}"; echo "${d^}:${p:6}"
    elif [[ "$p" == /[a-zA-Z]/* ]]; then
        local d="${p:1:1}"; echo "${d^}:${p:2}"
    else
        echo "$p"
    fi
}

# Detect WSL (where Windows .exe files can't run directly)
IN_WSL=false
if [[ -f /proc/version ]] && grep -qi microsoft /proc/version 2>/dev/null; then
    IN_WSL=true
fi

# All paths in native bash format (works in Git Bash and WSL)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(dirname "$SCRIPT_DIR")"
PLUGIN_DIR="$REPO_ROOT/public_html/wp-content/plugins/unipixel"
EXCLUDE_LIST="$SCRIPT_DIR/exclude-list.txt"

# Find PHP
PHP=""
if ! $IN_WSL; then
    for candidate in \
        "C:/xampp/php/php.exe" \
        "/c/xampp/php/php.exe" \
        "php.exe" \
        "php" \
    ; do
        if command -v "$candidate" &>/dev/null; then
            PHP="$candidate"
            break
        fi
    done
fi

# In WSL, run PHP through cmd.exe (WSL can't execute .exe directly)
run_php() {
    if $IN_WSL; then
        cmd.exe /c php "$@"
    else
        "$PHP" "$@"
    fi
}

# Verify PHP is available (skip for restore and WSL — WSL check is unreliable)
if [[ "${1:-}" != "restore" ]] && ! $IN_WSL; then
    if ! "$PHP" -v &>/dev/null 2>&1; then
        echo "ERROR: PHP not found."
        echo "Add C:\\xampp\\php to your system PATH, or use Git Bash instead of WSL."
        exit 1
    fi
fi

if [[ ! -d "$PLUGIN_DIR" ]]; then
    echo "ERROR: Plugin folder not found at: $PLUGIN_DIR"
    exit 1
fi

# Default export path — native bash format for the -d check later
if [[ -d "/mnt/c" ]]; then
    DEFAULT_EXPORT="/mnt/c/Users/RohanKleem/Documents/Rohan/buildio/plugin-unipixel/plugin-obf-exports"
else
    DEFAULT_EXPORT="/c/Users/RohanKleem/Documents/Rohan/buildio/plugin-unipixel/plugin-obf-exports"
fi

# Standard obfuscation flags
FLAGS="--encode-strings --minify --strip-comments --verbose"

if [[ -f "$EXCLUDE_LIST" ]]; then
    FLAGS="$FLAGS --exclude-list=$(to_win "$EXCLUDE_LIST")"
fi

usage() {
    echo "Usage: ./obf.sh <command> [options]"
    echo ""
    echo "Commands:"
    echo "  here                Obfuscate in-place (backup + obfuscate original)"
    echo "  export [path]       Export obfuscated copy (default: $(to_win "$DEFAULT_EXPORT"))"
    echo "  restore             Restore clean files after in-place obfuscation"
    echo "  dry                 Dry-run preview (no files changed)"
    echo ""
    echo "Plugin: $(to_win "$PLUGIN_DIR")"
    if $IN_WSL; then echo "PHP:    cmd.exe /c php (WSL)"; else echo "PHP:    $PHP"; fi
    exit 1
}

if [[ $# -lt 1 ]]; then
    usage
fi

COMMAND="$1"
shift

# Convert paths to Windows format only when passing to PHP.exe
W_OBF="$(to_win "$SCRIPT_DIR/wp_obfuscator_inplace.php")"
W_PLUGIN="$(to_win "$PLUGIN_DIR")"

case "$COMMAND" in
    here)
        echo "=== Obfuscating IN-PLACE ==="
        echo "Plugin: $W_PLUGIN"
        echo ""
        run_php "$W_OBF" "$W_PLUGIN" $FLAGS "$@"
        ;;

    export)
        EXPORT_PATH="${1:-$DEFAULT_EXPORT}"
        shift 2>/dev/null || true
        W_EXPORT="$(to_win "$EXPORT_PATH")"
        echo "=== Obfuscating to EXPORT ==="
        echo "Plugin: $W_PLUGIN"
        echo "Export: $W_EXPORT"
        echo ""
        run_php "$W_OBF" "$W_PLUGIN" --export-to="$W_EXPORT" $FLAGS "$@"
        ;;

    dry)
        echo "=== DRY RUN ==="
        echo "Plugin: $W_PLUGIN"
        echo ""
        run_php "$W_OBF" "$W_PLUGIN" $FLAGS --dry-run "$@"
        ;;

    restore)
        PLUGINS_PARENT="$(dirname "$PLUGIN_DIR")"
        PLUGIN_BASE="$(basename "$PLUGIN_DIR")"
        # Find the most recent _clean backup
        BACKUP=""
        for i in $(seq 100 -1 1); do
            candidate="$PLUGINS_PARENT/${PLUGIN_BASE}_clean${i}"
            if [[ -d "$candidate" ]]; then
                BACKUP="$candidate"
                break
            fi
        done
        if [[ -z "$BACKUP" ]] && [[ -d "$PLUGINS_PARENT/${PLUGIN_BASE}_clean" ]]; then
            BACKUP="$PLUGINS_PARENT/${PLUGIN_BASE}_clean"
        fi
        if [[ -z "$BACKUP" ]]; then
            echo "ERROR: No clean backup found next to $W_PLUGIN"
            echo "Run 'bash obf.sh here' first to create one."
            exit 1
        fi
        echo "=== RESTORING from backup ==="
        echo "Backup: $(to_win "$BACKUP")"
        echo "Target: $W_PLUGIN"
        echo ""
        cp -rf "$BACKUP"/* "$PLUGIN_DIR"/
        echo "Files restored."
        rm -rf "$BACKUP"
        echo "Backup removed: $(to_win "$BACKUP")"
        echo "Plugin is back to clean state."
        ;;

    *)
        echo "Unknown command: $COMMAND"
        echo ""
        usage
        ;;
esac
