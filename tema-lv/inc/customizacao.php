<?php
/**
 * Identidade visual por loja.
 *
 * Regra do template: nenhuma cor literal no CSS do tema. Se voce escreveu
 * #0B3D91 dentro de um arquivo .css, errou - a cor vem daqui.
 *
 * Os tokens saem em tres camadas:
 *   1. lv_tokens_base()     - o que nao muda entre claro e escuro (marca, fonte, geometria)
 *   2. lv_tokens_esquema()  - neutros e derivados, uma versao por esquema
 *   3. os blocos [data-tema] - o que o visitante escolhe no botao do menu
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

// ---------------------------------------------------------------------------
// Aritmetica de cor
// ---------------------------------------------------------------------------

/**
 * Normaliza um hex (aceita #abc) e devolve os tres canais 0-255.
 * Cor invalida cai no preto - e melhor um site feio do que um CSS quebrado.
 */
function lv_cor_canais( string $hex ): array {
	$hex = ltrim( trim( $hex ), '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return [ 0, 0, 0 ];
	}

	return [
		(int) hexdec( substr( $hex, 0, 2 ) ),
		(int) hexdec( substr( $hex, 2, 2 ) ),
		(int) hexdec( substr( $hex, 4, 2 ) ),
	];
}

function lv_cor_hex( array $canais ): string {
	$saida = '#';
	foreach ( $canais as $valor ) {
		$saida .= str_pad( dechex( (int) max( 0, min( 255, $valor ) ) ), 2, '0', STR_PAD_LEFT );
	}
	return $saida;
}

/**
 * Escurece ou clareia um hex. Usado para os estados :hover sem pedir mais
 * uma cor ao cliente.
 */
function lv_ajustar_cor( string $hex, int $percentual ): string {
	$canais = lv_cor_canais( $hex );

	foreach ( $canais as $i => $valor ) {
		$canais[ $i ] = (int) ( $valor + ( $valor * $percentual / 100 ) );
	}

	return lv_cor_hex( $canais );
}

/**
 * Mistura duas cores. $peso e quanto de $alvo entra, de 0 a 100.
 *
 * Diferente de lv_ajustar_cor(), funciona no escuro: clarear #0F1115 em 6%
 * nao move nada (6% de 15 e 1), misturar 6% de branco move.
 */
function lv_misturar_cor( string $hex, string $alvo, float $peso ): string {
	$a = lv_cor_canais( $hex );
	$b = lv_cor_canais( $alvo );
	$p = max( 0, min( 100, $peso ) ) / 100;

	foreach ( $a as $i => $valor ) {
		$a[ $i ] = (int) round( $valor + ( $b[ $i ] - $valor ) * $p );
	}

	return lv_cor_hex( $a );
}

/**
 * Luminancia relativa (WCAG 2.1).
 */
function lv_cor_luminancia( string $hex ): float {
	$canal = static function ( int $c ): float {
		$c /= 255;
		return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
	};

	[ $r, $g, $b ] = lv_cor_canais( $hex );

	return 0.2126 * $canal( $r ) + 0.7152 * $canal( $g ) + 0.0722 * $canal( $b );
}

/**
 * Razao de contraste entre duas cores, de 1 a 21.
 */
function lv_cor_contraste( string $a, string $b ): float {
	$la = lv_cor_luminancia( $a );
	$lb = lv_cor_luminancia( $b );

	return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
}

/**
 * Texto legivel sobre uma cor de fundo: branco ou quase-preto, o que contrastar
 * mais. E o que evita "Comprar" branco em cima de um amarelo claro.
 */
function lv_cor_texto_sobre( string $fundo ): string {
	return lv_cor_contraste( $fundo, '#FFFFFF' ) >= lv_cor_contraste( $fundo, '#101418' )
		? '#FFFFFF'
		: '#101418';
}

/**
 * Aproxima a cor do branco (ou do preto) ate ela ficar legivel sobre $fundo.
 *
 * No modo escuro o azul-marinho da loja some no preto: em vez de descartar a
 * cor da marca, clareamos ate o contraste minimo e continuamos reconheciveis.
 */
