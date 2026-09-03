<?php
/**
 * Taxonomias do veiculo. Marca/carroceria/cambio/combustivel sao taxonomia
 * (e nao texto livre) para gerar pagina indexavel e filtro consistente.
 */

defined( 'ABSPATH' ) || exit;

function lv_taxonomias_config(): array {
	return [
		'marca' => [
			'singular'    => 'Marca',
			'plural'      => 'Marcas',
			'slug'        => 'marca',
			'hierarchical'=> false,
		],
		'carroceria' => [
			'singular'    => 'Carroceria',
			'plural'      => 'Carrocerias',
			'slug'        => 'carroceria',
			'hierarchical'=> false,
		],
		'cambio' => [
			'singular'    => 'Cambio',
			'plural'      => 'Cambios',
			'slug'        => 'cambio',
			'hierarchical'=> false,
		],
		'combustivel' => [
			'singular'    => 'Combustivel',
			'plural'      => 'Combustiveis',
			'slug'        => 'combustivel',
			'hierarchical'=> false,
		],
		'opcional' => [
			'singular'    => 'Opcional',
			'plural'      => 'Opcionais',
			'slug'        => 'opcional',
			'hierarchical'=> false,
		],
	];
}

function lv_registrar_taxonomias(): void {
	foreach ( lv_taxonomias_config() as $tax => $cfg ) {
		register_taxonomy(
			$tax,
			[ 'veiculo' ],
			[
				'labels' => [
					'name'          => $cfg['plural'],
					'singular_name' => $cfg['singular'],
					'search_items'  => 'Buscar ' . $cfg['plural'],
					'all_items'     => 'Todas as ' . $cfg['plural'],
					'edit_item'     => 'Editar ' . $cfg['singular'],
					'update_item'   => 'Atualizar ' . $cfg['singular'],
					'add_new_item'  => 'Adicionar ' . $cfg['singular'],
					'new_item_name' => 'Nome da nova ' . $cfg['singular'],
					'menu_name'     => $cfg['plural'],
					'not_found'     => 'Nenhum termo cadastrado',
				],
				'public'            => true,
				'hierarchical'      => $cfg['hierarchical'],
				'show_ui'           => true,
				'show_admin_column' => in_array( $tax, [ 'marca', 'carroceria' ], true ),
				'show_in_rest'      => true,
				'show_in_nav_menus' => true,
				'rewrite'           => [ 'slug' => $cfg['slug'], 'with_front' => false ],
			]
		);
	}
}
add_action( 'init', 'lv_registrar_taxonomias', 6 );

/**
 * Popula termos que sao iguais em toda loja. Roda na ativacao.
 */
function lv_seed_termos_padrao(): void {
	$padrao = [
		'carroceria'  => [ 'Hatch', 'Seda', 'SUV', 'Picape', 'Utilitario', 'Minivan', 'Cupe', 'Conversivel' ],
		'cambio'      => [ 'Manual', 'Automatico', 'CVT', 'Automatizado' ],
		'combustivel' => [ 'Flex', 'Gasolina', 'Diesel', 'Etanol', 'Hibrido', 'Eletrico', 'GNV' ],
		'opcional'    => [
			'Ar-condicionado', 'Direcao eletrica', 'Direcao hidraulica', 'Vidros eletricos',
			'Travas eletricas', 'Airbag', 'ABS', 'Multimidia', 'Camera de re', 'Sensor de re',
			'Piloto automatico', 'Bancos de couro', 'Rodas de liga leve', 'Farol de neblina',
			'Start/Stop', 'Controle de estabilidade',
		],
	];

	foreach ( $padrao as $tax => $termos ) {
		foreach ( $termos as $termo ) {
			if ( ! term_exists( $termo, $tax ) ) {
				wp_insert_term( $termo, $tax );
			}
		}
	}

	$marcas_json = LV_ESTOQUE_DIR . 'seeds/marcas.json';
	if ( file_exists( $marcas_json ) ) {
		$marcas = json_decode( (string) file_get_contents( $marcas_json ), true );
		if ( is_array( $marcas ) ) {
			foreach ( $marcas as $marca ) {
				if ( ! term_exists( $marca, 'marca' ) ) {
					wp_insert_term( $marca, 'marca' );
				}
			}
		}
	}
}
