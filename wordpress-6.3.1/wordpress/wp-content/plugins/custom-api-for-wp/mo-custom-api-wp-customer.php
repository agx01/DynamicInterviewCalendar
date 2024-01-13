<?php
/**
 * Contains customer related functionalities.
 *
 * @package    Miniorange_Custom_API_For_WP
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

/**
 * Checks if the given variable is empty or null.
 *
 * @param mixed $value value given to check if it's empty or null.
 *
 * @return boolean
 */
function custom_api_authentication_check_empty_or_null( $value ) {
	if ( ! isset( $value ) || empty( $value ) ) {
		return true;
	}
	return false;
}

/**
 * Fetches customer details form miniOrange.
 *
 * @return string
 */
function custom_api_check_customer() {
	$url   = get_option( 'cutom_api_wp_host_name' ) . '/moas/rest/customer/check-if-exists';
	$email = get_option( 'custom_api_authentication_admin_email' );

	$fields       = array(
		'email' => $email,
	);
	$field_string = wp_json_encode( $fields );
	$headers      = array(
		'Content-Type'  => 'application/json',
		'charset'       => 'UTF - 8',
		'Authorization' => 'Basic',
	);
	$args         = array(
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
		echo 'Something went wrong: ' . esc_html( $error_message );
		exit();
	}

	return wp_remote_retrieve_body( $response );
}

/**
 * Customer registration in miniOrange.
 *
 * @param mixed $password password of the customer to be registered in miniOrange.
 * @return string
 */
function custom_api_create_customer( $password ) {
	$url        = get_option( 'cutom_api_wp_host_name' ) . '/moas/rest/customer/add';
	$email      = get_option( 'custom_api_authentication_admin_email' );
	$phone      = get_option( 'custom_api_authentication_admin_phone' );
	$first_name = get_option( 'custom_api_authentication_admin_fname' );
	$last_name  = get_option( 'custom_api_authentication_admin_lname' );
	$company    = get_option( 'custom_api_authentication_admin_company' );

	$fields       = array(
		'companyName'    => $company,
		'areaOfInterest' => 'Custom Api WP',
		'firstname'      => $first_name,
		'lastname'       => $last_name,
		'email'          => $email,
		'phone'          => $phone,
		'password'       => $password,
	);
	$field_string = wp_json_encode( $fields );

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
		echo 'Something went wrong: ' . esc_html( $error_message );
		exit();
	}

	return wp_remote_retrieve_body( $response );
}

/**
 * Function to fetch miniOrange customer's login key and token if a user registers an account in miniOrange.
 *
 * @param mixed $password password of the customer to be registered in miniOrange.
 * @return string
 */
function custom_api_auth_get_customer_key( $password ) {
	update_option( 'cutom_api_wp_host_name', 'https://login.xecurify.com' );
	$url   = get_option( 'cutom_api_wp_host_name' ) . '/moas/rest/customer/key';
	$email = get_option( 'custom_api_authentication_admin_email' );

	$fields       = array(
		'email'    => $email,
		'password' => $password,
	);
	$field_string = wp_json_encode( $fields );

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
		echo 'Something went wrong: ' . esc_html( $error_message );
		exit();
	}

	return wp_remote_retrieve_body( $response );
}