function lv_cor_legivel_em( string $cor, string $fundo, float $minimo = 4.5 ): string {
	$alvo = lv_cor_luminancia( $fundo ) < 0.5 ? '#FFFFFF' : '#000000';

	for ( $peso = 0; $peso <= 100; $peso += 4 ) {
		$tentativa = lv_misturar_cor( $cor, $alvo, $peso );
		if ( lv_cor_contraste( $tentativa, $fundo ) >= $minimo ) {
			return $tentativa;
		}
	}

	return $alvo;
}

/**
 * Converte hex em "r, g, b" para usar dentro de rgb() com alpha.
 */
function lv_cor_rgb( string $hex ): string {
	return implode( ', ', lv_cor_canais( $hex ) );
}

// ---------------------------------------------------------------------------
// Esquema de cores
// ---------------------------------------------------------------------------

/**
 * 'claro' | 'escuro' | 'automatico'.
 */
function lv_esquema_cores(): string {
	$escolha = (string) lv_option( 'esquema_cores', 'claro' );

	return in_array( $escolha, [ 'claro', 'escuro', 'automatico' ], true ) ? $escolha : 'claro';
}

/**
 * O visitante pode trocar de tema pelo menu?
 */
function lv_alternador_tema_ativo(): bool {
	return (bool) lv_option( 'alternador_tema', 0 );
}

/**
 * Tokens que nao dependem do esquema: marca, tipografia e geometria.
 */
