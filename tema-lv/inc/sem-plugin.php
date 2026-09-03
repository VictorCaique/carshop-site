<?php
/**
 * Template de emergencia: o tema esta ativo sem o plugin LV Estoque.
 *
 * Nao chama wp_head() nem wp_footer() de proposito - os hooks do tema
 * (tokens de cor, enqueue) dependem do plugin e cairiam junto.
 */

defined( 'ABSPATH' ) || exit;

status_header( 503 );
nocache_headers();
header( 'Retry-After: 3600' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title><?php bloginfo( 'name' ); ?></title>
	<style>
		body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;
			font:16px/1.6 system-ui,-apple-system,"Segoe UI",sans-serif;color:#1F2937;background:#F9FAFB}
		.caixa{max-width:44ch;text-align:center}
		h1{font-size:1.5rem;margin:0 0 .5em}
		p{color:#6B7280;margin:0}
	</style>
</head>
<body>
	<div class="caixa">
		<h1><?php bloginfo( 'name' ); ?></h1>
		<p>O site esta em manutencao e volta em instantes.</p>
	</div>
</body>
</html>
