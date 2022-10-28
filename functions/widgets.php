<?php
/**
 * Override default WordPress features.
 *
 * @package fuxt-backend
 */

/**
 * Renders the Funkhaus News dashboard widget.
 */
function fuxt_dashboard_events_news() {
	?>

	<div class="wordpress-news hide-if-no-js">
		<?php fuxt_dashboard_primary(); ?>
	</div>

	<?php
}

/**
 * Override 'WordPress Events and News' dashboard widget.
 */
function fuxt_dashboard_primary() {
	$feeds = array(
		'news' => array(

			/**
			 * Filters the primary link URL for the 'WordPress Events and News' dashboard widget.
			 *
			 * @param string $link The widget's primary link URL.
			 */
			'link'         => apply_filters( 'dashboard_primary_link', __( 'https://funkhaus.us/blog/', 'fuxt' ) ),

			/**
			 * Filters the primary feed URL for the 'WordPress Events and News' dashboard widget.
			 *
			 * @param string $url The widget's primary feed URL.
			 */
			'url'          => apply_filters( 'dashboard_primary_feed', __( 'https://api.funkhaus.us/feed/', 'fuxt' ) ),

			/**
			 * Filters the primary link title for the 'WordPress Events and News' dashboard widget.
			 *
			 * @param string $title Title attribute for the widget's primary link.
			 */
			'title'        => apply_filters( 'dashboard_primary_title', __( 'Funkhaus News', 'fuxt' ) ),
			'items'        => 5,
			'show_summary' => 0,
			'show_author'  => 0,
			'show_date'    => 0,
		),
	);

	wp_dashboard_cached_rss_widget( 'dashboard_primary', 'wp_dashboard_primary_output', $feeds );
}

/**
 * Add custom dashboard widget
 */
function fuxt_add_dashboard_widget() {
	// Unset default WordPress news widget.
	// Can't use remove_meta_box as it set false value instead of unset.
	global $wp_meta_boxes;
	unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'] );

	// Override default news block with custom one
	wp_add_dashboard_widget( 'dashboard_primary', __( 'Funkhaus News', 'fuxt' ), 'fuxt_dashboard_events_news' );
}
add_action( 'wp_network_dashboard_setup', 'fuxt_add_dashboard_widget' );
add_action( 'wp_user_dashboard_setup', 'fuxt_add_dashboard_widget' );
add_action( 'wp_dashboard_setup', 'fuxt_add_dashboard_widget' );

/**
 * Override behavior of wp_ajax_dashboard_widgets.
 */
function fuxt_ajax_dashboard_widgets() {
	require_once ABSPATH . 'wp-admin/includes/dashboard.php';

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['pagenow'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$pagenow = $_GET['pagenow'];
		if ( in_array( $pagenow, array( 'dashboard-user', 'dashboard-network', 'dashboard' ) ) ) {
			set_current_screen( $pagenow );
		}
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['widget'] ) && $_GET['widget'] == 'dashboard_primary' ) {
		fuxt_dashboard_primary();
	}
	wp_die();
}
add_action( 'wp_ajax_dashboard-widgets', 'fuxt_ajax_dashboard_widgets', 1 );
