<?php
/**
 * 404: nunca deixa o visitante numa rua sem saida - manda pra vitrine.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="lv-container lv-erro404">
	<p class="lv-erro404__codigo">404</p>
	<h1 class="lv-erro404__titulo">Essa pagina saiu do patio</h1>
	<p class="lv-erro404__texto">
		O link pode ter mudado ou o veiculo ja foi vendido. Veja o que temos disponivel agora.
	</p>

	<div class="lv-erro404__acoes">
		<a class="lv-btn lv-btn--primaria lv-btn--lg" href="<?php echo esc_url( lv_url_vitrine() ); ?>">
			Ver o estoque
		</a>
		<a class="lv-btn lv-btn--texto" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			Voltar para o inicio
		</a>
	</div>
</section>

<?php
$recentes = get_posts(
	[
		'post_type'      => 'veiculo',
		'posts_per_page' => 4,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	]
);

if ( $recentes ) :
	?>
	<section class="lv-secao lv-container">
		<h2 class="lv-secao__titulo">Chegaram por ultimo</h2>
		<div class="lv-grid lv-grid--cards">
			<?php
			global $post;
			foreach ( $recentes as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				setup_postdata( $post );
				get_template_part( 'template-parts/card-veiculo' );
			endforeach;
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
endif;

get_footer();
