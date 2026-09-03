<?php
/**
 * Vitrine: /veiculos/ e as paginas de taxonomia.
 */

defined( 'ABSPATH' ) || exit;

get_header();

global $wp_query;
$total = (int) $wp_query->found_posts;

$titulo = 'Estoque';
if ( is_tax() ) {
	$termo  = get_queried_object();
	$titulo = $termo instanceof WP_Term ? $termo->name : $titulo;
}
?>

<div class="lv-vitrine">

	<header class="lv-vitrine__topo">
		<div class="lv-container">
			<h1 class="lv-vitrine__titulo"><?php echo esc_html( $titulo ); ?></h1>
			<p class="lv-vitrine__contagem">
				<?php
				printf(
					'%d %s %s',
					$total,
					1 === $total ? 'veiculo' : 'veiculos',
					lv_tem_filtros() ? 'encontrados com os filtros aplicados' : 'no estoque'
				);
				?>
			</p>
		</div>
	</header>

	<div class="lv-container lv-vitrine__layout">

		<button class="lv-btn lv-btn--contorno lv-vitrine__abrir-filtros" type="button"
			aria-expanded="false" aria-controls="lv-filtros">
			<?php lv_icone( 'filtro' ); ?> Filtrar
		</button>

		<aside class="lv-vitrine__aside" id="lv-vitrine-aside">
			<?php get_template_part( 'template-parts/filtros' ); ?>
		</aside>

		<div class="lv-vitrine__conteudo">

			<div class="lv-vitrine__barra">
				<form method="get" class="lv-ordenacao" action="<?php echo esc_url( lv_url_vitrine() ); ?>">
					<?php
					// Preserva os filtros ativos ao trocar a ordenacao.
					foreach ( [ 'marca', 'carroceria', 'cambio', 'combustivel', 'preco_min', 'preco_max', 'ano_min', 'km_max', 'busca' ] as $campo ) {
						if ( lv_filtro( $campo ) ) {
							printf(
								'<input type="hidden" name="%s" value="%s">',
								esc_attr( $campo ),
								esc_attr( lv_filtro( $campo ) )
							);
						}
					}
					?>
					<label for="lv-ordem">Ordenar por</label>
					<select id="lv-ordem" name="ordem" data-auto-submit>
						<?php foreach ( lv_opcoes_ordem() as $valor => $label ) : ?>
							<option value="<?php echo esc_attr( $valor ); ?>" <?php selected( lv_filtro( 'ordem' ), $valor ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<noscript><button type="submit" class="lv-btn lv-btn--sm">Ordenar</button></noscript>
				</form>
			</div>

			<?php if ( have_posts() ) : ?>

				<div class="lv-grid lv-grid--cards">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/card-veiculo' );
					endwhile;
					?>
				</div>

				<nav class="lv-paginacao" aria-label="Paginacao">
					<?php
					echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput
						[
							'prev_text' => lv_icone_html( 'seta-esq' ) . ' Anterior',
							'next_text' => 'Proxima ' . lv_icone_html( 'seta-dir' ),
							'type'      => 'plain',
							'mid_size'  => 1,
						]
					);
					?>
				</nav>

			<?php else : ?>

				<div class="lv-vazio">
					<?php lv_icone( 'busca', 'lv-icone lv-icone--xl' ); ?>
					<h2>Nenhum carro encontrado com esses filtros</h2>
					<p>Tente ampliar a faixa de preco ou remover algum filtro.</p>
					<div class="lv-vazio__acoes">
						<a class="lv-btn lv-btn--primaria" href="<?php echo esc_url( lv_url_vitrine() ); ?>">
							Ver todo o estoque
						</a>
						<?php
						$wpp = lv_whatsapp_link( 0, '', 'Ola! Nao achei no site o carro que procuro. Voces conseguem me ajudar?' );
						if ( $wpp ) :
							?>
							<a class="lv-btn lv-btn--whatsapp" href="<?php echo esc_url( $wpp ); ?>" target="_blank" rel="noopener">
								<?php lv_icone( 'whatsapp' ); ?> Procurar pra mim
							</a>
						<?php endif; ?>
					</div>
				</div>

			<?php endif; ?>

		</div>
	</div>
</div>

<?php
get_footer();
