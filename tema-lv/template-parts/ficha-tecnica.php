<?php
/**
 * Tabela de ficha tecnica. So mostra a linha que tem valor.
 */

defined( 'ABSPATH' ) || exit;

$id = get_the_ID();

$linhas = [
	'Marca'         => lv_termo( 'marca', $id ),
	'Ano'           => lv_ano( $id ),
	'Quilometragem' => lv_km( $id ),
	'Carroceria'    => lv_termo( 'carroceria', $id ),
	'Cambio'        => lv_termo( 'cambio', $id ),
	'Combustivel'   => lv_termo( 'combustivel', $id ),
	'Motor'         => (string) lv_field( 'motor', $id, '' ),
	'Potencia'      => lv_field( 'potencia', $id ) ? lv_field( 'potencia', $id ) . ' cv' : '',
	'Cor'           => (string) lv_field( 'cor', $id, '' ),
	'Portas'        => lv_field( 'portas', $id ) ? lv_field( 'portas', $id ) . ' portas' : '',
	'Final da placa'=> ( null !== lv_field( 'final_placa', $id ) ) ? (string) lv_field( 'final_placa', $id ) : '',
];

$selos = array_filter(
	[
		'Unico dono'   => (bool) lv_field( 'unico_dono', $id ),
		'IPVA pago'    => (bool) lv_field( 'ipva_pago', $id ),
		'Aceita troca' => (bool) lv_field( 'aceita_troca', $id ),
	]
);

$opcionais = get_the_terms( $id, 'opcional' );
?>

<section class="lv-ficha" aria-labelledby="lv-ficha-titulo">
	<h2 id="lv-ficha-titulo" class="lv-secao__titulo">Ficha tecnica</h2>

	<?php if ( $selos ) : ?>
		<ul class="lv-chips lv-chips--destaque" role="list">
			<?php foreach ( array_keys( $selos ) as $selo ) : ?>
				<li class="lv-chip lv-chip--ok">
					<?php lv_icone( 'check', 'lv-icone lv-icone--sm' ); ?>
					<?php echo esc_html( $selo ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<table class="lv-tabela">
		<tbody>
			<?php foreach ( $linhas as $rotulo => $valor ) : ?>
				<?php if ( '' === trim( (string) $valor ) ) { continue; } ?>
				<tr>
					<th scope="row"><?php echo esc_html( $rotulo ); ?></th>
					<td><?php echo esc_html( (string) $valor ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $opcionais && ! is_wp_error( $opcionais ) ) : ?>
		<h3 class="lv-ficha__subtitulo">Opcionais</h3>
		<ul class="lv-chips" role="list">
			<?php foreach ( $opcionais as $opcional ) : ?>
				<li class="lv-chip"><?php echo esc_html( $opcional->name ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
