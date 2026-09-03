<?php
/**
 * Bloco de CTA da ficha do veiculo: preco, WhatsApp, ligar, simular.
 * E o pedaco que gera o dinheiro do cliente.
 */

defined( 'ABSPATH' ) || exit;

$id       = get_the_ID();
$status   = lv_status( $id );
$promo    = lv_preco_original( $id );
$wpp      = lv_whatsapp_link( $id );
$telefone = (string) lv_option( 'telefone_fixo', '' );

// Vendedor responsavel (para mostrar nome e foto).
$numero_resp = lv_whatsapp_numero( $id );
$vendedores  = (array) lv_option( 'vendedores', [] );
$vendedor    = null;

foreach ( $vendedores as $v ) {
	if ( preg_replace( '/\D/', '', (string) ( $v['whatsapp'] ?? '' ) ) === $numero_resp ) {
		$vendedor = $v;
		break;
	}
}
?>

<aside class="lv-cta" aria-label="Falar sobre este veiculo">

	<div class="lv-cta__preco">
		<?php if ( $promo ) : ?>
			<span class="lv-preco-antigo"><?php echo esc_html( $promo ); ?></span>
		<?php endif; ?>
		<strong class="lv-cta__valor"><?php echo esc_html( lv_preco( $id ) ); ?></strong>
		<?php if ( lv_field( 'aceita_troca', $id ) ) : ?>
			<span class="lv-cta__nota">Aceitamos seu usado na troca</span>
		<?php endif; ?>
	</div>

	<?php if ( 'vendido' === $status ) : ?>

		<p class="lv-cta__aviso">
			Este veiculo ja foi vendido. Podemos avisar quando chegar um parecido.
		</p>
		<a class="lv-btn lv-btn--primaria lv-btn--bloco" href="<?php echo esc_url( lv_url_vitrine() ); ?>">
			Ver carros similares
		</a>

	<?php else : ?>

		<?php if ( 'reservado' === $status ) : ?>
			<p class="lv-cta__aviso lv-cta__aviso--alerta">
				Veiculo reservado. Fale com a gente para entrar na lista de espera.
			</p>
		<?php endif; ?>

		<?php if ( $wpp ) : ?>
			<a class="lv-btn lv-btn--whatsapp lv-btn--bloco lv-btn--lg" href="<?php echo esc_url( $wpp ); ?>"
				target="_blank" rel="noopener" data-lv-evento="whatsapp">
				<?php lv_icone( 'whatsapp' ); ?> Falar no WhatsApp
			</a>
		<?php endif; ?>

	<?php endif; ?>

	<div class="lv-cta__secundarios">
		<?php if ( $telefone ) : ?>
			<a class="lv-btn lv-btn--contorno" href="<?php echo esc_url( lv_tel_link( $telefone ) ); ?>">
				<?php lv_icone( 'telefone' ); ?> Ligar agora
			</a>
		<?php endif; ?>

		<?php if ( 'vendido' !== $status ) : ?>
			<button class="lv-btn lv-btn--contorno" type="button" data-abre="lv-simulador">
				<?php lv_icone( 'card' ); ?> Simular financiamento
			</button>
		<?php endif; ?>
	</div>

	<?php if ( $vendedor ) : ?>
		<div class="lv-vendedor">
			<?php if ( ! empty( $vendedor['foto']['url'] ) ) : ?>
				<img class="lv-vendedor__foto" src="<?php echo esc_url( $vendedor['foto']['url'] ); ?>"
					alt="<?php echo esc_attr( (string) $vendedor['nome'] ); ?>" width="56" height="56" loading="lazy">
			<?php endif; ?>
			<div>
				<strong class="lv-vendedor__nome"><?php echo esc_html( (string) $vendedor['nome'] ); ?></strong>
				<?php if ( ! empty( $vendedor['cargo'] ) ) : ?>
					<span class="lv-vendedor__cargo"><?php echo esc_html( (string) $vendedor['cargo'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

</aside>

<?php if ( 'vendido' !== $status ) : ?>
	<div class="lv-modal" id="lv-simulador" hidden>
		<div class="lv-modal__caixa" role="dialog" aria-modal="true" aria-labelledby="lv-simulador-titulo">
			<button class="lv-modal__fechar" type="button" data-fecha aria-label="Fechar">
				<?php lv_icone( 'fechar' ); ?>
			</button>

			<h2 id="lv-simulador-titulo">Simular financiamento</h2>
			<p class="lv-modal__intro">
				Preencha e enviamos sua simulacao pelo WhatsApp. Os valores sao uma estimativa;
				a aprovacao e as taxas dependem do banco.
			</p>

			<form class="lv-form lv-form--simulador" data-simulador
				data-whatsapp="<?php echo esc_attr( lv_whatsapp_numero( $id ) ); ?>"
				data-veiculo="<?php echo esc_attr( get_the_title( $id ) ); ?>"
				data-url="<?php echo esc_url( get_permalink( $id ) ); ?>"
				data-preco="<?php echo esc_attr( (string) ( lv_field( 'preco_promocional', $id ) ?: lv_field( 'preco', $id, 0 ) ) ); ?>">

				<p>
					<label for="lv-sim-nome">Seu nome</label>
					<input type="text" id="lv-sim-nome" name="nome" required>
				</p>
				<p>
					<label for="lv-sim-entrada">Entrada (R$)</label>
					<input type="number" id="lv-sim-entrada" name="entrada" min="0" step="1000" value="0">
				</p>
				<p>
					<label for="lv-sim-parcelas">Parcelas</label>
					<select id="lv-sim-parcelas" name="parcelas">
						<option value="24">24x</option>
						<option value="36">36x</option>
						<option value="48" selected>48x</option>
						<option value="60">60x</option>
					</select>
				</p>

				<p class="lv-form__estimativa" data-estimativa hidden></p>

				<button type="submit" class="lv-btn lv-btn--whatsapp lv-btn--bloco">
					<?php lv_icone( 'whatsapp' ); ?> Enviar pelo WhatsApp
				</button>

				<small class="lv-form__legal">
					Estimativa com taxa de referencia de 1,79% a.m. Nao e proposta de credito.
				</small>
			</form>
		</div>
	</div>
<?php endif; ?>
