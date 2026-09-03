<?php
/**
 * Plugin Name:       LV Estoque
 * Plugin URI:        https://example.com/lv-estoque
 * Description:       Estoque de veiculos (CPT, taxonomias, campos, filtros e schema) para o template de lojas de veiculos. O dado mora aqui, nunca no tema.
 * Version:           1.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Victor C. Silva
 * Text Domain:       lv
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'LV_ESTOQUE_VERSION', '1.1.0' );
define( 'LV_ESTOQUE_FILE', __FILE__ );
define( 'LV_ESTOQUE_DIR', plugin_dir_path( __FILE__ ) );
define( 'LV_ESTOQUE_URL', plugin_dir_url( __FILE__ ) );

require_once LV_ESTOQUE_DIR . 'inc/helpers.php';
require_once LV_ESTOQUE_DIR . 'inc/cpt-veiculo.php';
require_once LV_ESTOQUE_DIR . 'inc/taxonomias.php';
require_once LV_ESTOQUE_DIR . 'inc/opcoes-schema.php';
require_once LV_ESTOQUE_DIR . 'inc/opcoes-loja.php';
require_once LV_ESTOQUE_DIR . 'inc/campos-metabox.php';
require_once LV_ESTOQUE_DIR . 'inc/query-filtros.php';
require_once LV_ESTOQUE_DIR . 'inc/schema.php';
require_once LV_ESTOQUE_DIR . 'inc/admin-colunas.php';

/**
 * Ativacao: registra tipos, semeia termos e limpa as rewrite rules para
 * /veiculos/ funcionar de cara.
 */
register_activation_hook( __FILE__, function (): void {
	lv_registrar_cpt_veiculo();
	lv_registrar_taxonomias();
	lv_seed_termos_padrao();

	// Garante o option com os padroes do esquema logo na ativacao.
	if ( false === get_option( 'lv_opcoes' ) ) {
		add_option( 'lv_opcoes', lv_opcoes_padrao() );
	}

	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Aviso quando o Meta Box nao esta presente.
 *
 * O site continua funcionando: o CPT, as taxonomias, a vitrine e as
 * Configuracoes do Site sao nossos e nao dependem de plugin nenhum.
 * Sem o Meta Box o que falta e a tela de cadastro dos campos do veiculo.
 */
add_action( 'admin_notices', function (): void {
	if ( defined( 'RWMB_VER' ) || class_exists( 'RWMB_Loader' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p><strong>LV Estoque:</strong> o plugin '
		. '<a href="' . esc_url( admin_url( 'plugin-install.php?s=meta-box&tab=search&type=term' ) ) . '">Meta Box</a> '
		. '(gratuito) nao esta ativo. O tipo <em>Veiculo</em>, a vitrine e as Configuracoes do Site funcionam normalmente, '
		. 'mas os campos da ficha tecnica e a galeria nao aparecem na tela de cadastro sem ele.</p></div>';
} );
