<?php
/**
 * Popula o ambiente de desenvolvimento com dados de demonstracao.
 *
 * Uso:  make seed
 *       wp eval-file wp-content/plugins/plugin-lv-estoque/seeds/seed.php
 *
 * Idempotente: rodar de novo nao duplica nada.
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Rode este arquivo com: wp eval-file\n" );
}

// ---------------------------------------------------------------------------
// 1. Termos
// ---------------------------------------------------------------------------
WP_CLI::log( '==> Termos padrao' );
lv_seed_termos_padrao();

// ---------------------------------------------------------------------------
// 2. Configuracoes da loja (loja ficticia, para a demonstracao)
// ---------------------------------------------------------------------------
WP_CLI::log( '==> Configuracoes da loja' );

$opcoes = [
	'nome_loja'             => 'Auto Center Modelo',
	'slogan'                => 'Seminovos revisados, com procedencia',
	'cnpj'                  => '00.000.000/0001-00',
	'ano_fundacao'          => 2009,
	'telefone_fixo'         => '(11) 4000-0000',
	'email'                 => 'contato@autocentermodelo.test',
	'endereco'              => 'Av. Exemplo, 1234 - Vila Teste',
	'cidade'                => 'Sao Paulo',
	'estado'                => 'SP',
	'cep'                   => '01000-000',
	'instagram'             => 'https://instagram.com/exemplo',
	'facebook'              => 'https://facebook.com/exemplo',
	'cor_primaria'          => '#0B3D91',
	'cor_secundaria'        => '#111827',
	'cor_destaque'          => '#F59E0B',
	'fonte'                 => 'moderno',
	'hero_titulo'           => 'O carro certo, sem enrolacao',
	'hero_subtitulo'        => 'Seminovos revisados, com garantia e transferencia inclusa.',
	'sobre_titulo'          => 'Quem somos',
	'sobre_texto'           => '<p>Somos uma loja de bairro que trabalha com seminovos revisados desde 2009. '
		. 'Cada carro passa por checagem mecanica e de documentacao antes de entrar no patio.</p>',
	'whatsapp_flutuante'    => 1,
	'horario_funcionamento' => [
		[ 'dia' => 'Segunda a sexta', 'horario' => '08h as 18h' ],
		[ 'dia' => 'Sabado', 'horario' => '09h as 14h' ],
		[ 'dia' => 'Domingo', 'horario' => 'Fechado' ],
	],
	'vendedores'            => [
		[ 'nome' => 'Marcos', 'whatsapp' => '5511987654321', 'cargo' => 'Vendedor' ],
		[ 'nome' => 'Juliana', 'whatsapp' => '5511912345678', 'cargo' => 'Gerente de vendas' ],
	],
	'diferenciais'          => [
		[ 'icone' => 'shield', 'titulo' => 'Garantia de 3 meses', 'texto' => 'Motor e cambio cobertos em todos os veiculos.' ],
		[ 'icone' => 'card', 'titulo' => 'Financiamos em ate 60x', 'texto' => 'Aprovacao rapida com os principais bancos.' ],
		[ 'icone' => 'exchange', 'titulo' => 'Aceitamos seu usado', 'texto' => 'Avaliacao na hora, sem compromisso.' ],
		[ 'icone' => 'check', 'titulo' => 'Procedencia checada', 'texto' => 'Laudo cautelar e historico do veiculo.' ],
	],
	'depoimentos'           => [
		[ 'nome' => 'Ricardo A.', 'nota' => 5, 'texto' => 'Comprei meu carro em uma tarde. Atendimento direto, sem empurrar nada.' ],
		[ 'nome' => 'Patricia M.', 'nota' => 5, 'texto' => 'Levaram o carro ate a minha mecanica de confianca antes de fechar. Isso vale muito.' ],
		[ 'nome' => 'Everton S.', 'nota' => 4, 'texto' => 'Documentacao saiu rapido e o preco foi justo. Recomendo.' ],
	],
];

// A tela Configuracoes do Site grava tudo num unico option; passamos pelo
// mesmo sanitizador para o seed produzir exatamente o mesmo formato.
update_option( 'lv_opcoes', lv_sanitizar_opcoes( $opcoes ) );

// ---------------------------------------------------------------------------
// 3. Imagem de demonstracao
// ---------------------------------------------------------------------------

/**
 * Gera um JPEG simples com o nome do veiculo. So para desenvolvimento -
 * em producao as fotos sao do celular do lojista.
 */
