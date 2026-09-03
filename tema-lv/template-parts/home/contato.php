<?php
/**
 * Localizacao e contato: mapa, endereco, horarios, CTA de WhatsApp.
 */

defined( 'ABSPATH' ) || exit;

$mapa      = (string) lv_option( 'google_maps_embed', '' );
$endereco  = (string) lv_option( 'endereco', '' );
$cidade    = (string) lv_option( 'cidade', '' );
$estado    = (string) lv_option( 'estado', '' );
$cep       = (string) lv_option( 'cep', '' );
$telefone  = (string) lv_option( 'telefone_fixo', '' );
$email     = (string) lv_option( 'email', '' );
$horarios  = (array) lv_option( 'horario_funcionamento', [] );
$wpp       = function_exists( 'lv_whatsapp_link' ) ? lv_whatsapp_link( 0 ) : '';

if ( ! $endereco && ! $telefone && ! $mapa ) {
	return;
}
?>

<section class="lv-secao lv-contato" id="contato">
	<div class="lv-container lv-contato__grid">

		<div class="lv-contato__info">
			<h2 class="lv-secao__titulo">Venha tomar um cafe</h2>

			<?php if ( $endereco ) : ?>
				<p class="lv-contato__item">
					<?php lv_icone( 'local' ); ?>
					<span>
						<?php echo esc_html( $endereco ); ?><br>
						<?php echo esc_html( trim( $cidade . '/' . $estado, '/' ) ); ?>
						<?php echo $cep ? ' - ' . esc_html( $cep ) : ''; ?>
					</span>
				</p>
			<?php endif; ?>

			<?php if ( $telefone ) : ?>
				<p class="lv-contato__item">
					<?php lv_icone( 'telefone' ); ?>
					<a href="<?php echo esc_url( lv_tel_link( $telefone ) ); ?>"><?php echo esc_html( $telefone ); ?></a>
				</p>
			<?php endif; ?>

			<?php if ( $email ) : ?>
				<p class="lv-contato__item">
					<?php lv_icone( 'email' ); ?>
					<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				</p>
			<?php endif; ?>

			<?php if ( $horarios ) : ?>
				<div class="lv-contato__horarios">
					<h3><?php lv_icone( 'clock', 'lv-icone lv-icone--sm' ); ?> Horario de atendimento</h3>
					<ul role="list">
						<?php foreach ( $horarios as $h ) : ?>
							<li>
								<span><?php echo esc_html( (string) ( $h['dia'] ?? '' ) ); ?></span>
								<strong><?php echo esc_html( (string) ( $h['horario'] ?? '' ) ); ?></strong>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( $wpp ) : ?>
				<a class="lv-btn lv-btn--whatsapp lv-btn--lg" href="<?php echo esc_url( $wpp ); ?>"
					target="_blank" rel="noopener">
					<?php lv_icone( 'whatsapp' ); ?> Chamar no WhatsApp
				</a>
			<?php endif; ?>
		</div>

		<?php if ( $mapa ) : ?>
			<div class="lv-contato__mapa">
				<?php
				echo wp_kses( // phpcs:ignore WordPress.Security.EscapeOutput
					$mapa,
					[
						'iframe' => [
							'src'             => true,
							'width'           => true,
							'height'          => true,
							'style'           => true,
							'allowfullscreen' => true,
							'loading'         => true,
							'referrerpolicy'  => true,
							'title'           => true,
						],
					]
				);
				?>
			</div>
		<?php endif; ?>

	</div>
</section>
