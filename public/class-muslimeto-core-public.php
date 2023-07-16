<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://muslimeto.com/
 * @since      1.0.0
 *
 * @package    Muslimeto_Core
 * @subpackage Muslimeto_Core/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Muslimeto_Core
 * @subpackage Muslimeto_Core/public
 * @author     Muslimeto <info@muslimeto.com>
 */
class Muslimeto_Core_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Muslimeto_Core_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Muslimeto_Core_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        wp_enqueue_style( 'select2-style', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), rand(), 'all' );
        wp_enqueue_style( 'muslimeto-micromodal-style', plugin_dir_url(__FILE__) . 'css/micromodal.css', array(), rand(), 'all');
        wp_enqueue_style( 'muslimeto-floating-messages-style', plugin_dir_url(__FILE__) . 'css/jquery.floating-messages.min.css', array(), rand(), 'all');
        wp_enqueue_style( 'datepicker-style', plugin_dir_url( __FILE__ ) . 'css/bootstrap-datetimepicker.min.css', array(), rand(), 'all' );
        wp_enqueue_style( 'dataTables-style', plugin_dir_url( __FILE__ ) . 'css/jquery.dataTables.min.css', array(), rand(), 'all' );
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/muslimeto-core-public.css', array(), rand(), 'all' );


	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Muslimeto_Core_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Muslimeto_Core_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
        wp_enqueue_script( 'select2-script', plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js', array( 'jquery' ), rand(), true );
        wp_enqueue_script( 'muslimeto-micromodal-script', plugin_dir_url(__FILE__) . 'public/js/micromodal.min.js', array('jquery'), rand(), true );
        wp_enqueue_script( 'datepicker-script', plugin_dir_url( __FILE__ ) . 'js/bootstrap-datetimepicker.min.js', array( 'jquery' ), rand(), true );
        wp_enqueue_script( 'parsley-script', plugin_dir_url( __FILE__ ) . 'js/parsley.min.js', array( 'jquery' ), rand(), true );
        wp_enqueue_script( 'floating-messages-script', plugin_dir_url( __FILE__ ) . 'js/jquery.floating-messages.min.js', array( 'jquery' ), rand(), true );
        wp_enqueue_script( 'dataTables-script', plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.min.js', array( 'jquery' ), rand(), true );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/muslimeto-core-public.js', array( 'jquery' ), rand(), true );
        wp_enqueue_script( 'custom-script', plugin_dir_url( __FILE__ ) . 'js/custom.js', array( 'jquery' ), rand(), true );

 
	}






}


// register bootstrap scripts
function select2_scripts_register() {
    wp_register_style( 'select2-full-styles', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), rand(), 'all' );
    wp_register_script( 'select2-full-scripts', plugin_dir_url(__FILE__) . 'js/select2.full.min.js', array('jquery'), rand(), true );
}
add_action( 'wp_enqueue_scripts', 'select2_scripts_register' );
