<?php
/**
 * This file is the main entry point for WordPress functions.
 *
 * @package fuxt-backend
 */

define( 'FUXT_VERSION', wp_get_theme( 'fuxt-backend' )->get( 'Version' ) );

/**
 * Load all modules.
 */
require_once get_template_directory() . '/functions/wp-functions.php';
require_once get_template_directory() . '/functions/gql-functions.php';
require_once get_template_directory() . '/functions/theme-config.php';
require_once get_template_directory() . '/functions/gutenberg-functions.php';
require_once get_template_directory() . '/functions/acf-functions.php';
require_once get_template_directory() . '/functions/cookie-manager.php';
require_once get_template_directory() . '/functions/plugin-manifest.php';
require_once get_template_directory() . '/functions/developer-role.php';
require_once get_template_directory() . '/functions/widgets.php';
