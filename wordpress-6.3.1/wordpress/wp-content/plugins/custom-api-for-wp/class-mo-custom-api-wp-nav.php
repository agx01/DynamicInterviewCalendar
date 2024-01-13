<?php
/**
 * Contains plugin's home page navigation flow.
 *
 * @package    Custom_Api_For_WordPress
 * @subpackage Custom_Api_For_WordPress/includes
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

/**
 * Adding required files
 */

require_once 'mo-custom-api-wp-ui.php';
require_once 'mo-custom-api-wp-license-purchase.php';
require_once 'class-mo-custom-api-wp-trial.php';



/**
 * Contains view and controller tab/button navigations for the plugin.
 */
class MO_Custom_API_WP_Nav {

	/**
	 * Loads main menu page
	 */
	public static function custom_api_wp_main_menu_load() {
		$currenttab = '';
		$action     = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- actions are assigned in the code and then fetched as a parameter in the URL, hence nonce not require.
		$api        = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- actions are assigned in the code and then fetched as a parameter in the URL, hence nonce not require.
		if ( isset( $action ) ) {
			$currenttab = sanitize_text_field( $action );
		}
		if ( isset( $api ) ) {
			$api = sanitize_text_field( $api );
		}
		self::custom_api_auth_show_menu( $currenttab );
		echo '
		<div id="mo_api_authentication_settings">';
		echo '
			<div class="mo_custom_api_miniorange_container">';
		echo '
			<table style="width:99%;">
				<tr>
					<td style="vertical-align:top;width:100%;" class="mo_api_authentication_content">';
		self::custom_api_auth_show_tab( $currenttab );
		echo '</tr>
			</table>
			<div class="mo_api_authentication_tutorial_overlay" id="mo_api_authentication_tutorial_overlay" style="display:none" ></div>
			</div></div>';
	}

	/**
	 * Handles tab flow.
	 *
	 * @param mixed $currenttab Current selected tab name.
	 */
	public static function custom_api_auth_show_menu( $currenttab ) {
		?>
		<div class="wrap">
			<div>
				<img style="float:left;margin-left:7px;margin-right: 15px;" src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>/images/miniorange.png">
			</div>
			<div>
				<h4 style="font-size:25px;font-weight:700;">
				miniOrange Custom API &nbsp;
				<a class="add-new-h2" href="https://forum.miniorange.com/" target="_blank" rel="noopener">Ask questions on our forum</a>
				<a class="add-new-h2" href="https://wordpress.org/support/plugin/custom-api-for-wp/" target="_blank" rel="noopener">WordPress Forum</a>
				<a class="add-new-h2" href="https://plugins.miniorange.com/custom-api-for-wordpress" target="_blank">Learn More</a>
			</h4>
				</div>

		</div>
		<div class="wrap">
		</div>

		<style>
			.add-new-hover:hover{
				color: white !important;
			}
		</style>
		<div id="tab">
			<h2 class="nav-tab-wrapper" style="line-height:40px;">
			<a class="nav-tab 
			<?php
			if ( 'list' === $currenttab || '' === $currenttab ) {
				echo 'mo_custom_api_nav_tab_active';}
			?>
			" href="admin.php?page=custom_api_wp_settings">Available APIs</a>&nbsp;
			<a class="nav-tab 
			<?php
			if ( 'savedcustomsql' === $currenttab ) {
				echo 'mo_custom_api_nav_tab_active';}
			?>
			" href="admin.php?page=custom_api_wp_settings&action=savedcustomsql">Custom SQL APIs</a>
			<a class="nav-tab 
			<?php
			if ( 'add_auth' === $currenttab ) {
				echo 'mo_custom_api_nav_tab_active';}
			?>
			" href="admin.php?page=custom_api_wp_settings&action=add_auth">Add Authentication</a>
			<a class="nav-tab 
			<?php
			if ( 'savedexternalapi' === $currenttab ) {
				echo 'mo_custom_api_nav_tab_active';}
			?>
			" href="admin.php?page=custom_api_wp_settings&action=savedexternalapi">External APIs</a>
			<a class="nav-tab 
			<?php
			if ( 'apiintegration' === $currenttab ) {
				echo 'mo_custom_api_nav_tab_active';}
			?>
			" href="admin.php?page=custom_api_wp_settings&action=apiintegration">Custom API Integration</a>
			<a class="nav-tab 
			<?php
			if ( 'register' === $currenttab ) {
				echo 'mo_custom_api_nav_tab_active';}
			?>
			" href="admin.php?page=custom_api_wp_settings&action=register">Account Setup</a>
			<a class="nav-tab 
			<?php
			if ( 'requestfortrial' === $currenttab ) {
				echo 'mo_custom_api_nav_tab_active';}
			?>
			" href="admin.php?page=custom_api_wp_settings&action=requestfortrial">Request for Trial</a>
			<a class="nav-tab 
			<?php
			if ( 'license' === $currenttab ) {
				echo 'mo_custom_api_nav_tab_active';}
			?>
			" href="admin.php?page=custom_api_wp_settings&action=license">Premium Plans</a>
			</h2>
		</div>
		<?php
	}

