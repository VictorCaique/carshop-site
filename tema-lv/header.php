<?php
/**
 * Cabecalho.
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="lv-skip" href="#conteudo">Ir para o conteudo</a>

<header class="lv-header" id="lv-header">
	<div class="lv-container lv-header__inner">

		<a class="lv-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php echo lv_logo_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>

		<button class="lv-menu-toggle" type="button"
			aria-expanded="false" aria-controls="lv-menu-principal" aria-label="Abrir menu">
			<span class="lv-menu-toggle__barra" aria-hidden="true"></span>
			<span class="lv-menu-toggle__barra" aria-hidden="true"></span>
			<span class="lv-menu-toggle__barra" aria-hidden="true"></span>
		</button>

		<nav class="lv-nav" id="lv-menu-principal" aria-label="Menu principal">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					[
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'lv-nav__lista',
						'depth'          => 2,
						'fallback_cb'    => false,
					]
				);
			} else {
				?>
				<ul class="lv-nav__lista">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a></li>
					<li><a href="<?php echo esc_url( lv_url_vitrine() ); ?>">Estoque</a></li>
				</ul>
				<?php
			}
			?>

			<?php
			$wpp_header = function_exists( 'lv_whatsapp_link' ) ? lv_whatsapp_link( 0 ) : '';
			if ( $wpp_header ) :
				?>
				<a class="lv-btn lv-btn--whatsapp lv-nav__cta" href="<?php echo esc_url( $wpp_header ); ?>"
					target="_blank" rel="noopener">
					<?php lv_icone( 'whatsapp' ); ?>
					<span>WhatsApp</span>
				</a>
			<?php endif; ?>
		</nav>

	</div>
</header>

<main id="conteudo" class="lv-main">
