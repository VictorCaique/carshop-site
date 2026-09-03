<?php
/**
 * Fallback obrigatorio do WordPress. Serve tambem para a busca do site.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="lv-container lv-lista">

	<?php if ( is_search() ) : ?>
		<header class="lv-lista__cabecalho">
			<h1 class="lv-pagina__titulo">
				Resultados para &ldquo;<?php echo esc_html( get_search_query() ); ?>&rdquo;
			</h1>
		</header>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>

		<div class="lv-grid lv-grid--cards">
			<?php
			while ( have_posts() ) :
				the_post();

				if ( 'veiculo' === get_post_type() ) {
					get_template_part( 'template-parts/card-veiculo' );
					continue;
				}
				?>
				<article <?php post_class( 'lv-card lv-card--post' ); ?>>
					<div class="lv-card__corpo">
						<h2 class="lv-card__titulo">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					</div>
				</article>
				<?php
			endwhile;
			?>
		</div>

		<nav class="lv-paginacao" aria-label="Paginacao">
			<?php
			echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput
				[
					'prev_text' => 'Anterior',
					'next_text' => 'Proxima',
					'type'      => 'plain',
				]
			);
			?>
		</nav>

	<?php else : ?>

		<div class="lv-vazio">
			<?php lv_icone( 'busca', 'lv-icone lv-icone--xl' ); ?>
			<h2>Nada encontrado</h2>
			<p>Tente outra busca ou veja o estoque completo.</p>
			<a class="lv-btn lv-btn--primaria" href="<?php echo esc_url( lv_url_vitrine() ); ?>">Ver o estoque</a>
		</div>

	<?php endif; ?>

</div>

<?php
get_footer();