function lv_tokens_base(): array {
	$secundaria = (string) ( lv_option( 'cor_secundaria' ) ?: '#111827' );
	$destaque   = (string) ( lv_option( 'cor_destaque' ) ?: '#F59E0B' );
	$fonte      = lv_preset_fonte_atual();

	$raios = [
		'reto'        => [ '0px', '0px' ],
		'suave'       => [ '12px', '8px' ],
		'arredondado' => [ '20px', '14px' ],
	];
	$raio = $raios[ (string) lv_option( 'raio_cantos', 'suave' ) ] ?? $raios['suave'];

	$larguras = [
		'compacto' => '1080px',
		'padrao'   => '1200px',
		'amplo'    => '1360px',
	];
	$largura = $larguras[ (string) lv_option( 'largura_conteudo', 'padrao' ) ] ?? $larguras['padrao'];

	// O campo e number: o min/max do HTML e so dica de navegador, quem garante
	// o intervalo e isto aqui.
	$topbar = max( 56, min( 120, (int) lv_option( 'altura_topbar', 72 ) ) );
	$logo   = max( 24, min( 96, (int) lv_option( 'altura_logo', 48 ) ) );

	// A barra nunca pode ser mais baixa que o proprio logo: no mobile a gaveta
	// do menu e posicionada com inset: var(--header-altura), entao um token
	// menor que a barra real faria o menu abrir por cima do cabecalho.
	$header = max( $topbar, $logo + 20 );

	// No celular a barra e fixa e rouba altura util da tela: teto mais baixo,
	// mantendo a mesma folga entre logo e barra.
	$logo_mob   = min( $logo, 48 );
	$header_mob = max( min( $header, 76 ), $logo_mob + 20 );

	// Padding lateral da barra. Na tela pequena a barra ja e apertada: teto
	// proprio para o logo e o menu nao se espremerem.
	$padding     = max( 0, min( 64, (int) lv_option( 'padding_topbar', 0 ) ) );
	$padding_mob = min( $padding, 16 );

	// O respiro vertical de cada faixa. Sao clamps porque o espaco tem que
	// encolher junto com a tela - um valor fixo que respira no desktop vira
	// meia tela vazia no celular.
	$espacos = [
		'compacto' => 'clamp(28px, 4vw, 48px)',
		'padrao'   => 'clamp(40px, 6vw, 72px)',
		'arejado'  => 'clamp(56px, 8vw, 104px)',
	];
	$espaco = $espacos[ (string) lv_option( 'espaco_secoes', 'padrao' ) ] ?? $espacos['padrao'];

	// Opacidades do degrade sobre a foto do hero: inicio (esquerda) e fim.
	$overlays = [
		'leve'  => [ '.55', '.10' ],
		'medio' => [ '.78', '.35' ],
		'forte' => [ '.90', '.62' ],
	];
	$overlay = $overlays[ (string) lv_option( 'hero_escurecer', 'medio' ) ] ?? $overlays['medio'];

	return [
		'--cor-secundaria'         => $secundaria,
		'--cor-secundaria-clara'   => lv_ajustar_cor( $secundaria, 30 ),

		// A secundaria escurecida ate contrastar com branco. E o que sustenta
		// as superficies que sao escuras nos dois esquemas (fundo do hero, seta
		// da galeria) mesmo quando a loja escolhe uma secundaria clara - sem
		// isso o hero fica branco com titulo branco.
		'--cor-marca-escura'       => lv_cor_legivel_em( $secundaria, '#FFFFFF', 4.5 ),

		// Os selos ficam em cima da foto do veiculo, nunca sobre o fundo da
		// pagina: nao seguem o esquema, so precisam segurar texto branco.
		'--cor-selo-vendido'       => '#B32D2E',
		'--cor-selo-reservado'     => '#8A5300',

		'--cor-destaque'           => $destaque,
		'--cor-destaque-escura'    => lv_ajustar_cor( $destaque, -18 ),
		'--cor-destaque-contraste' => lv_cor_texto_sobre( $destaque ),

		// Cor de marca do WhatsApp - fixa por definicao, nao e da loja.
		'--cor-whatsapp'           => '#25D366',
		'--cor-whatsapp-escura'    => '#1FBB59',
		'--cor-whatsapp-texto'     => '#05321A',

		// Neutros para texto sobre fundo escuro/colorido - valem nos dois esquemas.
		'--cor-branco'             => '#FFFFFF',
		'--cor-inverso'            => '#E5E7EB',
		'--cor-inverso-suave'      => '#9CA3AF',

		'--fonte-titulo'           => $fonte['titulo'],
		'--fonte-corpo'            => $fonte['corpo'],
		'--peso-titulo'            => $fonte['peso_titulo'],

		'--raio'                   => $raio[0],
		'--raio-sm'                => $raio[1],
		'--raio-pill'              => '999px',
		'--container'              => $largura,
		'--gap'                    => '24px',

		'--secao-espaco'           => $espaco,

		'--topbar-padding'         => $padding . 'px',
		'--topbar-padding-mob'     => $padding_mob . 'px',
		'--logo-altura'            => $logo . 'px',
		'--logo-altura-mob'        => $logo_mob . 'px',
		'--header-altura'          => $header . 'px',
		'--header-altura-mob'      => $header_mob . 'px',

		'--hero-overlay'           => sprintf(
			'linear-gradient(100deg, rgb(0 0 0 / %s) 20%%, rgb(0 0 0 / %s) 100%%)',
			$overlay[0],
			$overlay[1]
		),
	];
}

/**
 * Sombras. No escuro, preto sobre preto nao aparece: a mesma sombra precisa
 * de bem mais alpha para continuar separando o card do fundo.
 */
function lv_tokens_sombra( bool $escuro ): array {
	$niveis = [
		'nenhuma'   => null,
		'suave'     => [ .10, .06, .08 ],
		'destacada' => [ .18, .14, .14 ],
	];
	$nivel = $niveis[ (string) lv_option( 'sombra_cards', 'suave' ) ] ?? $niveis['suave'];

	if ( ! $nivel ) {
		return [ '--sombra' => 'none', '--sombra-sm' => 'none' ];
	}

	$fator = $escuro ? 4 : 1;
	$alpha = static fn( float $a ): string => rtrim( rtrim( number_format( min( 0.7, $a * $fator ), 2, '.', '' ), '0' ), '.' );

	return [
		'--sombra'    => sprintf(
			'0 1px 3px rgb(0 0 0 / %s), 0 8px 24px rgb(0 0 0 / %s)',
			$alpha( $nivel[0] ),
			$alpha( $nivel[1] )
		),
		'--sombra-sm' => sprintf( '0 1px 2px rgb(0 0 0 / %s)', $alpha( $nivel[2] ) ),
	];
}

