<?php
/**
 * Depoimentos: 3 cards.
 */

defined( 'ABSPATH' ) || exit;

$depoimentos = (array) lv_option( 'depoimentos', [] );

if ( ! $depoimentos ) {
	return;
}
?>

<section class="lv-secao lv-depoimentos">
	<div class="lv-container">
		<h2 class="lv-secao__titulo lv-secao__titulo--centro">O que dizem nossos clientes</h2>

		<ul class="lv-depoimentos__lista" role="list">
			<?php foreach ( array_slice( $depoimentos, 0, 6 ) as $d ) : ?>
				<li class="lv-depoimento">
					<?php $nota = (int) ( $d['nota'] ?? 5 ); ?>
					<div class="lv-depoimento__notas" aria-label="<?php echo esc_attr( $nota . ' de 5 estrelas' ); ?>">
						<?php for ( $i = 0; $i < $nota; $i++ ) : ?>
							<?php lv_icone( 'star', 'lv-icone lv-icone--sm lv-icone--estrela' ); ?>
						<?php endfor; ?>
					</div>

					<blockquote class="lv-depoimento__texto">
						<p><?php echo esc_html( (string) ( $d['texto'] ?? '' ) ); ?></p>
					</blockquote>

					<cite class="lv-depoimento__autor"><?php echo esc_html( (string) ( $d['nome'] ?? '' ) ); ?></cite>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
