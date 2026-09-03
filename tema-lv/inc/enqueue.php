<?php
/**
 * Assets. CSS < 60 KB, JS < 30 KB, sem jQuery no front.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function (): void {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	$ver = static function ( string $rel ) use ( $dir ): string {
		$caminho = $dir . $rel;
		return file_exists( $caminho ) ? (string) filemtime( $caminho ) : LV_TEMA_VERSION;
	};

	// O style.css do tema so carrega o cabecalho; o CSS real esta separado.
	wp_enqueue_style( 'lv-base', $uri . '/assets/css/base.css', [], $ver( '/assets/css/base.css' ) );
	wp_enqueue_style( 'lv-componentes', $uri . '/assets/css/componentes.css', [ 'lv-base' ], $ver( '/assets/css/componentes.css' ) );
	wp_enqueue_style( 'lv-paginas', $uri . '/assets/css/paginas.css', [ 'lv-componentes' ], $ver( '/assets/css/paginas.css' ) );

	wp_enqueue_script( 'lv-menu', $uri . '/assets/js/menu.js', [], $ver( '/assets/js/menu.js' ), true );

	if ( is_post_type_archive( 'veiculo' ) || is_tax( [ 'marca', 'carroceria', 'cambio', 'combustivel', 'opcional' ] ) ) {
		wp_enqueue_script( 'lv-filtros', $uri . '/assets/js/filtros.js', [], $ver( '/assets/js/filtros.js' ), true );
	}

	if ( is_singular( 'veiculo' ) ) {
		wp_enqueue_script( 'lv-galeria', $uri . '/assets/js/galeria.js', [], $ver( '/assets/js/galeria.js' ), true );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
} );

/**
 * Estilos do editor, para o admin ver o mesmo que o visitante.
 */
add_action( 'after_setup_theme', function (): void {
	add_editor_style( 'assets/css/base.css' );
} );

/**
 * type="module" nos nossos scripts: sao ES modules, e assim ja vem deferidos.
 */
add_filter( 'script_loader_tag', function ( string $tag, string $handle ): string {
	if ( ! in_array( $handle, [ 'lv-menu', 'lv-filtros', 'lv-galeria' ], true ) ) {
		return $tag;
	}
	return str_replace( ' src=', ' type="module" src=', $tag );
}, 10, 2 );
