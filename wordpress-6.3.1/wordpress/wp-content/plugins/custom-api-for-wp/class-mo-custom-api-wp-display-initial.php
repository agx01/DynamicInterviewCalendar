<?php
/**
 * Initialization for plugin display.
 *
 * @package    Custom_Api_For_WordPress
 * @subpackage Custom_Api_For_WordPress/includes
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

/**
 * Including required files.
 */
require_once 'class-mo-custom-api-wp-nav.php';

/**
 * Initializes all the requirements for plugin display.
 */
class MO_Custom_API_WP_Display_Initial {

	/**
	 * Constructor function
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'custom_api_wp_menu' ) );
		add_action( 'admin_footer', array( $this, 'custom_api_client_feedback_request' ) );

	}

	/**
	 * Add Custom API plugin option in WordPress's Side menu
	 */
	public function custom_api_wp_menu() {
		$slug = 'custom_api_wp_settings';
		add_menu_page(
			'MO API Settings ' . __( 'Configure Custom API Settings', 'custom_api_wp_settings' ),
			'Custom API plugin',
			'administrator',
			$slug,
			array(
				$this,
				'custom_api_wp_widget_options',
			),
			plugin_dir_url( __FILE__ ) . 'images/miniorange-logo.png'
		);
	}

	/**
	 * Enquies all the required styles and scripts.
	 * Loads the Main page of the plugin.
	 */
	public function custom_api_wp_widget_options() {
		wp_enqueue_script( 'custom-api-wp-phone', plugins_url( '/js/custom-api-wp-phone-min.js', __FILE__ ), array(), '0.8.3', false );
		wp_enqueue_script( 'custom-api-wp', plugins_url( '/js/custom-api-wp-min.js', __FILE__ ), array(), '2.5.0', false );
		wp_enqueue_script( 'custom-wp-popper-min', plugins_url( '/js/popper.min.js', __FILE__ ), array(), '3.0.0', false );
		wp_enqueue_script( 'custom-wp-bootstrap-min', plugins_url( '/js/bootstrap-min.js', __FILE__ ), array(), '4.5.0', false );
		wp_enqueue_script( 'custom-wp-bootstrap-multiselect', plugins_url( '/js/bootstrap-multiselect-min.js', __FILE__ ), array(), '2.0', false );

		wp_enqueue_style( 'custom-wp-bootstrap-min', plugins_url( '/css/bootstrap-min.css', __FILE__ ), array(), '4.5.0' );
		wp_enqueue_style( 'custom-wp-bootstrap-multiselect-css', plugins_url( '/css/bootstrap-multiselect-min.css', __FILE__ ), array(), '3.0.0' );
		wp_enqueue_style( 'custom-api-wp-css', plugins_url( '/css/custom-api-wp-css-min.css', __FILE__ ), array(), '2.5.0' );
		wp_enqueue_style( 'custom-api-wp-license-css', plugins_url( '/css/custom-api-wp-license-css-min.css', __FILE__ ), array(), '2.5.0' );
		wp_enqueue_style( 'custom-api-wp-phone-css', plugins_url( '/css/phone-min.css', __FILE__ ), array(), '2.5.0' );
		update_option( 'cutom_api_wp_host_name', 'https://login.xecurify.com' );
		MO_Custom_API_WP_Nav::custom_api_wp_main_menu_load();

	}

	/**
	 * Function call for feedback form display.
	 */
	public function custom_api_client_feedback_request() {
		MO_Custom_API_WP_Feedback_Form::custom_api_client_display_feedback_form();
	}
}
