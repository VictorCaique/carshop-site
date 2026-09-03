<?php
/**
 * Barra de filtros da vitrine.
 * Tudo por GET: a URL fica compartilhavel e indexavel.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Monta um <select> de taxonomia so com termos que tem veiculo.
 */
function lv_select_taxonomia( string $taxonomia, string $label ): void {
	$termos = get_terms(
		[
			'taxonomy'   => $taxonomia,
			'hide_empty' => true,
			'orderby'    => 'name',
		]
	);

	if ( is_wp_error( $termos ) || ! $termos ) {
		return;
	}

	$atual = lv_filtro( $taxonomia );
	?>
	<p class="lv-filtros__campo">
		<label for="lv-f-<?php echo esc_attr( $taxonomia ); ?>"><?php echo esc_html( $label ); ?></label>
		<select id="lv-f-<?php echo esc_attr( $taxonomia ); ?>" name="<?php echo esc_attr( $taxonomia ); ?>">
			<option value="">Todas</option>
			<?php foreach ( $termos as $termo ) : ?>
				<option value="<?php echo esc_attr( $termo->slug ); ?>" <?php selected( $atual, $termo->slug ); ?>>
					<?php echo esc_html( $termo->name ); ?> (<?php echo (int) $termo->count; ?>)
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}
?>

<form class="lv-filtros" method="get" action="<?php echo esc_url( lv_url_vitrine() ); ?>" id="lv-filtros">

	<div class="lv-filtros__cabecalho">
		<h2 class="lv-filtros__titulo"><?php lv_icone( 'filtro' ); ?> Filtrar</h2>
		<button type="button" class="lv-filtros__fechar" aria-label="Fechar filtros">
			<?php lv_icone( 'fechar' ); ?>
		</button>
	</div>

	<p class="lv-filtros__campo">
		<label for="lv-f-busca">Buscar</label>
		<input type="search" id="lv-f-busca" name="busca" placeholder="Ex: Civic, SUV automatico"
			value="<?php echo esc_attr( lv_filtro( 'busca' ) ); ?>">
	</p>

	<?php
	lv_select_taxonomia( 'marca', 'Marca' );
	lv_select_taxonomia( 'carroceria', 'Carroceria' );
	lv_select_taxonomia( 'cambio', 'Cambio' );
	lv_select_taxonomia( 'combustivel', 'Combustivel' );
	?>

	<fieldset class="lv-filtros__grupo">
		<legend>Faixa de preco</legend>
		<div class="lv-filtros__dupla">
			<label class="screen-reader-text" for="lv-f-preco-min">Preco minimo</label>
			<input type="number" id="lv-f-preco-min" name="preco_min" min="0" step="1000" placeholder="De R$"
				value="<?php echo esc_attr( lv_filtro( 'preco_min' ) ); ?>">

			<label class="screen-reader-text" for="lv-f-preco-max">Preco maximo</label>
			<input type="number" id="lv-f-preco-max" name="preco_max" min="0" step="1000" placeholder="Ate R$"
				value="<?php echo esc_attr( lv_filtro( 'preco_max' ) ); ?>">
		</div>
	</fieldset>

	<div class="lv-filtros__dupla">
		<p class="lv-filtros__campo">
			<label for="lv-f-ano">Ano minimo</label>
			<input type="number" id="lv-f-ano" name="ano_min" min="1980" max="<?php echo esc_attr( (string) ( (int) current_time( 'Y' ) + 1 ) ); ?>"
				placeholder="2015" value="<?php echo esc_attr( lv_filtro( 'ano_min' ) ); ?>">
		</p>

		<p class="lv-filtros__campo">
			<label for="lv-f-km">KM maxima</label>
			<input type="number" id="lv-f-km" name="km_max" min="0" step="5000" placeholder="80000"
				value="<?php echo esc_attr( lv_filtro( 'km_max' ) ); ?>">
		</p>
	</div>

	<?php if ( lv_filtro( 'ordem' ) ) : ?>
		<input type="hidden" name="ordem" value="<?php echo esc_attr( lv_filtro( 'ordem' ) ); ?>">
	<?php endif; ?>

	<div class="lv-filtros__acoes">
		<button type="submit" class="lv-btn lv-btn--primaria lv-btn--bloco">Aplicar filtros</button>
		<?php if ( lv_tem_filtros() ) : ?>
			<a class="lv-btn lv-btn--texto lv-btn--bloco" href="<?php echo esc_url( lv_url_vitrine() ); ?>">Limpar</a>
		<?php endif; ?>
	</div>

</form>
