<?php
/**
 * Contains Basic checks, user registration, and sending queries to miniOrange.
 *
 * @package    Miniorange_Custom_API_For_WP
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

/**
 * Displays success message after any successful operation.
 */
function custom_api_wp_show_success_message() {
	remove_action( 'admin_notices', 'custom_api_success_message' );
	add_action( 'admin_notices', 'custom_api_error_message' );
}

/**
 * Displays a success message after any unsuccessful operation.
 */
function custom_api_wp_show_error_message() {
	remove_action( 'admin_notices', 'custom_api_error_message' );
	add_action( 'admin_notices', 'custom_api_success_message' );
}

/**
 * Checks if a given variable is empty or null
 *
 * @param mixed $value value given to check if it's empty or null.
 *
 * @return boolean
 */
function custom_api_wp_empty_or_null( $value ) {
	if ( ! isset( $value ) || empty( $value ) ) {
		return true;
	}
	return false;
}

/**
 * Displays success message after any successful operation.
 */
function custom_api_success_message() {
	$class   = 'error';
	$message = get_option( 'custom_api_wp_message' );
	echo "<div style='margin-left:6px;' class='" . esc_html( $class ) . "'> <p>" . esc_html( $message ) . '</p></div>';
}

/**
 * Displays a success message after any unsuccessful operation.
 */
function custom_api_error_message() {
	$class   = 'updated';
	$message = get_option( 'custom_api_wp_message' );
	echo "<div style='margin-left:6px;' class='" . esc_html( $class ) . "'><p>" . esc_html( $message ) . '</p></div>';
}

/**
 * Checks if there is a proper internet connection
 *
 * @return boolean
 */
