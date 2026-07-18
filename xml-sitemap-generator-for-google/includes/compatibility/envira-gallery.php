<?php
/**
 * Envira Gallery Compatibility
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Envira Gallery image URLs to the sitemap.
 */
function sgg_add_envira_gallery_image_urls( $urls, $post_id ) {
	if ( ! shortcode_exists( 'envira-gallery' ) ) {
		return $urls;
	}

	$content = get_post_field( 'post_content', $post_id );

	if ( empty( $content ) || false === strpos( $content, 'envira' ) ) {
		return $urls;
	}

	$gallery_ids = array();

	// Shortcode: [envira-gallery id="123"]
	preg_match_all( '/\[envira-gallery[^\]]*?\bid=["\']?(\d+)["\']?/i', $content, $id_matches );
	if ( ! empty( $id_matches[1] ) ) {
		$gallery_ids = array_merge( $gallery_ids, array_map( 'intval', $id_matches[1] ) );
	}

	// Shortcode: [envira-gallery slug="my-gallery"]
	preg_match_all( '/\[envira-gallery[^\]]*?\bslug=["\']([^"\']+)["\']/i', $content, $slug_matches );
	if ( ! empty( $slug_matches[1] ) ) {
		foreach ( array_unique( $slug_matches[1] ) as $slug ) {
			$gallery_id = sgg_get_envira_gallery_id_by_slug( $slug );
			if ( $gallery_id ) {
				$gallery_ids[] = $gallery_id;
			}
		}
	}

	// Gutenberg block: <!-- wp:envira/envira-gallery {"galleryId":123} /-->
	preg_match_all( '/<!--\s*wp:envira\/envira-gallery\s+({.*?})\s*\/?-->/s', $content, $block_matches );
	if ( ! empty( $block_matches[1] ) ) {
		foreach ( $block_matches[1] as $json ) {
			$attrs = json_decode( $json, true );
			if ( ! empty( $attrs['galleryId'] ) ) {
				$gallery_ids[] = (int) $attrs['galleryId'];
			}
		}
	}

	$gallery_ids = array_unique( array_filter( $gallery_ids ) );
	$envira_urls = array();

	foreach ( $gallery_ids as $gallery_id ) {
		$envira_urls = array_merge( $envira_urls, sgg_get_envira_gallery_image_urls( $gallery_id ) );
	}

	if ( empty( $envira_urls ) ) {
		return $urls;
	}

	// Replace shortcode-generated resized variants with full-size Envira sources.
	$envira_bases = array_map( 'sgg_get_image_base_url', $envira_urls );
	$urls         = array_values(
		array_filter(
			$urls,
			function ( $url ) use ( $envira_bases ) {
				return ! in_array( sgg_get_image_base_url( $url ), $envira_bases, true );
			}
		)
	);

	return array_merge( $urls, $envira_urls );
}
add_filter( 'sgg_sitemap_post_media_urls', 'sgg_add_envira_gallery_image_urls', 10, 2 );

/**
 * Get image URL without WordPress intermediate size suffixes.
 *
 * @param string $url Image URL.
 * @return string Base image URL.
 */
function sgg_get_image_base_url( $url ) {
	$url = preg_replace( '/[?#].*$/', '', $url );

	return preg_replace( '/(?:-\d+x\d+)+(?=\.\w{3,4}$)/', '', $url );
}

/**
 * Get Envira Gallery ID by slug.
 *
 * @param string $slug Gallery slug.
 * @return int Gallery ID or 0.
 */
function sgg_get_envira_gallery_id_by_slug( $slug ) {
	$galleries = get_posts(
		array(
			'post_type'              => 'envira',
			'name'                   => sanitize_title( $slug ),
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'post_status'            => 'publish',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	return ! empty( $galleries[0] ) ? (int) $galleries[0] : 0;
}

/**
 * Get image URLs from an Envira Gallery.
 *
 * @param int $gallery_id Envira Gallery post ID.
 * @return array Image URLs.
 */
function sgg_get_envira_gallery_image_urls( $gallery_id ) {
	$urls         = array();
	$gallery_data = get_post_meta( $gallery_id, '_eg_gallery_data', true );

	if ( empty( $gallery_data['gallery'] ) || ! is_array( $gallery_data['gallery'] ) ) {
		return $urls;
	}

	foreach ( $gallery_data['gallery'] as $attachment_id => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		// Skip pending images.
		if ( isset( $item['status'] ) && 'pending' === $item['status'] ) {
			continue;
		}

		if ( ! empty( $item['src'] ) ) {
			$urls[] = $item['src'];
		} elseif ( is_numeric( $attachment_id ) ) {
			$attachment_url = wp_get_attachment_url( (int) $attachment_id );
			if ( $attachment_url ) {
				$urls[] = $attachment_url;
			}
		}
	}

	return $urls;
}
