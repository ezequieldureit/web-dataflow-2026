# Web Dataflow Services

Sitio web de Dataflow Services con formulario de contacto protegido con Cloudflare Turnstile.

## Configuración

### Variables de Entorno

Crea un archivo `.env` en la raíz del proyecto con las siguientes variables:

```env
# Cloudflare Turnstile
TURNSTILE_SITE_KEY=tu_clave_del_sitio_aqui
TURNSTILE_SECRET_KEY=tu_clave_secreta_aqui

# Configuración SMTP
SMTP_HOST=mail.tudominio.com
SMTP_PORT=587
SMTP_USERNAME=tu_email@tudominio.com
SMTP_PASSWORD=tu_contraseña_aqui
SMTP_ENCRYPTION=tls

# Configuración de email
TO_EMAIL=info@tudominio.com
FROM_EMAIL=info@tudominio.com
FROM_NAME=Dataflow Services Web
```

### Importante

- **NUNCA** subas el archivo `.env` al repositorio (está en `.gitignore`)
- Si el servidor no procesa PHP en archivos `.html`, renombra `contacto.html` a `contacto.php`
- Asegúrate de que el archivo `config.php` pueda leer el archivo `.env`

## Instalación

1. Clona el repositorio
2. Copia `.env.example` a `.env` y completa con tus credenciales
3. Configura tu servidor web para procesar PHP
4. Asegúrate de que los permisos del archivo `.env` sean correctos (no accesible públicamente)

## Estructura

- `contacto.html` - Página de contacto con formulario
- `send-email.php` - Script PHP para procesar el formulario
- `config.php` - Carga variables de entorno
- `.env` - Archivo de configuración (no se sube al repo)
- `.env.example` - Plantilla de configuración

## Seguridad

- Las claves secretas están en variables de entorno
- El archivo `.env` está excluido del repositorio
- Cloudflare Turnstile protege el formulario contra spam
