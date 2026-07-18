<?php
/**
 * BeeTheme Compatibility
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Use Betheme builder content for media sitemap parsing.
 */
function sgg_be_theme_compatibility( $content, $post ) {
	if ( defined( 'MFN_THEME_VERSION' ) ) {
		$mfn_items = get_post_meta( $post->ID, 'mfn-builder-preview', true );

		if ( ! empty( $mfn_items ) ) {
			if ( ! is_array( $mfn_items ) ) {
				$mfn_items = call_user_func( 'base64_decode', $mfn_items );
			}

			if ( ! empty( $mfn_items ) ) {
				$content = is_array( $mfn_items ) ? implode( '', $mfn_items ) : $mfn_items;
			}
		}
	}

	return $content;
}
add_filter( 'xml_media_sitemap_post_content', 'sgg_be_theme_compatibility', 10, 2 );
