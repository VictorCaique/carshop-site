#!/usr/bin/env bash
#
# Instalacao de uma NOVA LOJA em hospedagem (nao e o ambiente local).
# Meta: 30 minutos ate o site de pe, sem conteudo.
#
# Uso:
#   cp deploy/loja.env.example deploy/loja.env   # preencha
#   bash deploy/setup-loja.sh deploy/loja.env
#
set -euo pipefail

ENV_FILE="${1:-deploy/loja.env}"

if [ ! -f "$ENV_FILE" ]; then
  echo "Arquivo de variaveis nao encontrado: $ENV_FILE"
  exit 1
fi

# shellcheck disable=SC1090
source "$ENV_FILE"

: "${DB_NAME:?defina DB_NAME}"
: "${DB_USER:?defina DB_USER}"
: "${DB_PASS:?defina DB_PASS}"
: "${SITE_URL:?defina SITE_URL}"
: "${LOJA:?defina LOJA}"
: "${ADMIN_USER:?defina ADMIN_USER}"
: "${ADMIN_PASS:?defina ADMIN_PASS}"
: "${ADMIN_EMAIL:?defina ADMIN_EMAIL}"

REPO_TEMA="${REPO_TEMA:-}"
REPO_PLUGIN="${REPO_PLUGIN:-}"
ACF_ZIP="${ACF_ZIP:-}"

echo "==> [1/7] Baixando o core em pt_BR"
wp core download --locale=pt_BR --skip-content

echo "==> [2/7] wp-config"
wp config create \
  --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" \
  --dbhost="${DB_HOST:-localhost}" --dbprefix="${DB_PREFIX:-lv_}" \
  --extra-php <<'PHP'
define( 'DISALLOW_FILE_EDIT', true );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
define( 'WP_POST_REVISIONS', 5 );
define( 'EMPTY_TRASH_DAYS', 15 );
define( 'WP_ENVIRONMENT_TYPE', 'production' );
PHP

echo "==> [3/7] Instalando"
wp core install --url="$SITE_URL" --title="$LOJA" \
  --admin_user="$ADMIN_USER" --admin_password="$ADMIN_PASS" \
  --admin_email="$ADMIN_EMAIL" --skip-email

wp language core install pt_BR --activate
wp option update timezone_string 'America/Sao_Paulo'
wp option update date_format 'd/m/Y'
wp option update time_format 'H:i'
wp option update blogdescription "${SLOGAN:-}"
wp rewrite structure '/%postname%/' --hard

echo "==> [4/7] Nosso codigo"
if [ -n "$REPO_TEMA" ]; then
  git clone --depth 1 "$REPO_TEMA" wp-content/themes/tema-lv
fi
if [ -n "$REPO_PLUGIN" ]; then
  git clone --depth 1 "$REPO_PLUGIN" wp-content/plugins/plugin-lv-estoque
fi

if [ -n "$ACF_ZIP" ]; then
  wp plugin install "$ACF_ZIP" --activate
else
  echo "    ATENCAO: sem ACF PRO. Instalando a versao free (sem repeater/gallery/options page)."
  wp plugin install advanced-custom-fields --activate
fi

wp theme activate tema-lv
wp plugin activate plugin-lv-estoque

echo "==> [5/7] Plugins obrigatorios"
wp plugin install wordfence updraftplus webp-express --activate

echo "==> [6/7] Paginas base e limpeza"
for P in "Sobre" "Contato" "Politica de Privacidade"; do
  wp post list --post_type=page --field=post_title | grep -qx "$P" \
    || wp post create --post_type=page --post_title="$P" --post_status=publish
done

PRIV_ID=$(wp post list --post_type=page --title="Politica de Privacidade" --field=ID | head -1)
[ -n "$PRIV_ID" ] && wp option update wp_page_for_privacy_policy "$PRIV_ID"

wp post delete 1 2 --force || true
wp plugin delete akismet hello || true
wp theme delete --all --force || true

wp menu create "Principal" || true
wp menu location assign principal primary || true

echo "==> [7/7] Termos padrao e permalinks"
wp eval 'lv_seed_termos_padrao();'
wp rewrite flush --hard

echo ""
echo "======================================================"
echo " Loja instalada: $SITE_URL"
echo " Admin: $SITE_URL/wp-admin"
echo ""
echo " Proximo passo: Configuracoes do Site (logo, cores,"
echo " contato, vendedores, conteudo) - veja deploy/checklist.md"
echo "======================================================"
