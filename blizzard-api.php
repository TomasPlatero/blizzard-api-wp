<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://artictempest.es
 * @since             1.0.0
 * @package           Blizzard_Api
 *
 * @wordpress-plugin
 * Plugin Name:       Blizzard Api Connector
 * Plugin URI:        https://www.artictempest.es
 * Description:       WordPress Connector for Blizzard API
 * Version:           1.0.0
 * Author:            Zatoshi
 * Author URI:        https://artictempest.es/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       blizzard-api
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
define( 'BLIZZARD_API_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-blizzard-api-activator.php
 */
function activate_blizzard_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-blizzard-api-activator.php';
	Blizzard_Api_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-blizzard-api-deactivator.php
 */
function deactivate_blizzard_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-blizzard-api-deactivator.php';
	Blizzard_Api_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_blizzard_api' );
register_deactivation_hook( __FILE__, 'deactivate_blizzard_api' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-blizzard-api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_blizzard_api() {

	$plugin = new Blizzard_Api();
	$plugin->run();

}
run_blizzard_api();
