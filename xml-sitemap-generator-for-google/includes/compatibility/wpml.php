<?php
/**
 * WPML Compatibility
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the WPML language for a post.
 */
function sgg_wpml_post_language( $language, $post_id, $post_type = 'post' ) {
	global $sitepress;

	if ( $sitepress ) {
		$language = apply_filters(
			'wpml_element_language_code',
			$language,
			array(
				'element_id'   => $post_id,
				'element_type' => $post_type,
			)
		);
	}

	return $language;
}
add_filter( 'xml_sitemap_news_language', 'sgg_wpml_post_language', 10, 3 );
