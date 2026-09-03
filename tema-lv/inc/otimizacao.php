<?php
/**
 * Tirar peso do WordPress. O publico chega pelo celular, muitas vezes em 4G ruim.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Emojis: script + CSS que ninguem usa numa loja de carros.
 */
add_action( 'init', function (): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', function ( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, [ 'wpemoji' ] ) : [];
	} );
} );

/**
 * CSS dos blocos: o tema e classico, so o editor precisa disso.
 */
add_action( 'wp_enqueue_scripts', function (): void {
	if ( ! is_admin() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}, 100 );

/**
 * jQuery Migrate fora do front.
 */
add_action( 'wp_default_scripts', function ( $scripts ): void {
	if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
		return;
	}
	$scripts->registered['jquery']->deps = array_diff(
		$scripts->registered['jquery']->deps,
		[ 'jquery-migrate' ]
	);
} );

/**
 * Lixo no head.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

/**
 * XML-RPC: vetor de ataque sem uso aqui.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Nao expor a versao do WP nos assets.
 */
add_filter( 'style_loader_src', 'lv_remover_ver_wp', 9999 );
add_filter( 'script_loader_src', 'lv_remover_ver_wp', 9999 );

function lv_remover_ver_wp( $src ) {
	if ( is_string( $src ) && str_contains( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}

/**
 * Primeira imagem da galeria nunca e lazy (ela e o LCP).
 */
add_filter( 'wp_lazy_loading_enabled', function ( $default, $tag_name, $context ) {
	if ( 'lv-galeria-principal' === $context ) {
		return false;
	}
	return $default;
}, 10, 3 );

/**
 * Qualidade do JPEG: 82 e o ponto onde a foto de carro ainda fica boa
 * e o arquivo cai bastante.
 */
add_filter( 'jpeg_quality', fn() => 82 );
add_filter( 'wp_editor_set_quality', fn() => 82 );

/**
 * Tamanhos intermediarios que nao usamos.
 */
add_filter( 'intermediate_image_sizes_advanced', function ( array $sizes ): array {
	unset( $sizes['medium_large'], $sizes['1536x1536'], $sizes['2048x2048'] );
	return $sizes;
} );

/**
 * Limitar tentativas de login por IP (simples, sem plugin).
 * Em producao o Wordfence/Solid Security assume esse papel.
 */
add_filter( 'authenticate', function ( $user, $username ) {
	if ( empty( $username ) ) {
		return $user;
	}

	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$chave = 'lv_login_' . md5( $ip );
	$falhas = (int) get_transient( $chave );

	if ( $falhas >= 8 ) {
		return new WP_Error( 'lv_bloqueado', 'Muitas tentativas. Tente de novo em 15 minutos.' );
	}

	if ( is_wp_error( $user ) ) {
		set_transient( $chave, $falhas + 1, 15 * MINUTE_IN_SECONDS );
	} else {
		delete_transient( $chave );
	}

	return $user;
}, 30, 2 );

/**
 * Mensagem de erro de login generica: nao contar ao atacante se o usuario existe.
 */
add_filter( 'login_errors', fn() => 'Usuario ou senha incorretos.' );

/**
 * Cabecalhos de seguranca basicos.
 */
add_action( 'send_headers', function (): void {
	if ( is_admin() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'X-Frame-Options: SAMEORIGIN' );
} );
