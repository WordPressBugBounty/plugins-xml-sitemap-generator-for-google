<?php
/**
 * Polylang Compatibility
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Polylang language for a post.
 */
function sgg_polylang_post_language( $language, $post_id ) {
	if ( function_exists( 'pll_get_post_language' ) ) {
		$language = pll_get_post_language( $post_id, 'slug' );
	}

	return $language;
}
add_filter( 'xml_sitemap_news_language', 'sgg_polylang_post_language', 10, 2 );
