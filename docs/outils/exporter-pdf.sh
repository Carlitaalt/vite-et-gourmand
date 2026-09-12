#!/bin/bash
# Régénère les PDF de docs/pdf à partir des fichiers Markdown (diagrammes Mermaid compris).
# L'application doit tourner en local (docker compose up -d) car la page d'export est servie par Apache.
# Usage : bash docs/outils/exporter-pdf.sh [URL de base, par défaut http://localhost:8080]
set -e

BASE="${1:-http://localhost:8080}"
DOCS="$(cd "$(dirname "$0")/.." && pwd)"
CHROME="${CHROME:-/Applications/Google Chrome.app/Contents/MacOS/Google Chrome}"

exporter() {
    "$CHROME" --headless=new --disable-gpu --no-pdf-header-footer --run-all-compositor-stages-before-draw \
        --virtual-time-budget=30000 --print-to-pdf="$DOCS/pdf/$1" \
        "$BASE/docs/outils/export-pdf.html?titre=$2&docs=$3" 2>/dev/null
    echo "→ docs/pdf/$1"
}

T=../technique
exporter documentation-technique.pdf "Documentation%20technique" \
    "$T/01-choix-techniques.md,$T/02-environnement-de-travail.md,$T/03-mcd.md,$T/04-diagramme-de-classes.md,$T/05-cas-d-utilisation.md,$T/06-diagrammes-de-sequence.md,$T/07-securite.md,$T/08-deploiement.md"
exporter manuel-utilisation.pdf "Manuel%20d%27utilisation" "../manuel/manuel-utilisation.md"
