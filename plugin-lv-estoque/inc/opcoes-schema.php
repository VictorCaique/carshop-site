<?php
/**
 * Esquema das Configuracoes do Site.
 *
 * Este arquivo e a fonte unica da verdade: a tela de opcoes, a sanitizacao e
 * os valores padrao saem todos daqui. Campo novo = uma entrada neste array.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tipos aceitos:
 *   text · textarea · wysiwyg · number · email · url · tel
 *   color · select · checkbox · image · repeater
 *
 * Chaves de cada campo:
 *   label (obrigatorio) · desc · std · opcoes (select) · sub (repeater)
 *   largura: 'meia' | 'terco' | 'cheia' (default)
 */
function lv_opcoes_schema(): array {
	return [

		'identidade' => [
			'titulo' => 'Identidade',
			'campos' => [
				'logo' => [
					'label'   => 'Logo',
					'type'    => 'image',
					'desc'    => 'PNG ou SVG com fundo transparente.',
					'largura' => 'terco',
				],
				'logo_rodape' => [
					'label'   => 'Logo do rodape (versao clara)',
					'type'    => 'image',
					'desc'    => 'O rodape tem fundo escuro.',
					'largura' => 'terco',
				],
				'favicon' => [
					'label'   => 'Favicon',
					'type'    => 'image',
					'desc'    => 'Quadrado, 512x512.',
					'largura' => 'terco',
				],
			],
		],

		'aparencia' => [
			'titulo' => 'Aparencia',
			'campos' => [
				'cor_primaria' => [
					'label'   => 'Cor primaria',
					'type'    => 'color',
					'std'     => '#0B3D91',
					'desc'    => 'Links, botoes principais e destaques de navegacao.',
					'largura' => 'terco',
				],
				'cor_secundaria' => [
					'label'   => 'Cor secundaria',
					'type'    => 'color',
					'std'     => '#111827',
					'desc'    => 'Titulos, rodape e o fundo do hero.',
					'largura' => 'terco',
				],
				'cor_destaque' => [
					'label'   => 'Cor de destaque',
					'type'    => 'color',
					'std'     => '#F59E0B',
					'desc'    => 'Botao de acao, selos e estrelas.',
					'largura' => 'terco',
				],
				'fonte' => [
					'label'  => 'Preset de fonte',
					'type'   => 'select',
					'std'    => 'moderno',
					'opcoes' => [
						'moderno' => 'Moderno - Inter / Inter (padrao, seguro)',
						'robusto' => 'Robusto - Barlow Condensed / Inter (picape, 4x4, populares)',
						'premium' => 'Premium - Playfair Display / Source Sans 3 (importados, alto padrao)',
					],
				],
				'esquema_cores' => [
					'label'   => 'Fundo do site',
					'type'    => 'select',
					'std'     => 'claro',
					'largura' => 'meia',
					'desc'    => 'No modo automatico o site acompanha a preferencia do celular do visitante.',
					'opcoes'  => [
						'claro'      => 'Claro (padrao)',
						'escuro'     => 'Escuro',
						'automatico' => 'Automatico - segue o aparelho do visitante',
					],
				],
				'cor_fundo_escuro' => [
					'label'   => 'Cor base do modo escuro',
					'type'    => 'color',
					'std'     => '#0F1115',
					'desc'    => 'Os cinzas, as bordas e o rodape do modo escuro sao derivados dela.',
					'largura' => 'meia',
				],
				'alternador_tema' => [
					'label' => 'Botao de claro/escuro no menu',
					'type'  => 'checkbox',
					'std'   => 0,
					'desc'  => 'Deixa o visitante trocar. A escolha fica salva no navegador dele.',
				],
				'raio_cantos' => [
					'label'   => 'Cantos',
					'type'    => 'select',
					'std'     => 'suave',
					'largura' => 'terco',
					'opcoes'  => [
						'reto'        => 'Reto - sem arredondamento',
						'suave'       => 'Suave (padrao)',
						'arredondado' => 'Arredondado',
					],
				],
				'sombra_cards' => [
					'label'   => 'Sombra dos cards',
					'type'    => 'select',
					'std'     => 'suave',
					'largura' => 'terco',
					'opcoes'  => [
						'nenhuma'   => 'Nenhuma - visual plano',
						'suave'     => 'Suave (padrao)',
						'destacada' => 'Destacada',
					],
				],
				'largura_conteudo' => [
					'label'   => 'Largura do conteudo',
					'type'    => 'select',
					'std'     => 'padrao',
					'largura' => 'terco',
					'opcoes'  => [
						'compacto' => 'Compacta - 1080px',
						'padrao'   => 'Padrao - 1200px',
						'amplo'    => 'Ampla - 1360px',
					],
				],
				'hero_escurecer' => [
					'label'   => 'Escurecer a imagem do hero',
					'type'    => 'select',
					'std'     => 'medio',
					'largura' => 'meia',
					'desc'    => 'Aumente se o titulo estiver dificil de ler sobre a foto.',
					'opcoes'  => [
						'leve'  => 'Leve - foto mais visivel',
						'medio' => 'Medio (padrao)',
						'forte' => 'Forte - texto mais legivel',
					],
				],
			],
		],

		'contato' => [
			'titulo' => 'Contato',
			'campos' => [
				'nome_loja'    => [ 'label' => 'Nome da loja', 'type' => 'text', 'largura' => 'meia' ],
				'slogan'       => [ 'label' => 'Slogan', 'type' => 'text', 'largura' => 'meia' ],
				'cnpj'         => [ 'label' => 'CNPJ', 'type' => 'text', 'largura' => 'terco' ],
				'ano_fundacao' => [
					'label'   => 'Ano de fundacao',
					'type'    => 'number',
					'desc'    => 'Usado no selo "X anos de mercado".',
					'largura' => 'terco',
				],
				'telefone_fixo' => [ 'label' => 'Telefone', 'type' => 'tel', 'largura' => 'terco' ],
				'email'         => [ 'label' => 'E-mail', 'type' => 'email', 'largura' => 'meia' ],
				'endereco'      => [
					'label'   => 'Endereco',
					'type'    => 'text',
					'desc'    => 'Rua, numero e bairro.',
					'largura' => 'meia',
				],
				'cidade' => [ 'label' => 'Cidade', 'type' => 'text', 'largura' => 'terco' ],
				'estado' => [ 'label' => 'Estado (UF)', 'type' => 'text', 'largura' => 'terco' ],
				'cep'    => [ 'label' => 'CEP', 'type' => 'text', 'largura' => 'terco' ],
				'google_maps_embed' => [
					'label' => 'Google Maps (embed)',
					'type'  => 'textarea',
					'desc'  => 'Cole o iframe do Google Maps: Compartilhar > Incorporar um mapa.',
				],
				'horario_funcionamento' => [
					'label'  => 'Horario de funcionamento',
					'type'   => 'repeater',
					'rotulo' => 'horario',
					'sub'    => [
						'dia'     => [ 'label' => 'Dia', 'type' => 'text', 'placeholder' => 'Segunda a sexta', 'largura' => 'meia' ],
						'horario' => [ 'label' => 'Horario', 'type' => 'text', 'placeholder' => '08h as 18h', 'largura' => 'meia' ],
					],
				],
				'instagram' => [ 'label' => 'Instagram (URL)', 'type' => 'url', 'largura' => 'meia' ],
				'facebook'  => [ 'label' => 'Facebook (URL)', 'type' => 'url', 'largura' => 'meia' ],
			],
		],

		'vendedores' => [
			'titulo' => 'Vendedores',
			'campos' => [
				'vendedores' => [
					'label'  => 'Vendedores',
					'type'   => 'repeater',
					'rotulo' => 'vendedor',
					'desc'   => 'O primeiro da lista e o padrao para veiculos sem vendedor definido.',
					'sub'    => [
						'nome'     => [ 'label' => 'Nome', 'type' => 'text', 'largura' => 'terco' ],
						'whatsapp' => [
							'label'       => 'WhatsApp',
							'type'        => 'text',
							'desc'        => 'So digitos, com pais e DDD.',
							'placeholder' => '5511987654321',
							'largura'     => 'terco',
						],
						'cargo' => [ 'label' => 'Cargo', 'type' => 'text', 'largura' => 'terco' ],
						'foto'  => [ 'label' => 'Foto', 'type' => 'image' ],
					],
				],
			],
		],

		'conteudo' => [
			'titulo' => 'Conteudo',
			'campos' => [
				'hero_titulo'    => [ 'label' => 'Hero - titulo', 'type' => 'text', 'largura' => 'meia' ],
				'hero_subtitulo' => [ 'label' => 'Hero - subtitulo', 'type' => 'text', 'largura' => 'meia' ],
				'hero_imagem'    => [
					'label' => 'Hero - imagem de fundo',
					'type'  => 'image',
					'desc'  => 'A fachada da loja ou um carro do estoque. Minimo 1920px de largura.',
				],
				'sobre_titulo' => [ 'label' => 'Sobre - titulo', 'type' => 'text', 'largura' => 'meia' ],
				'sobre_imagem' => [ 'label' => 'Sobre - imagem', 'type' => 'image', 'largura' => 'meia' ],
				'sobre_texto'  => [ 'label' => 'Sobre - texto', 'type' => 'wysiwyg' ],
				'diferenciais' => [
					'label'  => 'Diferenciais',
					'type'   => 'repeater',
					'rotulo' => 'diferencial',
					'max'    => 4,
					'desc'   => 'Ex: "Garantia de 3 meses", "Financiamos em ate 60x".',
					'sub'    => [
						'icone' => [
							'label'   => 'Icone',
							'type'    => 'select',
							'std'     => 'check',
							'largura' => 'terco',
							'opcoes'  => [
								'shield'   => 'Escudo (garantia)',
								'card'     => 'Cartao (financiamento)',
								'exchange' => 'Troca',
								'check'    => 'Check (procedencia)',
								'wrench'   => 'Chave (revisao)',
								'clock'    => 'Relogio (agilidade)',
								'star'     => 'Estrela',
								'car'      => 'Carro',
							],
						],
						'titulo' => [ 'label' => 'Titulo', 'type' => 'text', 'largura' => 'terco' ],
						'texto'  => [ 'label' => 'Texto', 'type' => 'textarea', 'largura' => 'terco' ],
					],
				],
				'depoimentos' => [
					'label'  => 'Depoimentos',
					'type'   => 'repeater',
					'rotulo' => 'depoimento',
					'sub'    => [
						'nome' => [ 'label' => 'Nome', 'type' => 'text', 'largura' => 'meia' ],
						'nota' => [
							'label'   => 'Nota (1 a 5)',
							'type'    => 'number',
							'std'     => 5,
							'min'     => 1,
							'max'     => 5,
							'largura' => 'meia',
						],
						'texto' => [ 'label' => 'Depoimento', 'type' => 'textarea' ],
					],
				],
			],
		],

		'integracoes' => [
			'titulo' => 'Integracoes',
			'campos' => [
				'google_analytics_id' => [
					'label'       => 'Google Analytics (ID)',
					'type'        => 'text',
					'placeholder' => 'G-XXXXXXXXXX',
					'desc'        => 'So dispara em producao (WP_ENVIRONMENT_TYPE).',
					'largura'     => 'terco',
				],
				'meta_pixel_id' => [ 'label' => 'Meta Pixel (ID)', 'type' => 'text', 'largura' => 'terco' ],
				'google_site_verification' => [
					'label'   => 'Google Site Verification',
					'type'    => 'text',
					'largura' => 'terco',
				],
				'whatsapp_flutuante' => [
					'label' => 'Botao flutuante de WhatsApp',
					'type'  => 'checkbox',
					'std'   => 1,
				],
			],
		],
	];
}

/**
 * Valores padrao, achatados a partir do esquema.
 */
function lv_opcoes_padrao(): array {
	$padrao = [];

	foreach ( lv_opcoes_schema() as $aba ) {
		foreach ( $aba['campos'] as $id => $campo ) {
			if ( isset( $campo['std'] ) ) {
				$padrao[ $id ] = $campo['std'];
			} elseif ( 'repeater' === $campo['type'] ) {
				$padrao[ $id ] = [];
			}
		}
	}

	return $padrao;
}

/**
 * Encontra a definicao de um campo pelo id, em qualquer aba.
 */
function lv_opcoes_campo( string $id ): ?array {
	foreach ( lv_opcoes_schema() as $aba ) {
		if ( isset( $aba['campos'][ $id ] ) ) {
			return $aba['campos'][ $id ];
		}
	}
	return null;
}
