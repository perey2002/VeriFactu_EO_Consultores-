#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MODULE_SRC="${ROOT_DIR}/htdocs/custom/verifactu_eoconsultores"
MODULE_NAME="verifactu_eoconsultores"
VERSION="${1:-0.4.0}"
OUTPUT="${ROOT_DIR}/module_${MODULE_NAME}-${VERSION}.zip"

if [[ ! -d "${MODULE_SRC}" ]]; then
  echo "No se encontró el código fuente del módulo en ${MODULE_SRC}" >&2
  exit 1
fi

TMPDIR="$(mktemp -d)"
trap 'rm -rf "${TMPDIR}"' EXIT

cp -a "${MODULE_SRC}" "${TMPDIR}/${MODULE_NAME}"

( cd "${TMPDIR}" && zip -r "${OUTPUT}" "${MODULE_NAME}" >/dev/null )

echo "Paquete generado: ${OUTPUT}"
