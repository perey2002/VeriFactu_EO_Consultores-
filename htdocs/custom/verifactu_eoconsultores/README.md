# VeriFactu EO Consultores

Módulo para Dolibarr ERP/CRM compatible con versiones 17 a 20 que implementa los requisitos del Real Decreto 1007/2023 y Ley 11/2021 para sistemas informáticos de facturación.

## Funcionalidades clave

- Registro de operaciones Veri*Factu y NO Veri*Factu con hash encadenado SHA-256.
- Firma electrónica opcional mediante clave PEM para modalidad NO VERI*FACTU.
- Generación automática de QR tributario conforme a AEAT y colocación en PDF.
- Panel de configuración accesible (WCAG 2.1) con registro administrativo de cambios.
- Exportación JSON de registros con metadatos del módulo.
- Compatibilidad con Dolibarr 17, 18, 19 y 20.

## Instalación rápida

1. Copiar la carpeta `verifactu_eoconsultores` dentro de `htdocs/custom/`.
2. Activar el módulo desde *Configuración → Módulos* en Dolibarr.
3. Acceder a *Facturación → VeriFactu EO Consultores* para configurar NIF, modalidad, identificador y certificados.
4. Ejecutar la exportación desde `admin/export.php` según sea necesario.

Para más detalles consulte la guía incluida al final de este repositorio.