function lv_seed_imagem( string $texto, string $legenda, int $parent_id ): int {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return 0;
	}

	$largura = 1200;
	$altura  = 800;
	$img     = imagecreatetruecolor( $largura, $altura );

	// Fundo em degrade cinza-azulado.
	for ( $y = 0; $y < $altura; $y++ ) {
		$t   = $y / $altura;
		$cor = imagecolorallocate(
			$img,
			(int) ( 32 + 40 * $t ),
			(int) ( 41 + 45 * $t ),
			(int) ( 56 + 55 * $t )
		);
		imageline( $img, 0, $y, $largura, $y, $cor );
	}

	$branco = imagecolorallocate( $img, 245, 246, 248 );
	$suave  = imagecolorallocate( $img, 150, 160, 175 );

	// Silhueta bem simplificada de um carro, so para nao ficar um retangulo vazio.
	$corpo = imagecolorallocate( $img, 70, 80, 100 );
	imagefilledrectangle( $img, 260, 430, 940, 540, $corpo );
	imagefilledrectangle( $img, 380, 350, 820, 435, $corpo );
	imagefilledellipse( $img, 380, 545, 110, 110, imagecolorallocate( $img, 25, 28, 36 ) );
	imagefilledellipse( $img, 820, 545, 110, 110, imagecolorallocate( $img, 25, 28, 36 ) );

	imagestring( $img, 5, 60, 60, $texto, $branco );
	imagestring( $img, 3, 60, 90, $legenda, $suave );
	imagestring( $img, 2, 60, $altura - 40, 'imagem de demonstracao - ambiente de desenvolvimento', $suave );

	$tmp = wp_tempnam( 'lv-seed.jpg' );
	imagejpeg( $img, $tmp, 82 );
	imagedestroy( $img );

	$arquivo = [
		'name'     => sanitize_title( $texto . '-' . $legenda ) . '.jpg',
		'type'     => 'image/jpeg',
		'tmp_name' => $tmp,
		'error'    => 0,
		'size'     => (int) filesize( $tmp ),
	];

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$id = media_handle_sideload( $arquivo, $parent_id, $texto );

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		return 0;
	}

	update_post_meta( $id, '_lv_seed', 1 );

	return (int) $id;
}

// ---------------------------------------------------------------------------
// 4. Veiculos
// ---------------------------------------------------------------------------
WP_CLI::log( '==> Veiculos de demonstracao' );

$json = LV_ESTOQUE_DIR . 'seeds/veiculos-demo.json';
if ( ! file_exists( $json ) ) {
	WP_CLI::error( 'veiculos-demo.json nao encontrado.' );
}

$veiculos = json_decode( (string) file_get_contents( $json ), true );
if ( ! is_array( $veiculos ) ) {
	WP_CLI::error( 'veiculos-demo.json invalido.' );
}

$criados = 0;

