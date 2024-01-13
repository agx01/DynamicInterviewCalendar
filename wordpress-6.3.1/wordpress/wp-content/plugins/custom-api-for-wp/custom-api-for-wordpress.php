<?php //phpcs:ignore WPShield_Standard.Security.DisallowBrandAndImproperPluginName.ImproperPluginName -- The File name cannot be changed for the main file at this point.
/**
 * Main file to handle all functionalities flow.
 *
 * @package    Custom_Api_For_WordPress
 * @subpackage Custom_Api_For_WordPress/includes
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.miniorange.com
 * @since             1.0.0
 * @package           Custom_Api_For_WordPress
 *
 * @wordpress-plugin
 * Plugin Name:       Custom API for WP
 * Plugin URI:        custom-api-for-wp
 * Description:       This plugin helps in creating custom API endpoints for extracting customized data from the database. The plugin can also be extended to integrate external APIs in WordPress.
 * Version:           2.8.0
 * Author:            miniOrange
 * Author URI:        https://www.miniorange.com
 * License:           MIT/Expat
 * License URI:       https://docs.miniorange.com/mit-license
 */

require_once 'class-mo-custom-api-wp-nav.php';
require_once 'class-mo-custom-api-wp-display-initial.php';
require_once 'mo-custom-api-wp-customer.php';
require_once 'class-mo-custom-api-wp-feedback-form.php';

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CUSTOM_API_FOR_WORDPRESS_VERSION', '2.8.0' );

update_option( 'mo_custom_api_wp_version', CUSTOM_API_FOR_WORDPRESS_VERSION );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-custom-api-for-wordpress-activator.php
 */
function activate_custom_api_for_word_press() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-api-for-wordpress-activator.php';
	Custom_Api_For_WordPress_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-custom-api-for-wordpress-deactivator.php
 */
function deactivate_custom_api_for_word_press() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-api-for-wordpress-deactivator.php';
	Custom_Api_For_WordPress_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_custom_api_for_word_press' );
