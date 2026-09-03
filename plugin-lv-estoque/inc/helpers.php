<?php
/**
 * Helpers do template. Tudo que o tema chama passa por aqui, para que o tema
 * nunca dependa diretamente do ACF.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Le um campo do veiculo. Usa ACF quando existe, senao cai no post meta.
 */
function lv_field( string $name, $post_id = null, $default = null ) {
	$post_id = $post_id ?: get_the_ID();

	if ( function_exists( 'get_field' ) ) {
		$valor = get_field( $name, $post_id );
	} else {
		$valor = get_post_meta( (int) $post_id, $name, true );
	}

	if ( null === $valor || '' === $valor || ( is_array( $valor ) && ! $valor ) ) {
		return $default;
	}
	return $valor;
}

/**
 * Le uma opcao da loja (options page do ACF) com fallback para wp_option.
 */
function lv_option( string $name, $default = null ) {
	if ( function_exists( 'get_field' ) ) {
		$valor = get_field( $name, 'option' );
		if ( null !== $valor && '' !== $valor && ! ( is_array( $valor ) && ! $valor ) ) {
			return $valor;
		}
	}

	$fallback = get_option( 'lv_opcoes', [] );
	if ( is_array( $fallback ) && isset( $fallback[ $name ] ) && '' !== $fallback[ $name ] ) {
		return $fallback[ $name ];
	}

	return $default;
}

/**
 * Preco formatado, respeitando "mostrar preco" e o promocional.
 */
function lv_preco( $post_id = null ): string {
	$post_id = $post_id ?: get_the_ID();

	$mostrar = lv_field( 'mostrar_preco', $post_id, true );
	if ( ! $mostrar ) {
		return 'Consulte';
	}

	$preco = lv_field( 'preco_promocional', $post_id ) ?: lv_field( 'preco', $post_id );

	return $preco ? 'R$ ' . number_format( (float) $preco, 0, ',', '.' ) : 'Consulte';
}

/**
 * Preco original riscado (so quando existe promocional).
 */
function lv_preco_original( $post_id = null ): string {
	$post_id = $post_id ?: get_the_ID();

	if ( ! lv_field( 'mostrar_preco', $post_id, true ) ) {
		return '';
	}
	if ( ! lv_field( 'preco_promocional', $post_id ) ) {
		return '';
	}
	$preco = lv_field( 'preco', $post_id );

	return $preco ? 'R$ ' . number_format( (float) $preco, 0, ',', '.' ) : '';
}

function lv_km( $post_id = null ): string {
	$km = (int) lv_field( 'km', $post_id ?: get_the_ID(), 0 );
	return number_format( $km, 0, ',', '.' ) . ' km';
}

/**
 * "2019/2020" ou "2019" quando fabricacao e modelo sao iguais.
 */
function lv_ano( $post_id = null ): string {
	$post_id = $post_id ?: get_the_ID();
	$fab = (int) lv_field( 'ano_fabricacao', $post_id, 0 );
	$mod = (int) lv_field( 'ano_modelo', $post_id, 0 );

	if ( $fab && $mod && $fab !== $mod ) {
		return $fab . '/' . $mod;
	}
	return (string) ( $mod ?: $fab ?: '' );
}

/**
 * disponivel | reservado | vendido
 */
function lv_status( $post_id = null ): string {
	$status = lv_field( 'status_veiculo', $post_id ?: get_the_ID(), 'disponivel' );
	if ( is_array( $status ) ) {
		$status = $status['value'] ?? 'disponivel';
	}
	return (string) $status;
}

function lv_status_label( $post_id = null ): string {
	return [
		'disponivel' => 'Disponivel',
		'reservado'  => 'Reservado',
		'vendido'    => 'Vendido',
	][ lv_status( $post_id ) ] ?? 'Disponivel';
}

/**
 * IDs das imagens da galeria, normalizando os formatos que o ACF devolve.
 * Sempre coloca a imagem destacada na frente.
 */