/**
 * Neutros e derivados da primaria para um esquema ('claro' ou 'escuro').
 * Sao exatamente os tokens que trocam quando o visitante muda de tema.
 */
function lv_tokens_esquema( string $esquema ): array {
	$esquema  = 'escuro' === $esquema ? 'escuro' : 'claro';
	$escuro   = 'escuro' === $esquema;
	$primaria = (string) ( lv_option( 'cor_primaria' ) ?: '#0B3D91' );

	if ( $escuro ) {
		$fundo = (string) ( lv_option( 'cor_fundo_escuro' ) ?: '#0F1115' );

		// A primaria da loja sobre o fundo escuro, clareada ate dar para ler.
		$primaria = lv_cor_legivel_em( $primaria, $fundo, 4.5 );

		$tokens = [
			'--cor-texto'        => lv_misturar_cor( $fundo, '#FFFFFF', 88 ),
			'--cor-texto-suave'  => lv_misturar_cor( $fundo, '#FFFFFF', 60 ),
			'--cor-titulo'       => lv_misturar_cor( $fundo, '#FFFFFF', 96 ),
			'--cor-fundo'        => $fundo,
			'--cor-fundo-alt'    => lv_misturar_cor( $fundo, '#FFFFFF', 6 ),
			'--cor-borda'        => lv_misturar_cor( $fundo, '#FFFFFF', 18 ),

			// O rodape e um degrau acima do fundo: no escuro a cor da marca
			// encostaria no preto e a divisa sumiria.
			'--cor-rodape-fundo' => lv_misturar_cor( $fundo, '#FFFFFF', 4 ),
			'--cor-rodape-linha' => lv_misturar_cor( $fundo, '#FFFFFF', 22 ),

			// O selo fica em cima da foto do veiculo, entao ele precisa de um
			// cinza opaco proprio - nao serve o --cor-texto-suave do esquema.
			'--cor-selo-fundo'   => lv_misturar_cor( $fundo, '#FFFFFF', 38 ),

			// Verde/ambar/vermelho escuros nao passam de 3:1 no fundo preto.
			'--cor-sucesso'      => '#3DD68C',
			'--cor-alerta'       => '#F5B841',
			'--cor-erro'         => '#F87171',
		];

		// No escuro o :hover clareia; escurecer aproximaria a cor do fundo.
		$tokens['--cor-primaria']        = $primaria;
		$tokens['--cor-primaria-escura'] = lv_misturar_cor( $primaria, '#FFFFFF', 18 );
		$tokens['--cor-primaria-clara']  = lv_misturar_cor( $primaria, $fundo, 55 );
	} else {
		$secundaria = (string) ( lv_option( 'cor_secundaria' ) ?: '#111827' );
		$rodape     = lv_cor_legivel_em( $secundaria, '#E5E7EB', 4.5 );

		$tokens = [
			'--cor-texto'        => '#1F2937',
			'--cor-texto-suave'  => '#6B7280',
			'--cor-fundo'        => '#FFFFFF',
			'--cor-fundo-alt'    => '#F9FAFB',
			'--cor-borda'        => '#E5E7EB',

			// Titulo e rodape derivam da secundaria, mas nao podem sumir: o
			// titulo precisa contrastar com o fundo branco e o rodape com o
			// --cor-inverso do texto que ele carrega.
			'--cor-titulo'       => lv_cor_legivel_em( $secundaria, '#FFFFFF', 7 ),
			'--cor-rodape-fundo' => $rodape,
			'--cor-rodape-linha' => lv_misturar_cor( $rodape, '#FFFFFF', 14 ),

			'--cor-selo-fundo'   => '#6B7280',

			'--cor-sucesso'      => '#0A7C2F',
			'--cor-alerta'       => '#B26B00',
			'--cor-erro'         => '#B32D2E',
		];

		$tokens['--cor-primaria']        = $primaria;
		$tokens['--cor-primaria-escura'] = lv_ajustar_cor( $primaria, -18 );
		$tokens['--cor-primaria-clara']  = lv_ajustar_cor( $primaria, 45 );
	}

	$tokens['--cor-primaria-rgb']       = lv_cor_rgb( $tokens['--cor-primaria'] );
	$tokens['--cor-primaria-contraste'] = lv_cor_texto_sobre( $tokens['--cor-primaria'] );

	// Bandeira lida pelo JS do alternador. Quem resolve a cascata (esquema da
	// loja, prefers-color-scheme, [data-tema]) e o CSS; o JS so pergunta.
	$tokens['--lv-esquema'] = $esquema;

	// Nao e token: e o que faz a barra de rolagem, o seletor de data e o menu
	// do <select> nativos acompanharem o tema.
	$tokens['color-scheme'] = $escuro ? 'dark' : 'light';

	return $tokens + lv_tokens_sombra( $escuro );
}