register_deactivation_hook( __FILE__, 'deactivate_custom_api_for_word_press' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-custom-api-for-wordpress.php';

/**
 * Begins execution of the plugin.
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_custom_api_for_word_press() {
	$plugin = new Custom_Api_For_WordPress();
	$plugin->run();
}
run_custom_api_for_word_press();

add_action(
	'rest_api_init',
	function () {

		$get_var = get_option( 'CUSTOM_API_WP_LIST' );

		if ( isset( $get_var ) && ! empty( $get_var ) && is_array( $get_var ) ) {
			foreach ( $get_var as $api_name => $value ) {
				$namespace = 'mo/v1';
				$route     = '';
				if ( 'no condition' === $value['SelectedCondtion'] ) {
					$route = $api_name;
				} else {
					$route = $api_name . '/(?P<id>[A-Za-z0-9\@\_\.\-\+]+)';
				}

				register_rest_route(
					$namespace,
					$route,
					array(
						'methods'  => 'GET',
						'callback' => 'custom_api_wp_get_result',
						'args'     => array( $value ),
					)
				);
			}
		}
	}
);

add_action(
	'rest_api_init',
	function () {

		$mo_sql_var = get_option( 'custom_api_wp_sql' );

		if ( isset( $mo_sql_var ) && null !== $mo_sql_var && is_array( $mo_sql_var ) ) {
			foreach ( $mo_sql_var as $sqlkey => $sqlvalue ) {

				$namespace = 'mo/v1';
				$route     = $sqlkey;

				register_rest_route(
					$namespace,
					$route,
					array(
						'methods'  => 'DELETE' === $sqlvalue['method'] ? 'DELETE' : $sqlvalue['method'],
						'callback' => 'custom_api_wp_get_sql_result',
						'args'     => array( $sqlvalue ),
					)
				);
			}
		}

		$get_var = get_option( 'CUSTOM_API_WP_LIST' );

		if ( isset( $get_var ) && null !== $get_var && is_array( $get_var ) ) {
			foreach ( $get_var as $api_name => $value ) {

				$namespace = 'mo/v1';
				$route     = '';

				$param_order = array();
				$order_var   = 0;
				if ( 'GET' === $value['MethodName'] ) {

					if ( 'no condition' === $value['SelectedCondtion'] ) {
						$route = $api_name;
					} else {
						$route                                      = $api_name;
						$param_order[ $value['SelectedParameter'] ] = $value['ConditionColumn'];
						$order_var++;
						if ( array_key_exists( 'param_if_op', $value ) ) {
							$parameter = $value['param_if_op'];
						} else {
							$parameter = '';
						}
						if ( array_key_exists( 'column_if_op', $value ) ) {
							$op_column = $value['column_if_op'];
						} else {
							$op_column = '';
						}
						if ( array_key_exists( 'param_if_op', $value ) ) {
							for ( $y = 0; $y < $value['condition_count']; $y++ ) {
								if ( ! empty( $op_column[ $y ] ) ) {
									$param_order[ $parameter[ $y ] ] = $op_column[ $y ];
									$order_var++;
								}
							}
						}

						for ( $y = 1; $y <= $order_var; $y++ ) {
							$route = '/' . $route . '/(?P<' . $param_order[ $y ] . '>\S+)';
						}
					}
				}

				if ( 'POST' === $value['MethodName'] ) {

					$route = $api_name;
				}

				if ( 'PUT' === $value['MethodName'] ) {

					$route = $api_name;
				}
				if ( 'DELETE' === $value['MethodName'] ) {
					$route = $api_name;
				}

				register_rest_route(
					$namespace,
					$route,
					array(
						'methods'  => 'DELETE' === $value['MethodName'] ? 'DELETE' : $value['MethodName'],
						'callback' => 'custom_api_wp_get_result',
						'args'     => array( $value ),
					)
				);
			}
		}
	}
);

/**
 * Responds To SQL API Calls.
 *
 * @param mixed $request API Call Request.
 *
 * @return string  In JSON format.
 */
function custom_api_wp_get_sql_result( $request ) {

	global $wpdb;

	$need = $request->get_attributes();

	$sql_query = $need['args'][0]['sql_query'];

	if ( 'GET' === $need['args'][0]['method'] ) {
		$error_response = array(
			'error'             => 'invalid_format',
			'error_description' => 'Required arguments are missing or are not passed in the correct format.',
		);
		if ( 'on' !== $need['args'][0]['query_params'] && '1' !== $need['args'][0]['query_params'] ) {
			$result = $wpdb->get_results( $sql_query ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery -- SQL queries are taken from the administrator and are required for this feature to work, and there is nonce verification as well as administrator check while accepting the queries from the user.
			return $result;
		}

		$pattern = '/{{[A-Z]*[a-z]*_[A-Z]*[a-z]*[0-9]*}}/';
		$matches = preg_match_all( $pattern, $sql_query, $reg_array );

		if ( 0 !== $matches && ( count( $_GET ) === count( $reg_array[0] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET is used to fetch the API call arguments, hence is no need for nonce verification.
			$i    = 0;
			$size = count( $_GET ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET is used to fetch the API call arguments, hence is no need for nonce verification.
			for ( $i = 0; $i < $size; $i++ ) {
				$mo_regex = substr( $reg_array[0][ $i ], 2 );
				$mo_regex = substr( $mo_regex, 0, -2 );

				if ( isset( $_GET[ $mo_regex ] ) && null !== $_GET[ $mo_regex ] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET is used to fetch the API call arguments, hence is no need for nonce verification.
						$sql_query = str_replace( $reg_array[0][ $i ], sanitize_text_field( wp_unslash( $_GET[ $mo_regex ] ) ), $sql_query ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET is used to fetch the API call arguments, hence is no need for nonce verification.
				} else {
					wp_send_json( $error_response, 400 );
				}
			}
			$result = $wpdb->get_results( $sql_query ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery -- SQL queries are taken from the administrator and are required for this feature to work, and there is nonce verification, as well as administrator, check while accepting the queries from the user.
			return $result;
		}

		wp_send_json( $error_response, 400 );

	} elseif ( 'POST' === $need['args'][0]['method'] ) {
		$error_response = array(
			'error'             => 'invalid_format',
			'error_description' => 'Required body parameters are missing or does not pass in the correct format.',
		);

		if ( 'on' !== $need['args'][0]['query_params'] && 1 !== (int) $need['args'][0]['query_params'] ) {
			$result = $wpdb->query( $sql_query ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery -- SQL queries are taken from the administrator and are required for this feature to work, and there is nonce verification, as well as administrator, check while accepting the queries from the user.
			return $result;
		}
		$pattern = '/{{[A-Z]*[a-z]*_[A-Z]*[a-z]*[0-9]*}}/';

		$matches = preg_match_all( $pattern, $sql_query, $reg_array );
		if ( 0 !== $matches && ( count( $_POST ) === count( $reg_array[0] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing -- POST is used to fetch the API call arguments, hence is no need for nonce verification.
			$i    = 0;
			$size = count( $_POST ); //phpcs:ignore WordPress.Security.NonceVerification.Missing -- POST is used to fetch the API call arguments, hence is no need for nonce verification.
			for ( $i = 0; $i < $size; $i++ ) {
				$mo_regex = substr( $reg_array[0][ $i ], 2 );
				$mo_regex = substr( $mo_regex, 0, -2 );
				if ( isset( $_POST[ $mo_regex ] ) && null !== $_POST[ $mo_regex ] ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing -- POST is used to fetch the API call arguments, hence is no need for nonce verification.
						$sql_query = str_replace( $reg_array[0][ $i ], sanitize_text_field( wp_unslash( $_POST[ $mo_regex ] ) ), $sql_query ); //phpcs:ignore WordPress.Security.NonceVerification.Missing -- POST is used to fetch the API call arguments, hence is no need for nonce verification.
				} else {
					wp_send_json( $error_response, 400 );
				}
			}
			$result = $wpdb->query( $sql_query ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery -- SQL queries are taken from the administrator and are required for this feature to work, and there is nonce verification, as well as administrator, check while accepting the queries from the user.
			return $result;
		}
		wp_send_json( $error_response, 400 );
	} else {
		$error_response = array(
			'error'             => 'invalid_format',
			'error_description' => 'Required body parameters are missing or do not passed in the correct format.',
		);

		if ( 'on' !== $need['args'][0]['query_params'] && 1 !== (int) $need['args'][0]['query_params'] ) {
			$result = $wpdb->query( $sql_query ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery -- SQL queries are taken from the administrator and are required for this feature to work, and there is nonce verification, as well as administrator, check while accepting the queries from the user.
			return $result;
		}

		$pattern    = '/{{[A-Z]*[a-z]*_[A-Z]*[a-z]*[0-9]*}}/';
		$get_params = $request->get_params();
		$matches    = preg_match_all( $pattern, $sql_query, $reg_array );

		if ( 0 !== $matches && ( count( $get_params ) === count( $reg_array[0] ) ) ) {
			$i    = 0;
			$size = count( $get_params );
			for ( $i = 0; $i < $size; $i++ ) {
				$mo_regex = substr( $reg_array[0][ $i ], 2 );
				$mo_regex = substr( $mo_regex, 0, -2 );

				if ( isset( $get_params[ $mo_regex ] ) && null !== $get_params[ $mo_regex ] ) {
						$sql_query = str_replace( $reg_array[0][ $i ], $get_params[ $mo_regex ], $sql_query );
				} else {
					wp_send_json( $error_response, 400 );
				}
			}

			$result = $wpdb->query( $sql_query ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery -- SQL queries are taken from the administrator and are required for this feature to work, and there is nonce verification, as well as administrator, check while accepting the queries from the user.
			return $result;
		}
		wp_send_json( $error_response, 400 );
	}

}

/**
 * Responds To Custom API Calls.
 *
 * @param mixed $request API Call Request.
 *
 * @return string  In JSON format.
 */
function custom_api_wp_get_result( $request ) {
	global $wpdb;

	$need = $request->get_attributes();

	$get_query1        = $need['args'][0]['query'];
	$selected_condtion = $need['args'][0]['SelectedCondtion'];
	if ( ( 'no condition' === $selected_condtion ) ) {

		$myrows = $wpdb->get_results( $wpdb->prepare( '%1s', $get_query1 ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
		return $myrows;
	} else {
		$spliting = explode( $selected_condtion, $get_query1 );

		$main_query = $spliting[0];
		$type       = gettype( $request['id'] );
		if ( 'string' === $type && 'Like' === $selected_condtion ) {
			$param = '%' . $request['id'] . '%';
		} elseif ( 'string' === $type ) {
			$param = $request['id'];
		}

		if ( 'integer' === $type ) {
			$param = $request['id'];
		}

		if ( '>' === $selected_condtion ) {
			$selected_condtion = '>';
		}

		if ( 'less than' === $selected_condtion ) {
			$selected_condtion = '<';
		}
		$selected_condtion = $selected_condtion . ' ';
		if ( isset( $param ) ) {
			$final_query = $main_query . $selected_condtion;
			$myrows      = $wpdb->get_results( $wpdb->prepare( '%1s %s', array( $final_query, $param ) ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
		}

		if ( isset( $myrows ) ) {
			return $myrows;
		}
	}
}


new MO_Custom_API_WP_Display_Initial();


add_filter( 'ExternalApiHook', 'mo_custom_external_api', 10, 4 );

/**
 * Handles execution of external API
 *
 * @param mixed $value1 API Name.
 * @param mixed $value2 API Body.
 * @param mixed $value3 API Headers.
 * @param bool  $value4  API endpoint.
 * @return [type]
 */
function mo_custom_external_api( $value1, $value2, $value3, $value4 = false ) {
	$external_api_array = get_option( 'custom_api_save_ExternalApiConfiguration' );

	if ( isset( $external_api_array[ $value1 ]['ExternalEndpoint'] ) || false !== $value4 ) {

		// Request URL: $request_url.
		$request_url = htmlspecialchars_decode( $external_api_array[ $value1 ]['ExternalEndpoint'] );
		if ( $value4 ) {
			$request_url = $value4;
		}
		$request_url = str_replace( '&amp;', '&', $request_url );

		// Request Headers: $external_headers.
		$external_headers = array();
		if ( isset( $value3 ) && is_array( $value3 ) ) {
			if ( count( $value3 ) ) {
				$external_headers = $value3;
			}
		} else {
			$external_headers = $external_api_array[ $value1 ]['ExternalHeaders'];
		}

		$external_api_request_type = $external_api_array[ $value1 ]['ExternalApiRequestType'];

		// Request post field (for PUT and POST method): $external_api_post_field.
		if ( 'GET' !== $external_api_request_type ) {

			if ( ! empty( $value2 ) ) {

				if ( 'x-www-form-urlencode' === $external_api_array[ $value1 ]['ExternalApiBodyRequestType'] ) {
					$external_api_post_field = http_build_query( $value2 );
				} else {
					$external_api_post_field = $value2;
				}
			} else {
				if ( 'x-www-form-urlencode' === $external_api_array[ $value1 ]['ExternalApiBodyRequestType'] ) {
					$bodyarr         = $external_api_array[ $value1 ]['ExternalApiPostFieldNew'];
					$body_temp_array = array();

					foreach ( $bodyarr as $bodyval ) {
						$pos = strpos( $bodyval, ':' );
						if ( false !== $pos && substr_count( $bodyval, ':' ) > 1 ) {
							$bodyval                            = substr_replace( $bodyval, '##mo_remove##', $pos, strlen( ':' ) );
							$body_temp_array                    = explode( '##mo_remove##', $bodyval );
							$body_params[ $body_temp_array[0] ] = $body_temp_array[1];
						} else {
							$body_temp_array                    = explode( ':', $bodyval );
							$body_params[ $body_temp_array[0] ] = $body_temp_array[1];
						}
					}

					$external_api_post_field = http_build_query( $body_params );
				} else {
					$external_api_post_field = $external_api_array[ $value1 ]['ExternalApiPostFieldNew'];
				}
			}
		}

		// All request arguments.
		$arguments = array(
			'method'  => $external_api_request_type,
			'headers' => $external_headers,
			'timeout' => 120, // execution time
			// 'stream'   => true, //to set file download.
			// 'filename' => utils, //name of to be written in.
		);

		if ( 'GET' !== $external_api_request_type && $external_api_request_type ) {
			$arguments['body'] = $external_api_post_field;
		}
		// Request.
		$returned_response = wp_remote_request( $request_url, $arguments );
		// Process response.
		if ( is_wp_error( $returned_response ) ) {
			return $returned_response->get_error_message();
		} else {
			$returned_response = $returned_response['body'];
			if ( json_decode( $returned_response ) ) {
				$external_api_array[ $value1 ]['ResponseBodyType'] = 'json';
			} else {
				if ( simplexml_load_string( $returned_response ) ) {
					$external_api_array[ $value1 ]['ResponseBodyType'] = 'xml';
				}
			}
			update_option( 'custom_api_save_ExternalApiConfiguration', $external_api_array );

			if ( 'xml' === $external_api_array[ $value1 ]['ResponseBodyType'] ) {
				$returned_response = simplexml_load_string( $returned_response );
				$returned_response = wp_json_encode( $returned_response );
			}
			if ( ! empty( $external_api_array[ $value1 ]['ExternalApiResponseDataKey'] ) && 'custom_api_wp_getall' !== $external_api_array[ $value1 ]['ExternalApiResponseDataKey'][0] ) {
				if ( ! empty( $external_api_array[ $value1 ]['ExternalApiResponseDataKey'][0] ) ) {
					return wp_json_encode( testattrmappingconfig( '', json_decode( $returned_response ), true, $external_api_array[ $value1 ]['ExternalApiResponseDataKey'] ) );
				}
			}
			return $returned_response;
		}
	} else {
		wp_die( 'Invalid API Name passed in external api connection hook :' . esc_attr( $value1 ) );
	}
}

/**
 * Displays notice for successful SQL API creation.
 */
function sample_admin_notice__success() {
	?>
	<div class="notice notice-success is-dismissible" style="width: 30%">
		<p>Endpoint with custom SQL created successfully.</p>
	</div>
	<?php
}

add_action( 'admin_init', 'custom_api_wp_functions' );

/**
 * Used for form handling of custom API and custom SQL API.
 */
function custom_api_wp_functions() {
	if ( current_user_can( 'administrator' ) ) {
		if ( isset( $_POST['SubmitForm1'] ) && ( isset( $_POST['SubmitUser1'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser1'] ) ), 'CheckNonce1' ) : false ) ) {
			if ( isset( $_POST['SubmitUser1'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser1'] ) ), 'CheckNonce1' ) : false ) {
				$data1 = array(
					'ApiName'    => isset( $_POST['api_name_initial'] ) ? sanitize_text_field( wp_unslash( $_POST['api_name_initial'] ) ) : '',
					'MethodName' => isset( $_POST['method_name_initial'] ) ? sanitize_text_field( wp_unslash( $_POST['method_name_initial'] ) ) : '',
					'TableName'  => isset( $_POST['table_name_initial'] ) ? sanitize_text_field( wp_unslash( $_POST['table_name_initial'] ) ) : '',
				);
				update_option( 'mo_custom_api_form1', $data1 );
			}
		}
	}

	if ( current_user_can( 'administrator' ) ) {
		if ( isset( $_POST['SubmitForm2'] ) ) {
			if ( isset( $_POST['SubmitUser2'] ) && ( isset( $_POST['SubmitUser2'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser2'] ) ), 'CheckNonce2' ) : false ) ) {
				$data2 = array(
					'ApiName'    => isset( $_POST['api_name_initial2'] ) ? sanitize_text_field( wp_unslash( $_POST['api_name_initial2'] ) ) : '',
					'MethodName' => isset( $_POST['method_name_initial2'] ) ? sanitize_text_field( wp_unslash( $_POST['method_name_initial2'] ) ) : '',
					'TableName'  => isset( $_POST['table_name_initial2'] ) ? sanitize_text_field( wp_unslash( $_POST['table_name_initial2'] ) ) : '',
				);
				update_option( 'mo_custom_api_form2', $data2 );
			}
		}
	}

	if ( current_user_can( 'administrator' ) ) {
		if ( isset( $_POST['SendResult'] ) ) {
			if ( isset( $_POST['SubmitUser'] ) && ( isset( $_POST['SubmitUser'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) : false ) ) {
				$conditon_column         = isset( $_POST['OnColumn'] ) ? sanitize_text_field( wp_unslash( $_POST['OnColumn'] ) ) : '';
				$condition_to_be_applied = isset( $_POST['ColumnCondition'] ) ? sanitize_text_field( wp_unslash( $_POST['ColumnCondition'] ) ) : '';
				$conditon_column         = 'no condition' === $condition_to_be_applied ? '' : $conditon_column;
				$condition_to_be_applied = '' === $conditon_column ? 'no condition' : $condition_to_be_applied;
				$data                    = array(
					'status'            => 'yes',
					'ApiName'           => isset( $_POST['ApiName'] ) ? sanitize_text_field( wp_unslash( $_POST['ApiName'] ) ) : '',
					'TableName'         => isset( $_POST['select-table'] ) ? sanitize_text_field( wp_unslash( $_POST['select-table'] ) ) : '',
					'MethodName'        => isset( $_POST['MethodName'] ) ? sanitize_text_field( wp_unslash( $_POST['MethodName'] ) ) : '',
					'SelectedColumn'    => isset( $_POST['Selectedcolumn11'] ) ? sanitize_text_field( wp_unslash( $_POST['Selectedcolumn11'] ) ) : '',
					'ConditionColumn'   => $conditon_column,
					'SelectedCondtion'  => $condition_to_be_applied,
					'SelectedParameter' => isset( $_POST['ColumnParam'] ) ? sanitize_text_field( wp_unslash( $_POST['ColumnParam'] ) ) : '',
					'query'             => isset( $_POST['QueryVal'] ) ? sanitize_text_field( wp_unslash( $_POST['QueryVal'] ) ) : '',
				);

				update_option( 'mo_custom_api_form', $data );
			}
		}
	}

	if ( isset( $_POST['option'] ) && sanitize_text_field( wp_unslash( $_POST['option'] ) ) === 'custom_api_wp_sql' && ( isset( $_REQUEST['custom_api_wp_sql_field'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['custom_api_wp_sql_field'] ) ), 'custom_api_wp_sql' ) : false ) ) {

		if ( current_user_can( 'administrator' ) ) {

			$list     = false !== get_option( 'custom_api_wp_sql' ) ? get_option( 'custom_api_wp_sql' ) : array();
			$api_name = isset( $_POST['SQLApiName'] ) ? sanitize_text_field( wp_unslash( $_POST['SQLApiName'] ) ) : '';
			if ( count( $list ) > 0 ) {
				if ( ! ( array_key_exists( $api_name, $list ) ) ) {
					$site_url = site_url() . '/wp-admin/admin.php?page=custom_api_wp_settings&action=savedcustomsql';
					wp_safe_redirect( $site_url );
					update_option( 'custom_api_wp_message', 'Max Custom SQL API limit reached. Please purchase premium version to add more API(s)' );
					custom_api_success_message();
					die();
				}
			}
			$current_form = array(
				'method'       => isset( $_POST['MethodName'] ) ? sanitize_text_field( wp_unslash( $_POST['MethodName'] ) ) : '',
				'sql_query'    => isset( $_POST['customsql'] ) ? sanitize_text_field( wp_unslash( $_POST['customsql'] ) ) : '',
				'query_params' => isset( $_POST['QueryParameter'] ) ? sanitize_text_field( wp_unslash( $_POST['QueryParameter'] ) ) : 0,
			);

			$current_apis = get_option( 'custom_api_wp_sql' ) ? get_option( 'custom_api_wp_sql' ) : array();

			$temp_array = array();

			if ( isset( $current_apis ) && null !== $current_apis && is_array( $current_apis ) ) {
				foreach ( $current_apis as $key => $value ) {
					$temp_array[ $key ] = $value;
				}
			}
			$temp_array[ $api_name ] = $current_form;
			update_option( 'custom_api_wp_sql', $temp_array );

			add_action( 'admin_notices', 'sample_admin_notice__success' );

			$site_url = site_url() . '/wp-admin/admin.php?page=custom_api_wp_settings&action=viewsql&api=' . $api_name;
			wp_safe_redirect( $site_url );
			die();
		}
	}

	if ( current_user_can( 'administrator' ) ) {
		if ( isset( $_POST['ExternalApiConnection'] ) ) {
			if ( isset( $_POST['SubmitUser'] ) && ( isset( $_POST['SubmitUser'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) : false ) ) {

				$external_api_name   = isset( $_POST['ExternalApiName'] ) ? sanitize_text_field( wp_unslash( $_POST['ExternalApiName'] ) ) : '';
				$header_count        = isset( $_POST['ExternalHeaderCount'] ) ? sanitize_text_field( wp_unslash( $_POST['ExternalHeaderCount'] ) ) : '';
				$response_body_count = isset( $_POST['ExternalResponseBodyCount'] ) ? sanitize_text_field( wp_unslash( $_POST['ExternalResponseBodyCount'] ) ) : '';
				$external_headers    = array();
				$external_endpoint   = isset( $_POST['ExternalApi'] ) ? sanitize_text_field( wp_unslash( $_POST['ExternalApi'] ) ) : '';

				$external_api_request_type      = isset( $_POST['MethodName'] ) ? sanitize_text_field( wp_unslash( $_POST['MethodName'] ) ) : '';
				$external_api_body_request_type = isset( $_POST['RequestBodyType'] ) ? sanitize_text_field( wp_unslash( $_POST['RequestBodyType'] ) ) : '';
				$external_api_request_body      = array();
				$external_api_request_body_json = isset( $_POST['RequestBodyJson'] ) ? sanitize_text_field( wp_unslash( $_POST['RequestBodyJson'] ) ) : '';
				$external_api_post_field        = '';

				if ( isset( $_POST['ExternalHeaderKey'] ) && isset( $_POST['ExternalHeaderValue'] ) && sanitize_text_field( wp_unslash( $_POST['ExternalHeaderKey'] ) ) !== null && sanitize_text_field( wp_unslash( $_POST['ExternalHeaderValue'] ) ) !== null ) {
					$external_headers[ sanitize_text_field( wp_unslash( $_POST['ExternalHeaderKey'] ) ) ] = sanitize_text_field( wp_unslash( $_POST['ExternalHeaderValue'] ) );

					if ( $header_count ) {
						for ( $x = 1; $x <= $header_count; $x++ ) {
							$header_key   = 'ExternalHeaderKey' . $x;
							$header_value = 'ExternalHeaderValue' . $x;
							if ( isset( $_POST[ $header_key ] ) ) {
								$external_headers[ sanitize_text_field( wp_unslash( $_POST[ $header_key ] ) ) ] = ( isset( $_POST[ $header_value ] ) ? sanitize_text_field( wp_unslash( $_POST[ $header_value ] ) ) : '' );
							}
						}
					}
				}

				if ( 'x-www-form-urlencode' === $external_api_body_request_type ) {
					if ( isset( $_POST['RequestBodyKey'] ) && isset( $_POST['RequestBodyValue'] ) && null !== sanitize_text_field( wp_unslash( $_POST['RequestBodyKey'] ) ) && null !== sanitize_text_field( wp_unslash( $_POST['RequestBodyValue'] ) ) ) {
						array_push( $external_api_request_body, sanitize_text_field( wp_unslash( $_POST['RequestBodyKey'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['RequestBodyValue'] ) ) );
						if ( $response_body_count ) {
							for ( $x = 1; $x <= $response_body_count; $x++ ) {
								$request_body_key   = 'RequestBodyKey' . $x;
								$request_body_value = 'RequestBodyValue' . $x;
								if ( isset( $_POST[ $request_body_key ] ) ) {
									array_push( $external_api_request_body, sanitize_text_field( wp_unslash( $_POST[ $request_body_key ] ) ) . ':' . ( isset( $_POST[ $request_body_value ] ) ? sanitize_text_field( wp_unslash( $_POST[ $request_body_value ] ) ) : '' ) );
								}
							}
						}
					}
					$external_api_post_field = $external_api_request_body;
				} else {
					$external_api_post_field = null !== $external_api_request_body_json ? $external_api_request_body_json : '';
				}

				$external_api_configuration = array(
					'ExternalApiName'            => $external_api_name,
					'ExternalApiRequestType'     => $external_api_request_type,
					'ExternalApiBodyRequestType' => $external_api_body_request_type,
					'ExternalApiPostFieldNew'    => $external_api_post_field,
					'ExternalEndpoint'           => $external_endpoint,
					'ExternalHeaders'            => $external_headers,
				);

				update_option( 'custom_api_test_ExternalApiConfiguration', $external_api_configuration );
				echo '<script>  window.open("' . esc_url( site_url() . '/wp-admin/?customapiexternal=testexecute' ) . '", "Test External API Execution", "width=600, height=600"); window.location.reload;</script>';
			}
		}
	}

	if ( current_user_can( 'administrator' ) ) {
		if ( isset( $_POST['ExternalApiConnectionSave'] ) ) {

			if ( isset( $_POST['SubmitUser'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) ) {

				$list = false !== get_option( 'custom_api_save_ExternalApiConfiguration' ) ? get_option( 'custom_api_save_ExternalApiConfiguration' ) : array();
				if ( count( $list ) > 0 ) {

					if ( ! ( array_key_exists( sanitize_text_field( wp_unslash( $_POST['ExternalApiName'] ) ), $list ) ) ) {
						header( 'Location: ?page=custom_api_wp_settings&action=savedexternalapi' );
						update_option( 'custom_api_wp_message', 'Max External API limit reached. Please purchase premium version to add more API(s)' );
						custom_api_success_message();
						die();
					}
				}
				$external_api_name              = isset( $_POST['ExternalApiName'] ) ? sanitize_text_field( wp_unslash( $_POST['ExternalApiName'] ) ) : '';
				$header_count                   = isset( $_POST['ExternalHeaderCount'] ) ? sanitize_text_field( wp_unslash( $_POST['ExternalHeaderCount'] ) ) : '';
				$external_endpoint              = isset( $_POST['ExternalApi'] ) ? sanitize_text_field( wp_unslash( $_POST['ExternalApi'] ) ) : '';
				$external_api_request_type      = isset( $_POST['MethodName'] ) ? sanitize_text_field( wp_unslash( $_POST['MethodName'] ) ) : '';
				$external_api_body_request_type = isset( $_POST['RequestBodyType'] ) ? sanitize_text_field( wp_unslash( $_POST['RequestBodyType'] ) ) : '';
				$external_api_response_data_key = explode( ',', ( isset( $_POST['selected_column_all'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_column_all'] ) ) : '' ) );
				$external_headers               = array();

				$response_body_count            = isset( $_POST['ExternalResponseBodyCount'] ) ? sanitize_text_field( wp_unslash( $_POST['ExternalResponseBodyCount'] ) ) : '';
				$external_endpoint              = isset( $_POST['ExternalApi'] ) ? sanitize_text_field( wp_unslash( $_POST['ExternalApi'] ) ) : '';
				$external_api_request_body      = array();
				$external_api_request_body_json = isset( $_POST['RequestBodyJson'] ) ? sanitize_text_field( wp_unslash( $_POST['RequestBodyJson'] ) ) : '';
				$external_api_post_field        = '';

				if ( isset( $_POST['ExternalHeaderKey'] ) && isset( $_POST['ExternalHeaderValue'] ) && sanitize_text_field( wp_unslash( $_POST['ExternalHeaderKey'] ) ) !== null && sanitize_text_field( wp_unslash( $_POST['ExternalHeaderValue'] ) ) !== null ) {
					$external_headers[ sanitize_text_field( wp_unslash( $_POST['ExternalHeaderKey'] ) ) ] = sanitize_text_field( wp_unslash( $_POST['ExternalHeaderValue'] ) );

					if ( $header_count ) {
						for ( $x = 1; $x <= $header_count; $x++ ) {
							$header_key   = 'ExternalHeaderKey' . $x;
							$header_value = 'ExternalHeaderValue' . $x;
							if ( isset( $_POST[ $header_key ] ) ) {
								$external_headers[ sanitize_text_field( wp_unslash( $_POST[ $header_key ] ) ) ] = sanitize_text_field( wp_unslash( $_POST[ $header_value ] ) );
							}
						}
					}
				}

				if ( 'x-www-form-urlencode' === $external_api_body_request_type ) {
					if ( isset( $_POST['RequestBodyKey'] ) && isset( $_POST['RequestBodyValue'] ) && sanitize_text_field( wp_unslash( $_POST['RequestBodyKey'] ) ) !== null && sanitize_text_field( wp_unslash( $_POST['RequestBodyValue'] ) !== null ) ) {
						array_push( $external_api_request_body, sanitize_text_field( wp_unslash( $_POST['RequestBodyKey'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['RequestBodyValue'] ) ) );

						if ( $response_body_count ) {
							for ( $x = 1; $x <= $response_body_count; $x++ ) {
								$request_body_key   = 'RequestBodyKey' . $x;
								$request_body_value = 'RequestBodyValue' . $x;
								if ( isset( $_POST[ $request_body_key ] ) ) {
									array_push( $external_api_request_body, sanitize_text_field( wp_unslash( $_POST[ $request_body_key ] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST[ $request_body_value ] ) ) );
								}
							}
						}
					}
					$external_api_post_field = $external_api_request_body;
				} else {
					$external_api_post_field = null !== $external_api_request_body_json ? $external_api_request_body_json : '';
				}

				$external_api_configuration = array(
					'ExternalApiName'            => $external_api_name,
					'ExternalApiRequestType'     => $external_api_request_type,
					'ExternalApiBodyRequestType' => $external_api_body_request_type,
					'ExternalApiResponseDataKey' => $external_api_response_data_key,
					'ExternalEndpoint'           => $external_endpoint,
					'ExternalHeaders'            => $external_headers,
					'ExternalApiPostFieldNew'    => $external_api_post_field,
				);

				$existing_external_api_configuration                       = get_option( 'custom_api_save_ExternalApiConfiguration' );
				$existing_external_api_configuration[ $external_api_name ] = $external_api_configuration;
				update_option( 'custom_api_save_ExternalApiConfiguration', $existing_external_api_configuration );

				header( 'Location: ?page=custom_api_wp_settings&action=editexternal&apiname=' . $external_api_name . '' );
				die();
			}
		}
	}

	$list = ( false !== get_option( 'custom_api_save_ExternalApiConfiguration' ) ) ? get_option( 'custom_api_save_ExternalApiConfiguration' ) : array();
	foreach ( $list as $arr ) {
		$check = $arr;
	}
	$bool = isset( $check['ExternalApiPostFieldNew'] );
	if ( ! $bool && ! empty( $list ) ) {
		if ( isset( $_REQUEST['customapiexternal'] ) && 'testexecute' === sanitize_text_field( wp_unslash( $_REQUEST['customapiexternal'] ) ) ) {
			if ( current_user_can( 'administrator' ) ) {
				$external_api_configuration = get_option( 'custom_api_test_ExternalApiConfiguration' );
				$body_params                = array();
				$body_params                = isset( $external_api_configuration['ExternalApiPostField'] ) ? $external_api_configuration['ExternalApiPostField'] : '';
				$url                        = htmlspecialchars_decode( $external_api_configuration['ExternalEndpoint'] );
				$url                        = str_replace( '&amp;', '&', $url );
				$arguments                  = array(
					'method'  => $external_api_configuration['ExternalApiRequestType'],
					'headers' => $external_api_configuration['ExternalHeaders'],
					'timeout' => 120, // execution time
					// 'stream'   => true, //to set file download.
					// 'filename' => utils, //name of to be written in.
				);
				if ( 'GET' !== $external_api_configuration['ExternalApiRequestType'] && $external_api_configuration['ExternalApiRequestType'] ) {
					$arguments['body'] = $body_params;
				}
				// Request.
				$returned_response = wp_remote_request( $url, $arguments );
				// Process response.
				if ( is_wp_error( $returned_response ) ) {
					return $returned_response->get_error_message();
				}

				render_test_config_output( $returned_response['body'], false );
				exit;
			}
		}
	} else {
		if ( isset( $_REQUEST['customapiexternal'] ) && 'testexecute' === $_REQUEST['customapiexternal'] ) {
			if ( current_user_can( 'administrator' ) ) {
				$external_api_configuration = get_option( 'custom_api_test_ExternalApiConfiguration' );
				$body_params                = array();
				if ( 'x-www-form-urlencode' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
					$bodyarr         = $external_api_configuration['ExternalApiPostFieldNew'];
					$body_temp_array = array();
					if ( ! empty( $bodyarr ) ) {
						foreach ( $bodyarr as $bodyval ) {
							$pos = strpos( $bodyval, ':' );
							if ( false !== $pos && substr_count( $bodyval, ':' ) > 1 ) {
								$bodyval                            = substr_replace( $bodyval, '##mo_remove##', $pos, strlen( ':' ) );
								$body_temp_array                    = explode( '##mo_remove##', $bodyval );
								$body_params[ $body_temp_array[0] ] = $body_temp_array[1];
							} else {
								$body_temp_array                    = explode( ':', $bodyval );
								$body_params[ $body_temp_array[0] ] = $body_temp_array[1];
							}
						}
					}
					$body_params = http_build_query( $body_params );
				} else {
					$body_params = $external_api_configuration['ExternalApiPostFieldNew'];
				}
				$url       = htmlspecialchars_decode( $external_api_configuration['ExternalEndpoint'] );
				$url       = str_replace( '&amp;', '&', $url );
				$arguments = array(
					'method'  => $external_api_configuration['ExternalApiRequestType'],
					'headers' => $external_api_configuration['ExternalHeaders'],
					'timeout' => 120, // execution time
					// 'stream'   => true, //to set file download.
					// 'filename' => utils, //name of to be written in.
				);
				if ( 'GET' !== $external_api_configuration['ExternalApiRequestType'] && $external_api_configuration['ExternalApiRequestType'] ) {
					$arguments['body'] = $body_params;
				}
				// Request.
				$returned_response = wp_remote_request( $url, $arguments );
				// Process response.
				if ( is_wp_error( $returned_response ) ) {
					return $returned_response->get_error_message();
				}
				render_test_config_output( $returned_response['body'], false );
				exit;
			}
		}
	}

	if ( isset( $_POST['option'] ) ) {
		if ( sanitize_text_field( wp_unslash( $_POST['option'] ) ) === 'custom_api_wp_contact_us_query_option' ) {

			if ( wp_verify_nonce( ( isset( $_POST['mo_custom_api_submit_contact_us_field'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_custom_api_submit_contact_us_field'] ) ) : false ), 'mo_custom_api_submit_contact_us' ) ) {
				$email = isset( $_POST['custom_api_wp_contact_us_email'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_api_wp_contact_us_email'] ) ) : '';
				$phone = isset( $_POST['custom_api_wp_contact_us_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_api_wp_contact_us_phone'] ) ) : '';
				$query = isset( $_POST['custom_api_wp_contact_us_query'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_api_wp_contact_us_query'] ) ) : '';

				if ( custom_api_wp_empty_or_null( $email ) || custom_api_wp_empty_or_null( $query ) ) {
					update_option( 'custom_api_wp_message', 'Please fill up Email and Query fields to submit your query.' );
					custom_api_wp_show_error_message();
				} else {
					$submited = custom_api_wp_submit_contact_us( $email, $phone, $query );
					if ( 'Query submitted.' !== $submited ) {
						update_option( 'custom_api_wp_message', 'Your query could not be submitted. Please try again.' );
						custom_api_wp_show_error_message();
					} else {
						update_option( 'custom_api_wp_message', 'Thanks for getting in touch! We shall get back to you shortly.' );
						custom_api_wp_show_success_message();
					}
				}
			}
		}
	}

	if ( isset( $_POST['option'] ) ) {
		if ( sanitize_text_field( wp_unslash( $_POST['option'] ) ) === 'custom_api_authentication_verify_customer' ) {
			if ( wp_verify_nonce( ( isset( $_POST['mo_cusotm_api_verify_customer_field'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_cusotm_api_verify_customer_field'] ) ) : false ), 'mo_cusotm_api_verify_customer' ) ) {
				$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
				$password = isset( $_POST['password'] ) ? stripslashes( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- No need to sanitize the password as we are not storing it in the database.
				if ( custom_api_authentication_check_empty_or_null( $email ) || custom_api_authentication_check_empty_or_null( $password ) ) {
					update_option( 'custom_api_wp_message', 'All the fields are required. Please enter valid entries.' );
					custom_api_wp_show_error_message();
					return;
				}
				update_option( 'custom_api_authentication_admin_email', $email );
				$content      = custom_api_auth_get_customer_key( $password );
				$customer_key = json_decode( $content, true );
				if ( JSON_ERROR_NONE === json_last_error() ) {
					update_option( 'custom_api_authentication_admin_customer_key', $customer_key['id'] );
					update_option( 'custom_api_authentication_admin_api_key', $customer_key['apiKey'] );
					update_option( 'custom_api_authentication_customer_token', $customer_key['token'] );
					if ( isset( $customer_key['phone'] ) ) {
						update_option( 'custom_api_authentication_admin_phone', $customer_key['phone'] );
					}

					delete_option( 'password' );
					update_option( 'custom_api_wp_message', 'Customer retrieved successfully' );
					delete_option( 'custom_api_authentication_verify_customer' );
					custom_api_wp_show_success_message();
				} else {
					update_option( 'custom_api_wp_message', 'Invalid username or password. Please try again.' );
					custom_api_wp_show_error_message();
				}
			}
		}
	}

	if ( isset( $_POST['option'] ) ) {
		if ( sanitize_text_field( wp_unslash( $_POST['option'] ) ) === 'custom_api_authentication_register_customer' ) {
			if ( ( isset( $_POST['mo_custom_api_register_customer_field'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mo_custom_api_register_customer_field'] ) ), 'mo_custom_api_register_customer' ) : false ) ) {
				$email            = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
				$phone            = isset( $_POST['phone'] ) ? stripslashes( sanitize_text_field( wp_unslash( $_POST['phone'] ) ) ) : '';
				$password         = isset( $_POST['password'] ) ? stripslashes( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- not sanitizing password as it is not stored in database.
				$confirm_password = isset( $_POST['confirmPassword'] ) ? stripslashes( $_POST['confirmPassword'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- not sanitizing password as it is not stored in database.
				$fname            = isset( $_POST['fname'] ) ? stripslashes( sanitize_text_field( wp_unslash( $_POST['fname'] ) ) ) : '';
				$lname            = isset( $_POST['lname'] ) ? stripslashes( sanitize_text_field( wp_unslash( $_POST['lname'] ) ) ) : '';
				$company          = isset( $_POST['company'] ) ? stripslashes( sanitize_text_field( wp_unslash( $_POST['company'] ) ) ) : '';
				if ( custom_api_authentication_check_empty_or_null( $email ) || custom_api_authentication_check_empty_or_null( $password ) || custom_api_authentication_check_empty_or_null( $confirm_password ) ) {
					update_option( 'custom_api_wp_message', 'All the fields are required. Please enter valid entries.' );
					custom_api_wp_show_error_message();
					return;
				} elseif ( strlen( sanitize_text_field( $password ) ) < 8 || strlen( sanitize_text_field( $confirm_password ) ) < 8 ) {
					update_option( 'custom_api_wp_message', 'Choose a password with minimum length 8.' );
					custom_api_wp_show_error_message();
					return;
				}

				update_option( 'custom_api_authentication_admin_email', $email );
				update_option( 'custom_api_authentication_admin_phone', $phone );
				update_option( 'custom_api_authentication_admin_fname', $fname );
				update_option( 'custom_api_authentication_admin_lname', $lname );
				update_option( 'custom_api_authentication_admin_company', $company );

				if ( 0 === strcmp( $password, $confirm_password ) ) {

					$email   = get_option( 'custom_api_authentication_admin_email' );
					$content = json_decode( custom_api_check_customer(), true );

					if ( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND' ) === 0 ) {
						$response = json_decode( custom_api_create_customer( $password ), true );
						if ( strcasecmp( $response['status'], 'SUCCESS' ) !== 0 ) {
							update_option( 'custom_api_wp_message', 'Failed to create a customer. Try again.' );
							custom_api_wp_show_error_message();
						} else {
							update_option( 'custom_api_wp_message', 'You are successfully registered with miniOrange' );
							custom_api_wp_show_success_message();
						}
					} elseif ( strcasecmp( $content['status'], 'SUCCESS' ) === 0 ) {
						update_option( 'custom_api_wp_message', 'Account already exists. Please log in.' );
						custom_api_wp_show_error_message();
					}
				} else {
					update_option( 'custom_api_wp_message', 'Passwords do not match.' );
					custom_api_wp_show_error_message();
				}
			}
		}
	}

	if ( isset( $_POST['option2'] ) ) {
		if ( sanitize_text_field( wp_unslash( $_POST['option2'] ) ) === 'custom_api_authentication_goto_login1' ) {
			if ( ( isset( $_POST['mo_custom_api_goto_login_form1_field'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mo_custom_api_goto_login_form1_field'] ) ), 'mo_custom_api_goto_login_form1' ) : false ) ) {
				update_option( 'custom_api_authentication_verify_customer', 'yes' );
				delete_option( 'custom_api_authentication_new_customer' );
			}
		}
	}

	if ( isset( $_POST['option2'] ) ) {
		if ( sanitize_text_field( wp_unslash( $_POST['option2'] ) ) === 'custom_api_authentication_goto_register' ) {
			if ( ( isset( $_POST['mo_custom_api_goto_register_form_field'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mo_custom_api_goto_register_form_field'] ) ), 'mo_custom_api_goto_register_form' ) : false ) ) {
				update_option( 'custom_api_authentication_new_customer', 'yes' );
				delete_option( 'custom_api_authentication_verify_customer' );
			}
		}
	}

	if ( isset( $_POST['option'] ) ) {
		if ( sanitize_text_field( wp_unslash( $_POST['option'] ) ) === 'change_miniorange' ) {
			if ( ( isset( $_POST['mo_custom_api_goto_login_form_field'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mo_custom_api_goto_login_form_field'] ) ), 'mo_custom_api_goto_login_form' ) : false ) ) {
				update_option( 'custom_api_authentication_verify_customer', 'yes' );
			}
		}
	}

	if ( current_user_can( 'manage_options' ) ) {

		if ( isset( $_POST['option'] ) && sanitize_text_field( wp_unslash( $_POST['option'] ) ) === 'mo_custom_api_trial_request_form' && isset( $_REQUEST['mo_custom_api_trial_request_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_custom_api_trial_request_field'] ) ), 'mo_custom_api_trial_request' ) ) {
			$email      = isset( $_POST['mo_custom_api_trial_email'] ) ? sanitize_email( wp_unslash( $_POST['mo_custom_api_trial_email'] ) ) : '';
			$trial_plan = isset( $_POST['mo_custom_api_trial_plan'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_custom_api_trial_plan'] ) ) : '';
			$query      = isset( $_POST['mo_custom_api_trial_usecase'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_custom_api_trial_usecase'] ) ) : '';

			if ( empty( $email ) || null === $email || empty( $trial_plan ) || empty( $query ) || ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				update_option( 'custom_api_wp_message', 'Please fill up Usecase, Email field and Requested demo plan to submit your query.' );
				custom_api_wp_show_error_message();
			} else {
				$subject  = 'WP Custom API Trial Request - ' . $email;
				$response = mo_custom_api_send_trial_alert( $email, $trial_plan, $query, $subject );

				if ( false === $response ) {
					update_option( 'custom_api_wp_message', 'Your query could not be submitted. Please try again.' );
					custom_api_wp_show_error_message();
				} elseif ( 'WRONG_FORMAT' === $response ) {
					update_option( 'custom_api_wp_message', 'Please enter the correct format of the email.' );
					custom_api_wp_show_error_message();
				} else {
					update_option( 'custom_api_wp_message', 'Thanks for getting in touch! We shall get back to you shortly.' );
					custom_api_wp_show_success_message();
				}
			}
		}
		if ( ( isset( $_POST['miniorange_feedback_skip'] ) && sanitize_text_field( wp_unslash( $_POST['miniorange_feedback_skip'] ) ) === 'Skip' ) || ( isset( $_POST['mo_custom_feedback_skip'] ) && sanitize_text_field( wp_unslash( $_POST['mo_custom_feedback_skip'] ) ) === 'mo_custom_skip_feedback' ) ) {
			update_option( 'custom_api_wp_message', 'Plugin deactivated successfully.' );
				custom_api_wp_show_success_message();
			deactivate_plugins( __FILE__ );
		} elseif ( isset( $_POST['custom_api_client_feedback'] ) && sanitize_text_field( wp_unslash( $_POST['custom_api_client_feedback'] ) ) === 'true' ) {
			$user    = wp_get_current_user();
			$message = 'Plugin Deactivated:';
			if ( array_key_exists( 'deactivate_reason_select', $_POST ) ) {
				$rating            = isset( $_POST['rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rate'] ) ) : '-';
				$deactivate_reason = isset( $_POST['deactivate_reason_select'] ) ? sanitize_text_field( wp_unslash( $_POST['deactivate_reason_select'] ) ) : false;
			}
			$deactivate_reason_message = array_key_exists( 'query_feedback', $_POST ) ? sanitize_text_field( wp_unslash( $_POST['query_feedback'] ) ) : false;
			if ( $deactivate_reason ) {
				$message .= $deactivate_reason;
				if ( isset( $deactivate_reason_message ) ) {
					$message .= ':' . $deactivate_reason_message;
				}
				$email = $user->user_email;
				$phone = '';

				custom_api_send_email_alert( $email, $phone, $message, $rating );
				deactivate_plugins( __FILE__ );
				update_option( 'custom_api_wp_message', 'Thank you for the feedback.' );
				custom_api_wp_show_success_message();
			} else {
				update_option( 'custom_api_wp_message', 'Please Select one of the reasons, if your reason is not mentioned please select Other Reasons' );
				custom_api_wp_show_error_message();
			}
		}
	}

}

/**
 * Used to raise internal Query in miniOrange.
 *
 * @param mixed $email User's email.
 * @param mixed $phone User's phone number.
 * @param mixed $query User's Query string.
 *
 * @return string
 */
function custom_api_wp_submit_contact_us( $email, $phone, $query ) {
	global $current_user;
	$query        = '[Custom API WP - ' . CUSTOM_API_FOR_WORDPRESS_VERSION . ' ] ' . $query;
	$fields       = array(
		'firstName' => isset( $current_user->user_firstname ) ? $current_user->user_firstname : '',
		'lastName'  => isset( $current_user->user_lastname ) ? $current_user->user_lastname : '',
		'company'   => isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '',
		'email'     => $email,
		'ccEmail'   => 'apisupport@xecurify.com',
		'phone'     => $phone,
		'query'     => $query,
	);
	$field_string = wp_json_encode( $fields );
	update_option( 'custom_api_wp_cutom_api_wp_host_name', 'https://login.xecurify.com' );
	$url = get_option( 'custom_api_wp_cutom_api_wp_host_name' ) . '/moas/rest/customer/contact-us';

	$headers = array(
		'Content-Type'  => 'application/json',
		'charset'       => 'UTF - 8',
		'Authorization' => 'Basic',
	);
	$args    = array(
		'method'      => 'POST',
		'body'        => $field_string,
		'timeout'     => '15',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => $headers,

	);

	$response = wp_remote_post( $url, $args );
	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
		echo 'Something went wrong:' . esc_attr( $error_message );
		exit();
	}
	return wp_remote_retrieve_body( $response );

}


/**
 * Displays the API response data in the test configuration tab.
 *
 * @param mixed $api_response Response received from the API call.
 * @param bool  $group additional flag to handle the flow.
 */
function render_test_config_output( $api_response, $group = false ) {
	if ( is_array( json_decode( $api_response, true ) ) && ( json_last_error() === JSON_ERROR_NONE ) ) {
		echo '<div style="font-family:Calibri;padding:0 3%;">';
		echo '<style>table{border-collapse:collapse;}th {background-color: #eee; text-align: center; padding: 8px; border-width:1px; border-style:solid; border-color:#212121;}tr:nth-child(odd) {background-color: #f2f2f2;} td{padding:8px;border-width:1px; border-style:solid; border-color:#212121;}</style>';
		echo '<h2>';
		echo ( $group ) ? 'Group Info' : 'Test Configuration';
		echo '</h2><table><tr><th>Attribute Name</th><th>Attribute Value</th></tr>';
		testattrmappingconfig( '', json_decode( $api_response, true ) );
		echo '</table>';
		if ( ! $group ) {
			echo '<div style="padding: 10px;"></div><input style="padding:1%;width:100px;background: #473970 none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="opener.location.reload();self.close();"></div>';
		}
	} else {
		echo '<div style="font-family:Calibri;padding:0 3%;">';
		echo esc_attr( $api_response );
		echo '<div style="padding: 10px;"></div><input style="padding:1%;width:100px;background: #473970 none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="opener.location.reload();self.close();"></div>';
		update_option( 'ExternalApiResponseKey', 'false' );
	}
}

$api_response_key = array();

/**
 * Maps attributes so that data can be displayed in tabular format.
 *
 * @param mixed $nestedprefix         Prefix/parent keys to be represented in the tabular display.
 * @param mixed $api_response_details API response.
 * @param bool  $real_environment additional flag to handle the flow.
 * @param array $required_keys        list of required attributes to filter the API response.
 */
function testattrmappingconfig( $nestedprefix, $api_response_details, $real_environment = false, $required_keys = array() ) {
	global $api_response_key;

	if ( ! $real_environment ) {
		foreach ( $api_response_details as $key => $resource ) {

			if ( is_array( $resource ) || is_object( $resource ) ) {
				if ( ! empty( $nestedprefix ) ) {
					$nestedprefix .= '->';
				}
				testattrmappingconfig( $nestedprefix . $key, $resource );
				$nestedprefix = rtrim( $nestedprefix, '->' );
			} else {
				$completekey = '';
				echo '<tr><td>';
				if ( ! empty( $nestedprefix ) ) {
					echo esc_html( $nestedprefix ) . '->';
					$completekey = $nestedprefix . '->';
				}
				echo esc_html( $key ) . '</td><td>' . esc_html( $resource ) . '</td></tr>';
				$completekey = $completekey . $key;

				array_push( $api_response_key, $completekey );
			}
		}
		update_option( 'ExternalApiResponseKey', $api_response_key );
	} else {
		foreach ( $api_response_details as $key => $resource ) {

			if ( is_array( $resource ) || is_object( $resource ) ) {
				if ( ! empty( $nestedprefix ) ) {
					$nestedprefix .= '.';
				}
				testattrmappingconfig( $nestedprefix . $key, $resource, true, $required_keys );
				$nestedprefix = rtrim( $nestedprefix, '.' );
			} else {
				$completekey = '';

				if ( ! empty( $nestedprefix ) ) {

					$completekey = $nestedprefix . '.';
				}

				$completekey = $completekey . $key;
				if ( in_array( $completekey, $required_keys, true ) ) {
					$api_response_key[ $completekey ] = $resource;
				}
			}
		}

		return $api_response_key;
	}
}
update_option( 'cutom_api_wp_host_name', 'https://login.xecurify.com' );


