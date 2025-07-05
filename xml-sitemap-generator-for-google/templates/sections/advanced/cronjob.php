<?php

use GRIM_SG\Dashboard;
/**
 * @var $args
 */

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle">
	<?php
		esc_html_e( 'Cron Job', 'xml-sitemap-generator-for-google' );

		sgg_show_pro_badge();
	?>
	</h3>
	<div class="inside">
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p>
				<?php esc_html_e( 'Cron Job is a feature that allows you to schedule the generation of sitemaps at specific times.', 'xml-sitemap-generator-for-google' ); ?>
			</p>
			<p>
				<strong>
					<?php
					Dashboard::render(
						'fields/checkbox.php',
						array(
							'name'  => 'enable_cronjob',
							'value' => $settings->enable_cronjob ?? false,
							'label' => esc_html__( 'Enable Cron Job', 'xml-sitemap-generator-for-google' ),
							'class' => 'has-dependency',
							'data'  => array( 'target' => 'cronjob' ),
						)
					);
					?>
				</strong>
			</p>

			<p class="cronjob-runtime-group">
				<?php
				$schedules       = wp_get_schedules();
				$cronjob_options = array();

				foreach ( $schedules as $key => $schedule ) {
					$cronjob_options[ $key ] = $schedule['display'];
				}

				Dashboard::render(
					'fields/select.php',
					array(
						'name'    => 'cronjob_runtime',
						'label'   => esc_html__( 'Cron Job Run Time:', 'xml-sitemap-generator-for-google' ),
						'value'   => $settings->cronjob_runtime ?? 'daily',
						'options' => $cronjob_options,
						'class'   => 'cronjob',
					)
				);
				?>
			</p>

			<p class="cronjob">
				<?php
				$next_run = wp_next_scheduled( 'xml_sitemap_cronjob' );

				if ( $next_run ) {
					printf(
						'%s <strong>%s</strong>',
						esc_html__( 'Cron Job Next Run:', 'xml-sitemap-generator-for-google' ),
						esc_html( gmdate( 'Y-m-d H:i:s', $next_run ) )
					);
				}
				?>
			</p>

			<p class="cronjob">
				<?php esc_html_e( 'Cron Job will run at the time you selected and will be triggered by WordPress Cron. It will clear all caches and re-generate Sitemaps.', 'xml-sitemap-generator-for-google' ); ?>
				<br>
				<?php esc_html_e( 'You can move the cron job to the system task scheduler by following the instructions', 'xml-sitemap-generator-for-google' ); ?> - 
				<a href="https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/" target="_blank"><?php esc_html_e( 'Hooking WP Cron into the System Task Scheduler', 'xml-sitemap-generator-for-google' ); ?></a>.
			</p>

			<?php sgg_show_pro_overlay(); ?>
		</div>
	</div>
</div>
