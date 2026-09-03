<?php
/**
 * Plugin Name:       LV Estoque
 * Plugin URI:        https://example.com/lv-estoque
 * Description:       Estoque de veiculos (CPT, taxonomias, campos, filtros e schema) para o template de lojas de veiculos. O dado mora aqui, nunca no tema.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Victor C. Silva
 * Text Domain:       lv
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'LV_ESTOQUE_VERSION', '1.0.0' );
define( 'LV_ESTOQUE_FILE', __FILE__ );
define( 'LV_ESTOQUE_DIR', plugin_dir_path( __FILE__ ) );
define( 'LV_ESTOQUE_URL', plugin_dir_url( __FILE__ ) );

require_once LV_ESTOQUE_DIR . 'inc/helpers.php';
require_once LV_ESTOQUE_DIR . 'inc/cpt-veiculo.php';
require_once LV_ESTOQUE_DIR . 'inc/taxonomias.php';
require_once LV_ESTOQUE_DIR . 'inc/campos-acf.php';
require_once LV_ESTOQUE_DIR . 'inc/opcoes-loja.php';
require_once LV_ESTOQUE_DIR . 'inc/query-filtros.php';
require_once LV_ESTOQUE_DIR . 'inc/schema.php';
require_once LV_ESTOQUE_DIR . 'inc/admin-colunas.php';

/**
 * Ativacao: registra tipos e limpa as rewrite rules para /veiculos/ funcionar de cara.
 */
register_activation_hook( __FILE__, function () {
	lv_registrar_cpt_veiculo();
	lv_registrar_taxonomias();
	lv_seed_termos_padrao();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Aviso quando o ACF nao esta presente: o plugin continua funcionando com
 * post meta cru, mas o painel de cadastro fica sem os campos.
 */
add_action( 'admin_notices', function () {
	if ( function_exists( 'get_field' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>LV Estoque:</strong> o Advanced Custom Fields nao esta ativo. '
		. 'O tipo <em>Veiculo</em> funciona, mas os campos da ficha tecnica so aparecem com o ACF instalado. '
		. 'Use o ACF PRO (licenca unica) para ter galeria, repeater e a tela de Configuracoes do Site.</p></div>';
} );
