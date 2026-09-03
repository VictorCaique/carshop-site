<?php
/**
 * Filtros da vitrine via query nativa + parametros GET.
 * Sem plugin de filtro: mais rapido, sem custo recorrente e URL indexavel.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Le um inteiro do GET, ignorando valor vazio ou negativo.
 */
function lv_get_int( string $chave ): ?int {
	if ( ! isset( $_GET[ $chave ] ) || '' === $_GET[ $chave ] ) {
		return null;
	}
	$valor = (int) preg_replace( '/\D/', '', (string) wp_unslash( $_GET[ $chave ] ) );
	return $valor > 0 ? $valor : null;
}

add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}

	$eh_vitrine = $q->is_post_type_archive( 'veiculo' )
		|| $q->is_tax( [ 'marca', 'carroceria', 'cambio', 'combustivel', 'opcional' ] );

	if ( ! $eh_vitrine ) {
		return;
	}

	$q->set( 'posts_per_page', 12 );
	$q->set( 'post_status', 'publish' );

	if ( '' !== lv_filtro( 'busca' ) ) {
		$q->set( 's', lv_filtro( 'busca' ) );
	}

	// ---------- meta_query ----------
	$meta = [ 'relation' => 'AND' ];

	// Vendido sai da vitrine, mas a URL do veiculo continua viva (SEO + lead).
	$meta[] = [
		'relation' => 'OR',
		[ 'key' => 'status_veiculo', 'value' => 'vendido', 'compare' => '!=' ],
		[ 'key' => 'status_veiculo', 'compare' => 'NOT EXISTS' ],
	];

	$preco_min = lv_get_int( 'preco_min' );
	if ( null !== $preco_min ) {
		$meta[] = [ 'key' => 'preco', 'value' => $preco_min, 'type' => 'NUMERIC', 'compare' => '>=' ];
	}

	$preco_max = lv_get_int( 'preco_max' );
	if ( null !== $preco_max ) {
		$meta[] = [ 'key' => 'preco', 'value' => $preco_max, 'type' => 'NUMERIC', 'compare' => '<=' ];
	}

	$ano_min = lv_get_int( 'ano_min' );
	if ( null !== $ano_min ) {
		$meta[] = [ 'key' => 'ano_modelo', 'value' => $ano_min, 'type' => 'NUMERIC', 'compare' => '>=' ];
	}

	$km_max = lv_get_int( 'km_max' );
	if ( null !== $km_max ) {
		$meta[] = [ 'key' => 'km', 'value' => $km_max, 'type' => 'NUMERIC', 'compare' => '<=' ];
	}

	if ( count( $meta ) > 1 ) {
		$q->set( 'meta_query', $meta );
	}

	// ---------- tax_query ----------
	$tax = [];
	foreach ( [ 'marca', 'carroceria', 'cambio', 'combustivel', 'opcional' ] as $t ) {
		// Numa pagina de taxonomia o proprio termo ja esta na query.
		if ( $q->is_tax( $t ) || empty( $_GET[ $t ] ) ) {
			continue;
		}
		$termos = array_filter( array_map( 'sanitize_title', (array) wp_unslash( $_GET[ $t ] ) ) );
		if ( $termos ) {
			$tax[] = [
				'taxonomy' => $t,
				'field'    => 'slug',
				'terms'    => $termos,
				'operator' => 'IN',
			];
		}
	}
	if ( $tax ) {
		$tax['relation'] = 'AND';
		$q->set( 'tax_query', $tax );
	}

	// ---------- ordenacao ----------
	switch ( lv_filtro( 'ordem' ) ) {
		case 'preco_asc':
			$q->set( 'meta_key', 'preco' );
			$q->set( 'orderby', 'meta_value_num' );
			$q->set( 'order', 'ASC' );
			break;

		case 'preco_desc':
			$q->set( 'meta_key', 'preco' );
			$q->set( 'orderby', 'meta_value_num' );
			$q->set( 'order', 'DESC' );
			break;

		case 'km_asc':
			$q->set( 'meta_key', 'km' );
			$q->set( 'orderby', 'meta_value_num' );
			$q->set( 'order', 'ASC' );
			break;

		case 'ano_desc':
			$q->set( 'meta_key', 'ano_modelo' );
			$q->set( 'orderby', 'meta_value_num' );
			$q->set( 'order', 'DESC' );
			break;

		default:
			$q->set( 'orderby', 'date' );
			$q->set( 'order', 'DESC' );
	}
} );

/**
 * Busca do site tambem enxerga veiculos.
 */
add_filter( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_search() && ! $q->get( 'post_type' ) ) {
		$q->set( 'post_type', [ 'post', 'page', 'veiculo' ] );
	}
	return $q;
} );

/**
 * Opcoes de ordenacao usadas pelo select do tema.
 */
function lv_opcoes_ordem(): array {
	return [
		''           => 'Mais recentes',
		'preco_asc'  => 'Menor preco',
		'preco_desc' => 'Maior preco',
		'km_asc'     => 'Menor KM',
		'ano_desc'   => 'Mais novo',
	];
}

/**
 * Veiculos relacionados: mesma marca primeiro, completando por faixa de preco.
 */
function lv_relacionados( int $post_id, int $qtd = 4 ): array {
	$marcas = wp_get_post_terms( $post_id, 'marca', [ 'fields' => 'ids' ] );

	$args = [
		'post_type'           => 'veiculo',
		'post_status'         => 'publish',
		'posts_per_page'      => $qtd,
		'post__not_in'        => [ $post_id ],
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'meta_query'          => [
			[
				'relation' => 'OR',
				[ 'key' => 'status_veiculo', 'value' => 'vendido', 'compare' => '!=' ],
				[ 'key' => 'status_veiculo', 'compare' => 'NOT EXISTS' ],
			],
		],
	];

	if ( $marcas && ! is_wp_error( $marcas ) ) {
		$args['tax_query'] = [
			[ 'taxonomy' => 'marca', 'field' => 'term_id', 'terms' => $marcas ],
		];
	}

	$posts = get_posts( $args );

	if ( count( $posts ) < $qtd ) {
		$preco = (float) lv_field( 'preco', $post_id, 0 );
		$excl  = array_merge( [ $post_id ], wp_list_pluck( $posts, 'ID' ) );

		$extra_args = [
			'post_type'      => 'veiculo',
			'post_status'    => 'publish',
			'posts_per_page' => $qtd - count( $posts ),
			'post__not_in'   => $excl,
			'no_found_rows'  => true,
		];

		if ( $preco > 0 ) {
			$extra_args['meta_query'] = [
				[
					'key'     => 'preco',
					'value'   => [ $preco * 0.7, $preco * 1.3 ],
					'type'    => 'NUMERIC',
					'compare' => 'BETWEEN',
				],
			];
		}

		$posts = array_merge( $posts, get_posts( $extra_args ) );
	}

	return $posts;
}
