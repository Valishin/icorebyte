#!/bin/bash

# ============================================
# 🔑 SETUP SSH KEY — icorebyte.com
# Uso: pnpm run deploy:setup-ssh  (una sola vez)
#
# Instala tu clave pública en el servidor para
# que pnpm run deploy no pida contraseña nunca más.
# ============================================

SSH_USER="u863791557"
SSH_HOST="195.35.49.28"
SSH_PORT="65002"
KEY_FILE="$HOME/.ssh/id_ed25519_icorebyte"

echo ""
echo "🔑 Configurando autenticación SSH sin contraseña..."
echo "=================================================="

# 1. Generar clave si no existe
if [ ! -f "$KEY_FILE" ]; then
  echo ""
  echo "📝 Generando clave SSH Ed25519..."
  ssh-keygen -t ed25519 -f "$KEY_FILE" -C "icorebyte-deploy" -N ""
  echo "✅ Clave generada en $KEY_FILE"
else
  echo "ℹ️  Ya existe una clave en $KEY_FILE (no se sobreescribe)"
fi

# 2. Configurar ~/.ssh/config para usar esta clave con el servidor
SSH_CONFIG="$HOME/.ssh/config"
MARKER="# icorebyte-deploy"

if ! grep -q "$MARKER" "$SSH_CONFIG" 2>/dev/null; then
  echo ""
  echo "📝 Añadiendo entrada en ~/.ssh/config..."
  cat >> "$SSH_CONFIG" << SSHCONF

$MARKER
Host icorebyte-prod
  HostName $SSH_HOST
  Port $SSH_PORT
  User $SSH_USER
  IdentityFile $KEY_FILE
  IdentitiesOnly yes
  ControlMaster auto
  ControlPath /tmp/ssh-icorebyte-%r@%h:%p
  ControlPersist 10m
  ServerAliveInterval 60
SSHCONF
  chmod 600 "$SSH_CONFIG"
  echo "✅ ~/.ssh/config actualizado"
else
  echo "ℹ️  ~/.ssh/config ya tiene la entrada (no se modifica)"
fi

# 3. Instalar clave pública en el servidor
echo ""
echo "📤 Instalando clave pública en el servidor..."
echo "   (te pedirá la contraseña por ÚLTIMA vez)"
echo ""
ssh-copy-id -i "${KEY_FILE}.pub" -p "$SSH_PORT" "$SSH_USER@$SSH_HOST"

if [ $? -eq 0 ]; then
  echo ""
  echo "=================================================="
  echo "✅ ¡Listo! A partir de ahora pnpm run deploy"
  echo "   no pedirá contraseña."
  echo ""
  echo "   Prueba la conexión:"
  echo "   ssh -p $SSH_PORT $SSH_USER@$SSH_HOST"
  echo "=================================================="
else
  echo ""
  echo "❌ No se pudo instalar la clave."
  echo "   Comprueba que la contraseña es correcta y vuelve a intentarlo."
  exit 1
fi
