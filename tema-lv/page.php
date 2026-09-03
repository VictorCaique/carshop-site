<?php
/**
 * Pagina fixa (Sobre, Contato, Politica de Privacidade).
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<article <?php post_class( 'lv-pagina' ); ?>>
		<header class="lv-pagina__cabecalho">
			<div class="lv-container">
				<h1 class="lv-pagina__titulo"><?php the_title(); ?></h1>
			</div>
		</header>

		<div class="lv-container lv-pagina__corpo">
			<div class="lv-prosa">
				<?php
				the_content();

				wp_link_pages(
					[
						'before' => '<nav class="lv-paginacao">',
						'after'  => '</nav>',
					]
				);
				?>
			</div>

			<?php if ( has_shortcode( (string) get_the_content(), 'lv_contato' ) || 'contato' === get_post_field( 'post_name' ) ) : ?>
				<?php get_template_part( 'template-parts/form-contato' ); ?>
			<?php endif; ?>
		</div>
	</article>

	<?php
endwhile;

get_footer();