function lv_galeria_ids( $post_id = null ): array {
	$post_id = $post_id ?: get_the_ID();
	$galeria = lv_field( 'galeria', $post_id, [] );
	$ids     = [];

	foreach ( (array) $galeria as $item ) {
		if ( is_array( $item ) && isset( $item['ID'] ) ) {
			$ids[] = (int) $item['ID'];
		} elseif ( is_numeric( $item ) ) {
			$ids[] = (int) $item;
		} elseif ( is_object( $item ) && isset( $item->ID ) ) {
			$ids[] = (int) $item->ID;
		}
	}

	$capa = (int) get_post_thumbnail_id( $post_id );
	if ( $capa ) {
		array_unshift( $ids, $capa );
	}

	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Numero de WhatsApp do vendedor responsavel, com fallback para o primeiro
 * vendedor cadastrado nas configuracoes.
 */
function lv_whatsapp_numero( $post_id = null ): string {
	$post_id = $post_id ?: get_the_ID();

	$numero = (string) lv_field( 'vendedor', $post_id, '' );

	if ( ! $numero ) {
		$vendedores = lv_option( 'vendedores', [] );
		if ( is_array( $vendedores ) && isset( $vendedores[0]['whatsapp'] ) ) {
			$numero = (string) $vendedores[0]['whatsapp'];
		}
	}

	return preg_replace( '/\D/', '', $numero );
}

/**
 * Link do WhatsApp com mensagem pre-preenchida. E o CTA que gera o dinheiro
 * do cliente: mostre isso primeiro na reuniao de venda.
 */
function lv_whatsapp_link( $post_id = null, string $numero = '', string $mensagem = '' ): string {
	$post_id = $post_id ?: get_the_ID();
	$numero  = $numero ? preg_replace( '/\D/', '', $numero ) : lv_whatsapp_numero( $post_id );

	if ( ! $numero ) {
		return '';
	}

	if ( ! $mensagem ) {
		if ( $post_id && 'veiculo' === get_post_type( $post_id ) ) {
			$mensagem = sprintf(
				"Ola! Vi o %s no site e queria mais informacoes.\n%s",
				get_the_title( $post_id ),
				get_permalink( $post_id )
			);
		} else {
			$mensagem = sprintf(
				'Ola! Vim pelo site da %s e queria mais informacoes.',
				lv_option( 'nome_loja', get_bloginfo( 'name' ) )
			);
		}
	}

	return 'https://wa.me/' . $numero . '?text=' . rawurlencode( $mensagem );
}

/**
 * Lista de termos de uma taxonomia como string simples.
 */
function lv_termos( string $taxonomia, $post_id = null, string $sep = ', ' ): string {
	$termos = get_the_terms( $post_id ?: get_the_ID(), $taxonomia );
	if ( ! $termos || is_wp_error( $termos ) ) {
		return '';
	}
	return implode( $sep, wp_list_pluck( $termos, 'name' ) );
}

/**
 * Primeiro termo (usado em cards, onde so cabe um).
 */
function lv_termo( string $taxonomia, $post_id = null ): string {
	$termos = get_the_terms( $post_id ?: get_the_ID(), $taxonomia );
	if ( ! $termos || is_wp_error( $termos ) ) {
		return '';
	}
	return $termos[0]->name;
}

/**
 * URL da vitrine.
 */
function lv_url_vitrine( array $args = [] ): string {
	$base = get_post_type_archive_link( 'veiculo' ) ?: home_url( '/veiculos/' );
	return $args ? add_query_arg( $args, $base ) : $base;
}

/**
 * Valor atual de um filtro vindo do GET, ja sanitizado.
 */
function lv_filtro( string $chave, string $default = '' ): string {
	if ( ! isset( $_GET[ $chave ] ) ) {
		return $default;
	}
	return sanitize_text_field( wp_unslash( (string) $_GET[ $chave ] ) );
}

/**
 * Ha algum filtro aplicado?
 */
function lv_tem_filtros(): bool {
	foreach ( [ 'marca', 'carroceria', 'cambio', 'combustivel', 'preco_min', 'preco_max', 'ano_min', 'km_max', 'ordem', 'busca' ] as $c ) {
		if ( ! empty( $_GET[ $c ] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Imagem de capa com fallback para o placeholder do tema.
 */
function lv_capa( $post_id = null, string $tamanho = 'lv-card', array $attr = [] ): string {
	$post_id = $post_id ?: get_the_ID();

	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $tamanho, $attr );
	}

	$placeholder = get_template_directory_uri() . '/assets/img/placeholder-carro.svg';
	return sprintf(
		'<img src="%s" alt="%s" loading="lazy" width="600" height="400" class="lv-card__placeholder">',
		esc_url( $placeholder ),
		esc_attr( get_the_title( $post_id ) )
	);
}

/**
 * Telefone so com digitos, para href="tel:".
 */
function lv_tel_link( string $numero = '' ): string {
	$numero = $numero ?: (string) lv_option( 'telefone_fixo', '' );
	$digits = preg_replace( '/\D/', '', (string) $numero );

	if ( ! $digits ) {
		return '';
	}
	// 10 ou 11 digitos = DDD + numero, falta o codigo do pais.
	if ( in_array( strlen( $digits ), [ 10, 11 ], true ) ) {
		$digits = '55' . $digits;
	}

	return 'tel:+' . $digits;
}
