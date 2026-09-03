<?php
/**
 * Tema LV - bootstrap.
 * A aparencia mora aqui. O dado mora no plugin LV Estoque.
 */

defined( 'ABSPATH' ) || exit;

define( 'LV_TEMA_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/icones.php';
require_once get_template_directory() . '/inc/customizacao.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/otimizacao.php';

add_action( 'after_setup_theme', function (): void {
	load_theme_textdomain( 'lv', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'html5',
		[ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ]
	);

	register_nav_menus(
		[
			'primary' => 'Menu principal',
			'footer'  => 'Menu do rodape',
		]
	);
} );

/**
 * Aviso claro quando o tema esta ativo sem o plugin de estoque.
 */
add_action( 'admin_notices', function (): void {
	if ( function_exists( 'lv_preco' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p><strong>Tema LV:</strong> o plugin <em>LV Estoque</em> nao esta ativo. '
		. 'O estoque, os campos e as configuracoes da loja vivem nele - ative-o antes de continuar.</p></div>';
} );

/**
 * O plugin LV Estoque esta carregado?
 *
 * IMPORTANTE: o tema NAO declara funcoes de fallback com os nomes do plugin.
 * Na ativacao do plugin pelo painel, o WordPress ja carregou o tema antes de
 * incluir o arquivo do plugin - os stubs do tema existiriam primeiro e o
 * plugin morreria com "Cannot redeclare". Em vez de stubs, o tema desvia a
 * renderizacao para um template proprio quando o plugin nao esta la.
 */
function lv_tema_tem_plugin(): bool {
	return function_exists( 'lv_preco' );
}

/**
 * Sem o plugin, o front nao tenta renderizar: devolve um 503 sobrio em vez de
 * um fatal error com caminho de arquivo na cara do visitante.
 */
add_filter( 'template_include', function ( $template ) {
	if ( lv_tema_tem_plugin() ) {
		return $template;
	}
	return get_template_directory() . '/inc/sem-plugin.php';
}, 999 );

/**
 * Titulo do documento quando o carro nao tem SEO plugin.
 */
add_filter( 'document_title_parts', function ( array $partes ): array {
	if ( is_singular( 'veiculo' ) && function_exists( 'lv_ano' ) ) {
		$partes['title'] = get_the_title() . ' - ' . lv_ano( get_the_ID() ) . ' - ' . lv_km( get_the_ID() );
	}
	if ( is_post_type_archive( 'veiculo' ) ) {
		$partes['title'] = 'Estoque de veiculos';
	}
	return $partes;
} );

/**
 * Classes uteis no body.
 */
add_filter( 'body_class', function ( array $classes ): array {
	if ( ! lv_tema_tem_plugin() ) {
		return $classes;
	}

	$classes[] = 'lv-fonte-' . sanitize_html_class( (string) lv_option( 'fonte', 'moderno' ) );

	if ( is_singular( 'veiculo' ) ) {
		$classes[] = 'lv-status-' . sanitize_html_class( lv_status( get_the_ID() ) );
	}
	return $classes;
} );

/**
 * Excerpt mais curto e sem "[...]".
 */
add_filter( 'excerpt_more', fn() => '&hellip;' );
add_filter( 'excerpt_length', fn() => 24, 20 );

/**
 * Formulario de contato simples: envia por e-mail, nao grava lead no banco
 * (LGPD - so guardamos o que precisamos guardar).
 */
add_action( 'admin_post_nopriv_lv_contato', 'lv_processar_contato' );
add_action( 'admin_post_lv_contato', 'lv_processar_contato' );

function lv_processar_contato(): void {
	$origem = wp_get_referer() ?: home_url( '/' );

	if ( ! isset( $_POST['lv_contato_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['lv_contato_nonce'] ), 'lv_contato' ) ) {
		wp_safe_redirect( add_query_arg( 'contato', 'erro', $origem ) );
		exit;
	}

	// Honeypot: bot preenche, humano nao ve.
	if ( ! empty( $_POST['lv_site'] ) ) {
		wp_safe_redirect( add_query_arg( 'contato', 'ok', $origem ) );
		exit;
	}

	if ( empty( $_POST['lv_consentimento'] ) ) {
		wp_safe_redirect( add_query_arg( 'contato', 'consentimento', $origem ) );
		exit;
	}

	$nome     = sanitize_text_field( wp_unslash( $_POST['lv_nome'] ?? '' ) );
	$email    = sanitize_email( wp_unslash( $_POST['lv_email'] ?? '' ) );
	$telefone = sanitize_text_field( wp_unslash( $_POST['lv_telefone'] ?? '' ) );
	$mensagem = sanitize_textarea_field( wp_unslash( $_POST['lv_mensagem'] ?? '' ) );
	$assunto  = sanitize_text_field( wp_unslash( $_POST['lv_assunto'] ?? 'Contato pelo site' ) );

	if ( ! $nome || ! $email || ! $mensagem ) {
		wp_safe_redirect( add_query_arg( 'contato', 'erro', $origem ) );
		exit;
	}

	$destino = (string) ( lv_option( 'email' ) ?: get_option( 'admin_email' ) );

	$corpo = sprintf(
		"Nome: %s\nE-mail: %s\nTelefone: %s\n\nMensagem:\n%s\n\n---\nEnviado por: %s",
		$nome,
		$email,
		$telefone,
		$mensagem,
		$origem
	);

	wp_mail(
		$destino,
		'[Site] ' . $assunto,
		$corpo,
		[ 'Reply-To: ' . $nome . ' <' . $email . '>' ]
	);

	wp_safe_redirect( add_query_arg( 'contato', 'ok', $origem ) . '#contato' );
	exit;
}
