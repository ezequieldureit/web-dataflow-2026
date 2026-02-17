#!/bin/bash
# Script para iniciar servidor PHP local

echo "🚀 Iniciando servidor PHP local..."
echo "📍 Servidor disponible en: http://localhost:8000"
echo "📝 Formulario de contacto: http://localhost:8000/contacto.html"
echo "🔧 Diagnóstico: http://localhost:8000/test-form.php"
echo ""
echo "Presiona Ctrl+C para detener el servidor"
echo ""

# Cambiar al directorio del script
cd "$(dirname "$0")"

# Iniciar servidor PHP
php -S localhost:8000
