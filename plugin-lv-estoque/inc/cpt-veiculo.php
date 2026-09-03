<?php
/**
 * Custom Post Type: veiculo.
 * Fica no plugin de proposito: trocar de tema nao pode apagar o estoque do cliente.
 */

defined( 'ABSPATH' ) || exit;

function lv_registrar_cpt_veiculo(): void {
	register_post_type(
		'veiculo',
		[
			'labels' => [
				'name'                  => 'Veiculos',
				'singular_name'         => 'Veiculo',
				'menu_name'             => 'Estoque',
				'add_new'               => 'Adicionar',
				'add_new_item'          => 'Adicionar veiculo',
				'edit_item'             => 'Editar veiculo',
				'new_item'              => 'Novo veiculo',
				'view_item'             => 'Ver veiculo',
				'view_items'            => 'Ver veiculos',
				'search_items'          => 'Buscar veiculos',
				'not_found'             => 'Nenhum veiculo cadastrado',
				'not_found_in_trash'    => 'Nenhum veiculo na lixeira',
				'all_items'             => 'Todos os veiculos',
				'featured_image'        => 'Foto de capa',
				'set_featured_image'    => 'Definir foto de capa',
				'remove_featured_image' => 'Remover foto de capa',
				'use_featured_image'    => 'Usar como foto de capa',
			],
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_rest'        => true,
			'has_archive'         => 'veiculos',
			'rewrite'             => [ 'slug' => 'veiculos', 'with_front' => false ],
			'menu_icon'           => 'dashicons-car',
			'menu_position'       => 5,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
			'exclude_from_search' => false,
		]
	);
}
add_action( 'init', 'lv_registrar_cpt_veiculo', 5 );

/**
 * Tamanhos de imagem do template (SPEC 9).
 */
add_action( 'after_setup_theme', function () {
	add_image_size( 'lv-card', 600, 400, true );
	add_image_size( 'lv-galeria', 1200, 800, false );
	add_image_size( 'lv-thumb', 200, 140, true );
}, 20 );

/**
 * Alt automatico a partir do titulo do veiculo quando o anexo nao tem alt.
 */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	if ( ! empty( $attr['alt'] ) ) {
		return $attr;
	}
	$parent = (int) wp_get_post_parent_id( $attachment->ID );
	if ( $parent && 'veiculo' === get_post_type( $parent ) ) {
		$attr['alt'] = get_the_title( $parent );
	} elseif ( is_singular( 'veiculo' ) ) {
		$attr['alt'] = get_the_title();
	}
	return $attr;
}, 10, 2 );
