<?php
/**
 * Tela "Configuracoes do Site" em WordPress nativo (Settings API).
 *
 * Este e o coracao da replicabilidade: tudo que muda de cliente para cliente
 * esta aqui, nada em arquivo. Sem plugin pago - o Meta Box gratuito nao tem
 * options page, entao esta tela e nossa.
 *
 * Tudo e gravado num unico option: `lv_opcoes`.
 */

defined( 'ABSPATH' ) || exit;

const LV_OPCOES_KEY   = 'lv_opcoes';
const LV_OPCOES_GROUP = 'lv_opcoes_group';
const LV_OPCOES_SLUG  = 'lv-configuracoes';

/**
 * Menu.
 */
add_action( 'admin_menu', function (): void {
	add_menu_page(
		'Configuracoes do Site',
		'Configuracoes do Site',
		'manage_options',
		LV_OPCOES_SLUG,
		'lv_render_pagina_opcoes',
		'dashicons-admin-customizer',
		4
	);
} );

add_action( 'admin_init', function (): void {
	register_setting(
		LV_OPCOES_GROUP,
		LV_OPCOES_KEY,
		[
			'type'              => 'array',
			'sanitize_callback' => 'lv_sanitizar_opcoes',
			'default'           => lv_opcoes_padrao(),
		]
	);
} );

/**
 * Assets da tela. Carrega o media uploader e o color picker do proprio WP.
 */
add_action( 'admin_enqueue_scripts', function ( string $hook ): void {
	if ( 'toplevel_page_' . LV_OPCOES_SLUG !== $hook ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );

	wp_enqueue_style(
		'lv-admin-opcoes',
		LV_ESTOQUE_URL . 'assets/admin-opcoes.css',
		[ 'wp-color-picker' ],
		LV_ESTOQUE_VERSION
	);

	wp_enqueue_script(
		'lv-admin-opcoes',
		LV_ESTOQUE_URL . 'assets/admin-opcoes.js',
		[ 'wp-color-picker', 'jquery' ],
		LV_ESTOQUE_VERSION,
		true
	);

	wp_localize_script(
		'lv-admin-opcoes',
		'lvOpcoes',
		[
			'selecionar' => 'Selecionar imagem',
			'usar'       => 'Usar esta imagem',
			'remover'    => 'Remover',
		]
	);
} );

// ---------------------------------------------------------------------------
// Sanitizacao
// ---------------------------------------------------------------------------

/**
 * Sanitiza um valor conforme o tipo declarado no esquema.
 */
