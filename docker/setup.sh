#!/usr/bin/env bash
# Instalacao completa do WordPress local. Roda DENTRO do container wpcli.
set -euo pipefail

WP="wp --path=/var/www/html"

URL="${WP_URL:-http://localhost:8080}"
TITLE="${WP_TITLE:-Loja de Veiculos (DEV)}"
ADMIN_USER="${WP_ADMIN_USER:-admin}"
ADMIN_PASS="${WP_ADMIN_PASS:-admin}"
ADMIN_EMAIL="${WP_ADMIN_EMAIL:-dev@localhost.test}"

echo "==> Aguardando o core do WordPress aparecer no volume..."
for i in $(seq 1 60); do
  [ -f /var/www/html/wp-load.php ] && break
  sleep 2
done

if $WP core is-installed 2>/dev/null; then
  echo "==> WordPress ja instalado. Seguindo para a configuracao."
else
  echo "==> Instalando WordPress..."
  $WP core install --url="$URL" --title="$TITLE" \
    --admin_user="$ADMIN_USER" --admin_password="$ADMIN_PASS" \
    --admin_email="$ADMIN_EMAIL" --skip-email
fi

echo "==> Idioma e regionalizacao"
$WP language core install pt_BR --activate || true
$WP option update timezone_string 'America/Sao_Paulo'
$WP option update date_format 'd/m/Y'
$WP option update time_format 'H:i'
$WP option update start_of_week 0
$WP rewrite structure '/%postname%/' --hard

echo "==> ACF"
ACF_ZIP=$(ls /lv-docker/acf-pro/*.zip 2>/dev/null | head -1 || true)
if [ -n "$ACF_ZIP" ]; then
  echo "    Instalando ACF PRO de $ACF_ZIP"
  $WP plugin install "$ACF_ZIP" --force --activate
else
  echo "    ACF PRO nao encontrado em docker/acf-pro/. Instalando ACF free (repeater/gallery/options page limitados)."
  $WP plugin install advanced-custom-fields --activate || true
fi

echo "==> Plugins de apoio (dev)"
$WP plugin install wordpress-importer --activate || true

echo "==> Nosso codigo"
$WP theme activate tema-lv
$WP plugin activate plugin-lv-estoque

echo "==> Paginas base"
for P in "Sobre" "Contato" "Politica de Privacidade"; do
  if ! $WP post list --post_type=page --field=post_title | grep -qx "$P"; then
    $WP post create --post_type=page --post_title="$P" --post_status=publish
  fi
done

PRIV_ID=$($WP post list --post_type=page --title="Politica de Privacidade" --field=ID | head -1 || true)
[ -n "$PRIV_ID" ] && $WP option update wp_page_for_privacy_policy "$PRIV_ID"

echo "==> Limpando conteudo de exemplo"
$WP post delete 1 2 3 --force 2>/dev/null || true
$WP plugin delete akismet hello 2>/dev/null || true
$WP theme delete twentytwentythree twentytwentytwo twentytwentyone 2>/dev/null || true

echo "==> Menu principal"
if ! $WP menu list --field=slug | grep -qx "principal"; then
  $WP menu create "Principal"
  $WP menu location assign principal primary || true
fi

echo "==> Descoberta pelos buscadores desligada (ambiente local)"
$WP option update blog_public 0

echo "==> Flush de rewrite"
$WP rewrite flush --hard

echo ""
echo "======================================================"
echo " Pronto."
echo " Site:      $URL"
echo " Admin:     $URL/wp-admin  ($ADMIN_USER / $ADMIN_PASS)"
echo " phpMyAdmin: http://localhost:8081"
echo " Mailpit:    http://localhost:8025"
echo "======================================================"
