<?php
/**
 * FooGallery Compatibility
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add FooGallery image URLs to the sitemap.
 */
function sgg_add_foogallery_image_urls( $urls, $post_id ) {
	if ( defined( 'FOOGALLERY_CPT_GALLERY' ) && class_exists( 'FooGallery' ) ) {
		$content = get_post_field( 'post_content', $post_id );

		if ( false !== strpos( $content, FOOGALLERY_CPT_GALLERY ) ) {
			$gallery_ids = array();

			preg_match_all( '/<!--\s*wp:fooplugins\/foogallery\s*{"id":\s*(\d+)\s*}\s*\/-->/', $content, $matches );

			if ( ! empty( $matches[1] ) ) {
				$gallery_ids = array_map( 'intval', $matches[1] );
			}

			if ( ! empty( $gallery_ids ) ) {
				$gallery_ids = array_unique( $gallery_ids );

				foreach ( $gallery_ids as $gallery_id ) {
					$gallery = FooGallery::get_by_id( $gallery_id );
					if ( $gallery ) {
						foreach ( $gallery->attachments() as $attachment ) {
							$urls[] = wp_get_attachment_url( $attachment->ID );
						}
					}
				}
			}
		}
	}

	return $urls;
}
add_filter( 'sgg_sitemap_post_media_urls', 'sgg_add_foogallery_image_urls', 10, 2 );
