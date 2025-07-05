<?php

use GRIM_SG\Dashboard;
/**
 * @var $args
 */

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'WP CLI', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p>
			<?php esc_html_e( 'WP CLI is a feature that allows you to generate sitemaps using the command line. You can use the following command to generate sitemaps:', 'xml-sitemap-generator-for-google' ); ?>
			<pre><code style="max-width: 400px;">
				$ wp sitemap generate --template=sitemap
				<br>
				$ wp sitemap generate --template=image-sitemap
				<br>
				$ wp sitemap generate --template=video-sitemap
				<br>
				$ wp sitemap generate --template=google-news
			</code></pre>
		</p>
		<p>
			<?php esc_html_e( 'You can find more information about WP CLI in', 'xml-sitemap-generator-for-google' ); ?>
			<a href="https://wp-cli.org/" target="_blank">https://wp-cli.org/</a>.
		</p>
		<p>
			<?php esc_html_e( 'Also, you can set Cron Job to generate sitemaps automatically using WP CLI command. Here is an example for daily cron job:', 'xml-sitemap-generator-for-google' ); ?>
			<pre><code style="max-width: 800px;">0 0 * * * /usr/local/bin/wp sitemap generate --template=image-sitemap --path=/var/www/html --quiet</code></pre>
		</p>
	</div>
</div>
