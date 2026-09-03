<?php
/**
 * JSON-LD: Car na ficha do veiculo, AutoDealer na home. Mais Open Graph,
 * que e o que faz o link compartilhado no WhatsApp aparecer com a foto certa.
 */

defined( 'ABSPATH' ) || exit;

function lv_schema_endereco(): array {
	return array_filter(
		[
			'@type'           => 'PostalAddress',
			'streetAddress'   => lv_option( 'endereco' ),
			'addressLocality' => lv_option( 'cidade' ),
			'addressRegion'   => lv_option( 'estado' ),
			'postalCode'      => lv_option( 'cep' ),
			'addressCountry'  => 'BR',
		]
	);
}

function lv_schema_dealer(): array {
	$logo = lv_option( 'logo' );

	return array_filter(
		[
			'@type'      => 'AutoDealer',
			'name'       => lv_option( 'nome_loja', get_bloginfo( 'name' ) ),
			'url'        => home_url( '/' ),
			'image'      => is_array( $logo ) ? ( $logo['url'] ?? null ) : null,
			'telephone'  => lv_option( 'telefone_fixo' ),
			'email'      => lv_option( 'email' ),
			'address'    => lv_schema_endereco(),
			'sameAs'     => array_values( array_filter( [ lv_option( 'instagram' ), lv_option( 'facebook' ) ] ) ),
			'priceRange' => '$$',
		]
	);
}

/**
 * Ficha do veiculo.
 */
add_action( 'wp_head', function () {
	if ( ! is_singular( 'veiculo' ) ) {
		return;
	}

	$id     = get_the_ID();
	$preco  = (float) ( lv_field( 'preco_promocional', $id ) ?: lv_field( 'preco', $id, 0 ) );
	$marca  = lv_termo( 'marca', $id );
	$status = lv_status( $id );

	$disponibilidade = 'https://schema.org/SoldOut';
	if ( 'disponivel' === $status ) {
		$disponibilidade = 'https://schema.org/InStock';
	} elseif ( 'reservado' === $status ) {
		$disponibilidade = 'https://schema.org/LimitedAvailability';
	}

	$imagens = array_values(
		array_filter(
			array_map(
				static function ( $img_id ) {
					return wp_get_attachment_image_url( (int) $img_id, 'lv-galeria' );
				},
				array_slice( lv_galeria_ids( $id ), 0, 6 )
			)
		)
	);

	$data = array_filter(
		[
			'@context'            => 'https://schema.org',
			'@type'               => 'Car',
			'name'                => get_the_title(),
			'url'                 => get_permalink(),
			'description'         => wp_strip_all_tags( get_the_excerpt() ),
			'image'               => $imagens,
			'brand'               => $marca ? [ '@type' => 'Brand', 'name' => $marca ] : null,
			'color'               => lv_field( 'cor', $id ),
			'vehicleModelDate'    => lv_field( 'ano_modelo', $id ),
			'productionDate'      => lv_field( 'ano_fabricacao', $id ),
			'numberOfDoors'       => lv_field( 'portas', $id ),
			'bodyType'            => lv_termo( 'carroceria', $id ),
			'vehicleTransmission' => lv_termo( 'cambio', $id ),
			'fuelType'            => lv_termo( 'combustivel', $id ),
			'mileageFromOdometer' => [
				'@type'    => 'QuantitativeValue',
				'value'    => (int) lv_field( 'km', $id, 0 ),
				'unitCode' => 'KMT',
			],
			'offers'              => array_filter(
				[
					'@type'         => 'Offer',
					'price'         => $preco ?: null,
					'priceCurrency' => 'BRL',
					'url'           => get_permalink(),
					'itemCondition' => 'https://schema.org/UsedCondition',
					'availability'  => $disponibilidade,
					'seller'        => lv_schema_dealer(),
				]
			),
		]
	);

	echo "\n" . '<script type="application/ld+json">'
		. wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		. '</script>' . "\n";
}, 20 );

/**
 * Home: a loja em si. E o que faz aparecer na busca local do Google.
 */
