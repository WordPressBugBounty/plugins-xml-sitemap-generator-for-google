<?php
/**
 * SEO Plugins Compatibility
 *
 * Supports Yoast SEO, Rank Math, and The SEO Framework.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exclude posts with noindex from the sitemap.
 */
function sgg_exclude_noindex_posts( $value, $post_id ) {
	// Yoast SEO noindex
	if ( defined( 'WPSEO_VERSION' ) && '1' === get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true ) ) {
		return false;
	}

	// Rank Math noindex
	if ( class_exists( 'RankMath' ) ) {
		$rank_math_robots = get_post_meta( $post_id, 'rank_math_robots', true );
		if ( ! empty( $rank_math_robots ) && is_array( $rank_math_robots ) && in_array( 'noindex', $rank_math_robots, true ) ) {
			return false;
		}
	}

	// SEO Framework noindex
	if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
		$seo_framework_noindex = get_post_meta( $post_id, '_genesis_noindex', true );
		if ( '1' === $seo_framework_noindex ) {
			return false;
		}
	}

	return $value;
}
add_filter( 'xml_sitemap_include_post', 'sgg_exclude_noindex_posts', 99, 2 );

/**
 * Exclude terms with noindex from the sitemap.
 */
function sgg_exclude_noindex_terms( $value, $term_id, $taxonomy ) {
	// Yoast SEO noindex
	if ( is_callable( '\WPSEO_Taxonomy_Meta::get_term_meta' ) ) {
		$noindex = \WPSEO_Taxonomy_Meta::get_term_meta( $term_id, $taxonomy, 'noindex' );
		if ( 'noindex' === $noindex ) {
			return true;
		}
	}

	// Rank Math noindex
	if ( class_exists( 'RankMath' ) ) {
		$rank_math_robots = get_term_meta( $term_id, 'rank_math_robots', true );
		if ( ! empty( $rank_math_robots ) && is_array( $rank_math_robots ) && in_array( 'noindex', $rank_math_robots, true ) ) {
			return true;
		}
	}

	// SEO Framework noindex
	if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
		$seo_framework_meta = get_term_meta( $term_id, 'autodescription-term-settings', true );
		if ( ! empty( $seo_framework_meta ) && isset( $seo_framework_meta['noindex'] ) && 1 === $seo_framework_meta['noindex'] ) {
			return true;
		}
	}

	return $value;
}
add_filter( 'sgg_sitemap_exclude_single_term', 'sgg_exclude_noindex_terms', 99, 3 );

/**
 * Google News Title Filter
 */
function sgg_google_news_title( $title, $post_id ) {
	// SEO Framework title
	if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
		$seo_title = get_post_meta( $post_id, '_genesis_title', true );
		if ( ! empty( $seo_title ) ) {
			$title = $seo_title;
		}
	}

	return $title;
}
add_filter( 'xml_sitemap_google_news_title', 'sgg_google_news_title', 10, 2 );
