<?php
/**
 * Card do veiculo. Usado na vitrine, nos destaques e nos relacionados.
 */

defined( 'ABSPATH' ) || exit;

$id     = get_the_ID();
$status = lv_status( $id );
$promo  = lv_preco_original( $id );
?>
<article <?php post_class( 'lv-card' ); ?>>

	<a class="lv-card__midia" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php echo lv_capa( $id, 'lv-card', [ 'class' => 'lv-card__img', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<?php if ( 'disponivel' !== $status ) : ?>
			<span class="lv-selo lv-selo--<?php echo esc_attr( $status ); ?>">
				<?php echo esc_html( lv_status_label( $id ) ); ?>
			</span>
		<?php elseif ( $promo ) : ?>
			<span class="lv-selo lv-selo--promo">Oferta</span>
		<?php endif; ?>
	</a>

	<div class="lv-card__corpo">
		<?php if ( lv_termo( 'marca', $id ) ) : ?>
			<span class="lv-card__marca"><?php echo esc_html( lv_termo( 'marca', $id ) ); ?></span>
		<?php endif; ?>

		<h3 class="lv-card__titulo">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<ul class="lv-card__specs">
			<li><?php lv_icone( 'calendario', 'lv-icone lv-icone--sm' ); ?><?php echo esc_html( lv_ano( $id ) ); ?></li>
			<li><?php lv_icone( 'gauge', 'lv-icone lv-icone--sm' ); ?><?php echo esc_html( lv_km( $id ) ); ?></li>
			<?php if ( lv_termo( 'cambio', $id ) ) : ?>
				<li><?php lv_icone( 'cambio', 'lv-icone lv-icone--sm' ); ?><?php echo esc_html( lv_termo( 'cambio', $id ) ); ?></li>
			<?php endif; ?>
			<?php if ( lv_termo( 'combustivel', $id ) ) : ?>
				<li><?php lv_icone( 'combustivel', 'lv-icone lv-icone--sm' ); ?><?php echo esc_html( lv_termo( 'combustivel', $id ) ); ?></li>
			<?php endif; ?>
		</ul>

		<div class="lv-card__rodape">
			<div class="lv-card__preco">
				<?php if ( $promo ) : ?>
					<span class="lv-preco-antigo"><?php echo esc_html( $promo ); ?></span>
				<?php endif; ?>
				<strong><?php echo esc_html( lv_preco( $id ) ); ?></strong>
			</div>

			<a class="lv-btn lv-btn--sm lv-btn--primaria" href="<?php the_permalink(); ?>">
				Ver detalhes
			</a>
		</div>
	</div>

</article>