/**
 * Imprime um bloco de declaracoes CSS a partir do mapa de tokens.
 *
 * Aqui NAO se usa esc_attr(): dentro de <style> o navegador nao decodifica
 * entidades, entao um &#039; viraria literal e quebraria "'Inter', sans-serif".
 * Em vez disso, lista branca de caracteres - o que importa e nunca deixar
 * passar um "<" que feche a tag.
 */
function lv_css_valor( string $valor ): string {
	return (string) preg_replace( '/[^A-Za-z0-9 ,.%#()\/\'"_-]/', '', $valor );
}

function lv_imprimir_tokens( array $tokens ): void {
	foreach ( $tokens as $nome => $valor ) {
		echo "\t\t" . lv_css_valor( (string) $nome ) . ': ' . lv_css_valor( (string) $valor ) . ";\n";
	}
}

/**
 * Antes da primeira pintura: aplica o tema que o visitante escolheu.
 * Inline e sincrono de proposito - um script deferido pisca a tela branca.
 */
add_action( 'wp_head', function (): void {
	if ( ! lv_alternador_tema_ativo() ) {
		return;
	}
	?>
	<script id="lv-tema-inicial">
	try{var t=localStorage.getItem('lv-tema');if('claro'===t||'escuro'===t){document.documentElement.dataset.tema=t}}catch(e){}
	</script>
	<?php
}, 5 );

/**
 * Tokens de design injetados no head.
 */
add_action( 'wp_head', function (): void {
	$esquema = lv_esquema_cores();
	$claro   = lv_tokens_esquema( 'claro' );
	$escuro  = lv_tokens_esquema( 'escuro' );

	// Os blocos [data-tema] so existem se alguem puder trocar de tema: no
	// automatico, o proprio aparelho; com o botao, o visitante.
	$alternavel = lv_alternador_tema_ativo() || 'automatico' === $esquema;
	?>
	<style id="lv-tokens">
	:root{
<?php lv_imprimir_tokens( lv_tokens_base() ); ?>
<?php lv_imprimir_tokens( 'escuro' === $esquema ? $escuro : $claro ); ?>
	}
	<?php if ( 'automatico' === $esquema ) : ?>
	@media (prefers-color-scheme: dark){
		:root:not([data-tema="claro"]){
<?php lv_imprimir_tokens( $escuro ); ?>
		}
	}
	<?php endif; ?>
	<?php if ( $alternavel ) : ?>
	/* Mesma especificidade da media query acima: quem vem depois vence, e a
	   escolha explicita do visitante tem que vencer a do aparelho. */
	:root[data-tema="claro"]{
<?php lv_imprimir_tokens( $claro ); ?>
	}
	:root[data-tema="escuro"]{
<?php lv_imprimir_tokens( $escuro ); ?>
	}
	<?php endif; ?>
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
 * Botao de claro/escuro. Sem JS o botao nao aparece: ele nao teria o que fazer.
 */
function lv_botao_tema(): void {
	if ( ! lv_alternador_tema_ativo() ) {
		return;
	}
	?>
	<button type="button" class="lv-tema-toggle" data-lv-tema hidden
		aria-pressed="false" aria-label="Alternar entre tema claro e escuro">
		<?php lv_icone( 'sol', 'lv-icone lv-tema-toggle__sol' ); ?>
		<?php lv_icone( 'lua', 'lv-icone lv-tema-toggle__lua' ); ?>
		<span class="lv-tema-toggle__texto">Tema escuro</span>
	</button>
	<?php
}

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
