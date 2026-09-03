<?php
/**
 * Destaques da home: veiculos com destaque = true.
 * Carrossel no mobile (scroll-snap puro, sem JS), grid no desktop.
 */

defined( 'ABSPATH' ) || exit;

$destaques = new WP_Query(
	[
		'post_type'      => 'veiculo',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'no_found_rows'  => true,
		'meta_query'     => [
			'relation' => 'AND',
			[ 'key' => 'destaque', 'value' => '1', 'compare' => '=' ],
			[
				'relation' => 'OR',
				[ 'key' => 'status_veiculo', 'value' => 'vendido', 'compare' => '!=' ],
				[ 'key' => 'status_veiculo', 'compare' => 'NOT EXISTS' ],
			],
		],
	]
);

// Sem destaques marcados, mostra os mais recentes: a home nunca fica vazia.
if ( ! $destaques->have_posts() ) {
	$destaques = new WP_Query(
		[
			'post_type'      => 'veiculo',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'no_found_rows'  => true,
		]
	);
}

if ( ! $destaques->have_posts() ) {
	return;
}
?>

<section class="lv-secao lv-destaques">
	<div class="lv-container">

		<header class="lv-secao__cabecalho">
			<h2 class="lv-secao__titulo">Destaques do estoque</h2>
			<a class="lv-link-mais" href="<?php echo esc_url( lv_url_vitrine() ); ?>">
				Ver todos <?php lv_icone( 'seta-dir', 'lv-icone lv-icone--sm' ); ?>
			</a>
		</header>

		<div class="lv-grid lv-grid--cards lv-grid--carrossel">
			<?php
			while ( $destaques->have_posts() ) :
				$destaques->the_post();
				get_template_part( 'template-parts/card-veiculo' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>

	</div>
</section>
