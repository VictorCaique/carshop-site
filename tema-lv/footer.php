<?php
/**
 * Rodape.
 */

defined( 'ABSPATH' ) || exit;

$nome_loja  = (string) lv_option( 'nome_loja', get_bloginfo( 'name' ) );
$endereco   = (string) lv_option( 'endereco', '' );
$cidade     = (string) lv_option( 'cidade', '' );
$estado     = (string) lv_option( 'estado', '' );
$telefone   = (string) lv_option( 'telefone_fixo', '' );
$email      = (string) lv_option( 'email', '' );
$cnpj       = (string) lv_option( 'cnpj', '' );
$instagram  = (string) lv_option( 'instagram', '' );
$facebook   = (string) lv_option( 'facebook', '' );
$horarios   = (array) lv_option( 'horario_funcionamento', [] );
$wpp        = function_exists( 'lv_whatsapp_link' ) ? lv_whatsapp_link( 0 ) : '';
?>
</main><!-- /.lv-main -->

<footer class="lv-footer">
	<div class="lv-container lv-footer__grid">

		<div class="lv-footer__col">
			<div class="lv-footer__logo"><?php echo lv_logo_html( true ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php if ( lv_option( 'slogan' ) ) : ?>
				<p class="lv-footer__slogan"><?php echo esc_html( (string) lv_option( 'slogan' ) ); ?></p>
			<?php endif; ?>

			<?php if ( $instagram || $facebook ) : ?>
				<div class="lv-footer__social">
					<?php if ( $instagram ) : ?>
						<a href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener" aria-label="Instagram">
							<?php lv_icone( 'instagram' ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $facebook ) : ?>
						<a href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener" aria-label="Facebook">
							<?php lv_icone( 'facebook' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="lv-footer__col">
			<h2 class="lv-footer__titulo">Navegacao</h2>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					[
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'lv-footer__lista',
						'depth'          => 1,
						'fallback_cb'    => false,
					]
				);
			} else {
				?>
				<ul class="lv-footer__lista">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a></li>
					<li><a href="<?php echo esc_url( lv_url_vitrine() ); ?>">Estoque</a></li>
					<?php
					$privacidade = get_privacy_policy_url();
					if ( $privacidade ) :
						?>
						<li><a href="<?php echo esc_url( $privacidade ); ?>">Politica de Privacidade</a></li>
					<?php endif; ?>
				</ul>
				<?php
			}
			?>
		</div>

		<div class="lv-footer__col">
			<h2 class="lv-footer__titulo">Contato</h2>
			<ul class="lv-footer__lista lv-footer__lista--contato">
				<?php if ( $endereco ) : ?>
					<li>
						<?php lv_icone( 'local' ); ?>
						<span><?php echo esc_html( trim( $endereco . ' - ' . $cidade . '/' . $estado, ' -/' ) ); ?></span>
					</li>
				<?php endif; ?>
				<?php if ( $telefone ) : ?>
					<li>
						<?php lv_icone( 'telefone' ); ?>
						<a href="<?php echo esc_url( lv_tel_link( $telefone ) ); ?>"><?php echo esc_html( $telefone ); ?></a>
					</li>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<li>
						<?php lv_icone( 'email' ); ?>
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<?php if ( $horarios ) : ?>
			<div class="lv-footer__col">
				<h2 class="lv-footer__titulo">Horarios</h2>
				<ul class="lv-footer__lista lv-footer__lista--horarios">
					<?php foreach ( $horarios as $h ) : ?>
						<li>
							<span><?php echo esc_html( (string) ( $h['dia'] ?? '' ) ); ?></span>
							<strong><?php echo esc_html( (string) ( $h['horario'] ?? '' ) ); ?></strong>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

	</div>

	<div class="lv-footer__base">
		<div class="lv-container lv-footer__base-inner">
			<p>
				&copy; <?php echo esc_html( (string) gmdate( 'Y' ) ); ?> <?php echo esc_html( $nome_loja ); ?>
				<?php if ( $cnpj ) : ?>
					&middot; CNPJ <?php echo esc_html( $cnpj ); ?>
				<?php endif; ?>
			</p>
			<p class="lv-footer__credito">
				Desenvolvido por <a href="https://example.com" target="_blank" rel="noopener">Victor C. Silva</a>
			</p>
		</div>
	</div>
</footer>

<?php if ( $wpp && lv_option( 'whatsapp_flutuante', true ) ) : ?>
	<a class="lv-wpp-flutuante" href="<?php echo esc_url( $wpp ); ?>" target="_blank" rel="noopener"
		aria-label="Falar no WhatsApp">
		<?php lv_icone( 'whatsapp', 'lv-icone lv-icone--lg' ); ?>
	</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