	/**
	 * Handles action flow.
	 *
	 * @param mixed $currenttab  Current selected action name.
	 */
	public static function custom_api_auth_show_tab( $currenttab ) {
		$apiname = isset( $_GET['apiname'] ) ? sanitize_text_field( wp_unslash( $_GET['apiname'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- actions are assigned in the code and then fetched as a parameter in the url, hence nonce not require.
		$api     = isset( $_GET['api'] ) ? sanitize_text_field( wp_unslash( $_GET['api'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- actions are assigned in the code and then fetched as a parameter in the url, hence nonce not require.
		$apisql  = isset( $_GET['apisql'] ) ? sanitize_text_field( wp_unslash( $_GET['apisql'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- actions are assigned in the code and then fetched as a parameter in the url, hence nonce not require.
		if ( 'register' === $currenttab ) {
			if ( get_option( 'custom_api_authentication_verify_customer' ) ) {
				custom_api_already_customer();
			} elseif ( trim( get_option( 'custom_api_authentication_email' ) ) !== '' && trim( get_option( 'mo_api_authentication_admin_api_key' ) ) === '' ) {
				custom_api_already_customer();
			} else {
				register();
			}
		} elseif ( '' === $currenttab || 'list' === $currenttab ) {
			custom_api_wp_list_api();
		} elseif ( 'addapi' === $currenttab ) {
			custom_api_wp_add_api();
		} elseif ( 'savedcustomsql' === $currenttab ) {
			custom_api_wp_saved_sql_api();
		} elseif ( 'customsql' === $currenttab ) {
			custom_api_wp_custom_sql();
		} elseif ( 'add_auth' === $currenttab ) {
			custom_api_wp_authentication();
		} elseif ( 'savedexternalapi' === $currenttab ) {
			custom_api_wp_saved_external_api_connection();
		} elseif ( 'externalapi' === $currenttab ) {
			custom_api_wp_external_api_connection();
		} elseif ( 'requestfortrial' === $currenttab ) {
			MO_Custom_API_WP_Trial::mo_custom_api_request_for_trial();
		} elseif ( 'apiintegration' === $currenttab ) {
			custom_api_integration_page();
		} elseif ( 'license' === $currenttab ) {
			custom_api_authentication_licensing_page();
		} elseif ( 'edit' === $currenttab ) {
			if ( isset( $api ) ) {
				$api = sanitize_text_field( $api );
				custom_api_wp_edit_api( $api );
			}
		} elseif ( 'delete' === $currenttab ) {
			if ( isset( $api ) ) {
				$api = sanitize_text_field( $api );
				custom_api_wp_delete_api( $api );
			}
		} elseif ( 'view' === $currenttab ) {
			if ( isset( $api ) ) {
				$api = sanitize_text_field( $api );
				custom_api_wp_view_api( $api );
			}
		} elseif ( 'viewsql' === $currenttab ) {
			if ( isset( $api ) ) {
				$api = sanitize_text_field( $api );
				custom_api_wp_view_sqlapi( $api );
			}
		} elseif ( 'deletesql' === $currenttab ) {
			if ( isset( $apisql ) ) {
				$api = sanitize_text_field( $apisql );
				custom_api_wp_delete_sqlapi( $api );
			}
		} elseif ( 'sqledit' === $currenttab ) {

			if ( isset( $apisql ) ) {
				$api = sanitize_text_field( $apisql );
				custom_api_wp_edit_sqlapi( $api );
			}
		} elseif ( 'editexternal' === $currenttab ) {
			if ( isset( $apiname ) ) {
				$api = sanitize_text_field( $apiname );
				custom_api_wp_edit_externalapi( $api );
			}
		} elseif ( 'deleteexternal' === $currenttab ) {
			if ( isset( $apiname ) ) {
				$api = sanitize_text_field( $apiname );
				custom_api_wp_delete_externalapi( $api );
			}
		}
	}
}
