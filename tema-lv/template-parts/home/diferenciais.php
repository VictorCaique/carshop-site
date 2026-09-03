<?php
/**
 * Diferenciais: 3 ou 4 blocos com icone, vindos das configuracoes.
 */

defined( 'ABSPATH' ) || exit;

$diferenciais = (array) lv_option( 'diferenciais', [] );

if ( ! $diferenciais ) {
	return;
}
?>

<section class="lv-secao lv-diferenciais">
	<div class="lv-container">
		<ul class="lv-diferenciais__lista" role="list">
			<?php foreach ( $diferenciais as $d ) : ?>
				<li class="lv-diferencial">
					<span class="lv-diferencial__icone">
						<?php lv_icone( (string) ( $d['icone'] ?? 'check' ), 'lv-icone lv-icone--lg' ); ?>
					</span>
					<h3 class="lv-diferencial__titulo"><?php echo esc_html( (string) ( $d['titulo'] ?? '' ) ); ?></h3>
					<?php if ( ! empty( $d['texto'] ) ) : ?>
						<p class="lv-diferencial__texto"><?php echo esc_html( (string) $d['texto'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
