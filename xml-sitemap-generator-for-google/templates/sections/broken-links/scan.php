<?php
/**
 * Broken Links — scan links and images inside published sitemap content.
 *
 * @var $args
 */
?>
<div class="grim-section sgg-broken-links-section <?php echo esc_attr( sgg_pro_class() ); ?>" id="broken_links">
	<?php sgg_show_pro_badge(); ?>
	<h3 class="grim-section-title" data-search-id="broken_links"><?php esc_html_e( 'Broken Links', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="grim-section-desc grim-mb-20">
				<?php esc_html_e( 'Scan links and images inside published content that is included in your sitemap, and find the ones that are broken.', 'xml-sitemap-generator-for-google' ); ?>
			</p>

			<div class="sgg-bl-summary grim-mb-20">
				<button type="button" class="sgg-bl-summary-card" data-filter="broken">
					<span class="sgg-bl-summary-count sgg-bl-status-broken" id="sgg-bl-summary-broken">0</span>
					<span class="sgg-bl-summary-label"><?php esc_html_e( 'Broken', 'xml-sitemap-generator-for-google' ); ?></span>
				</button>
				<button type="button" class="sgg-bl-summary-card" data-filter="redirect">
					<span class="sgg-bl-summary-count" id="sgg-bl-summary-redirect">0</span>
					<span class="sgg-bl-summary-label"><?php esc_html_e( 'Redirects', 'xml-sitemap-generator-for-google' ); ?></span>
				</button>
				<button type="button" class="sgg-bl-summary-card" data-filter="unknown">
					<span class="sgg-bl-summary-count" id="sgg-bl-summary-unknown">0</span>
					<span class="sgg-bl-summary-label"><?php esc_html_e( 'Could not check', 'xml-sitemap-generator-for-google' ); ?></span>
				</button>
				<button type="button" class="sgg-bl-summary-card" data-filter="all">
					<span class="sgg-bl-summary-count" id="sgg-bl-summary-checked">0</span>
					<span class="sgg-bl-summary-label"><?php esc_html_e( 'URLs checked', 'xml-sitemap-generator-for-google' ); ?></span>
				</button>
			</div>

			<div class="sgg-bl-actions grim-mb-20">
				<button type="button" class="grim-button secondary" id="sgg-bl-run-scan">
					<span><?php esc_html_e( 'Run scan', 'xml-sitemap-generator-for-google' ); ?></span>
				</button>
				<div class="sgg-bl-progress hidden-area" id="sgg-bl-progress">
					<div class="sgg-bl-progress-bar">
						<div class="sgg-bl-progress-fill" id="sgg-bl-progress-fill"></div>
					</div>
					<span class="sgg-bl-progress-text" id="sgg-bl-progress-text"></span>
				</div>
				<span class="sgg-bl-last-scan" id="sgg-bl-last-scan"></span>
			</div>

			<div class="sgg-bl-filters grim-mb-15">
				<div class="sgg-bl-filter-pills">
					<button type="button" class="sgg-bl-filter-pill active" data-filter="all"><?php esc_html_e( 'All issues', 'xml-sitemap-generator-for-google' ); ?></button>
					<button type="button" class="sgg-bl-filter-pill" data-filter="broken"><?php esc_html_e( 'Broken', 'xml-sitemap-generator-for-google' ); ?></button>
					<button type="button" class="sgg-bl-filter-pill" data-filter="redirect"><?php esc_html_e( 'Redirects', 'xml-sitemap-generator-for-google' ); ?></button>
					<button type="button" class="sgg-bl-filter-pill" data-filter="unknown"><?php esc_html_e( 'Could not check', 'xml-sitemap-generator-for-google' ); ?></button>
				</div>
				<input type="search" class="grim-input sgg-bl-search" id="sgg-bl-search" placeholder="<?php esc_attr_e( 'Search URLs…', 'xml-sitemap-generator-for-google' ); ?>">
			</div>

			<div class="sgg-bl-table-wrapper">
				<table class="grim-table wp-list-table widefat fixed striped" id="sgg-bl-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'URL', 'xml-sitemap-generator-for-google' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Found in', 'xml-sitemap-generator-for-google' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'xml-sitemap-generator-for-google' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Type', 'xml-sitemap-generator-for-google' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Actions', 'xml-sitemap-generator-for-google' ); ?></th>
						</tr>
					</thead>
					<tbody id="sgg-bl-table-body">
						<tr class="sgg-bl-empty-row">
							<td colspan="5"><?php esc_html_e( 'No scan yet. Run a scan to check links and images in your published content.', 'xml-sitemap-generator-for-google' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="sgg-bl-footer">
				<div class="sgg-bl-pagination hidden-area" id="sgg-bl-pagination">
					<span class="sgg-bl-pagination-info" id="sgg-bl-pagination-info"></span>
					<div class="sgg-bl-pagination-controls" id="sgg-bl-pagination-controls"></div>
				</div>
				<a href="#" class="sgg-bl-show-ignored hidden-area" id="sgg-bl-show-ignored"><?php esc_html_e( 'Show ignored', 'xml-sitemap-generator-for-google' ); ?></a>
			</div>

			<div class="sgg-bl-ignored hidden-area" id="sgg-bl-ignored">
				<h4 class="sgg-bl-ignored-title"><?php esc_html_e( 'Ignored URLs', 'xml-sitemap-generator-for-google' ); ?></h4>
				<ul class="sgg-bl-ignored-list" id="sgg-bl-ignored-list"></ul>
			</div>
		</div>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>

<?php
$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>" id="broken_links_schedule">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-toggle-section">
		<strong>
			<?php
			\GRIM_SG\Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'enable_broken_links_cron',
					'value' => $settings->enable_broken_links_cron ?? false,
					'label' => esc_html__( 'Scheduled Scans', 'xml-sitemap-generator-for-google' ),
					'class' => 'has-dependency',
					'data'  => array( 'target' => 'broken-links-cron' ),
				)
			);
			?>
		</strong>
	</div>
	<div class="inside">
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="grim-section-desc grim-mb-20 broken-links-cron">
				<?php esc_html_e( 'Automatically re-scan links and images in published content.', 'xml-sitemap-generator-for-google' ); ?>
			</p>

			<p class="broken-links-cron">
				<?php
				\GRIM_SG\Dashboard::render(
					'fields/select.php',
					array(
						'name'        => 'broken_links_schedule',
						'label'       => esc_html__( 'Scan Schedule:', 'xml-sitemap-generator-for-google' ),
						'value'       => $settings->broken_links_schedule ?? 'weekly',
						'options'     => array(
							'daily'   => esc_html__( 'Daily', 'xml-sitemap-generator-for-google' ),
							'weekly'  => esc_html__( 'Weekly', 'xml-sitemap-generator-for-google' ),
							'monthly' => esc_html__( 'Monthly', 'xml-sitemap-generator-for-google' ),
						),
						'class'       => 'broken-links-cron',
						'select_size' => 'grim-select-long',
					)
				);
				?>
			</p>

			<p class="broken-links-cron grim-mt-20">
				<?php
				$next_run = wp_next_scheduled( 'sgg_broken_links_cron' );

				if ( $next_run ) {
					printf(
						'%s <strong>%s</strong>',
						esc_html__( 'Next scheduled scan:', 'xml-sitemap-generator-for-google' ),
						esc_html(
							wp_date(
								get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
								$next_run
							)
						)
					);
				}
				?>
			</p>
		</div>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>
