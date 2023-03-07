<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://santoro.studio
 * @since             1.0.0
 * @package           Wpmu_Client
 *
 * @wordpress-plugin
 * Plugin Name:       WPMU Client
 * Plugin URI:        https://santoro.studio
 * Description:       Adds a simple client field to the WPMU new blog form on WordPress dashboard.
 * Version:           1.0.0
 * Author:            Edson Del Santoro
 * Author URI:        https://santoro.studio
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpmu-client
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPMU_CLIENT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpmu-client-activator.php
 */
function activate_wpmu_client() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpmu-client-activator.php';
	Wpmu_Client_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpmu-client-deactivator.php
 */
function deactivate_wpmu_client() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpmu-client-deactivator.php';
	Wpmu_Client_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpmu_client' );
register_deactivation_hook( __FILE__, 'deactivate_wpmu_client' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpmu-client.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpmu_client() {

	$plugin = new Wpmu_Client();
	$plugin->run();

}
run_wpmu_client();
