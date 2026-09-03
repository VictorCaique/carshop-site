<?php
/**
 * Sobre a loja: texto + foto, com selo de anos de mercado.
 */

defined( 'ABSPATH' ) || exit;

$texto  = (string) lv_option( 'sobre_texto', '' );
$imagem = lv_option( 'sobre_imagem' );
$titulo = (string) lv_option( 'sobre_titulo', 'Quem somos' );
$ano    = (int) lv_option( 'ano_fundacao', 0 );
$anos   = $ano ? ( (int) current_time( 'Y' ) - $ano ) : 0;

if ( ! $texto && ! $imagem ) {
	return;
}
?>

<section class="lv-secao lv-sobre">
	<div class="lv-container lv-sobre__grid">

		<?php if ( is_array( $imagem ) && ! empty( $imagem['url'] ) ) : ?>
			<figure class="lv-sobre__midia">
				<img src="<?php echo esc_url( $imagem['url'] ); ?>"
					alt="<?php echo esc_attr( (string) lv_option( 'nome_loja', get_bloginfo( 'name' ) ) ); ?>"
					loading="lazy" decoding="async"
					width="<?php echo (int) ( $imagem['width'] ?? 800 ); ?>"
					height="<?php echo (int) ( $imagem['height'] ?? 600 ); ?>">

				<?php if ( $anos > 0 ) : ?>
					<figcaption class="lv-sobre__selo">
						<strong><?php echo (int) $anos; ?></strong>
						<span>anos de mercado</span>
					</figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>

		<div class="lv-sobre__texto">
			<h2 class="lv-secao__titulo"><?php echo esc_html( $titulo ); ?></h2>
			<div class="lv-prosa"><?php echo wp_kses_post( wpautop( $texto ) ); ?></div>

			<a class="lv-btn lv-btn--contorno" href="<?php echo esc_url( lv_url_vitrine() ); ?>">
				Conhecer o estoque
			</a>
		</div>

	</div>
</section>
