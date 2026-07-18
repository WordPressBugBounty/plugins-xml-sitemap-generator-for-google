<?php
/**
 * Plugin Activation Hook
 */
function sgg_activation() {
	\GRIM_SG\Frontend::activate_plugin();

	\GRIM_SG\Vendor\Migration::update_version();

	( new \GRIM_SG\IndexNow() )->set_api_key();

	update_option( 'xml_sitemap_installation_time', time(), false );

	// Set the activation redirect transient.
	set_transient( 'sgg_activation_redirect', true, MINUTE_IN_SECONDS );
}

/**
 * Plugin Deactivation Hook
 */
function sgg_deactivation() {
	\GRIM_SG\IndexNow::delete_api_key();

	delete_option( 'xml_sitemap_installation_time' );
}

/**
 * Serve IndexNow API key.
 */
function sgg_serve_indexnow_api_key() {
	global $wp;

	$indexnow    = new \GRIM_SG\IndexNow();
	$current_url = home_url( $wp->request );

	if ( ! empty( $current_url ) && $indexnow->get_api_key_location() === $current_url ) {
		header( 'Content-Type: text/plain' );
		header( 'X-Robots-Tag: noindex' );
		status_header( 200 );

		echo esc_html( $indexnow->get_api_key() );

		exit();
	}
}
add_action( 'wp', 'sgg_serve_indexnow_api_key' );

/**
 * Clear Media Sitemap cache when a post status is changed.
 */
function sgg_clear_media_sitemap_cache( $new_status, $old_status, $post ) {
	$settings = get_option( \GRIM_SG\Vendor\Controller::$slug );
	if ( is_object( $settings ) && ! empty( $settings->disable_media_sitemap_cache ) ) {
		return;
	}

	if ( ( 'publish' === $old_status && 'publish' !== $new_status )
		|| ( 'publish' === $new_status && 'publish' !== $old_status ) ) {
		\GRIM_SG\MediaSitemap::delete_all_cache();
	}
}
add_action( 'transition_post_status', 'sgg_clear_media_sitemap_cache', 10, 3 );

/**
 * Disable default WordPress Sitemaps.
 */
add_filter( 'wp_sitemaps_enabled', '__return_false' );
