#!/bin/bash

# ============================================
# 🚀 DEPLOY SCRIPT — icorebyte.com
# Uso: pnpm run deploy  o  ./deploy.sh
# ============================================

# — Configuración —
SSH_USER="u863791557"
SSH_HOST="icorebyte.com"
SSH_PORT="65002"
REMOTE_PATH="~/domains/icorebyte.com/icorebyte"
REMOTE_PUBLIC="~/domains/icorebyte.com/public_html"
PHP="/opt/alt/php84/usr/bin/php"

echo ""
echo "🚀 Iniciando deploy a producción..."
echo "======================================"

# — PASO 1: Build de Vue/Vite en local —
echo ""
echo "📦 [1/4] Generando build de Vue..."
npm run build

if [ $? -ne 0 ]; then
  echo "❌ Error en npm run build. Deploy cancelado."
  exit 1
fi
echo "✅ Build generado correctamente."

# — PASO 2: Subir carpeta build al servidor —
echo ""
echo "📤 [2/4] Subiendo assets al servidor..."
scp -P $SSH_PORT -r ./public/build $SSH_USER@$SSH_HOST:/home/$SSH_USER/domains/icorebyte.com/icorebyte/public/

if [ $? -ne 0 ]; then
  echo "❌ Error subiendo el build. Deploy cancelado."
  exit 1
fi
echo "✅ Assets subidos correctamente."

# — PASO 3: En el servidor: git pull + copiar build + optimizar —
echo ""
echo "🔄 [3/4] Actualizando servidor..."
ssh -p $SSH_PORT $SSH_USER@$SSH_HOST << EOF
  set -e

  # Usa PHP 8.4
  export PATH=/opt/alt/php84/usr/bin:\$PATH

  cd $REMOTE_PATH

  # Git pull
  echo "  → git pull..."
  git pull

  # Instala dependencias si cambió composer.lock
  if git diff HEAD@{1} --name-only | grep -q "composer.lock"; then
    echo "  → composer.lock cambió, instalando dependencias..."
    composer install --optimize-autoloader --no-dev
  fi

  # Copia build a public_html
  echo "  → Copiando assets a public_html..."
  cp -r /home/$SSH_USER/domains/icorebyte.com/icorebyte/public/build/. /home/$SSH_USER/domains/icorebyte.com/public_html/build/

  # Ejecuta migraciones si hay archivos nuevos en database/migrations
  if git diff HEAD@{1} --name-only | grep -q "database/migrations"; then
    echo "  → Migraciones nuevas detectadas, ejecutando..."
    $PHP artisan migrate --force
  fi

  # Limpia y regenera caché
  echo "  → Optimizando icorebyte..."
  $PHP artisan optimize:clear
  $PHP artisan optimize

  echo ""
  echo "✅ Servidor actualizado correctamente."
EOF

if [ $? -ne 0 ]; then
  echo "❌ Error al actualizar el servidor."
  exit 1
fi

# — PASO 4: Resumen —
echo ""
echo "======================================"
echo "🎉 Deploy completado con éxito!"
echo "🌐 https://icorebyte.com"
echo "======================================"
echo ""
