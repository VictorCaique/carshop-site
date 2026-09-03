<?php
/**
 * Options page "Configuracoes do Site".
 * Este e o coracao da replicabilidade: tudo que muda de cliente para cliente
 * esta aqui, nada em arquivo do tema.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		[
			'page_title'      => 'Configuracoes do Site',
			'menu_title'      => 'Configuracoes do Site',
			'menu_slug'       => 'lv-configuracoes',
			'capability'      => 'manage_options',
			'position'        => 4,
			'icon_url'        => 'dashicons-admin-customizer',
			'redirect'        => false,
			'updated_message' => 'Configuracoes salvas.',
		]
	);
} );

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		[
			'key'      => 'group_lv_opcoes',
			'title'    => 'Configuracoes da Loja',
			'location' => [
				[
					[ 'param' => 'options_page', 'operator' => '==', 'value' => 'lv-configuracoes' ],
				],
			],
			'menu_order'      => 0,
			'label_placement' => 'top',
			'fields'          => [

				// ==================== IDENTIDADE ====================
				[ 'key' => 'field_lv_tab_identidade', 'label' => 'Identidade', 'name' => '', 'type' => 'tab', 'placement' => 'left' ],
				[
					'key'           => 'field_lv_logo',
					'label'         => 'Logo',
					'name'          => 'logo',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'instructions'  => 'PNG ou SVG com fundo transparente.',
					'wrapper'       => [ 'width' => '33' ],
				],
				[
					'key'           => 'field_lv_logo_rodape',
					'label'         => 'Logo do rodape (versao clara)',
					'name'          => 'logo_rodape',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'wrapper'       => [ 'width' => '33' ],
				],
				[
					'key'           => 'field_lv_favicon',
					'label'         => 'Favicon',
					'name'          => 'favicon',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
					'wrapper'       => [ 'width' => '34' ],
				],
				[
					'key'           => 'field_lv_cor_primaria',
					'label'         => 'Cor primaria',
					'name'          => 'cor_primaria',
					'type'          => 'color_picker',
					'default_value' => '#0B3D91',
					'wrapper'       => [ 'width' => '33' ],
				],
				[
					'key'           => 'field_lv_cor_secundaria',
					'label'         => 'Cor secundaria',
					'name'          => 'cor_secundaria',
					'type'          => 'color_picker',
					'default_value' => '#111827',
					'wrapper'       => [ 'width' => '33' ],
				],
				[
					'key'           => 'field_lv_cor_destaque',
					'label'         => 'Cor de destaque',
					'name'          => 'cor_destaque',
					'type'          => 'color_picker',
					'default_value' => '#F59E0B',
					'wrapper'       => [ 'width' => '34' ],
				],
				[
					'key'           => 'field_lv_fonte',
					'label'         => 'Preset de fonte',
					'name'          => 'fonte',
					'type'          => 'select',
					'choices'       => [
						'moderno' => 'Moderno - Inter / Inter (padrao, seguro)',
						'robusto' => 'Robusto - Barlow Condensed / Inter (picape, 4x4, populares)',
						'premium' => 'Premium - Playfair Display / Source Sans 3 (importados, alto padrao)',
					],
					'default_value' => 'moderno',
					'return_format' => 'value',
				],

				// ==================== CONTATO ====================
				[ 'key' => 'field_lv_tab_contato', 'label' => 'Contato', 'name' => '', 'type' => 'tab', 'placement' => 'left' ],
				[ 'key' => 'field_lv_nome_loja', 'label' => 'Nome da loja', 'name' => 'nome_loja', 'type' => 'text', 'wrapper' => [ 'width' => '50' ] ],
				[ 'key' => 'field_lv_slogan', 'label' => 'Slogan', 'name' => 'slogan', 'type' => 'text', 'wrapper' => [ 'width' => '50' ] ],
				[ 'key' => 'field_lv_cnpj', 'label' => 'CNPJ', 'name' => 'cnpj', 'type' => 'text', 'wrapper' => [ 'width' => '33' ] ],
				[
					'key'          => 'field_lv_ano_fundacao',
					'label'        => 'Ano de fundacao',
					'name'         => 'ano_fundacao',
					'type'         => 'number',
					'instructions' => 'Usado no selo "X anos de mercado".',
					'wrapper'      => [ 'width' => '33' ],
				],
				[ 'key' => 'field_lv_telefone_fixo', 'label' => 'Telefone', 'name' => 'telefone_fixo', 'type' => 'text', 'wrapper' => [ 'width' => '34' ] ],
				[ 'key' => 'field_lv_email', 'label' => 'E-mail', 'name' => 'email', 'type' => 'email', 'wrapper' => [ 'width' => '50' ] ],
				[
					'key'          => 'field_lv_endereco',
					'label'        => 'Endereco',
					'name'         => 'endereco',
					'type'         => 'text',
					'instructions' => 'Rua, numero e bairro.',
					'wrapper'      => [ 'width' => '50' ],
				],
				[ 'key' => 'field_lv_cidade', 'label' => 'Cidade', 'name' => 'cidade', 'type' => 'text', 'wrapper' => [ 'width' => '33' ] ],
				[ 'key' => 'field_lv_estado', 'label' => 'Estado (UF)', 'name' => 'estado', 'type' => 'text', 'maxlength' => 2, 'wrapper' => [ 'width' => '33' ] ],
				[ 'key' => 'field_lv_cep', 'label' => 'CEP', 'name' => 'cep', 'type' => 'text', 'wrapper' => [ 'width' => '34' ] ],
				[
					'key'          => 'field_lv_google_maps_embed',
					'label'        => 'Google Maps (embed)',
					'name'         => 'google_maps_embed',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => 'Cole o iframe do Google Maps (Compartilhar > Incorporar um mapa).',
				],
				[
					'key'          => 'field_lv_horario_funcionamento',
					'label'        => 'Horario de funcionamento',
					'name'         => 'horario_funcionamento',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Adicionar horario',
					'sub_fields'   => [
						[ 'key' => 'field_lv_hf_dia', 'label' => 'Dia', 'name' => 'dia', 'type' => 'text', 'placeholder' => 'Segunda a sexta' ],
						[ 'key' => 'field_lv_hf_horario', 'label' => 'Horario', 'name' => 'horario', 'type' => 'text', 'placeholder' => '08h as 18h' ],
					],
				],
				[ 'key' => 'field_lv_instagram', 'label' => 'Instagram (URL)', 'name' => 'instagram', 'type' => 'url', 'wrapper' => [ 'width' => '50' ] ],
				[ 'key' => 'field_lv_facebook', 'label' => 'Facebook (URL)', 'name' => 'facebook', 'type' => 'url', 'wrapper' => [ 'width' => '50' ] ],

				// ==================== VENDEDORES ====================
				[ 'key' => 'field_lv_tab_vendedores', 'label' => 'Vendedores', 'name' => '', 'type' => 'tab', 'placement' => 'left' ],
				[
					'key'          => 'field_lv_vendedores',
					'label'        => 'Vendedores',
					'name'         => 'vendedores',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar vendedor',
					'instructions' => 'O primeiro da lista e o padrao para veiculos sem vendedor definido.',
					'sub_fields'   => [
						[ 'key' => 'field_lv_v_nome', 'label' => 'Nome', 'name' => 'nome', 'type' => 'text', 'required' => 1, 'wrapper' => [ 'width' => '35' ] ],
						[
							'key'          => 'field_lv_v_whatsapp',
							'label'        => 'WhatsApp',
							'name'         => 'whatsapp',
							'type'         => 'text',
							'required'     => 1,
							'instructions' => 'So digitos, com pais e DDD. Ex: 5511987654321',
							'placeholder'  => '5511987654321',
							'wrapper'      => [ 'width' => '35' ],
						],
						[ 'key' => 'field_lv_v_cargo', 'label' => 'Cargo', 'name' => 'cargo', 'type' => 'text', 'wrapper' => [ 'width' => '30' ] ],
						[ 'key' => 'field_lv_v_foto', 'label' => 'Foto', 'name' => 'foto', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'thumbnail' ],
					],
				],

				// ==================== CONTEUDO ====================
				[ 'key' => 'field_lv_tab_conteudo', 'label' => 'Conteudo', 'name' => '', 'type' => 'tab', 'placement' => 'left' ],
				[ 'key' => 'field_lv_hero_titulo', 'label' => 'Hero - titulo', 'name' => 'hero_titulo', 'type' => 'text', 'wrapper' => [ 'width' => '50' ] ],
				[ 'key' => 'field_lv_hero_subtitulo', 'label' => 'Hero - subtitulo', 'name' => 'hero_subtitulo', 'type' => 'text', 'wrapper' => [ 'width' => '50' ] ],
				[
					'key'           => 'field_lv_hero_imagem',
					'label'         => 'Hero - imagem de fundo',
					'name'          => 'hero_imagem',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'instructions'  => 'A fachada da loja ou um carro do estoque. Minimo 1920px de largura.',
				],
				[ 'key' => 'field_lv_sobre_titulo', 'label' => 'Sobre - titulo', 'name' => 'sobre_titulo', 'type' => 'text' ],
				[ 'key' => 'field_lv_sobre_texto', 'label' => 'Sobre - texto', 'name' => 'sobre_texto', 'type' => 'wysiwyg', 'media_upload' => 0, 'toolbar' => 'basic' ],
				[ 'key' => 'field_lv_sobre_imagem', 'label' => 'Sobre - imagem', 'name' => 'sobre_imagem', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ],
				[
					'key'          => 'field_lv_diferenciais',
					'label'        => 'Diferenciais',
					'name'         => 'diferenciais',
					'type'         => 'repeater',
					'layout'       => 'table',
					'max'          => 4,
					'button_label' => 'Adicionar diferencial',
					'sub_fields'   => [
						[
							'key'           => 'field_lv_d_icone',
							'label'         => 'Icone',
							'name'          => 'icone',
							'type'          => 'select',
							'choices'       => [
								'shield'   => 'Escudo (garantia)',
								'card'     => 'Cartao (financiamento)',
								'exchange' => 'Troca',
								'check'    => 'Check (procedencia)',
								'wrench'   => 'Chave (revisao)',
								'clock'    => 'Relogio (agilidade)',
								'star'     => 'Estrela',
								'car'      => 'Carro',
							],
							'default_value' => 'check',
						],
						[ 'key' => 'field_lv_d_titulo', 'label' => 'Titulo', 'name' => 'titulo', 'type' => 'text' ],
						[ 'key' => 'field_lv_d_texto', 'label' => 'Texto', 'name' => 'texto', 'type' => 'textarea', 'rows' => 2 ],
					],
				],
				[
					'key'          => 'field_lv_depoimentos',
					'label'        => 'Depoimentos',
					'name'         => 'depoimentos',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar depoimento',
					'sub_fields'   => [
						[ 'key' => 'field_lv_dep_nome', 'label' => 'Nome', 'name' => 'nome', 'type' => 'text', 'wrapper' => [ 'width' => '50' ] ],
						[
							'key'           => 'field_lv_dep_nota',
							'label'         => 'Nota (1 a 5)',
							'name'          => 'nota',
							'type'          => 'number',
							'min'           => 1,
							'max'           => 5,
							'default_value' => 5,
							'wrapper'       => [ 'width' => '50' ],
						],
						[ 'key' => 'field_lv_dep_texto', 'label' => 'Depoimento', 'name' => 'texto', 'type' => 'textarea', 'rows' => 3 ],
					],
				],

				// ==================== INTEGRACOES ====================
				[ 'key' => 'field_lv_tab_integracoes', 'label' => 'Integracoes', 'name' => '', 'type' => 'tab', 'placement' => 'left' ],
				[
					'key'         => 'field_lv_google_analytics_id',
					'label'       => 'Google Analytics (ID)',
					'name'        => 'google_analytics_id',
					'type'        => 'text',
					'placeholder' => 'G-XXXXXXXXXX',
					'wrapper'     => [ 'width' => '33' ],
				],
				[ 'key' => 'field_lv_meta_pixel_id', 'label' => 'Meta Pixel (ID)', 'name' => 'meta_pixel_id', 'type' => 'text', 'wrapper' => [ 'width' => '33' ] ],
				[
					'key'     => 'field_lv_google_site_verification',
					'label'   => 'Google Site Verification',
					'name'    => 'google_site_verification',
					'type'    => 'text',
					'wrapper' => [ 'width' => '34' ],
				],
				[
					'key'           => 'field_lv_whatsapp_flutuante',
					'label'         => 'Botao flutuante de WhatsApp',
					'name'          => 'whatsapp_flutuante',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				],
			],
		]
	);
}, 20 );
