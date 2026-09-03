<?php
/**
 * Lista de veiculos no admin: o dono da loja precisa bater o olho e ver
 * foto, preco, KM e status sem abrir cada carro.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'manage_veiculo_posts_columns', function ( array $cols ): array {
	$novo = [];

	foreach ( $cols as $chave => $label ) {
		if ( 'title' === $chave ) {
			$novo['lv_foto'] = 'Foto';
		}
		$novo[ $chave ] = $label;

		if ( 'title' === $chave ) {
			$novo['lv_preco']  = 'Preco';
			$novo['lv_ano']    = 'Ano';
			$novo['lv_km']     = 'KM';
			$novo['lv_status'] = 'Status';
		}
	}

	unset( $novo['comments'] );

	return $novo;
} );

add_action( 'manage_veiculo_posts_custom_column', function ( string $col, int $post_id ): void {
	switch ( $col ) {
		case 'lv_foto':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, [ 60, 40 ], [ 'style' => 'border-radius:4px;object-fit:cover;' ] );
			} else {
				echo '<span style="color:#b32d2e;">sem foto</span>';
			}
			break;

		case 'lv_preco':
			echo esc_html( lv_preco( $post_id ) );
			break;

		case 'lv_ano':
			echo esc_html( lv_ano( $post_id ) );
			break;

		case 'lv_km':
			echo esc_html( lv_km( $post_id ) );
			break;

		case 'lv_status':
			$status = lv_status( $post_id );
			$cores  = [
				'disponivel' => '#0a7c2f',
				'reservado'  => '#b26b00',
				'vendido'    => '#b32d2e',
			];
			printf(
				'<span style="display:inline-block;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:600;color:#fff;background:%s">%s</span>',
				esc_attr( $cores[ $status ] ?? '#555' ),
				esc_html( lv_status_label( $post_id ) )
			);
			if ( lv_field( 'destaque', $post_id ) ) {
				echo ' <span title="Destaque na home" style="color:#F59E0B">&#9733;</span>';
			}
			break;
	}
}, 10, 2 );

/**
 * Ordenacao por preco e KM na listagem do admin.
 */
add_filter( 'manage_edit-veiculo_sortable_columns', function ( array $cols ): array {
	$cols['lv_preco'] = 'lv_preco';
	$cols['lv_km']    = 'lv_km';
	$cols['lv_ano']   = 'lv_ano';
	return $cols;
} );

add_action( 'pre_get_posts', function ( $q ): void {
	if ( ! is_admin() || ! $q->is_main_query() ) {
		return;
	}

	$mapa = [
		'lv_preco' => 'preco',
		'lv_km'    => 'km',
		'lv_ano'   => 'ano_modelo',
	];

	$orderby = $q->get( 'orderby' );
	if ( isset( $mapa[ $orderby ] ) ) {
		$q->set( 'meta_key', $mapa[ $orderby ] );
		$q->set( 'orderby', 'meta_value_num' );
	}
} );

/**
 * Filtro por status na listagem do admin.
 */
add_action( 'restrict_manage_posts', function ( string $post_type ): void {
	if ( 'veiculo' !== $post_type ) {
		return;
	}

	$atual = isset( $_GET['lv_status'] ) ? sanitize_key( wp_unslash( $_GET['lv_status'] ) ) : '';

	echo '<select name="lv_status"><option value="">Todos os status</option>';
	foreach ( [ 'disponivel' => 'Disponivel', 'reservado' => 'Reservado', 'vendido' => 'Vendido' ] as $valor => $label ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $valor ),
			selected( $atual, $valor, false ),
			esc_html( $label )
		);
	}
	echo '</select>';
} );

add_action( 'pre_get_posts', function ( $q ): void {
	if ( ! is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( 'veiculo' !== $q->get( 'post_type' ) || empty( $_GET['lv_status'] ) ) {
		return;
	}

	$q->set(
		'meta_query',
		[
			[
				'key'   => 'status_veiculo',
				'value' => sanitize_key( wp_unslash( $_GET['lv_status'] ) ),
			],
		]
	);
} );

/**
 * Aviso na edicao quando o veiculo esta abaixo do minimo de fotos.
 */
add_action( 'admin_notices', function (): void {
	$tela = get_current_screen();
	if ( ! $tela || 'veiculo' !== $tela->post_type || 'post' !== $tela->base ) {
		return;
	}

	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
	if ( ! $post_id ) {
		return;
	}

	$total = count( lv_galeria_ids( $post_id ) );
	if ( $total > 0 && $total < 4 ) {
		printf(
			'<div class="notice notice-warning"><p><strong>Poucas fotos:</strong> este veiculo tem %d. '
			. 'O minimo recomendado e 4 (frente 3/4, traseira 3/4, interior/painel, porta-malas).</p></div>',
			(int) $total
		);
	}
} );