function check_internet_connection() {
	return (bool) @fsockopen( 'test.miniorange.in', 443, $errno, $errstr, 5 ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fsockopen, WordPress.PHP.NoSilencedErrors.Discouraged -- Using default PHP function to check socket connection.
}

/**
 * Fetches timestamp from miniOrange server
 *
 * @return array
 */
function get_timestamp() {
	$url     = get_option( 'cutom_api_wp_host_name' ) . '/moas/rest/mobile/get-timestamp';
	$headers = array(
		'Content-Type'  => 'application/json',
		'charset'       => 'UTF - 8',
		'Authorization' => 'Basic',
	);
	$args    = array(
		'method'      => 'POST',
		'body'        => array(),
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
 * Sends email queries to miniOrange
 *
 * @param mixed $email user email.
 * @param mixed $phone user phone number.
 * @param mixed $message query written by the user.
 * @param mixed $rating rating given by the user.
 *
 * @return string
 */
function custom_api_send_email_alert( $email, $phone, $message, $rating ) {
	if ( ! check_internet_connection() ) {
		return '';
	}

	$url                    = get_option( 'cutom_api_wp_host_name' ) . '/moas/api/notify/send';
	$default_customer_key   = '16555';
	$default_api_key        = 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
	$customer_key           = $default_customer_key;
	$api_key                = $default_api_key;
	$current_time_in_millis = get_timestamp();
	$string_to_hash         = $customer_key . $current_time_in_millis . $api_key;
	$hash_value             = hash( 'sha512', $string_to_hash );
	$customer_key_header    = 'Customer-Key: ' . $customer_key;
	$timestamp_header       = 'Timestamp: ' . $current_time_in_millis;
	$authorization_header   = 'Authorization: ' . $hash_value;
	$from_email             = $email;
	$subject                = 'Feedback: Custom API for WP';
	$site_url               = site_url();

	global $user;
	$user  = wp_get_current_user();
	$query = '[Custom API for WP - ' . CUSTOM_API_FOR_WORDPRESS_VERSION . ' ] : ' . $message;
	if ( ! ( 'Feedback skipped' === $message ) ) {
		$content = '<div >Hello, <br><br>First Name :' . $user->user_firstname . '<br><br>Last  Name :' . $user->user_lastname . '   <br><br>Company :<a href="' . ( isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '' ) . '" target="_blank" >' . ( isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '' ) . '</a><br><br>Phone Number :' . $phone . '<br><br>Email :<a href="mailto:' . $from_email . '" target="_blank">' . $from_email . '</a><br><br>Rating: ' . $rating . '<br><br>Query :' . $query . '</div>';
	}
	$fields                   = array(
		'customerKey' => $customer_key,
		'sendEmail'   => true,
		'email'       => array(
			'customerKey' => $customer_key,
			'fromEmail'   => $from_email,
			'bccEmail'    => 'apisupport@xecurify.com',
			'fromName'    => 'miniOrange',
			'toEmail'     => 'apisupport@xecurify.com',
			'toName'      => 'apisupport@xecurify.com',
			'subject'     => $subject,
			'content'     => $content,
		),
	);
	$field_string             = wp_json_encode( $fields );
	$headers                  = array( 'Content-Type' => 'application/json' );
	$headers['Customer-Key']  = $customer_key;
	$headers['Timestamp']     = $current_time_in_millis;
	$headers['Authorization'] = $hash_value;
	$args                     = array(
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
}

/**
 * Checks if a customer is registered with miniOrange.
 *
 * @return int
 */
function custom_api_authentication_is_customer_registered() {
	$email        = get_option( 'custom_api_authentication_admin_email' );
	$customer_key = get_option( 'custom_api_authentication_admin_customer_key' );
	if ( ! $email || ! $customer_key || ! is_numeric( trim( $customer_key ) ) ) {

		return 0;
	} else {
		return 1;
	}
}

/**
 * Raises query for the trial plugin.
 *
 * @param mixed $email user email.
 * @param mixed $trial_plan plan selected by the user.
 * @param mixed $message query entered by the user.
 * @param mixed $subject email subject.
 *
 * @return null|bool|string
 */
function mo_custom_api_send_trial_alert( $email, $trial_plan, $message, $subject ) {
	if ( ! check_internet_connection() ) {
		return;
	}
	$url                    = get_option( 'cutom_api_wp_host_name' ) . '/moas/api/notify/send';
	$default_customer_key   = '16555';
	$default_api_key        = 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
	$customer_key           = $default_customer_key;
	$api_key                = $default_api_key;
	$current_time_in_millis = get_timestamp();
	$string_to_hash         = $customer_key . $current_time_in_millis . $api_key;
	$hash_value             = hash( 'sha512', $string_to_hash );
	$customer_key_header    = 'Customer-Key: ' . $customer_key;
	$timestamp_header       = 'Timestamp: ' . $current_time_in_millis;
	$authorization_header   = 'Authorization: ' . $hash_value;
	$from_email             = $email;
	$site_url               = site_url();

	global $user;
	$user = wp_get_current_user();

	$content = '<div >Hello, </a><br><br><b>Email :</b><a href="mailto:' . $from_email . '" target="_blank">' . $from_email . '</a><br><br><b>Requested Trial for :</b> ' . $trial_plan . '<br><br><b>Requirements (Usecase) :</b> ' . $message . '</div>';

	$fields                   = array(
		'customerKey' => $customer_key,
		'sendEmail'   => true,
		'email'       => array(
			'customerKey' => $customer_key,
			'fromEmail'   => $from_email,
			'bccEmail'    => 'apisupport@xecurify.com',
			'fromName'    => 'miniOrange',
			'toEmail'     => 'apisupport@xecurify.com',
			'toName'      => 'apisupport@xecurify.com',
			'subject'     => $subject,
			'content'     => $content,
		),
	);
	$field_string             = wp_json_encode( $fields );
	$headers                  = array( 'Content-Type' => 'application/json' );
	$headers['Customer-Key']  = $customer_key;
	$headers['Timestamp']     = $current_time_in_millis;
	$headers['Authorization'] = $hash_value;
	$args                     = array(
		'method'      => 'POST',
		'body'        => $field_string,
		'timeout'     => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => $headers,

	);

	$response = wp_remote_post( $url, $args );
	$body     = wp_remote_retrieve_body( $response );
	$body     = json_decode( $body, true );
	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
		echo 'Something went wrong: ' . esc_html( $error_message );
		exit();
	} elseif ( isset( $body ) && 'ERROR' === $body['status'] ) {
		return 'WRONG_FORMAT';
	}
		return true;
}


