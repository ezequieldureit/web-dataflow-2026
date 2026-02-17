# Guía para Probar el Formulario Localmente

## Requisitos
- PHP instalado (versión 7.4 o superior)
- Terminal/Consola

## Pasos para Probar

### 1. Verificar que PHP esté instalado
```bash
php -v
```

### 2. Asegurarse de que el archivo .env existe
El archivo `.env` debe estar en la raíz del proyecto con las siguientes variables:

```env
# Cloudflare Turnstile
TURNSTILE_SITE_KEY=0x4AAAAAACXsmLEMR_ltHYhG
TURNSTILE_SECRET_KEY=0x4AAAAAACXsmGwdEvvJnDV6xiUDsQiaG84

# Configuración SMTP (para pruebas locales, puedes usar valores de prueba)
SMTP_HOST=mail.dataflow-services.com
SMTP_PORT=587
SMTP_USERNAME=info@dataflow-services.com
SMTP_PASSWORD=Datainfo1726
SMTP_ENCRYPTION=tls

# Configuración de email
TO_EMAIL=info@dataflow-services.com
FROM_EMAIL=info@dataflow-services.com
FROM_NAME=Dataflow Services Web
```

### 3. Iniciar el servidor PHP local

**Opción A: Usar el script automático**
```bash
./start-server.sh
```

**Opción B: Comando manual**
```bash
php -S localhost:8000
```

### 4. Abrir en el navegador

- **Página principal**: http://localhost:8000/index.html
- **Formulario de contacto**: http://localhost:8000/contacto.html
- **Diagnóstico**: http://localhost:8000/test-form.php

### 5. Probar el formulario

1. Abre http://localhost:8000/contacto.html
2. Completa todos los campos
3. Completa la verificación de Turnstile (debería aparecer automáticamente)
4. Envía el formulario
5. Verifica que recibas el email

## Solución de Problemas

### Turnstile no aparece
- Verifica que `TURNSTILE_SITE_KEY` esté en el `.env`
- Abre la consola del navegador (F12) y busca errores
- Prueba el endpoint: http://localhost:8000/get-turnstile-config.php

### Error al enviar el formulario
- Abre http://localhost:8000/test-form.php para ver el diagnóstico
- Verifica que todas las variables en `.env` estén configuradas
- Revisa la consola del navegador para ver errores JavaScript
- Revisa los logs de PHP (si están habilitados)

### El email no se envía
- Verifica las credenciales SMTP en `.env`
- Asegúrate de que el servidor SMTP permita conexiones desde tu IP
- Para pruebas, puedes desactivar temporalmente la verificación de Turnstile

## Notas Importantes

- El archivo `.env` NO se sube al repositorio (está en `.gitignore`)
- Para producción, asegúrate de crear el `.env` en el servidor con las credenciales correctas
- Turnstile funciona en localhost sin problemas
