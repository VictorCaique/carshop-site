<?php
/**
 * Formulario de contato. Envia por e-mail, com consentimento explicito,
 * e nao grava o lead no banco (LGPD - so guardamos o que precisamos).
 */

defined( 'ABSPATH' ) || exit;

$retorno = isset( $_GET['contato'] ) ? sanitize_key( wp_unslash( $_GET['contato'] ) ) : '';
$mapa    = (string) lv_option( 'google_maps_embed', '' );
?>

<section class="lv-secao lv-form-contato" id="contato">
	<h2 class="lv-secao__titulo">Fale com a gente</h2>

	<?php if ( 'ok' === $retorno ) : ?>
		<p class="lv-aviso lv-aviso--sucesso">Mensagem enviada. Respondemos no proximo horario de atendimento.</p>
	<?php elseif ( 'consentimento' === $retorno ) : ?>
		<p class="lv-aviso lv-aviso--erro">Precisamos do seu consentimento para responder o contato.</p>
	<?php elseif ( 'erro' === $retorno ) : ?>
		<p class="lv-aviso lv-aviso--erro">Nao conseguimos enviar. Confira os campos e tente de novo.</p>
	<?php endif; ?>

	<form class="lv-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="lv_contato">
		<?php wp_nonce_field( 'lv_contato', 'lv_contato_nonce' ); ?>

		<div class="lv-form__linha">
			<p>
				<label for="lv-nome">Nome <span aria-hidden="true">*</span></label>
				<input type="text" id="lv-nome" name="lv_nome" required autocomplete="name">
			</p>
			<p>
				<label for="lv-telefone">Telefone / WhatsApp</label>
				<input type="tel" id="lv-telefone" name="lv_telefone" autocomplete="tel">
			</p>
		</div>

		<p>
			<label for="lv-email">E-mail <span aria-hidden="true">*</span></label>
			<input type="email" id="lv-email" name="lv_email" required autocomplete="email">
		</p>

		<p>
			<label for="lv-assunto">Assunto</label>
			<select id="lv-assunto" name="lv_assunto">
				<option>Quero informacoes sobre um carro</option>
				<option>Quero vender meu carro</option>
				<option>Financiamento</option>
				<option>Outro assunto</option>
			</select>
		</p>

		<p>
			<label for="lv-mensagem">Mensagem <span aria-hidden="true">*</span></label>
			<textarea id="lv-mensagem" name="lv_mensagem" rows="5" required></textarea>
		</p>

		<p class="lv-form__consentimento">
			<label>
				<input type="checkbox" name="lv_consentimento" value="1" required>
				Autorizo o contato por e-mail, telefone ou WhatsApp para responder esta mensagem.
				<?php
				$privacidade = get_privacy_policy_url();
				if ( $privacidade ) :
					?>
					Leia a <a href="<?php echo esc_url( $privacidade ); ?>">Politica de Privacidade</a>.
				<?php endif; ?>
			</label>
		</p>

		<?php // Honeypot: bot preenche, humano nao ve. ?>
		<p class="lv-form__honeypot" aria-hidden="true">
			<label for="lv-site">Deixe em branco</label>
			<input type="text" id="lv-site" name="lv_site" tabindex="-1" autocomplete="off">
		</p>

		<button type="submit" class="lv-btn lv-btn--primaria lv-btn--lg">Enviar mensagem</button>
	</form>

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
</section>
