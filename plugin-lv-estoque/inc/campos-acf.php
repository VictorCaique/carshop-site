<?php
/**
 * Campos do veiculo declarados em PHP (acf_add_local_field_group).
 * Declarado em codigo = nao precisa configurar campo a campo em cada instalacao.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$ano_atual = (int) current_time( 'Y' ) + 1;

	acf_add_local_field_group(
		[
			'key'      => 'group_lv_veiculo',
			'title'    => 'Dados do Veiculo',
			'location' => [
				[
					[ 'param' => 'post_type', 'operator' => '==', 'value' => 'veiculo' ],
				],
			],
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'active'                => true,
			'show_in_rest'          => true,
			'hide_on_screen'        => [ 'custom_fields', 'discussion', 'comments', 'trackbacks' ],
			'fields'                => [

				// ---------------- Aba: Preco ----------------
				[
					'key'       => 'field_lv_tab_preco',
					'label'     => 'Preco',
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
				],
				[
					'key'           => 'field_lv_preco',
					'label'         => 'Preco',
					'name'          => 'preco',
					'type'          => 'number',
					'required'      => 1,
					'instructions'  => 'Em reais, sem formatacao. Ex: 78900',
					'min'           => 0,
					'step'          => 100,
					'prepend'       => 'R$',
					'wrapper'       => [ 'width' => '33' ],
				],
				[
					'key'          => 'field_lv_preco_promocional',
					'label'        => 'Preco promocional',
					'name'         => 'preco_promocional',
					'type'         => 'number',
					'instructions' => 'Se preenchido, o preco original aparece riscado.',
					'min'          => 0,
					'step'         => 100,
					'prepend'      => 'R$',
					'wrapper'      => [ 'width' => '33' ],
				],
				[
					'key'           => 'field_lv_mostrar_preco',
					'label'         => 'Mostrar preco no site',
					'name'          => 'mostrar_preco',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
					'instructions'  => 'Se desligado, o site exibe "Consulte".',
					'wrapper'       => [ 'width' => '34' ],
				],

				// ---------------- Aba: Ficha tecnica ----------------
				[
					'key'   => 'field_lv_tab_ficha',
					'label' => 'Ficha tecnica',
					'name'  => '',
					'type'  => 'tab',
				],
				[
					'key'      => 'field_lv_ano_fabricacao',
					'label'    => 'Ano de fabricacao',
					'name'     => 'ano_fabricacao',
					'type'     => 'number',
					'required' => 1,
					'min'      => 1900,
					'max'      => $ano_atual,
					'wrapper'  => [ 'width' => '25' ],
				],
				[
					'key'      => 'field_lv_ano_modelo',
					'label'    => 'Ano do modelo',
					'name'     => 'ano_modelo',
					'type'     => 'number',
					'required' => 1,
					'min'      => 1900,
					'max'      => $ano_atual,
					'wrapper'  => [ 'width' => '25' ],
				],
				[
					'key'      => 'field_lv_km',
					'label'    => 'Quilometragem',
					'name'     => 'km',
					'type'     => 'number',
					'required' => 1,
					'min'      => 0,
					'append'   => 'km',
					'wrapper'  => [ 'width' => '25' ],
				],
				[
					'key'      => 'field_lv_cor',
					'label'    => 'Cor',
					'name'     => 'cor',
					'type'     => 'text',
					'required' => 1,
					'wrapper'  => [ 'width' => '25' ],
				],
				[
					'key'           => 'field_lv_portas',
					'label'         => 'Portas',
					'name'          => 'portas',
					'type'          => 'select',
					'choices'       => [ '2' => '2', '4' => '4' ],
					'allow_null'    => 1,
					'return_format' => 'value',
					'wrapper'       => [ 'width' => '25' ],
				],
				[
					'key'          => 'field_lv_motor',
					'label'        => 'Motor',
					'name'         => 'motor',
					'type'         => 'text',
					'instructions' => 'Ex: 1.0 Turbo 12V',
					'wrapper'      => [ 'width' => '25' ],
				],
				[
					'key'     => 'field_lv_final_placa',
					'label'   => 'Final da placa',
					'name'    => 'final_placa',
					'type'    => 'number',
					'min'     => 0,
					'max'     => 9,
					'wrapper' => [ 'width' => '25' ],
				],
				[
					'key'     => 'field_lv_potencia',
					'label'   => 'Potencia (cv)',
					'name'    => 'potencia',
					'type'    => 'number',
					'min'     => 0,
					'wrapper' => [ 'width' => '25' ],
				],
				[
					'key'     => 'field_lv_unico_dono',
					'label'   => 'Unico dono',
					'name'    => 'unico_dono',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'     => 'field_lv_ipva_pago',
					'label'   => 'IPVA pago',
					'name'    => 'ipva_pago',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'     => 'field_lv_aceita_troca',
					'label'   => 'Aceita troca',
					'name'    => 'aceita_troca',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '34' ],
				],

				// ---------------- Aba: Fotos ----------------
				[
					'key'   => 'field_lv_tab_fotos',
					'label' => 'Fotos',
					'name'  => '',
					'type'  => 'tab',
				],
				[
					'key'           => 'field_lv_galeria',
					'label'         => 'Galeria',
					'name'          => 'galeria',
					'type'          => 'gallery',
					'required'      => 1,
					'instructions'  => 'Minimo 4 fotos, ideal de 8 a 12. Sugestao de ordem: frente 3/4, traseira 3/4, lateral, interior/painel, bancos, porta-malas, rodas, motor.',
					'return_format' => 'id',
					'min'           => 4,
					'insert'        => 'append',
					'library'       => 'all',
				],

				// ---------------- Aba: Venda ----------------
				[
					'key'   => 'field_lv_tab_venda',
					'label' => 'Venda',
					'name'  => '',
					'type'  => 'tab',
				],
				[
					'key'           => 'field_lv_status_veiculo',
					'label'         => 'Status',
					'name'          => 'status_veiculo',
					'type'          => 'select',
					'required'      => 1,
					'choices'       => [
						'disponivel' => 'Disponivel',
						'reservado'  => 'Reservado',
						'vendido'    => 'Vendido',
					],
					'default_value' => 'disponivel',
					'return_format' => 'value',
					'instructions'  => 'Veiculo vendido sai da vitrine mas mantem a URL com o selo VENDIDO.',
					'wrapper'       => [ 'width' => '33' ],
				],
				[
					'key'          => 'field_lv_destaque',
					'label'        => 'Destaque na home',
					'name'         => 'destaque',
					'type'         => 'true_false',
					'ui'           => 1,
					'instructions' => 'Aparece na secao de destaques da home.',
					'wrapper'      => [ 'width' => '33' ],
				],
				[
					'key'           => 'field_lv_vendedor',
					'label'         => 'Vendedor responsavel',
					'name'          => 'vendedor',
					'type'          => 'select',
					'choices'       => [],
					'allow_null'    => 1,
					'return_format' => 'value',
					'instructions'  => 'Define para qual WhatsApp o botao aponta. Vazio = primeiro vendedor das configuracoes.',
					'wrapper'       => [ 'width' => '34' ],
				],
			],
		]
	);
}, 10 );

/**
 * Alimenta o select de vendedor com o repeater das configuracoes da loja.
 * O valor guardado e o numero de WhatsApp, para o link nao quebrar se o
 * cliente renomear o vendedor depois.
 */
add_filter( 'acf/load_field/key=field_lv_vendedor', function ( $field ) {
	$field['choices'] = [];

	$vendedores = function_exists( 'get_field' ) ? get_field( 'vendedores', 'option' ) : [];
	if ( ! is_array( $vendedores ) ) {
		return $field;
	}

	foreach ( $vendedores as $v ) {
		$numero = preg_replace( '/\D/', '', (string) ( $v['whatsapp'] ?? '' ) );
		$nome   = trim( (string) ( $v['nome'] ?? '' ) );
		if ( $numero ) {
			$field['choices'][ $numero ] = $nome ? $nome : $numero;
		}
	}

	return $field;
} );

/**
 * Sem ACF, ainda assim salvamos o basico para o tema nao quebrar:
 * define status padrao ao publicar um veiculo.
 */
add_action( 'save_post_veiculo', function ( $post_id, $post, $update ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( '' === get_post_meta( $post_id, 'status_veiculo', true ) ) {
		update_post_meta( $post_id, 'status_veiculo', 'disponivel' );
	}
}, 20, 3 );
