<?php
/**
 * Hero da home: imagem de fundo + busca rapida.
 */

defined( 'ABSPATH' ) || exit;

$imagem    = lv_option( 'hero_imagem' );
$url_bg    = is_array( $imagem ) ? (string) ( $imagem['url'] ?? '' ) : '';
$titulo    = (string) lv_option( 'hero_titulo', 'O carro certo, sem enrolacao' );
$subtitulo = (string) lv_option( 'hero_subtitulo', 'Seminovos revisados, com garantia e transferencia inclusa.' );

$marcas = get_terms(
	[ 'taxonomy' => 'marca', 'hide_empty' => true, 'orderby' => 'name' ]
);
?>

<section class="lv-hero"<?php echo $url_bg ? ' style="--hero-bg:url(' . esc_url( $url_bg ) . ')"' : ''; ?>>
	<div class="lv-container lv-hero__inner">

		<h1 class="lv-hero__titulo"><?php echo esc_html( $titulo ); ?></h1>
		<p class="lv-hero__subtitulo"><?php echo esc_html( $subtitulo ); ?></p>

		<form class="lv-busca-rapida" method="get" action="<?php echo esc_url( lv_url_vitrine() ); ?>">
			<p class="lv-busca-rapida__campo">
				<label for="lv-hero-marca">Marca</label>
				<select id="lv-hero-marca" name="marca">
					<option value="">Todas as marcas</option>
					<?php if ( ! is_wp_error( $marcas ) ) : ?>
						<?php foreach ( $marcas as $marca ) : ?>
							<option value="<?php echo esc_attr( $marca->slug ); ?>"><?php echo esc_html( $marca->name ); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</p>

			<p class="lv-busca-rapida__campo">
				<label for="lv-hero-preco">Ate quanto</label>
				<select id="lv-hero-preco" name="preco_max">
					<option value="">Qualquer valor</option>
					<option value="40000">Ate R$ 40 mil</option>
					<option value="60000">Ate R$ 60 mil</option>
					<option value="80000">Ate R$ 80 mil</option>
					<option value="120000">Ate R$ 120 mil</option>
					<option value="200000">Ate R$ 200 mil</option>
				</select>
			</p>

			<button type="submit" class="lv-btn lv-btn--destaque lv-btn--lg">
				<?php lv_icone( 'busca' ); ?> Ver carros
			</button>
		</form>

	</div>
</section>
