<?php
/**
 * Identidade visual por loja.
 *
 * Regra do template: nenhuma cor literal no CSS do tema. Se voce escreveu
 * #0B3D91 dentro de um arquivo .css, errou - a cor vem daqui.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Presets de fonte disponiveis nas configuracoes.
 */
function lv_presets_fonte(): array {
	return [
		'moderno' => [
			'titulo'      => "'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif",
			'corpo'       => "'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif",
			'google'      => 'family=Inter:wght@400;500;700',
			'peso_titulo' => '700',
		],
		'robusto' => [
			'titulo'      => "'Barlow Condensed', 'Arial Narrow', system-ui, sans-serif",
			'corpo'       => "'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif",
			'google'      => 'family=Barlow+Condensed:wght@600;700&family=Inter:wght@400;500',
			'peso_titulo' => '700',
		],
		'premium' => [
			'titulo'      => "'Playfair Display', Georgia, 'Times New Roman', serif",
			'corpo'       => "'Source Sans 3', system-ui, -apple-system, 'Segoe UI', sans-serif",
			'google'      => 'family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;600',
			'peso_titulo' => '700',
		],
	];
}

function lv_preset_fonte_atual(): array {
	$presets = lv_presets_fonte();
	$escolha = (string) lv_option( 'fonte', 'moderno' );

	return $presets[ $escolha ] ?? $presets['moderno'];
}

/**
 * Escurece ou clareia um hex. Usado para os estados :hover sem pedir mais
 * uma cor ao cliente.
 */
function lv_ajustar_cor( string $hex, int $percentual ): string {
	$hex = ltrim( trim( $hex ), '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return '#' . $hex;
	}

	$saida = '#';
	for ( $i = 0; $i < 3; $i++ ) {
		$valor  = hexdec( substr( $hex, $i * 2, 2 ) );
		$valor  = (int) max( 0, min( 255, $valor + ( $valor * $percentual / 100 ) ) );
		$saida .= str_pad( dechex( $valor ), 2, '0', STR_PAD_LEFT );
	}

	return $saida;
}

/**
 * Converte hex em "r, g, b" para usar dentro de rgb() com alpha.
 */
function lv_cor_rgb( string $hex ): string {
	$hex = ltrim( trim( $hex ), '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return '0, 0, 0';
	}

	return implode(
		', ',
		[
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		]
	);
}

/**
 * Tokens de design injetados no head.
 */
add_action( 'wp_head', function (): void {
	$primaria   = (string) ( lv_option( 'cor_primaria' ) ?: '#0B3D91' );
	$secundaria = (string) ( lv_option( 'cor_secundaria' ) ?: '#111827' );
	$destaque   = (string) ( lv_option( 'cor_destaque' ) ?: '#F59E0B' );
	$fonte      = lv_preset_fonte_atual();
	?>
	<style id="lv-tokens">
	:root{
		--cor-primaria: <?php echo esc_attr( $primaria ); ?>;
		--cor-primaria-escura: <?php echo esc_attr( lv_ajustar_cor( $primaria, -18 ) ); ?>;
		--cor-primaria-clara: <?php echo esc_attr( lv_ajustar_cor( $primaria, 45 ) ); ?>;
		--cor-primaria-rgb: <?php echo esc_attr( lv_cor_rgb( $primaria ) ); ?>;
		--cor-secundaria: <?php echo esc_attr( $secundaria ); ?>;
		--cor-secundaria-clara: <?php echo esc_attr( lv_ajustar_cor( $secundaria, 30 ) ); ?>;
		--cor-destaque: <?php echo esc_attr( $destaque ); ?>;
		--cor-destaque-escura: <?php echo esc_attr( lv_ajustar_cor( $destaque, -18 ) ); ?>;

		--cor-texto:#1F2937;
		--cor-texto-suave:#6B7280;
		--cor-fundo:#FFFFFF;
		--cor-fundo-alt:#F9FAFB;
		--cor-borda:#E5E7EB;

		--cor-sucesso:#0A7C2F;
		--cor-alerta:#B26B00;
		--cor-erro:#B32D2E;

		/* Cor de marca do WhatsApp - fixa por definicao, nao e da loja. */
		--cor-whatsapp:#25D366;
		--cor-whatsapp-escura:#1FBB59;
		--cor-whatsapp-texto:#05321A;

		/* Neutros para texto sobre fundo escuro/colorido. */
		--cor-branco:#FFFFFF;
		--cor-inverso:#E5E7EB;
		--cor-inverso-suave:#9CA3AF;

		--fonte-titulo: <?php echo $fonte['titulo']; // phpcs:ignore WordPress.Security.EscapeOutput ?>;
		--fonte-corpo: <?php echo $fonte['corpo']; // phpcs:ignore WordPress.Security.EscapeOutput ?>;
		--peso-titulo: <?php echo esc_attr( $fonte['peso_titulo'] ); ?>;

		--raio:12px;
		--raio-sm:8px;
		--raio-pill:999px;
		--sombra:0 1px 3px rgb(0 0 0 / .1), 0 8px 24px rgb(0 0 0 / .06);
		--sombra-sm:0 1px 2px rgb(0 0 0 / .08);
		--container:1200px;
		--gap:24px;
		--header-altura:72px;
	}
	</style>
	<?php
}, 6 );

/**
 * Google Fonts do preset escolhido, com display=swap e preconnect.
 */
add_action( 'wp_head', function (): void {
	$fonte = lv_preset_fonte_atual();
	?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?<?php echo esc_attr( $fonte['google'] ); ?>&display=swap">
	<?php
}, 7 );

/**
 * Logo das configuracoes vira o custom logo do WordPress.
 */
function lv_logo_html( bool $rodape = false ): string {
	$logo = lv_option( $rodape ? 'logo_rodape' : 'logo' );
	$nome = (string) lv_option( 'nome_loja', get_bloginfo( 'name' ) );

	if ( is_array( $logo ) && ! empty( $logo['url'] ) ) {
		return sprintf(
			'<img src="%s" alt="%s" class="lv-logo__img" width="%d" height="%d">',
			esc_url( $logo['url'] ),
			esc_attr( $nome ),
			(int) ( $logo['width'] ?? 200 ),
			(int) ( $logo['height'] ?? 60 )
		);
	}

	return '<span class="lv-logo__texto">' . esc_html( $nome ) . '</span>';
}
