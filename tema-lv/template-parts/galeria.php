<?php
/**
 * Galeria da ficha do veiculo. Lightbox e swipe ficam no JS (assets/js/galeria.js),
 * sem biblioteca externa.
 */

defined( 'ABSPATH' ) || exit;

$id  = get_the_ID();
$ids = lv_galeria_ids( $id );

if ( ! $ids ) {
	echo '<div class="lv-galeria lv-galeria--vazia">';
	echo lv_capa( $id, 'lv-galeria' ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</div>';
	return;
}

$status = lv_status( $id );
?>

<div class="lv-galeria" id="lv-galeria" data-total="<?php echo esc_attr( (string) count( $ids ) ); ?>">

	<div class="lv-galeria__palco">
		<?php if ( 'disponivel' !== $status ) : ?>
			<span class="lv-selo lv-selo--<?php echo esc_attr( $status ); ?> lv-selo--grande">
				<?php echo esc_html( lv_status_label( $id ) ); ?>
			</span>
		<?php endif; ?>

		<button class="lv-galeria__seta lv-galeria__seta--esq" type="button" aria-label="Foto anterior">
			<?php lv_icone( 'seta-esq' ); ?>
		</button>

		<ul class="lv-galeria__slides" role="list">
			<?php foreach ( $ids as $i => $img_id ) : ?>
				<li class="lv-galeria__slide<?php echo 0 === $i ? ' is-ativo' : ''; ?>" data-indice="<?php echo (int) $i; ?>">
					<?php
					echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput
						(int) $img_id,
						'lv-galeria',
						false,
						[
							'class'    => 'lv-galeria__img',
							'loading'  => 0 === $i ? 'eager' : 'lazy',
							'fetchpriority' => 0 === $i ? 'high' : 'auto',
							'decoding' => 'async',
							'alt'      => get_the_title( $id ) . ' - foto ' . ( $i + 1 ),
						]
					);
					?>
				</li>
			<?php endforeach; ?>
		</ul>

		<button class="lv-galeria__seta lv-galeria__seta--dir" type="button" aria-label="Proxima foto">
			<?php lv_icone( 'seta-dir' ); ?>
		</button>

		<span class="lv-galeria__contador"><span data-atual>1</span>/<?php echo (int) count( $ids ); ?></span>
	</div>

	<?php if ( count( $ids ) > 1 ) : ?>
		<ul class="lv-galeria__thumbs" role="list">
			<?php foreach ( $ids as $i => $img_id ) : ?>
				<li>
					<button type="button" class="lv-galeria__thumb<?php echo 0 === $i ? ' is-ativo' : ''; ?>"
						data-ir-para="<?php echo (int) $i; ?>"
						aria-label="Ver foto <?php echo (int) ( $i + 1 ); ?>">
						<?php
						echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput
							(int) $img_id,
							'lv-thumb',
							false,
							[ 'loading' => 'lazy', 'alt' => '' ]
						);
						?>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

</div>