function lv_sanitizar_valor( array $campo, $valor ) {
	switch ( $campo['type'] ) {
		case 'image':
			$id = (int) $valor;
			if ( ! $id || ! wp_attachment_is_image( $id ) ) {
				return '';
			}
			$src = wp_get_attachment_image_src( $id, 'full' );
			return [
				'id'     => $id,
				'url'    => $src ? $src[0] : wp_get_attachment_url( $id ),
				'width'  => $src ? (int) $src[1] : 0,
				'height' => $src ? (int) $src[2] : 0,
			];

		case 'checkbox':
			return empty( $valor ) ? 0 : 1;

		case 'number':
			if ( '' === $valor ) {
				return '';
			}
			// O min/max do esquema ja vai para o HTML, mas isso e so dica de
			// navegador: quem grava fora do intervalo tem o valor corrigido
			// aqui, senao o campo volta para a tela mostrando o valor invalido.
			$numero = (int) $valor;
			if ( isset( $campo['min'] ) ) {
				$numero = max( (int) $campo['min'], $numero );
			}
			if ( isset( $campo['max'] ) ) {
				$numero = min( (int) $campo['max'], $numero );
			}
			return $numero;

		case 'email':
			return sanitize_email( (string) $valor );

		case 'url':
			return esc_url_raw( (string) $valor );

		case 'color':
			$cor = sanitize_hex_color( (string) $valor );
			return $cor ?: ( $campo['std'] ?? '' );

		case 'select':
			$opcoes = array_keys( $campo['opcoes'] ?? [] );
			return in_array( (string) $valor, $opcoes, true ) ? (string) $valor : ( $campo['std'] ?? '' );

		case 'textarea':
			// O embed do Maps precisa passar o iframe inteiro.
			if ( str_contains( (string) $valor, '<iframe' ) ) {
				return wp_kses(
					(string) $valor,
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
			}
			return sanitize_textarea_field( (string) $valor );

		case 'wysiwyg':
			return wp_kses_post( (string) $valor );

		case 'tel':
		case 'text':
		default:
			return sanitize_text_field( (string) $valor );
	}
}

/**
 * Sanitizacao do option inteiro, guiada pelo esquema.
 * O que nao esta no esquema nao entra no banco.
 */
function lv_sanitizar_opcoes( $entrada ): array {
	$entrada = is_array( $entrada ) ? $entrada : [];
	$limpo   = [];

	foreach ( lv_opcoes_schema() as $aba ) {
		foreach ( $aba['campos'] as $id => $campo ) {

			if ( 'repeater' === $campo['type'] ) {
				$linhas = [];

				foreach ( (array) ( $entrada[ $id ] ?? [] ) as $linha ) {
					if ( ! is_array( $linha ) ) {
						continue;
					}

					$limpa = [];
					foreach ( $campo['sub'] as $sub_id => $sub ) {
						$limpa[ $sub_id ] = lv_sanitizar_valor( $sub, $linha[ $sub_id ] ?? '' );
					}

					// Linha totalmente vazia (o usuario adicionou e nao preencheu) some.
					$tem_conteudo = array_filter(
						$limpa,
						static fn( $v ) => is_array( $v ) ? ! empty( $v ) : '' !== (string) $v && '0' !== (string) $v
					);
					if ( $tem_conteudo ) {
						$linhas[] = $limpa;
					}
				}

				if ( isset( $campo['max'] ) ) {
					$linhas = array_slice( $linhas, 0, (int) $campo['max'] );
				}

				$limpo[ $id ] = $linhas;
				continue;
			}

			$limpo[ $id ] = lv_sanitizar_valor( $campo, $entrada[ $id ] ?? '' );
		}
	}

	// O WhatsApp precisa ser so digitos para o wa.me funcionar.
	foreach ( $limpo['vendedores'] ?? [] as $i => $v ) {
		$limpo['vendedores'][ $i ]['whatsapp'] = preg_replace( '/\D/', '', (string) ( $v['whatsapp'] ?? '' ) );
	}

	return $limpo;
}

// ---------------------------------------------------------------------------
// Renderizacao
// ---------------------------------------------------------------------------

/**
 * Um campo. $name e o caminho no array do option.
 */
function lv_render_campo( string $name, string $id_html, array $campo, $valor ): void {
	$desc = ! empty( $campo['desc'] )
		? '<p class="description">' . esc_html( $campo['desc'] ) . '</p>'
		: '';

	switch ( $campo['type'] ) {

		case 'image':
			$url = is_array( $valor ) ? ( $valor['url'] ?? '' ) : '';
			$aid = is_array( $valor ) ? (int) ( $valor['id'] ?? 0 ) : 0;
			?>
			<div class="lv-campo-imagem" data-lv-imagem>
				<div class="lv-campo-imagem__preview">
					<?php if ( $url ) : ?>
						<img src="<?php echo esc_url( $url ); ?>" alt="">
					<?php endif; ?>
				</div>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( (string) $aid ); ?>" data-lv-imagem-id>
				<p>
					<button type="button" class="button" data-lv-imagem-escolher>Selecionar imagem</button>
					<button type="button" class="button-link lv-remover" data-lv-imagem-remover
						<?php echo $aid ? '' : 'hidden'; ?>>Remover</button>
				</p>
			</div>
			<?php
			echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput
			break;

		case 'color':
			printf(
				'<input type="text" class="lv-color" name="%s" id="%s" value="%s" data-default-color="%s">',
				esc_attr( $name ),
				esc_attr( $id_html ),
				esc_attr( (string) $valor ),
				esc_attr( (string) ( $campo['std'] ?? '' ) )
			);
			echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput
			break;

		case 'select':
			printf( '<select name="%s" id="%s">', esc_attr( $name ), esc_attr( $id_html ) );
			if ( ! empty( $campo['placeholder'] ) ) {
				printf( '<option value="">%s</option>', esc_html( $campo['placeholder'] ) );
			}
			foreach ( (array) ( $campo['opcoes'] ?? [] ) as $opcao => $label ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( (string) $opcao ),
					selected( (string) $valor, (string) $opcao, false ),
					esc_html( (string) $label )
				);
			}
			echo '</select>';
			echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput
			break;

		case 'checkbox':
			printf(
				'<label class="lv-switch"><input type="hidden" name="%1$s" value="0">'
				. '<input type="checkbox" name="%1$s" id="%2$s" value="1"%3$s> <span>Ativado</span></label>',
				esc_attr( $name ),
				esc_attr( $id_html ),
				checked( ! empty( $valor ), true, false )
			);
			echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput
			break;

		case 'textarea':
			printf(
				'<textarea name="%s" id="%s" rows="4" class="large-text" placeholder="%s">%s</textarea>',
				esc_attr( $name ),
				esc_attr( $id_html ),
				esc_attr( (string) ( $campo['placeholder'] ?? '' ) ),
				esc_textarea( (string) $valor )
			);
			echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput
			break;

		case 'wysiwyg':
			// O TinyMCE so aceita id com letras, numeros e underscore.
			wp_editor(
				(string) $valor,
				preg_replace( '/[^a-z0-9_]/', '_', strtolower( $id_html ) ),
				[
					'textarea_name' => $name,
					'textarea_rows' => 8,
					'media_buttons' => false,
					'teeny'         => true,
				]
			);
			echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput
			break;

		case 'number':
			printf(
				'<input type="number" name="%s" id="%s" value="%s" class="regular-text"%s%s%s>',
				esc_attr( $name ),
				esc_attr( $id_html ),
				esc_attr( (string) $valor ),
				isset( $campo['min'] ) ? ' min="' . esc_attr( (string) $campo['min'] ) . '"' : '',
				isset( $campo['max'] ) ? ' max="' . esc_attr( (string) $campo['max'] ) . '"' : '',
				! empty( $campo['placeholder'] ) ? ' placeholder="' . esc_attr( $campo['placeholder'] ) . '"' : ''
			);
			echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput
			break;

		default:
			$tipos = [ 'email' => 'email', 'url' => 'url', 'tel' => 'tel' ];
			printf(
				'<input type="%s" name="%s" id="%s" value="%s" class="regular-text" placeholder="%s">',
				esc_attr( $tipos[ $campo['type'] ] ?? 'text' ),
				esc_attr( $name ),
				esc_attr( $id_html ),
				esc_attr( (string) $valor ),
				esc_attr( (string) ( $campo['placeholder'] ?? '' ) )
			);
			echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Uma linha do repeater. $indice pode ser o placeholder __i__ do template.
 */
function lv_render_linha_repeater( string $id, array $campo, $indice, array $valores ): void {
	$rotulo = $campo['rotulo'] ?? 'item';
	?>
	<div class="lv-repeater__linha" data-lv-linha>
		<div class="lv-repeater__cabecalho">
			<span class="lv-repeater__handle" title="Arraste para reordenar">&#8942;&#8942;</span>
			<strong><?php echo esc_html( ucfirst( $rotulo ) ); ?></strong>
			<button type="button" class="button-link lv-remover" data-lv-remover-linha>Remover</button>
		</div>

		<div class="lv-grade">
			<?php foreach ( $campo['sub'] as $sub_id => $sub ) : ?>
				<?php
				$name    = sprintf( '%s[%s][%s][%s]', LV_OPCOES_KEY, $id, $indice, $sub_id );
				$id_html = sprintf( 'lv-%s-%s-%s', $id, $indice, $sub_id );
				$valor   = $valores[ $sub_id ] ?? ( $sub['std'] ?? '' );
				?>
				<div class="lv-campo lv-campo--<?php echo esc_attr( $sub['largura'] ?? 'cheia' ); ?>">
					<label for="<?php echo esc_attr( $id_html ); ?>"><?php echo esc_html( $sub['label'] ); ?></label>
					<?php lv_render_campo( $name, $id_html, $sub, $valor ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * A pagina.
 */
function lv_render_pagina_opcoes(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$schema = lv_opcoes_schema();
	$opcoes = wp_parse_args( (array) get_option( LV_OPCOES_KEY, [] ), lv_opcoes_padrao() );
	$abas   = array_keys( $schema );
	$ativa  = isset( $_GET['aba'] ) && in_array( sanitize_key( wp_unslash( $_GET['aba'] ) ), $abas, true )
		? sanitize_key( wp_unslash( $_GET['aba'] ) )
		: $abas[0];
	?>
	<div class="wrap lv-opcoes">
		<h1>Configuracoes do Site</h1>
		<p class="lv-opcoes__intro">
			Tudo que muda de loja para loja esta aqui. Nada precisa ser editado em arquivo.
		</p>

		<h2 class="nav-tab-wrapper">
			<?php foreach ( $schema as $slug => $aba ) : ?>
				<a href="<?php echo esc_url( add_query_arg( [ 'page' => LV_OPCOES_SLUG, 'aba' => $slug ], admin_url( 'admin.php' ) ) ); ?>"
					class="nav-tab <?php echo $slug === $ativa ? 'nav-tab-active' : ''; ?>"
					data-lv-aba="<?php echo esc_attr( $slug ); ?>">
					<?php echo esc_html( $aba['titulo'] ); ?>
				</a>
			<?php endforeach; ?>
		</h2>

		<form method="post" action="options.php" class="lv-opcoes__form">
			<?php settings_fields( LV_OPCOES_GROUP ); ?>

			<?php foreach ( $schema as $slug => $aba ) : ?>
				<div class="lv-painel" data-lv-painel="<?php echo esc_attr( $slug ); ?>"
					<?php echo $slug === $ativa ? '' : 'hidden'; ?>>

					<div class="lv-grade">
						<?php foreach ( $aba['campos'] as $id => $campo ) : ?>

							<?php if ( 'repeater' === $campo['type'] ) : ?>
								<div class="lv-campo lv-campo--cheia">
									<label><?php echo esc_html( $campo['label'] ); ?></label>
									<?php if ( ! empty( $campo['desc'] ) ) : ?>
										<p class="description"><?php echo esc_html( $campo['desc'] ); ?></p>
									<?php endif; ?>

									<div class="lv-repeater" data-lv-repeater="<?php echo esc_attr( $id ); ?>"
										<?php echo isset( $campo['max'] ) ? 'data-lv-max="' . esc_attr( (string) $campo['max'] ) . '"' : ''; ?>>

										<div class="lv-repeater__linhas" data-lv-linhas>
											<?php foreach ( (array) ( $opcoes[ $id ] ?? [] ) as $i => $linha ) : ?>
												<?php lv_render_linha_repeater( $id, $campo, $i, (array) $linha ); ?>
											<?php endforeach; ?>
										</div>

										<template data-lv-template>
											<?php lv_render_linha_repeater( $id, $campo, '__i__', [] ); ?>
										</template>

										<button type="button" class="button" data-lv-adicionar>
											Adicionar <?php echo esc_html( $campo['rotulo'] ?? 'item' ); ?>
										</button>
									</div>
								</div>

							<?php else : ?>
								<?php
								$name    = sprintf( '%s[%s]', LV_OPCOES_KEY, $id );
								$id_html = 'lv-' . $id;
								?>
								<div class="lv-campo lv-campo--<?php echo esc_attr( $campo['largura'] ?? 'cheia' ); ?>">
									<label for="<?php echo esc_attr( $id_html ); ?>"><?php echo esc_html( $campo['label'] ); ?></label>
									<?php lv_render_campo( $name, $id_html, $campo, $opcoes[ $id ] ?? '' ); ?>
								</div>
							<?php endif; ?>

						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<?php submit_button( 'Salvar configuracoes' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Atalho para a tela na barra de admin - o lojista vive nessa pagina.
 */
add_action( 'admin_bar_menu', function ( $barra ): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$barra->add_node(
		[
			'id'    => 'lv-configuracoes',
			'title' => 'Configuracoes do Site',
			'href'  => admin_url( 'admin.php?page=' . LV_OPCOES_SLUG ),
		]
	);
}, 90 );
