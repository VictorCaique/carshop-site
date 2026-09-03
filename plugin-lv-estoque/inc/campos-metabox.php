<?php
/**
 * Campos do veiculo declarados em PHP via Meta Box (versao gratuita).
 *
 * Declarado em codigo = nao precisa configurar campo a campo em cada instalacao.
 * Usa apenas o core gratuito do Meta Box (wordpress.org/plugins/meta-box):
 * nada de tab/columns, que dependem de extensoes.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Choices do select de vendedor, vindas das Configuracoes do Site.
 * O valor guardado e o numero de WhatsApp, para o link nao quebrar se o
 * cliente renomear o vendedor depois.
 */
function lv_choices_vendedores(): array {
	$choices    = [];
	$vendedores = lv_option( 'vendedores', [] );

	foreach ( (array) $vendedores as $v ) {
		$numero = preg_replace( '/\D/', '', (string) ( $v['whatsapp'] ?? '' ) );
		$nome   = trim( (string) ( $v['nome'] ?? '' ) );

		if ( $numero ) {
			$choices[ $numero ] = $nome ?: $numero;
		}
	}

	return $choices;
}

add_filter( 'rwmb_meta_boxes', function ( array $meta_boxes ): array {
	$ano_max = (int) current_time( 'Y' ) + 1;

	$meta_boxes[] = [
		'id'         => 'lv_veiculo_preco',
		'title'      => 'Preco',
		'post_types' => [ 'veiculo' ],
		'context'    => 'normal',
		'priority'   => 'high',
		'fields'     => [
			[
				'id'       => 'preco',
				'name'     => 'Preco',
				'type'     => 'number',
				'required' => true,
				'desc'     => 'Em reais, sem formatacao. Ex: 78900',
				'min'      => 0,
				'step'     => 100,
				'prepend'  => 'R$',
			],
			[
				'id'      => 'preco_promocional',
				'name'    => 'Preco promocional',
				'type'    => 'number',
				'desc'    => 'Se preenchido, o preco original aparece riscado no site.',
				'min'     => 0,
				'step'    => 100,
				'prepend' => 'R$',
			],
			[
				'id'   => 'mostrar_preco',
				'name' => 'Mostrar preco no site',
				'type' => 'checkbox',
				'std'  => 1,
				'desc' => 'Desligado, o site exibe "Consulte".',
			],
		],
	];

	$meta_boxes[] = [
		'id'         => 'lv_veiculo_ficha',
		'title'      => 'Ficha tecnica',
		'post_types' => [ 'veiculo' ],
		'context'    => 'normal',
		'priority'   => 'high',
		'fields'     => [
			[
				'id'       => 'ano_fabricacao',
				'name'     => 'Ano de fabricacao',
				'type'     => 'number',
				'required' => true,
				'min'      => 1900,
				'max'      => $ano_max,
			],
			[
				'id'       => 'ano_modelo',
				'name'     => 'Ano do modelo',
				'type'     => 'number',
				'required' => true,
				'min'      => 1900,
				'max'      => $ano_max,
			],
			[
				'id'       => 'km',
				'name'     => 'Quilometragem',
				'type'     => 'number',
				'required' => true,
				'min'      => 0,
				'append'   => 'km',
			],
			[
				'id'       => 'cor',
				'name'     => 'Cor',
				'type'     => 'text',
				'required' => true,
			],
			[
				'id'          => 'portas',
				'name'        => 'Portas',
				'type'        => 'select',
				'options'     => [ '2' => '2', '4' => '4' ],
				'placeholder' => 'Selecione',
			],
			[
				'id'   => 'motor',
				'name' => 'Motor',
				'type' => 'text',
				'desc' => 'Ex: 1.0 Turbo 12V',
			],
			[
				'id'     => 'potencia',
				'name'   => 'Potencia',
				'type'   => 'number',
				'min'    => 0,
				'append' => 'cv',
			],
			[
				'id'   => 'final_placa',
				'name' => 'Final da placa',
				'type' => 'number',
				'min'  => 0,
				'max'  => 9,
			],
			[
				'id'   => 'unico_dono',
				'name' => 'Unico dono',
				'type' => 'checkbox',
			],
			[
				'id'   => 'ipva_pago',
				'name' => 'IPVA pago',
				'type' => 'checkbox',
			],
			[
				'id'   => 'aceita_troca',
				'name' => 'Aceita troca',
				'type' => 'checkbox',
			],
		],
	];

	$meta_boxes[] = [
		'id'         => 'lv_veiculo_fotos',
		'title'      => 'Fotos',
		'post_types' => [ 'veiculo' ],
		'context'    => 'normal',
		'priority'   => 'high',
		'fields'     => [
			[
				'id'               => 'galeria',
				'name'             => 'Galeria',
				'type'             => 'image_advanced',
				'desc'             => 'Minimo 4 fotos, ideal de 8 a 12. Ordem sugerida: frente 3/4, traseira 3/4, '
					. 'lateral, interior/painel, bancos, porta-malas, rodas, motor. '
					. 'A primeira foto da galeria e usada como capa quando nao houver imagem destacada.',
				'max_file_uploads' => 20,
				'force_delete'     => false,
				'image_size'       => 'lv-thumb',
			],
		],
	];

	$meta_boxes[] = [
		'id'         => 'lv_veiculo_venda',
		'title'      => 'Venda',
		'post_types' => [ 'veiculo' ],
		'context'    => 'side',
		'priority'   => 'default',
		'fields'     => [
			[
				'id'      => 'status_veiculo',
				'name'    => 'Status',
				'type'    => 'select',
				'options' => [
					'disponivel' => 'Disponivel',
					'reservado'  => 'Reservado',
					'vendido'    => 'Vendido',
				],
				'std'     => 'disponivel',
				'desc'    => 'Vendido sai da vitrine mas mantem a URL com o selo.',
			],
			[
				'id'   => 'destaque',
				'name' => 'Destaque na home',
				'type' => 'checkbox',
				'desc' => 'Aparece na secao de destaques.',
			],
			[
				'id'          => 'vendedor',
				'name'        => 'Vendedor responsavel',
				'type'        => 'select',
				'options'     => lv_choices_vendedores(),
				'placeholder' => 'Primeiro vendedor da lista',
				'desc'        => 'Define para qual WhatsApp o botao aponta.',
			],
		],
	];

	return $meta_boxes;
} );

/**
 * Status padrao ao publicar, para o tema nunca ler um status vazio.
 */
add_action( 'save_post_veiculo', function ( int $post_id ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( '' === get_post_meta( $post_id, 'status_veiculo', true ) ) {
		update_post_meta( $post_id, 'status_veiculo', 'disponivel' );
	}
}, 20 );

/**
 * Sem imagem destacada, promove a primeira foto da galeria a capa.
 * O lojista sobe as fotos e pronto - nao precisa lembrar de dois passos.
 */
add_action( 'save_post_veiculo', function ( int $post_id ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( has_post_thumbnail( $post_id ) ) {
		return;
	}

	$galeria = get_post_meta( $post_id, 'galeria', false );
	$primeira = (int) ( is_array( $galeria ) ? ( $galeria[0] ?? 0 ) : 0 );

	if ( $primeira && wp_attachment_is_image( $primeira ) ) {
		set_post_thumbnail( $post_id, $primeira );
	}
}, 30 );
