<?php

namespace GRIM_SG;

use SGG_PRO\Classes\Video_Sitemap;

class VideoSitemap extends MediaSitemap {
	public static $template = 'video-sitemap';

	/**
	 * Adding Google News Sitemap Headers
	 */
	public function extraSitemapHeader() {
		return array( 'xmlns:video' => 'http://www.google.com/schemas/sitemap-video/1.1' );
	}

	public function add_urls( string $url, array $media ): void {
		$videos = array();

		foreach ( $media as $video ) {
			$extensions = explode( '.', $video );
			$extension  = end( $extensions );

			if ( 'video' === wp_ext2type( $extension ) ) {
				$attachment_id = attachment_url_to_postid( $video );
				if ( $attachment_id ) {
					$thumbnail = get_the_post_thumbnail_url( $attachment_id, 'thumbnail' );
					$metadata  = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
					$videos[]  = array(
						'thumbnail'   => ! empty( $thumbnail ) ? $thumbnail : trailingslashit( includes_url() ) . 'images/media/video.png',
						'title'       => get_the_title( $attachment_id ),
						'description' => wp_get_attachment_caption( $attachment_id ),
						'player_loc'  => $video,
						'duration'    => $metadata['length'] ?? '',
					);
				}
			} elseif ( sgg_pro_enabled() && class_exists( 'SGG_PRO\Classes\Video_Sitemap' ) ) {
				if ( ! empty( $this->settings->youtube_api_key ) && $this->is_youtube_url( $video ) ) {
					$youtube_data = Video_Sitemap::get_youtube_data( $video, $this->settings->youtube_api_key, $this->settings->enable_video_api_cache );

					if ( ! empty( $youtube_data ) ) {
						$videos[] = $youtube_data;
					}
				} elseif ( ! empty( $this->settings->vimeo_api_key ) && $this->is_vimeo_url( $video ) ) {
					$vimeo_data = Video_Sitemap::get_vimeo_data( $video, $this->settings->vimeo_api_key, $this->settings->enable_video_api_cache );

					if ( ! empty( $vimeo_data ) ) {
						$videos[] = $vimeo_data;
					}
				} elseif ( $this->is_twitter_url( $video ) ) {
					$twitter_data = Video_Sitemap::get_twitter_data( $video, $this->settings->enable_video_api_cache );

					if ( ! empty( $twitter_data ) ) {
						$videos[] = $twitter_data;
					}
				}
			}
		}

		if ( ! empty( $videos ) ) {
			// Remove old URL if it exists
			$this->urls = array_filter(
				$this->urls,
				function( $item ) use ( $url ) {
					return $item[0] !== $url;
				}
			);

			$this->urls[] = array(
				$url, // URL
				$videos, // Videos
			);
		}
	}

	public function filter_value( string $value ): bool {
		$extensions = explode( '.', $value );
		$extension  = end( $extensions );

		return 'video' === wp_ext2type( $extension )
			|| ( sgg_pro_enabled() && (
				$this->is_youtube_url( $value ) ||
				$this->is_vimeo_url( $value ) ||
				$this->is_twitter_url( $value )
			) );
	}

	/**
	 * Detect a YouTube video URL
	 */
	public function is_youtube_url( $url ) {
		return (
			false !== strpos( $url, 'https://www.youtube.com/embed/' ) ||
			false !== strpos( $url, 'https://youtu.be/' ) ||
			false !== strpos( $url, 'https://www.youtube.com/watch?v=' ) ||
			false !== strpos( $url, '//www.youtube.com/embed/' ) ||
			false !== strpos( $url, '//youtu.be/' ) ||
			false !== strpos( $url, '//www.youtube.com/watch?v=' )
		);
	}

	/**
	 * Detect a Vimeo video URL
	 */
	public function is_vimeo_url( $url ) {
		return (
			false !== strpos( $url, 'https://vimeo.com/' ) ||
			false !== strpos( $url, 'https://player.vimeo.com/video/' )
		);
	}

	/**
	 * Detect a Twitter post URL
	 */
	public function is_twitter_url( $url ) {
		return preg_match( '#https?://(?:www\.)?(?:twitter\.com|x\.com)/[^/]+/status/(\d+)#i', $url );
	}

	/**
	 * Detect an Instagram post, Reel or IGTV URL
	 */
	public function is_instagram_url( $url ) {
		return preg_match( '#https?://(?:www\.)?instagram\.com/(?:p|reel|tv)/([A-Za-z0-9_-]+)/?#i', $url );
	}

	/**
	 * Detect a TikTok video URL (full or “vm.” shortlink)
	 */
	public function is_tiktok_url( $url ): bool {
		return (bool) preg_match( '~^https?://(?:www\.|m\.)?tiktok\.com/@[A-Za-z0-9._-]+/video/\d+(?:[/?].*)?$~i', $url )
			|| (bool) preg_match( '~^https?://vm\.tiktok\.com/[A-Za-z0-9_-]+/?$~i', $url );
	}
}
