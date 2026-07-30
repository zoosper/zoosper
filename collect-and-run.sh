#!/usr/bin/env bash
set -u

OUT_DIR="var/reports"
OUT_FILE="$OUT_DIR/files-content.txt"
SEPARATOR=$'\n--------------------------------------------\n\n'

mkdir -p "$OUT_DIR"
: > "$OUT_FILE"   # truncate output file

echo "Paste filenames (comma separated or one per line)."
echo "Finish input with Ctrl+D (EOF)."
# Read all stdin until EOF
file_input=$(</dev/stdin)

# Normalize: replace commas with newlines, remove any standalone 'cat' words, trim whitespace
# Use awk to split lines and sed to remove word 'cat'
mapfile -t files < <(printf '%s' "$file_input" \
  | tr ',' '\n' \
  | sed -E 's/\bcat\b//g' \
  | sed -E 's/^[[:space:]]+|[[:space:]]+$//g' \
  | awk 'NF' )

if [ "${#files[@]}" -eq 0 ]; then
  echo "No filenames provided. Exiting."
  exit 1
fi

echo "Writing file contents to: $OUT_FILE"
for file in "${files[@]}"; do
  echo "Processing: $file"
  printf '%s\n' "$file" >> "$OUT_FILE"
  if [ -f "$file" ]; then
    # Append file contents
    cat "$file" >> "$OUT_FILE"
  else
    printf '*** File not found: %s\n' "$file" >> "$OUT_FILE"
  fi
  printf '%s' "$SEPARATOR" >> "$OUT_FILE"
done

# Ask for commands
echo
echo "If you want to run additional commands and append their output to the report,"
echo "paste commands now (comma separated or one per line). Leave empty and press Enter to skip."
echo "Finish input with Ctrl+D (EOF)."
cmd_input=$(</dev/stdin)

# Normalize commands same way (commas -> newlines, trim)
mapfile -t cmds < <(printf '%s' "$cmd_input" \
  | tr ',' '\n' \
  | sed -E 's/^[[:space:]]+|[[:space:]]+$//g' \
  | awk 'NF' )

if [ "${#cmds[@]}" -gt 0 ]; then
  for cmd in "${cmds[@]}"; do
    printf '%s\n' ">>> $cmd" >> "$OUT_FILE"
    # Run the command and append both stdout and stderr
    bash -lc "$cmd" >> "$OUT_FILE" 2>&1 || true
    printf '%s' "$SEPARATOR" >> "$OUT_FILE"
  done
else
  echo "No commands to run."
fi

echo "Done. Report saved to: $OUT_FILE"
