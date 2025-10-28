# VeriFactu EO Consultores

Repositorio del módulo VeriFactu EO Consultores para Dolibarr ERP/CRM (versiones 17–20). El objetivo es cumplir con el Real Decreto 1007/2023 y la Ley 11/2021 garantizando integridad, trazabilidad e inalterabilidad de los registros de facturación.

## Contenido

- `htdocs/custom/verifactu_eoconsultores/`: Código fuente del módulo.
- `scripts/build_package.sh`: Script para generar el paquete instalable.

## Características principales

- Registro RRSIF en modalidades VERI*FACTU y NO VERI*FACTU con hash encadenado.
- Firma electrónica opcional en modo NO VERI*FACTU.
- Generación de QR tributario AEAT e inserción automática en PDFs de facturas.
- Panel de configuración accesible con log administrativo y exportación JSON.

## Instalación rápida desde código

1. Copiar la carpeta `htdocs/custom/verifactu_eoconsultores/` en la misma ruta de su instancia Dolibarr.
2. Limpiar caché de módulos y activar “VeriFactu EO Consultores” desde *Configuración → Módulos/Aplicaciones*.
3. Configurar los parámetros desde *Facturación → VeriFactu EO Consultores*.

Para una guía detallada, consulte la sección “Guía de instalación” al final de este documento.

### Generar el paquete ZIP

Los binarios no se versionan en el repositorio. Para crear el paquete instalable ejecute:

```bash
./scripts/build_package.sh 0.4.0
```

El archivo `module_verifactu_eoconsultores-0.4.0.zip` quedará disponible en la raíz del proyecto listo para subirse desde la interfaz de módulos externos de Dolibarr.

## Guía de instalación

1. Acceda a Dolibarr con un usuario administrador.
2. Vaya a *Inicio → Configuración → Módulos/Aplicaciones* y habilite “VeriFactu EO Consultores”.
3. En el menú de facturación, abra *VeriFactu EO Consultores*.
4. Rellene el formulario:
   - **NIF Emisor**: identificador fiscal del emisor.
   - **Modalidad**: seleccione VERI*FACTU o NO VERI*FACTU.
   - **Sistema ID**: identificador único del sistema certificado.
   - **Ruta Clave PEM** y **Password PEM**: requeridos para modo NO VERI*FACTU.
   - **Activar QR**: marque para insertar el QR en PDFs.
5. Pulse **💾 Guardar configuración**. El cambio quedará registrado en `/documents/verifactu_eoconsultores_setup.log`.
6. Opcional: utilice **🧪 Generar QR de prueba (EO Consultores)** para validar la generación, lo que creará `/documents/test_qr.png`.
7. Valide o anule facturas para generar registros automáticos; los QR se guardan en `/documents/facture/{ref}/verifactu_qr.png`.
8. Exporte los registros desde `/custom/verifactu_eoconsultores/admin/export.php` utilizando parámetros `from` y `to` si necesita acotar por fecha.

## Compatibilidad

- PHP ≥ 7.4.
- Dolibarr ERP/CRM 17, 18, 19 y 20.

## Licencia

GPL-3.0-or-later.
