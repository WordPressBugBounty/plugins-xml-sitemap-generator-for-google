<?php
/**
 * Load third-party plugin and theme compatibility.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sgg_compatibility_files = array(
	'polylang.php',
	'wpml.php',
	'seo.php',
	'foogallery.php',
	'envira-gallery.php',
	'be-theme.php',
);

foreach ( $sgg_compatibility_files as $sgg_compatibility_file ) {
	require_once GRIM_SG_INCLUDES . 'compatibility/' . $sgg_compatibility_file;
}
