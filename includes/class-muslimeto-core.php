<?php

@ini_set( 'display_errors', 1 );



/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://muslimeto.com/
 * @since      1.0.0
 *
 * @package    Muslimeto_Core
 * @subpackage Muslimeto_Core/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Muslimeto_Core
 * @subpackage Muslimeto_Core/includes
 * @author     Muslimeto <info@muslimeto.com>
 */
class Muslimeto_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Muslimeto_Core_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'MUSLIMETO_CORE_VERSION' ) ) {
			$this->version = MUSLIMETO_CORE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'muslimeto-core';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Muslimeto_Core_Loader. Orchestrates the hooks of the plugin.
	 * - Muslimeto_Core_i18n. Defines internationalization functionality.
	 * - Muslimeto_Core_Admin. Defines all hooks for the admin area.
	 * - Muslimeto_Core_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-muslimeto-core-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-muslimeto-core-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-muslimeto-core-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-muslimeto-core-public.php';



		$this->loader = new Muslimeto_Core_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Muslimeto_Core_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Muslimeto_Core_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Muslimeto_Core_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Muslimeto_Core_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Muslimeto_Core_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}



// include TGM activator class
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-tgm-plugin-activation.php' );
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-muslimeto-TGM.php' );

/**
 * Detect plugin. For use on Front End and Back End.
 */

function plugin_is_active($plugin_var) {
    return in_array( $plugin_var. '/' .$plugin_var. '.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
}



if ( class_exists( 'Redux_Core' ) ) {
// include Redux config file
    require_once(plugin_dir_path(dirname(__FILE__)) . '/includes/class-muslimeto-redux-config.php');
}

// include core functions
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-functions.php' );

// include new bookly appoitment form
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-bookly-appoitment-form.php' );

// include Gravity forms functions
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-gravity-forms-functions.php' );

// include Calendar Shortcode functions
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-calendar-shortcode.php' );

// include Test Shortcode functions
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-test.php' );

// include sync Bookly Learners & Staff
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-sync-bookly-users.php' );

// include check Staff availability
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-check-staff-available.php' );

// include check Staff availability
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-shortcodes.php' );

// include custom func.
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/custom-func.php' );

// include cron jobs
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-cron-jobs.php' );

// include ajax actions
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-ajax-calls.php' );

// include background sync tasks
//require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/wp-background-process.php' );
//require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/wp-async-request.php' );
