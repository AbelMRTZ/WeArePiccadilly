#!/bin/bash
PHP=/Applications/XAMPP/xamppfiles/bin/php
PORT=8000
DIR="$(cd "$(dirname "$0")" && pwd)"

# Matar instancia previa si la hay
lsof -ti :$PORT | xargs kill -9 2>/dev/null

echo ""
echo "  WeArePiccadilly — modo local"
echo "  http://localhost:$PORT"
echo "  (Ctrl+C para parar)"
echo ""

$PHP -S localhost:$PORT -t "$DIR" "$DIR/router.php"
