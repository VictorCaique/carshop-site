<?php
/**
 * Home. A ordem das secoes segue o SPEC 5.1.
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/destaques' );
get_template_part( 'template-parts/home/diferenciais' );
get_template_part( 'template-parts/home/sobre' );
get_template_part( 'template-parts/home/depoimentos' );
get_template_part( 'template-parts/home/contato' );

get_footer();
