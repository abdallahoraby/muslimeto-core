<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://muslimeto.com/
 * @package           Muslimeto_Core
 *
 * @wordpress-plugin
 * Plugin Name:       Muslimeto Core
 * Plugin URI:        https://muslimeto.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.85
 * Author:            Muslimeto
 * Author URI:        https://muslimeto.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       muslimeto-core
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
define( 'MUSLIMETO_CORE_VERSION', '1.0.85' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-muslimeto-core-activator.php
 */
function activate_muslimeto_core() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-muslimeto-core-activator.php';
	Muslimeto_Core_Activator::activate();
}

/*
*
* Use the code at the beginning of a plugin that you want to be laoded at last
*
*/
function this_plugin_last() {
    $wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
    $this_plugin = plugin_basename(trim($wp_path_to_this_file));
    $active_plugins = get_option('active_plugins');
    $this_plugin_key = array_search($this_plugin, $active_plugins);
    array_splice($active_plugins, $this_plugin_key, 1);
    array_push($active_plugins, $this_plugin);
    update_option('active_plugins', $active_plugins);
}
add_action("activated_plugin", "this_plugin_last");

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-muslimeto-core-deactivator.php
 */
function deactivate_muslimeto_core() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-muslimeto-core-deactivator.php';
	Muslimeto_Core_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_muslimeto_core' );
register_deactivation_hook( __FILE__, 'deactivate_muslimeto_core' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-muslimeto-core.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_muslimeto_core() {

	$plugin = new Muslimeto_Core();
	$plugin->run();

}
run_muslimeto_core();