foreach ( $veiculos as $v ) {
	$existente = get_page_by_path( sanitize_title( $v['titulo'] ), OBJECT, 'veiculo' );
	if ( $existente ) {
		WP_CLI::log( '    ja existe: ' . $v['titulo'] );
		continue;
	}

	$post_id = wp_insert_post(
		[
			'post_type'    => 'veiculo',
			'post_status'  => 'publish',
			'post_title'   => $v['titulo'],
			'post_name'    => sanitize_title( $v['titulo'] ),
			'post_content' => $v['descricao'] ?? '',
			'post_excerpt' => wp_trim_words( $v['descricao'] ?? '', 28 ),
		],
		true
	);

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( 'falhou: ' . $v['titulo'] );
		continue;
	}

	// Taxonomias.
	wp_set_object_terms( $post_id, [ $v['marca'] ], 'marca' );
	wp_set_object_terms( $post_id, [ $v['carroceria'] ], 'carroceria' );
	wp_set_object_terms( $post_id, [ $v['cambio'] ], 'cambio' );
	wp_set_object_terms( $post_id, [ $v['combustivel'] ], 'combustivel' );
	wp_set_object_terms( $post_id, (array) ( $v['opcionais'] ?? [] ), 'opcional' );

	// Campos.
	$campos = [
		'preco'             => $v['preco'],
		'preco_promocional' => $v['preco_promocional'],
		'mostrar_preco'     => 1,
		'ano_fabricacao'    => $v['ano_fabricacao'],
		'ano_modelo'        => $v['ano_modelo'],
		'km'                => $v['km'],
		'cor'               => $v['cor'],
		'portas'            => $v['portas'],
		'motor'             => $v['motor'],
		'final_placa'       => $v['final_placa'],
		'potencia'          => $v['potencia'] ?? null,
		'unico_dono'        => ! empty( $v['unico_dono'] ) ? 1 : 0,
		'ipva_pago'         => ! empty( $v['ipva_pago'] ) ? 1 : 0,
		'aceita_troca'      => ! empty( $v['aceita_troca'] ) ? 1 : 0,
		'status_veiculo'    => $v['status_veiculo'],
		'destaque'          => ! empty( $v['destaque'] ) ? 1 : 0,
		'vendedor'          => '5511987654321',
	];

	foreach ( $campos as $chave => $valor ) {
		if ( null === $valor ) {
			continue;
		}
		update_post_meta( $post_id, $chave, $valor );
	}

	// Fotos: 5 por carro.
	$galeria = [];
	$angulos = [ 'frente 3/4', 'traseira 3/4', 'painel', 'bancos', 'porta-malas' ];

	foreach ( $angulos as $i => $angulo ) {
		$img_id = lv_seed_imagem( $v['titulo'], $angulo, $post_id );
		if ( $img_id ) {
			$galeria[] = $img_id;
			if ( 0 === $i ) {
				set_post_thumbnail( $post_id, $img_id );
			}
		}
	}

	// image_advanced grava um ID por linha, todas com a mesma chave.
	delete_post_meta( $post_id, 'galeria' );
	foreach ( $galeria as $img_id ) {
		add_post_meta( $post_id, 'galeria', $img_id );
	}

	$criados++;
	WP_CLI::log( '    criado: ' . $v['titulo'] . ' (' . count( $galeria ) . ' fotos)' );
}

// ---------------------------------------------------------------------------
// 5. Menu
// ---------------------------------------------------------------------------
WP_CLI::log( '==> Menu principal' );

$menu = wp_get_nav_menu_object( 'Principal' );
if ( $menu && ! wp_get_nav_menu_items( $menu->term_id ) ) {
	wp_update_nav_menu_item(
		$menu->term_id,
		0,
		[ 'menu-item-title' => 'Inicio', 'menu-item-url' => home_url( '/' ), 'menu-item-status' => 'publish' ]
	);
	wp_update_nav_menu_item(
		$menu->term_id,
		0,
		[ 'menu-item-title' => 'Estoque', 'menu-item-url' => get_post_type_archive_link( 'veiculo' ), 'menu-item-status' => 'publish' ]
	);

	foreach ( [ 'Sobre', 'Contato' ] as $titulo ) {
		$pagina = get_page_by_title( $titulo );
		if ( $pagina ) {
			wp_update_nav_menu_item(
				$menu->term_id,
				0,
				[
					'menu-item-object-id' => $pagina->ID,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				]
			);
		}
	}
}

flush_rewrite_rules();

WP_CLI::success( sprintf( '%d veiculos criados. Abra %s', $criados, home_url( '/veiculos/' ) ) );