add_action( 'wp_head', function () {
	if ( ! is_front_page() ) {
		return;
	}

	$dealer             = lv_schema_dealer();
	$dealer['@context'] = 'https://schema.org';

	$horarios = lv_option( 'horario_funcionamento', [] );
	if ( is_array( $horarios ) && $horarios ) {
		$dealer['openingHours'] = array_values(
			array_filter(
				array_map(
					static function ( $h ) {
						return trim( ( $h['dia'] ?? '' ) . ' ' . ( $h['horario'] ?? '' ) );
					},
					$horarios
				)
			)
		);
	}

	echo "\n" . '<script type="application/ld+json">'
		. wp_json_encode( $dealer, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		. '</script>' . "\n";
}, 20 );

/**
 * Open Graph. O compartilhamento no WhatsApp precisa da foto e do preco.
 */
add_action( 'wp_head', function () {
	$imagem = '';
	$desc   = get_bloginfo( 'description' );

	if ( is_singular( 'veiculo' ) ) {
		$ids    = lv_galeria_ids( get_the_ID() );
		$imagem = $ids ? (string) wp_get_attachment_image_url( $ids[0], 'lv-galeria' ) : '';
		$desc   = sprintf(
			'%s - %s - %s',
			lv_ano( get_the_ID() ),
			lv_km( get_the_ID() ),
			lv_preco( get_the_ID() )
		);
	} elseif ( is_front_page() ) {
		$hero   = lv_option( 'hero_imagem' );
		$imagem = is_array( $hero ) ? (string) ( $hero['url'] ?? '' ) : '';
	}

	printf(
		'<meta property="og:title" content="%s">' . "\n"
		. '<meta property="og:description" content="%s">' . "\n"
		. '<meta property="og:type" content="website">' . "\n"
		. '<meta property="og:url" content="%s">' . "\n"
		. '<meta property="og:site_name" content="%s">' . "\n"
		. '<meta property="og:locale" content="pt_BR">' . "\n"
		. '<meta name="twitter:card" content="summary_large_image">' . "\n",
		esc_attr( wp_get_document_title() ),
		esc_attr( $desc ),
		esc_url( home_url( add_query_arg( [] ) ) ),
		esc_attr( lv_option( 'nome_loja', get_bloginfo( 'name' ) ) )
	);

	if ( $imagem ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $imagem ) );
	}
}, 5 );

/**
 * Verificacao do Search Console e tags de rastreio.
 * Analytics/Pixel so carregam em producao, para nao poluir o dado com dev.
 */
add_action( 'wp_head', function () {
	$verif = lv_option( 'google_site_verification' );
	if ( $verif ) {
		printf( '<meta name="google-site-verification" content="%s">' . "\n", esc_attr( $verif ) );
	}

	if ( 'production' !== wp_get_environment_type() ) {
		return;
	}

	$ga = lv_option( 'google_analytics_id' );
	if ( $ga ) {
		printf(
			'<script async src="https://www.googletagmanager.com/gtag/js?id=%1$s"></script>'
			. '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}'
			. 'gtag("js",new Date());gtag("config","%1$s");</script>' . "\n",
			esc_attr( $ga )
		);
	}

	$pixel = lv_option( 'meta_pixel_id' );
	if ( $pixel ) {
		printf(
			'<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?'
			. 'n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;'
			. 'n.push=n;n.loaded=!0;n.version="2.0";n.queue=[];t=b.createElement(e);t.async=!0;'
			. 't.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'
			. '"script","https://connect.facebook.net/en_US/fbevents.js");'
			. 'fbq("init","%s");fbq("track","PageView");</script>' . "\n",
			esc_attr( $pixel )
		);
	}
}, 3 );

/**
 * Favicon vindo das configuracoes.
 */
add_action( 'wp_head', function () {
	$favicon = lv_option( 'favicon' );
	if ( is_array( $favicon ) && ! empty( $favicon['url'] ) ) {
		printf( '<link rel="icon" href="%s">' . "\n", esc_url( $favicon['url'] ) );
	}
}, 4 );
