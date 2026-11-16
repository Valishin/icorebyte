#!/bin/bash

# ============================================
# 🚀 DEPLOY SCRIPT — icorebyte.com
# Uso: pnpm run deploy  o  ./deploy.sh
#
# Para no introducir contraseña en cada deploy:
#   pnpm run deploy:setup-ssh   (una sola vez)
# ============================================

# — Configuración —
SSH_USER="u863791557"
SSH_HOST="195.35.49.28"
SSH_PORT="65002"
REMOTE_PATH="~/domains/icorebyte.com/icorebyte"
REMOTE_PUBLIC="~/domains/icorebyte.com/public_html"
PHP="/opt/alt/php84/usr/bin/php"

# — ControlMaster: una sola conexión SSH para todo el deploy —
SSH_SOCKET="/tmp/ssh-icorebyte-${SSH_USER}@${SSH_HOST}:${SSH_PORT}"
SSH_COMMON="-o ControlMaster=auto -o ControlPath=$SSH_SOCKET -o ControlPersist=600 -o BatchMode=no"
SSH_OPTS="-p $SSH_PORT $SSH_COMMON"   # ssh  usa -p (minúscula)
SCP_OPTS="-P $SSH_PORT $SSH_COMMON"   # scp  usa -P (mayúscula)

cleanup() {
  ssh -O exit -o ControlPath="$SSH_SOCKET" "$SSH_USER@$SSH_HOST" 2>/dev/null
}
trap cleanup EXIT

echo ""
echo "🚀 Iniciando deploy a producción..."
echo "======================================"

# Abrir la conexión master (pide contraseña una sola vez si no hay clave SSH)
echo ""
echo "🔐 Conectando al servidor (una sola vez)..."
ssh -f -N $SSH_OPTS $SSH_USER@$SSH_HOST
if [ $? -ne 0 ]; then
  echo "❌ No se pudo conectar al servidor."
  exit 1
fi
echo "✅ Conexión establecida."

# — PASO 1: Build de Vue/Vite en local —
echo ""
echo "📦 [1/4] Generando build de Vue..."
pnpm run build

if [ $? -ne 0 ]; then
  echo "❌ Error en pnpm run build. Deploy cancelado."
  exit 1
fi
echo "✅ Build generado correctamente."

# — PASO 2: Subir carpeta build al servidor —
echo ""
echo "📤 [2/4] Subiendo assets al servidor..."
scp $SCP_OPTS -r ./public/build $SSH_USER@$SSH_HOST:/home/$SSH_USER/domains/icorebyte.com/icorebyte/public/

if [ $? -ne 0 ]; then
  echo "❌ Error subiendo el build. Deploy cancelado."
  exit 1
fi
echo "✅ Assets subidos correctamente."

# — PASO 2b: Subir archivos estáticos directamente a public_html —
# favicon, robots.txt, sitemap.xml y og-image no forman parte del build
# de Vite y deben copiarse al web root manualmente.
echo ""
echo "📤 [2b] Subiendo archivos estáticos a public_html..."
STATIC_FILES=(
  "public/favicon.ico"
  "public/favicon.png"
  "public/robots.txt"
  "public/sitemap.xml"
  "public/og-image.png"
)
for FILE in "${STATIC_FILES[@]}"; do
  if [ -f "./$FILE" ]; then
    scp $SCP_OPTS "./$FILE" $SSH_USER@$SSH_HOST:/home/$SSH_USER/domains/icorebyte.com/public_html/
    echo "  ✅ $(basename $FILE)"
  else
    echo "  ⚠️  No encontrado: $FILE (saltando)"
  fi
done

# — PASO 3: En el servidor: git pull + copiar build + optimizar —
echo ""
echo "🔄 [3/5] Actualizando servidor..."
ssh $SSH_OPTS $SSH_USER@$SSH_HOST << EOF
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
    echo "  → Publicando assets de Filament..."
    $PHP artisan filament:assets
  fi

  # Copia build a public_html
  echo "  → Copiando assets a public_html..."
  cp -r /home/$SSH_USER/domains/icorebyte.com/icorebyte/public/build/. /home/$SSH_USER/domains/icorebyte.com/public_html/build/

  # Ejecuta migraciones si hay archivos nuevos en database/migrations
  if git diff HEAD@{1} --name-only | grep -q "database/migrations"; then
    echo "  → Migraciones nuevas detectadas, ejecutando..."
    $PHP artisan migrate --force
  fi

  # Forzar APP_URL correcto en producción
  echo "  → Actualizando APP_URL en .env..."
  sed -i 's|^APP_URL=.*|APP_URL=https://icorebyte.com|' .env
  echo "  ✅ APP_URL=https://icorebyte.com"

  # Limpia y regenera caché
  echo "  → Optimizando icorebyte..."
  $PHP artisan optimize:clear
  $PHP artisan optimize
  $PHP artisan filament:optimize

  echo ""
  echo "✅ Servidor actualizado correctamente."
EOF

if [ $? -ne 0 ]; then
  echo "❌ Error al actualizar el servidor."
  exit 1
fi

# — PASO 4/5: Resumen —
echo ""
echo "======================================"
echo "🎉 Deploy completado con éxito!"
echo "🌐 https://icorebyte.com"
echo "======================================"
echo ""
