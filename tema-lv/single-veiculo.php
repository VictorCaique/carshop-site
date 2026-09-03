<?php
/**
 * Ficha do veiculo.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$id = get_the_ID();
	?>

	<article <?php post_class( 'lv-single' ); ?>>

		<div class="lv-container">

			<nav class="lv-breadcrumb" aria-label="Voce esta aqui">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a>
				<span aria-hidden="true">/</span>
				<a href="<?php echo esc_url( lv_url_vitrine() ); ?>">Estoque</a>
				<?php if ( lv_termo( 'marca', $id ) ) : ?>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( (string) get_term_link( get_the_terms( $id, 'marca' )[0] ) ); ?>">
						<?php echo esc_html( lv_termo( 'marca', $id ) ); ?>
					</a>
				<?php endif; ?>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php the_title(); ?></span>
			</nav>

			<header class="lv-single__cabecalho">
				<h1 class="lv-single__titulo"><?php the_title(); ?></h1>
				<ul class="lv-single__resumo">
					<li><?php lv_icone( 'calendario', 'lv-icone lv-icone--sm' ); ?><?php echo esc_html( lv_ano( $id ) ); ?></li>
					<li><?php lv_icone( 'gauge', 'lv-icone lv-icone--sm' ); ?><?php echo esc_html( lv_km( $id ) ); ?></li>
					<?php if ( lv_termo( 'cambio', $id ) ) : ?>
						<li><?php lv_icone( 'cambio', 'lv-icone lv-icone--sm' ); ?><?php echo esc_html( lv_termo( 'cambio', $id ) ); ?></li>
					<?php endif; ?>
					<?php if ( lv_field( 'cor', $id ) ) : ?>
						<li><?php lv_icone( 'cor', 'lv-icone lv-icone--sm' ); ?><?php echo esc_html( (string) lv_field( 'cor', $id ) ); ?></li>
					<?php endif; ?>
				</ul>
			</header>

			<div class="lv-single__layout">

				<div class="lv-single__principal">
					<?php get_template_part( 'template-parts/galeria' ); ?>

					<?php if ( get_the_content() ) : ?>
						<section class="lv-single__descricao">
							<h2 class="lv-secao__titulo">Sobre este veiculo</h2>
							<div class="lv-prosa"><?php the_content(); ?></div>
						</section>
					<?php endif; ?>

					<?php get_template_part( 'template-parts/ficha-tecnica' ); ?>
				</div>

				<div class="lv-single__lateral">
					<?php get_template_part( 'template-parts/cta-whatsapp' ); ?>
				</div>

			</div>

			<?php
			$relacionados = function_exists( 'lv_relacionados' ) ? lv_relacionados( $id, 4 ) : [];
			if ( $relacionados ) :
				?>
				<section class="lv-relacionados">
					<h2 class="lv-secao__titulo">Veiculos parecidos</h2>
					<div class="lv-grid lv-grid--cards">
						<?php
						global $post;
						foreach ( $relacionados as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride
							setup_postdata( $post );
							get_template_part( 'template-parts/card-veiculo' );
						endforeach;
						wp_reset_postdata();
						?>
					</div>
				</section>
			<?php endif; ?>

		</div>
	</article>

	<?php
endwhile;

get_footer();
