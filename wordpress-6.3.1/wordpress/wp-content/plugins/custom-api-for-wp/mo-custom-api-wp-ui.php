<?php
/**
 * Complete UI flow.
 *
 * @package    Custom_Api_For_WordPress
 * @subpackage Custom_Api_For_WordPress/includes
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

/**
 * Including required file.
 */
require_once 'mo-custom-api-wp-handler.php';
require_once 'mo-custom-api-wp-customer-register-ui.php';

/**
 * Delete specified Custom API configuration.
 *
 * @param mixed $api11 API Name.
 */
function custom_api_wp_delete_api( $api11 ) {
	$get_api = get_option( 'CUSTOM_API_WP_LIST' );
	unset( $get_api[ $api11 ] );
	update_option( 'CUSTOM_API_WP_LIST', $get_api );
	update_option( 'custom_api_wp_message', 'API Deleted Successfully.' );
	custom_api_wp_show_success_message();
	custom_api_wp_list_api();
}

/**
 * Delete specified Custom SQL API configuration.
 *
 * @param mixed $api11 API Name.
 */
function custom_api_wp_delete_sqlapi( $api11 ) {
	$get_api = get_option( 'custom_api_wp_sql' );
	unset( $get_api[ $api11 ] );
	update_option( 'custom_api_wp_sql', $get_api );
	update_option( 'custom_api_wp_message', 'API Deleted Successfully.' );
	custom_api_error_message();
	custom_api_wp_saved_sql_api();
}

/**
 * Delete specified External API configuration.
 *
 * @param mixed $api11 API Name.
 */
function custom_api_wp_delete_externalapi( $api11 ) {
	$get_api = get_option( 'custom_api_save_ExternalApiConfiguration' );
	unset( $get_api[ $api11 ] );
	update_option( 'custom_api_save_ExternalApiConfiguration', $get_api );
	update_option( 'ExternalApiResponseKey', '' );
	update_option( 'custom_api_wp_message', 'API Deleted Successfully.' );
	custom_api_error_message();
	custom_api_wp_saved_external_api_connection();
}

/**
 * Displays Custom API Configuration.
 *
 * @param mixed $api1 API Name.
 */
function custom_api_wp_view_api( $api1 ) {
	$get_api            = get_option( 'CUSTOM_API_WP_LIST' );
	$get_form           = get_option( 'mo_custom_api_form' );
	$details            = $get_api[ $api1 ];
	$method_name        = $details['MethodName'];
	$table_name         = $details['TableName'];
	$selected_column    = $details['SelectedColumn'];
	$condition_column   = $details['ConditionColumn'];
	$selected_condtion  = $details['SelectedCondtion'];
	$selected_parameter = $details['SelectedParameter'];

	$api = get_site_url();
	if ( 'no condition' === $selected_condtion ) {
		$api_display = "{$api}/wp-json/mo/v1/{$api1}";
	} else {
		$api_display = "{$api}/wp-json/mo/v1/{$api1}/{" . $condition_column . '}';
	}

	custom_api_wp_view_api_details( $api_display, $api1, $method_name, $condition_column, $selected_condtion, $selected_parameter );
}


/**
 * Displays Custom SQL API Configuration.
 *
 * @param mixed $api1 API Name.
 */
function custom_api_wp_view_sqlapi( $api1 ) {
	$get_api         = get_option( 'custom_api_wp_sql' );
	$details         = $get_api[ $api1 ];
	$method_name     = $details['method'];
	$query_parameter = $details['query_params'];
	$sql_query       = $details['sql_query'];
	$pattern         = '/{{[A-Z]*[a-z]*_[A-Z]*[a-z]*[0-9]*}}/';

	$customparams = array();

	if ( preg_match_all( $pattern, $sql_query, $reg_array ) ) {
		foreach ( $reg_array[0] as $attr ) {
			$mo_regex = substr( $attr, 2 );
			$mo_regex = substr( $mo_regex, 0, -2 );
			array_push( $customparams, $mo_regex );
		}
	}

	$api = get_site_url();
	if ( ! $query_parameter ) {
		$api_display = "{$api}/wp-json/mo/v1/{$api1}";
	} else {

			$api_display = "{$api}/wp-json/mo/v1/{$api1}";

		if ( 'GET' === $method_name ) {
			$api_display = $api_display . '?';
			$size        = count( $customparams );
			for ( $i = 0; $i < $size; $i++ ) {

				$api_display = $api_display . $customparams[ $i ] . '=<' . $customparams[ $i ] . '_value>';
				if ( $i !== $size - 1 ) {
					$api_display = $api_display . '&';
				}
			}
		}
	}

	custom_api_wp_view_sql_api_details( $api_display, $api1, $method_name, $customparams );
}

/**
 * Performs save operation for Custom API Configuration.
 *
 * @param mixed $api1 API Name.
 */
function custom_api_wp_edit_api( $api1 ) {
	$get_api            = get_option( 'CUSTOM_API_WP_LIST' );
	$get_form           = get_option( 'mo_custom_api_form' );
	$details            = $get_api[ $api1 ];
	$method_name        = $details['MethodName'];
	$table_name         = $details['TableName'];
	$selected_column    = $details['SelectedColumn'];
	$condition_column   = $details['ConditionColumn'];
	$selected_condtion  = $details['SelectedCondtion'];
	$selected_parameter = $details['SelectedParameter'];

	if ( isset( $_POST['SendResult'] ) && ( isset( $_POST['SubmitUser'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) : false ) && current_user_can( 'administrator' ) ) {
		if ( 'yes' === $get_form['status'] ) {
			$query = $get_form['query'];

			$api_name_edit           = $get_form['ApiName'];
			$method_name_edit        = $get_form['MethodName'];
			$table_name_edit         = $get_form['TableName'];
			$selected_column_edit    = $get_form['SelectedColumn'];
			$condition_column_edit   = $get_form['ConditionColumn'];
			$selected_condition_edit = $get_form['SelectedCondtion'];
			$selected_parameter_edit = $get_form['SelectedParameter'];

			$current = array(
				$api_name_edit => array(
					'TableName'         => $table_name_edit,
					'MethodName'        => $method_name,
					'SelectedColumn'    => $selected_column_edit,
					'ConditionColumn'   => $condition_column_edit,
					'SelectedCondtion'  => $selected_condition_edit,
					'SelectedParameter' => $selected_parameter_edit,
					'query'             => $query,
				),

			);

			$list = get_option( 'CUSTOM_API_WP_LIST' );
			unset( $list[ $api_name_edit ] );
			$list[ $api_name_edit ] = $current[ $api_name_edit ];
			$api                    = get_site_url();

			if ( 'no condition' === $selected_condition_edit ) {
				$api_display = "{$api}/wp-json/mo/v1/{$api_name_edit}";
			} else {
				$api_display = "{$api}/wp-json/mo/v1/{$api_name_edit}/{" . $condition_column_edit . '}';
			}

			update_option( 'CUSTOM_API_WP_LIST', $list );
			unset( $get_form['status'] );
			update_option( 'mo_custom_api_form', $get_form );
			custom_api_wp_view_api_details( $api_display, $api_name_edit, $method_name, $condition_column_edit, $selected_condition_edit, $selected_parameter_edit );
			return;
		}
	}

	?>
		<div class="wrap mo_custom_api_page_layout_wrap">
			<div class="box-body">
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="padding: 15px 25px 25px 25px;margin-left:3px">

						<form method="POST" style="visibility: hidden;">
								<?php wp_nonce_field( 'CheckNonce2', 'SubmitUser2' ); ?>
								<input type="text" id="api_name_initial2" name="api_name_initial2" style="visibility: hidden;">
								<input type="text" id="method_name_initial2" name="method_name_initial2" style="visibility: hidden;">
								<input type="text" id="table_name_initial2" name="table_name_initial2" style="visibility: hidden;">
								<input type="submit" id="SubmitForm2" name="SubmitForm2" style="visibility: hidden;">
						</form>

						<form method="POST">
							<?php wp_nonce_field( 'CheckNonce', 'SubmitUser' ); ?>
							<p style="margin-top: -30px;" class="mo_custom_api_heading">Update Custom API: <span style="float:right;">  <a class="mo_custom_api_setup_guide_button" href="https://plugins.miniorange.com/wordpress-create-custom-rest-api-endpoints#step1" target="_blank">Setup Guide</a> </span></p>
							<hr class="mo_custom_api_hr">
							<div class='row'>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels">API Name</label>
								</div>
								<div class='col-md-6'>
									<input type="text" class="mo_custom_api_SelectColumn mo_custom_api_name" id="ApiName" name="ApiName" <?php echo 'value = ' . esc_attr( $api1 ); ?> readonly>
								</div>
							</div>
							<br>
							<div class='row'>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels"> Select Method</label>
								</div>
								<div class='col-md-6'>
									<select class="mo_custom_api_SelectColumn" id="MethodName" name="MethodName" disabled>
										<option value="GET" 
										<?php
										if ( 'GET' === $method_name ) {
											echo " selected='selected'";}
										?>
										>GET</option>
										<option value="POST" 
										<?php
										if ( 'POST' === $method_name ) {
											echo " selected='selected'";}
										?>
										>POST</option>
										<option value="PUT" 
										<?php
										if ( 'PUT' === $method_name ) {
											echo " selected='selected'";}
										?>
										>PUT</option>
										<option value="DELETE" 
										<?php
										if ( 'DELETE' === $method_name ) {
											echo " selected='selected'";}
										?>
										>DELETE</option>
									</select>
								</div>
							</div>
							<br>
							<div class='row'>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels"> Select Table</label>
								</div>
								<div class='col-md-6'>
									<select class="mo_custom_api_SelectColumn" name="select-table" id="select-table" onchange="custom_api_wp_GetTbColumn2()">
										<?php
											global $wpdb;
											$sql_query        = '%%';
											$results          = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLES LIKE %s', $sql_query ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Caching not required as our custom API implementation
											$table_name_array = array();
										foreach ( $results as $index => $value ) {
											foreach ( $value as $table_name_temp ) {
												array_push( $table_name_array, $table_name_temp );
											}
										}
										$data      = get_option( 'mo_custom_api_form2' );
										$form_data = get_option( 'mo_custom_api_form' );
										foreach ( $table_name_array as $tb ) {
											echo '<option value=' . esc_attr( $tb );
											if ( isset( $_POST['SubmitForm2'] ) ) {
												if ( ! empty( $data['TableName'] ) ) {
													if ( $data['TableName'] === $tb ) {
														echo " selected='selected'";
													}
												}
											} else {
												if ( ! empty( $form_data['TableName'] ) ) {
													if ( $form_data['TableName'] === $tb ) {
														echo " selected='selected'";
													}
												}
											}
											echo ' >  ' . esc_html( $tb ) . ' </option>';
										}
										?>
									</select>
								</div>
							</div>
							<br>
							<div class='row'>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels"> Select Columns</label>
								</div>
								<div class='col-md-6'>
									<select class="mo_custom_api_SelectColumn" id="SelectedColumn" multiple="multiple" name="SelectedColumn">
										<?php
											global $wpdb;
											$data      = get_option( 'mo_custom_api_form2' );
											$form_data = get_option( 'mo_custom_api_form' );
										if ( ! empty( $data['TableName'] ) ) {
											$table1           = $data['TableName'];
											$column           = array();
											$existing_columns = $wpdb->get_col( $wpdb->prepare( 'DESC %1s', $table1 ), 0 ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
											foreach ( $existing_columns as $col ) {
												array_push( $column, $col );
											}
											foreach ( $column as $colu ) {
												echo '<option value=' . esc_attr( $colu );
												echo '>' . esc_html( $colu ) . '</option>';
											}
										}
										if ( empty( $data['TableName'] ) ) {
											if ( ! empty( $form_data['status'] ) && ( 'yes' === $form_data['status'] ) && ! empty( $form_data['TableName'] ) ) {

												$table1   = $form_data['TableName'];
												$column11 = $form_data['SelectedColumn'];

												$column           = array();
												$existing_columns = $wpdb->get_col( $wpdb->prepare( 'DESC %1s', $table1 ), 0 ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
												foreach ( $existing_columns as $col ) {
													array_push( $column, $col );
												}
												foreach ( $column as $colu ) {
													$split = explode( ',', $column11 );

													echo '<option value=' . esc_attr( $colu );

													foreach ( $split as $s ) {
														if ( $s === $colu ) {
															echo " selected='selected'";
														}
													}

													echo '>' . esc_html( $colu ) . '</option>';
												}
											} else {
												$column           = array();
												$existing_columns = $wpdb->get_col( $wpdb->prepare( 'DESC %1s', $form_data['TableName'] ), 0 ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
												foreach ( $existing_columns as $col ) {
													array_push( $column, $col );
												}
												foreach ( $column as $colu ) {
													$split = explode( ',', $selected_column );
													echo '<option value=' . esc_attr( $colu );
													foreach ( $split as $s ) {
														if ( $s === $colu ) {
															echo " selected='selected'";
														}
													}
													echo '>' . esc_html( $colu ) . '</option>';
												}
											}
										}
										?>

									</select>
								</div>
							</div>
							<br>

							<div class='row'>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels">Choose Column to apply condition</label>
									<br>
									<select class="mo_custom_api_SelectColumn" id="OnColumn" name="OnColumn">
										<option value="" >None selected </option>
											<?php
												global $wpdb;
												$form_data = get_option( 'mo_custom_api_form' );
												$data      = get_option( 'mo_custom_api_form2' );
											if ( ! empty( $data['TableName'] ) ) {
												$table1           = $data['TableName'];
												$column           = array();
												$existing_columns = $wpdb->get_col( $wpdb->prepare( 'DESC %1s', $table1 ), 0 ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
												foreach ( $existing_columns as $col ) {
													array_push( $column, $col );
												}
												foreach ( $column as $colu ) {
													echo '<option value=' . esc_attr( $colu );

													echo '>' . esc_html( $colu ) . '</option>';
												}
											}
											if ( ! empty( $form_data['status'] ) && ( 'yes' === $form_data['status'] ) && ! empty( $form_data['TableName'] ) ) {
												$table1 = $form_data['TableName'];

												$column           = array();
												$existing_columns = $wpdb->get_col( $wpdb->prepare( 'DESC %1s', $table1 ), 0 ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
												foreach ( $existing_columns as $col ) {
													array_push( $column, $col );
												}
												foreach ( $column as $colu ) {
													echo '<option value=' . esc_attr( $colu );

													if ( $form_data['ConditionColumn'] === $colu ) {
														echo " selected = 'selected' ";
													}

													echo '>' . esc_html( $colu ) . '</option>';
												}
											} else {
												$column           = array();
												$existing_columns = $wpdb->get_col( $wpdb->prepare( 'DESC %1s', $table_name ), 0 ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
												foreach ( $existing_columns as $col ) {
													array_push( $column, $col );
												}
												foreach ( $column as $colu ) {
													echo '<option value=' . esc_attr( $colu );
													if ( $condition_column === $colu ) {
														echo " selected='selected'";
													}
													echo '>' . esc_html( $colu ) . '</option>';
												}
											}
											if ( isset( $data['TableName'] ) ) {
												unset( $data['TableName'] );
											}
											update_option( 'mo_custom_api_form2', $data );

											?>
									</select>
								</div>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels">Choose Condition</label>
									<br>
									<select class="mo_custom_api_SelectColumn" id="ColumnCondition" name="ColumnCondition">
										<option value="no condition" 
										<?php
										$check_flag = false;
										if ( isset( $_POST['SubmitUser'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) : false ) {
											$check_flag = $check_flag;
										}
										if ( 'no condition' === $selected_condtion || $check_flag ) {
											echo " selected='selected'";}
										?>
										>no condition </option>
										<option value="=" 
										<?php
										if ( '=' === $selected_condtion && ! $check_flag ) {
											echo " selected='selected'";}
										?>
										>Equal </option>
										<option value="Like" 
										<?php
										if ( 'Like' === $selected_condtion && ! $check_flag ) {
											echo " selected='selected'";}
										?>
										>Like</option>
										<option value=">" 
										<?php
										if ( '>' === $selected_condtion && ! $check_flag ) {
											echo " selected='selected'";}
										?>
										>Greater Than</option>
										<option value="less than" 
										<?php
										if ( 'less than' === $selected_condtion && ! $check_flag ) {
											echo " selected='selected'";}
										?>
										>Less Than</option>
										<option value="!=" 
										<?php
										if ( '!=' === $selected_condtion && ! $check_flag ) {
											echo " selected='selected'";}
										?>
										>Not Equal</option>
									</select>
								</div>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels">URL Parameters<span style="font-size:12px;"> [Default: First Parameter]</span></label>
									<br>
									<select class="mo_custom_api_SelectColumn" id="ColumnParam" onchange="custom_api_wp_CustomText()" name="ColumnParam">
										<option value="1" 
										<?php
										if ( 1 === (int) $selected_parameter ) {
											echo " selected='selected'";}
										?>
										>First Parameter </option>
										<option value="2" disabled 
										<?php
										if ( 2 === (int) $selected_parameter ) {
											echo " selected='selected'";}
										?>
										>Second Parameter</option>
										<option value="3" disabled 
										<?php
										if ( 3 === (int) $selected_parameter ) {
											echo " selected='selected'";}
										?>
										>Third Parameter</option>
										<option value="4" disabled 
										<?php
										if ( 4 === (int) $selected_parameter ) {
											echo " selected='selected'";}
										?>
										>Fourth Parameter</option>
										<option value="5" disabled 
										<?php
										if ( 5 === (int) $selected_parameter ) {
											echo " selected='selected'";}
										?>
										>Custom value</option>
									</select>
									<div id="Param" style="visibility: hidden;">
										<input type="text" id="CustomParam">
									</div>
								</div>
							</div>
							<br>
							<hr class="mo_custom_api_hr">
							<input type="submit" class='mo_custom_api_create_update_btn' value="Update API" name="SendResult" id="SendResult" onclick="custom_api_wp_ShowData()">
							<input type="text" id="QueryVal" name="QueryVal" style="visibility: hidden;">
							<input type="text" id="Selectedcolumn11" name="Selectedcolumn11" style="visibility: hidden;">
						</form>
					</div>
					<?php
						contact_form();
						mo_custom_api_advertisement();
					?>
				</div>
			</div>
		</div>
	<?php
}


/**
 * Displays Custom APIs List.
 */
function custom_api_wp_list_api() {
	if ( get_option( 'CUSTOM_API_WP_LIST' ) ) {
		?>
			<div class="wrap mo_custom_api_page_layout_wrap" style="margin-left: 18px;">
				<div class="box-body">		
					<div class="row mo_custom_api_page_layout_row">

						<div class="col-md-8 mo_custom_api_page_layout">
							<div style="display: flex; justify-content: space-between;">
							<p style="margin: 15px 0px 10px 13px;"class="mo_custom_api_heading">Configured API's:</p>
							<a class="mo_custom_api_ext_btn" style="float:right;  margin-top:12px;padding:6px" href="admin.php?page=custom_api_wp_settings&action=addapi"> Create API</a>
							</div>
							<table id="tbldata" class="table table-hover" style="width: 75%">
								<thead>
									<tr class="header">
										<th style="display:none">RowId</th>
										<th style="font-weight:700;">API NAME</th>
										<th style="font-weight:700;">ACTIONS</th>
									</tr>
								</thead>
								<tbody id="tbodyid">
									<?php
									if ( get_option( 'CUSTOM_API_WP_LIST' ) ) {
										$list = get_option( 'CUSTOM_API_WP_LIST' );
										foreach ( $list as $key => $value ) {
											echo '<tr>';
											echo "<td class='mo_custom_api_list_api_name'>" . esc_html( $key ) . '</td>';
											echo "<td>  <button class='mo_custom_api_ext_btn' onclick = 'custom_api_wp_edit(this)'>Edit<i class='fas fa-user-edit'></i></button>&nbsp
                                                            <button class='mo_custom_api_ext_btn' onclick ='custom_api_wp_delete(this)'>Delete<i class='fas fa-user-edit'></i></button>&nbsp
                                                            <button class='mo_custom_api_ext_btn' onclick = 'custom_api_wp_view(this)'>View<i class='fas fa-user-edit'></i></button>
                                                    </td>";
										}
									}
									?>
								</tbody>
							</table>					
						</div>
						<?php
							contact_form();
							mo_custom_api_advertisement();
						?>
					</div>
				</div> 
			</div>
		<?php
	} else {
		?>
		<div class="wrap mo_custom_api_page_layout_wrap">
			<div class="box-body">
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="margin:0px 0px 0px 3px;padding:30px;padding-top: 20px;">
						<p class="mo_custom_api_heading">Configured Custom APIs:</p>
						<hr>
						<h6 style="margin-bottom:18px;">You have not created any custom API, to start <a class="mo_custom_api_ext_btn" href="admin.php?page=custom_api_wp_settings&action=addapi"><button class="mo_custom_api_ext_btn"> Click here</button></a></h6>
					</div>
				<?php
				contact_form();
				mo_custom_api_advertisement();
				?>
				</div>
			</div>
		</div>
			<?php
	}
}


/**
 * Displays Additonal Details For Custom SQL API.
 *
 * @param mixed $api_display API Link.
 * @param mixed $api_name API Name.
 * @param mixed $method_name Method Name.
 * @param mixed $customparams Custom Params.
 */
function custom_api_wp_view_sql_api_details( $api_display, $api_name, $method_name, $customparams ) {

	?>
		<div class="wrap mo_custom_api_page_layout_wrap">
			<div class="box-body">
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="margin-left:3px;padding-left: 25px;padding-top: 20px;">
						<h5><?php echo ( " <span style='color:green;font-weight:700'>" . esc_attr( $method_name ) . '</span> /' . esc_attr( $api_name ) ); ?></h5>
						<p style="margin-top:20px;">
						<div class="mo_custom_api_method_name"><?php echo esc_html( "{$method_name}" ); ?></div>
							<input id="mo_custom_api_copy_text" class="mo_custom_api_display" value='<?php echo esc_attr( "{$api_display}" ); ?>' readonly>
							<button onclick="mo_custom_api_copy_icon()" style="border: none;background-color: white;outline:none;"><img style="width:25px;height:25px;margin-top:-6px;"  src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>/images/copy3.png"></button>
						</p>

						<script>
							function mo_custom_api_copy_icon() {
								var copyText = document.getElementById("mo_custom_api_copy_text");
								copyText.select();
								copyText.setSelectionRange(0, 99999);
								navigator.clipboard.writeText(copyText.value);
							}
						</script>

						<div class="mo_custom_api_view_api_table">
							<div class="mo_custom_api_view_api_table_heading">
								<h6>Example</h6>
							</div>
							<table class="table table-bordered">
								<thead>
									<tr>
										<td><b>Request</b></td>
										<td><b>Format</b></td>
									</tr>
								</thead>
								<tbody>
									<?php
									if ( 'GET' === $method_name ) {
										?>
											<tr>
												<td>Curl</td>
												<td>curl -X GET <?php echo esc_attr( "$api_display" ); ?></td>
											</tr>
											<?php
									} else {
										if ( count( $customparams ) ) {
											$curlbody = '';
											$i        = 0;
											$size     = count( $customparams );
											for ( $i = 0; $i < $size; $i++ ) {
												$curlbody = $curlbody . $customparams[ $i ] . '={' . $customparams[ $i ] . '_value}';

												if ( ( $size - 1 ) !== $i ) {
														$curlbody = $curlbody . '&';
												}
											}
											?>
													<tr>
														<td>Curl</td>
														<td>curl -d "<?php echo esc_attr( $curlbody ); ?>" -X <?php echo esc_attr( $method_name ); ?> "<?php echo esc_attr( $api_display ); ?>"</td>
													</tr>
												<?php
										} else {
											?>
												<tr>
													<td>Curl</td>
													<td>curl -X <?php echo esc_attr( $method_name ); ?> <?php echo esc_attr( "$api_display" ); ?></td>
												</tr>
												<?php
										}
									}
									?>
								</tbody>
							</table>
						</div>
						<?php $api = get_site_url(); ?>
						<form action="<?php echo esc_attr( "$api" ); ?>/wp-admin/admin.php?page=custom_api_wp_settings&action=sqledit&apisql=<?php echo esc_attr( "$api_name" ); ?>" method="POST">
							<input class="mo_custom_api_contact_us_submit_btn" style="margin-bottom: 20px;margin-top:20px;" type="submit" value="Edit API" onclick="">
						</form>      
					</div>
					<?php
						contact_form();
						mo_custom_api_advertisement();
					?>
				</div>
			</div>
		</div>    
	<?php
}


/**
 * Displays Additional Details For Custom API.
 *
 * @param mixed $api_display API Link.
 * @param mixed $api_name API Name.
 * @param mixed $method_name Method Name.
 * @param mixed $condition_column Column Selected For Applying Condition.
 * @param mixed $selected_condtion Selected Condition To Apply On Column.
 * @param mixed $selected_parameter Selected Parameters.
 */
function custom_api_wp_view_api_details( $api_display, $api_name, $method_name, $condition_column, $selected_condtion, $selected_parameter ) {
	?>
		<div class="wrap mo_custom_api_page_layout_wrap">
			<div class="box-body">
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="margin-left:3px;padding-left: 25px;padding-top: 20px;">
						<h5><?php echo " <span style='color:green;font-weight:700'>" . esc_html( $method_name ) . '</span>/' . esc_html( $api_name ); ?></h5>
						<p style="margin-top:20px;">
							<div class="mo_custom_api_method_name"><?php echo esc_html( $method_name ); ?></div>
							<input id="mo_custom_api_copy_text1" class="mo_custom_api_display" value='<?php echo esc_attr( $api_display ); ?>' readonly>
							<button onclick="mo_custom_api_copy_icon()" style="border: none;background-color: white;outline:none;"><img style="width:25px;height:25px;margin-top:-6px;"  src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>/images/copy3.png"></button>
						</p>

						<script>
							function mo_custom_api_copy_icon() {
								var copyText = document.getElementById("mo_custom_api_copy_text1");
								copyText.select();
								copyText.setSelectionRange(0, 99999);
								navigator.clipboard.writeText(copyText.value);
							}
						</script>

						<?php
						if ( 'no condition' !== $selected_condtion ) {
							?>
									<div class="mo_custom_api_view_api_table">
										<div class="mo_custom_api_view_api_table_heading">
											<h6>Request Body</h6>
										</div>
										<table class="table table-bordered">
											<thead>
												<tr>
													<th>Column Name</th>
													<th>Description</th>
													<th>Condition Applied</th>
													<th>Parameter place in API</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td> <?php echo esc_html( $condition_column ); ?> </td>
													<td>Enter data of respective column in mentioned parameter</td>
													<td>
													<?php
													if ( '>' === $selected_condtion ) {
														echo 'Greater Than';
													} else {
														echo esc_html( $selected_condtion );
													}
													?>
													</td>
													<td> <?php echo esc_html( $selected_parameter ); ?> </td>
												</tr>
											</tbody>
										</table>
									</div>
								<?php
						}
						?>

						<div class="mo_custom_api_view_api_table">
							<div class="mo_custom_api_view_api_table_heading">
								<h6>Example</h6>
							</div>
							<table class="table table-bordered">
								<thead>
									<tr>
										<td><b>Request</b></td>
										<td><b>Format</b></td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Curl</td>
										<td>curl -X GET <?php echo esc_html( $api_display ); ?></td>
									</tr>
								</tbody>
							</table>
						</div>
						<?php $api = get_site_url(); ?>
						<form action="<?php echo esc_attr( $api ); ?>/wp-admin/admin.php?page=custom_api_wp_settings&action=edit&api=<?php echo esc_attr( $api_name ); ?>" method="POST">
							<input class="mo_custom_api_contact_us_submit_btn" style="margin-bottom: 20px;margin-top:20px;" type="submit" value="Edit API" onclick="">
						</form>      
					</div>
					<?php
						contact_form();
						mo_custom_api_advertisement();
					?>
				</div>
			</div>
		</div>    
	<?php
}


/**
 * Displays invalid field notice.
 */
function custom_api_wp_invalid_notice() {
	?>
		<div class="error notice" style="margin-left: 3px;">
			<p>Invalid API or API Name field is empty</p>

		</div>
	<?php
}


/**
 * Checks If the Method Name Is Selected And Unsets It From The Form Array.
 *
 * @param mixed $var Method Name.
 */
function custom_wp_api_check_method( $var ) {
	if ( isset( $_POST['SubmitForm1'] ) && ( isset( $_POST['SubmitUser1'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser1'] ) ), 'CheckNonce1' ) : false ) ) {
		$data = get_option( 'mo_custom_api_form1' );
		if ( ! empty( $data['MethodName'] ) ) {
			if ( $data['MethodName'] === $var ) {
				echo " selected='selected'";
				unset( $data['MethodName'] );
				update_option( 'mo_custom_api_form1', $data );
			}
		}
	}
	if ( isset( $_POST['SendResult'] ) && ( isset( $_POST['SubmitUser'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) : false ) && current_user_can( 'administrator' ) ) {
		$form_data = get_option( 'mo_custom_api_form' );
		if ( ( ! empty( $form_data['MethodName'] ) ) && ( 'yes' === $form_data['status'] ) ) {
			if ( $form_data['MethodName'] === $var ) {
				echo " selected='selected'";
			}
		}
	}
}


/**
 * Checks If The Given Condition Is Selected.
 *
 * @param mixed $var1 Conditon.
 */
function custom_api_wp_condition( $var1 ) {
	if ( isset( $_POST['SendResult'] ) && ( isset( $_POST['SubmitUser'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) : false ) ) {
		$form_data = get_option( 'mo_custom_api_form' );

		if ( ! empty( $form_data['SelectedCondtion'] ) && ( 'yes' === $form_data['status'] ) ) {
			if ( $form_data['SelectedCondtion'] === $var1 ) {
				echo " selected= 'selected' ";
			}
		}
	}
}


/**
 * Checks If The Given Condition Is Selected.
 *
 * @param mixed $var Parameter.
 */
function custom_api_wp_param( $var ) {
	$form_data = get_option( 'mo_custom_api_form' );
	if ( isset( $_POST['SendResult'] ) && ( isset( $_POST['SubmitUser'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) : false ) ) {
		if ( ! empty( $form_data['SelectedParameter'] ) && ( 'yes' === $form_data['status'] ) ) {
			if ( $form_data['SelectedParameter'] === $var ) {
				echo "selected='selected'";

				$form_data['status'] = 'no';
				update_option( 'mo_custom_api_form', $form_data );
			}
		}
	}
}

/**
 * Handles Flow For Saving Custom API Configuration.
 */
function custom_api_wp_add_api() {
	$check    = true;
	$get_form = get_option( 'mo_custom_api_form' );

	if ( isset( $_POST['SendResult'] ) && ( isset( $_POST['SubmitUser'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) : false ) ) {
		if ( current_user_can( 'administrator' ) ) {
			if ( 'yes' === $get_form['status'] ) {
				$api_name = $get_form['ApiName'];
				if ( empty( $api_name ) ) {
					custom_api_wp_invalid_notice();
					$check = false;
				}
				$query              = $get_form['query'];
				$method_name        = $get_form['MethodName'];
				$selected_table     = $get_form['TableName'];
				$selected_column    = $get_form['SelectedColumn'];
				$condition_column   = $get_form['ConditionColumn'];
				$selected_condtion  = $get_form['SelectedCondtion'];
				$selected_parameter = $get_form['SelectedParameter'];

				$current = array(
					$api_name => array(
						'TableName'         => $selected_table,
						'MethodName'        => $method_name,
						'SelectedColumn'    => $selected_column,
						'ConditionColumn'   => $condition_column,
						'SelectedCondtion'  => $selected_condtion,
						'SelectedParameter' => $selected_parameter,
						'query'             => $query,
					),
				);

				if ( get_option( 'CUSTOM_API_WP_LIST' ) ) {
					$list = get_option( 'CUSTOM_API_WP_LIST' );
					if ( isset( $list[ $api_name ] ) && ! empty( $list[ $api_name ] ) ) {
						echo '
							<div class="error notice" style="margin-left:3px">
								<p style="color:red;"><b>API name already exist !!</b></p>
							</div>';

							$check = false;
					}
				}
				if ( true === $check ) {
					if ( get_option( 'CUSTOM_API_WP_LIST' ) ) {
						$list[ $api_name ] = $current[ $api_name ];

						$api = get_site_url();
						if ( 'no condition' === $selected_condtion ) {
							$api_display = "{$api}/wp-json/mo/v1/{$api_name}";
						} else {
							$api_display = "{$api}/wp-json/mo/v1/{$api_name}/{" . $condition_column . '}';
						}

						update_option( 'CUSTOM_API_WP_LIST', $list );
						unset( $get_form['status'] );
						update_option( 'mo_custom_api_form', $get_form );
						custom_api_wp_view_api_details( $api_display, $api_name, $method_name, $condition_column, $selected_condtion, $selected_parameter );
						return;
					} else {
						$api = get_site_url();
						if ( 'no condition' === $selected_condtion ) {
							$api_display = "{$api}/wp-json/mo/v1/{$api_name}";
						} else {
							$api_display = "{$api}/wp-json/mo/v1/{$api_name}/{" . $condition_column . '}';
						}
						update_option( 'CUSTOM_API_WP_LIST', $current );
						unset( $get_form['status'] );
						update_option( 'mo_custom_api_form', $get_form );
						custom_api_wp_view_api_details( $api_display, $api_name, $method_name, $condition_column, $selected_condtion, $selected_parameter );
						return;
					}
				}
			}
		}
	}

	?>
		<div class="wrap mo_custom_api_page_layout_wrap">
			<div class="box-body">
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="padding: 15px 25px 25px 25px;margin-left:3px">

						<form method="POST" style="visibility: hidden;">
							<?php wp_nonce_field( 'CheckNonce1', 'SubmitUser1' ); ?>
							<input type="text" id="api_name_initial" name="api_name_initial" style="visibility: hidden;">
							<input type="text" id="method_name_initial" name="method_name_initial" style="visibility: hidden;">
							<input type="text" id="table_name_initial" name="table_name_initial" style="visibility: hidden;">
							<input type="submit" id="SubmitForm1" name="SubmitForm1" style="visibility: hidden;">
						</form>

						<p style="margin-top: -30px;" class="mo_custom_api_heading">Create Custom API: <span style="float:right;">  <a class="mo_custom_api_setup_guide_button" href="https://plugins.miniorange.com/wordpress-create-custom-rest-api-endpoints#step1" target="_blank">Setup Guide</a> </span></p>
						<hr class="mo_custom_api_hr">
						<form method="POST">
							<?php wp_nonce_field( 'CheckNonce', 'SubmitUser' ); ?>
							<div class='row'>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels"> API Name</label>
								</div>
								<div class='col-md-6'>
									<input class="mo_custom_api_name" type="text" id="ApiName" 
									<?php
									$data = get_option( 'mo_custom_api_form1' );
									if ( isset( $_POST['SubmitForm1'] ) && ( isset( $_POST['SubmitUser1'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser1'] ) ), 'CheckNonce1' ) : false ) && current_user_can( 'administrator' ) ) {
										if ( ! empty( $data['ApiName'] ) ) {
											echo 'value ="' . esc_attr( $data['ApiName'] ) . '" ';
											unset( $data['ApiName'] );
											update_option( 'mo_custom_api_form1', $data );
										}
									}
										$form_data = get_option( 'mo_custom_api_form' );
									if ( isset( $_POST['SendResult'] ) && ( isset( $_POST['SubmitUser'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) : false ) && current_user_can( 'administrator' ) ) {
										if ( ( 'yes' === $form_data['status'] ) && ! empty( $form_data['ApiName'] ) ) {
											echo 'value ="' . esc_attr( $form_data['ApiName'] ) . '" ';
										}
									}
									?>
									name="ApiName">
								</div>
							</div>
							<br>      
							<div class='row'>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels"> Select Method</label>
								</div>
								<div class='col-md-6'>
									<select required class="mo_custom_api_SelectColumn" id="MethodName" name="MethodName">
										<option value="GET" selected<?php custom_wp_api_check_method( 'GET' ); ?>>GET</option>
										<option value="POST" disabled <?php custom_wp_api_check_method( 'POST' ); ?>>POST &nbsp &nbsp &nbsp &nbsp<span style="text-color:red;text-size:30px;">[PREMIUM]</span></option>
										<option value="PUT" disabled <?php custom_wp_api_check_method( 'PUT' ); ?>>PUT &nbsp &nbsp &nbsp &nbsp &nbsp<span>[PREMIUM]</span></option>
										<option value="DELETE" disabled <?php custom_wp_api_check_method( 'DELETE' ); ?>>DELETE &nbsp &nbsp <span>[PREMIUM]</span></option>
									</select>
								</div>
							</div>
							<br>
							<div class='row'>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels"> Select Table</label>
								</div>
								<div class='col-md-6'>
									<select class="mo_custom_api_SelectColumn" name="select-table" onchange="custom_api_wp_GetTbColumn()" id="select-table">
										<?php
											global $wpdb;
											$sql_query        = '%%';
											$results          = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLES LIKE %s', $sql_query ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
											$table_name_array = array();
										foreach ( $results as $index => $value ) {
											foreach ( $value as $table_name ) {
												array_push( $table_name_array, $table_name );
											}
										}
											$data      = get_option( 'mo_custom_api_form1' );
											$form_data = get_option( 'mo_custom_api_form' );
										foreach ( $table_name_array as $tb ) {
											echo '<option value=' . esc_attr( $tb );
											if ( isset( $_POST['SubmitForm1'] ) && ( isset( $_POST['SubmitUser1'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser1'] ) ), 'CheckNonce1' ) : false ) && current_user_can( 'administrator' ) ) {
												if ( ! empty( $data['TableName'] ) ) {
													if ( $data['TableName'] === $tb ) {
														echo " selected='selected'";
													}
												}
											}
											if ( isset( $_POST['SendResult'] ) && ( isset( $_POST['SubmitUser'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['SubmitUser'] ) ), 'CheckNonce' ) : false ) && current_user_can( 'administrator' ) ) {
												if ( ( 'yes' === $form_data['status'] ) && ! empty( $form_data['TableName'] ) && current_user_can( 'administrator' ) ) {
													if ( $form_data['TableName'] === $tb ) {
														echo " selected='selected'";
													}
												}
											}

											echo '>' . esc_html( $tb ) . '</option>';
										}
										?>
									</select>
								</div>
							</div>
							<br>         
							<div class='row'>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels"> Select Columns</label>
								</div>
								<div class='col-md-6'>
									<select class="mo_custom_api_SelectColumn" id="SelectedColumn" multiple="multiple" name="Selectedcolumn">
										<?php
											global $wpdb;
											$data = get_option( 'mo_custom_api_form1' );
										if ( ! empty( $data['TableName'] ) ) {
											$table1 = $data['TableName'];

											$column           = array();
											$existing_columns = $wpdb->get_col( $wpdb->prepare( 'DESC %1s', $table1 ), 0 ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
											foreach ( $existing_columns as $col ) {
												array_push( $column, $col );
											}
											foreach ( $column as $colu ) {
												echo '<option value=' . esc_attr( $colu );

												echo '>' . esc_html( $colu ) . '</option>';
											}
										}
										?>
										<?php
											global $wpdb;
											$data      = get_option( 'mo_custom_api_form1' );
											$form_data = get_option( 'mo_custom_api_form' );
										if ( empty( $data['TableName'] ) ) {
											if ( ! empty( $form_data['status'] ) && ( 'yes' === $form_data['status'] ) && ! empty( $form_data['TableName'] ) ) {

												$table1 = $form_data['TableName'];

												$column11 = $form_data['SelectedColumn'];

												$column           = array();
												$existing_columns = $wpdb->get_col( $wpdb->prepare( 'DESC %1s', $table1 ), 0 ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
												foreach ( $existing_columns as $col ) {
													array_push( $column, $col );
												}
												foreach ( $column as $colu ) {
													$split = explode( ',', $column11 );

													echo '<option value=' . esc_attr( $colu );

													foreach ( $split as $s ) {
														if ( $s === $colu ) {
															echo " selected='selected'";
														}
													}

													echo '>' . esc_html( $colu ) . '</option>';
												}
											}
										}
										?>
									</select>           
								</div>               
							</div>
							<br>            
							<div class='row'>
								<div class='col-md-4'>
									<label class="mo_custom_api_labels">Choose Column to apply condition</label>
									<br>
									<select class="mo_custom_api_SelectColumn custom_field" id="OnColumn" name="OnColumn">
										<option value="">none selected </option>
										<?php
											global $wpdb;
											$data = get_option( 'mo_custom_api_form1' );
										if ( ! empty( $data['TableName'] ) ) {
											$table1 = $data['TableName'];

											$column           = array();
											$existing_columns = $wpdb->get_col( $wpdb->prepare( 'DESC %1s', $table1 ), 0 ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
											foreach ( $existing_columns as $col ) {
												array_push( $column, $col );
											}
											foreach ( $column as $colu ) {
												echo '<option value=' . esc_attr( $colu );
												echo '>' . esc_html( $colu ) . '</option>';
											}

											unset( $data['TableName'] );
											update_option( 'mo_custom_api_form1', $data );
										}
										?>
										<?php
											global $wpdb;
											$form_data = get_option( 'mo_custom_api_form' );
										if ( ! empty( $form_data['status'] ) && ( 'yes' === $form_data['status'] ) && ! empty( $form_data['TableName'] ) ) {
											$table1 = $form_data['TableName'];

											$column           = array();
											$existing_columns = $wpdb->get_col( $wpdb->prepare( 'DESC %1s', $table1 ), 0 );  //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- quotes around string is not needed in our variables.
											foreach ( $existing_columns as $col ) {
												array_push( $column, $col );
											}
											foreach ( $column as $colu ) {
												echo '<option value=' . esc_attr( $colu );

												if ( $form_data['ConditionColumn'] === $colu ) {
													echo " selected = 'selected' ";
												}

												echo '>' . esc_html( $colu ) . '</option>';
											}
										}
										?>
									</select>
								</div>           
								<div class='col-md-4'>
									<label class="mo_custom_api_labels">Choose Condition</label>
									<br>
									<select class="mo_custom_api_SelectColumn custom_field" id="ColumnCondition" name="ColumnCondition">
										<option value="no condition" <?php custom_api_wp_condition( 'no condition' ); ?>>No Condition </option>
										<option value="=" <?php custom_api_wp_condition( '=' ); ?>>Equal </option>
										<option value="Like" <?php custom_api_wp_condition( 'Like' ); ?>>Like</option>
										<option value=">" <?php custom_api_wp_condition( '>' ); ?>>Greater Than</option>
										<option value="less than" <?php custom_api_wp_condition( 'less than' ); ?>>Less Than</option>
										<option value="!=" <?php custom_api_wp_condition( '!=' ); ?>>Not Equal</option>
									</select>
								</div>        
								<div class='col-md-4'>
									<label class="mo_custom_api_labels">URL Parameters  <span style="font-size:12px">[Default: First Parameter]</span></label>
									<br>
									<select class="mo_custom_api_SelectColumn" id="ColumnParam" onchange="custom_api_wp_CustomText()" name="ColumnParam">
										<option value="1">First Parameter </option>
										<option value="2" disabled>Second Parameter</option>
										<option value="3" disabled>Third Parameter</option>
										<option value="4" disabled>Fourth Parameter</option>
										<option value="5" disabled>Custom value</option>
									</select>
									<div id="Param" style="visibility: hidden;">
										<input type="text" id="CustomParam">
									</div>
								</div>              
							</div>
							<br>
							<hr class="mo_custom_api_hr">
							<input class='mo_custom_api_create_update_btn' type="submit" value="Generate API" name="SendResult" id="SendResult" onclick="custom_api_wp_ShowData()">
							<input type="text" id="QueryVal" name="QueryVal" style="visibility:hidden;">
							<input type="text" id="Selectedcolumn11" name="Selectedcolumn11" style="visibility: hidden;">
						</form>
					</div>
					<?php
						contact_form();
						mo_custom_api_advertisement();
					?>
				</div>
			</div>
		</div>
	<?php
}


/**
 * Displays API Authentication Page.
 */
function custom_api_wp_authentication() {
	?>
		<div class="wrap mo_custom_api_page_layout_wrap" style="margin-left:18px">
			<div class="box-body" >
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="padding: 30px">
						<div>
							<p  class="mo_custom_api_heading" style="margin-top: -12px;">API Key Authentication:<span > <a class="mo_custom_api_plan_link" href="admin.php?page=custom_api_wp_settings&action=license">[PREMIUM]</a></span></h1>
						</div>
						<hr class="mo_custom_api_hr">
						<h5>Universal API Key: </h5>
						<br>
						<h6>You can use the below API key to authenticate your WordPress REST APIs.</h6>
						<div class="row" style="margin-top: 20px;">
							<div class="col-md-4">
								<h6 style="margin-top:5px;"><strong>API Key:</strong></h6>
							</div>
							<div class="col-md-8">
								<input class="mo_custom_api_name" style="padding-right:40px;" type="password" id="password1" placeholder="" readonly value="kgjygfgvjgfthdrdsrye5786utyy6">&nbsp;&nbsp;
								<img id="show_btn" style="height:20px;width:20px;margin-left:-45px;" src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>/images/eye.png">
								<br><br>
								<button class="mo_custom_api_ext_btn" type="button" style="width:135px; padding:4px;" id="myBtn2" ><a id="regeneratetoken" name="action" ><h6 style="font-size:14px">Generate New Key</h6></a></button>
								<br>
							</div>
						</div>
						<hr class="mo_custom_api_hr">
						<br>
						<div class="row">
							<div style="margin-left:10px;">
								<?php $restricted = array(); ?>
								<h6 style="font-weight:500;font-size:1rem;margin-left:0px;">Choose HTTP Methods which you want to restrict from public access :</h6>
								<br>
								<input type="checkbox" id="get_check" name="get_check" value="GET" disabled>
								<label for="get_check"> GET </label><br>
								<input type="checkbox" id="post_check" name="post_check" value="POST" disabled>
								<label for="post_check"> POST</label><br>
								<input type="checkbox" id="put_check" name="put_check" value="PUT" disabled>
								<label for="put_check"> PUT</label><br>
								<input type="checkbox" id="del_check" name="del_check" value="DELETE" disabled>
								<label for="del_check"> DELETE</label><br>
								<button type="button" class="mo_custom_api_contact_us_submit_btn"  style="width:70px;margin-top:12px;" id="myBtn1">Save</button>
							</div>
							<div id="myModal1" class="mo_custom_api_modal">
								<div class="modal-dialog" style="text-align: center;">
									<div class="modal-content">
										<div class="modal-header" style="text-align: center;">
											<h5 class="modal-title mo_custom_api_modal_title">Upgrade Required</h5>
											<button type="button" class="close" data-dismiss="modal">&times;</button>
										</div>
										<div class="modal-body">
											<b><p>You are on the free version. Please upgrade to <span style="color:red;">Premium+</span> Plan to use this feature.</p></b>
										</div>
										<div class="modal-footer" style="text-align: center;">
											<a href="admin.php?page=custom_api_wp_settings&action=license" class="mo_custom_api_upgrade_plan" >Click here to checkout plans</a>
										</div>
									</div>
								</div>
							</div>
							<script>
								var modal = document.getElementById("myModal1");
								var btn = document.getElementById("myBtn1");
								var btn2 = document.getElementById("myBtn2");
								var span = document.getElementsByClassName("close")[0];
								btn.onclick = function() {
									modal.style.display = "block";
								}
								span.onclick = function() {
									modal.style.display = "none";
								}
								btn2.onclick = function() {
									modal.style.display = "block";
								}
								span2.onclick = function() {
									modal.style.display = "none";
								}
								window.onclick = function(event) {
									if (event.target == modal) {
										modal.style.display = "none";
									}
								}
							</script>
						</div>
					</div>
					<?php
						contact_form();
						mo_custom_api_advertisement();
					?>
				</div>
			</div>
		</div>
	<?php
}

/**
 * Displays Custom SQL API Creation Form.
 */
function custom_api_wp_custom_sql() {
	?>
		<div class="wrap mo_custom_api_page_layout_wrap">
			<div class="box-body">
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="padding:30px;padding-top: 15px;">
						<p class="mo_custom_api_heading">Create Custom SQL API:<span style="float:right"> <a class="mo_custom_api_setup_guide_button" href="https://plugins.miniorange.com/integrate-external-third-party-rest-api-endpoints-into-wordpress#step_3" target="_blank">Setup Guide</a> </span></p>
						<hr class="mo_custom_api_hr">
						<form id="custom_api_wp_sql" method="post">
							<?php wp_nonce_field( 'custom_api_wp_sql', 'custom_api_wp_sql_field' ); ?>
							<input type="hidden" name="option" value="custom_api_wp_sql">
							<div class=row>
								<div class=col-md-5>
									<label class="mo_custom_api_labels"> API Name</label>
								</div>
								<div class=col-md-6>
									<input type="text" class="mo_custom_api_custom_field" id="SQLApiName" name="SQLApiName"  required value="">
								</div>
							</div>
							<br>
							<div class=row>
								<div class=col-md-5>
									<label class="mo_custom_api_labels"> Select Method</label>
								</div>
								<p>
									<div class=col-md-7>
										<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="MethodName" name="MethodName" onchange="change_description(this)">
										<option value="GET">GET</option>
										<option value="POST">POST</option>
										<option value="PUT">PUT</option>
										<option value="DELETE">DELETE</option>
										</select>
										<span style="margin-left:210px" class="mo_custom_api_method_description" id="method_description"> Fetch data via API </span> 
									</div>
								</p>
							</div>
							<br>
							<div class=row>
								<div class=col-md-5>
									<label class="mo_custom_api_labels"> Enable custom query parameters:</label>
								</div>
								<div class=col-md-6>
									<input type="checkbox" class="mo_custom_api_SelectColumn" style="margin-top:5px;" id="QueryParameter" name="QueryParameter" value="1"  >
								</div>
							</div>
							<br>
							<div class=row>
								<div class=col-md-5>
									<label class="mo_custom_api_labels"> Enter SQL Query</label>
								</div>
								<div class=col-md-6>
									<textarea id="customsql" name="customsql" rows=10  class="mo_custom_api_txtarea" required></textarea>
								</div>
							</div>
							<hr class="mo_custom_api_hr">
							<input type="submit" class='mo_custom_api_create_update_btn' id="custom_api_wp_sql_submit" value="Generate API">
						</form>
					</div>
					<?php
					contact_form();
					mo_custom_api_advertisement();
					?>
				</div>
			</div>
		</div>
	<?php
}


/**
 * Displays Custom SQL API Configuration.
 *
 * @param mixed $api1 API name.
 */
function custom_api_wp_edit_sqlapi( $api1 ) {
	$get_api     = get_option( 'custom_api_wp_sql' );
	$selectedsql = $get_api[ $api1 ];
	$description = '';
	if ( 'GET' === $selectedsql['method'] ) {
		$description = 'Fetch data via API';
	} elseif ( 'POST' === $selectedsql['method'] ) {
		$description = 'Create/Add data via API';
	} elseif ( 'PUT' === $selectedsql['method'] ) {
		$description = 'Modify data values via API';
	} else {
		$description = 'Delete existing data via API';
	}
	?>
		<div class="wrap mo_custom_api_page_layout_wrap" style="margin-left:18px;">
			<div class="box-body">
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="padding:30px;padding-top: 15px;">
						<p class="mo_custom_api_heading">Update Custom SQL API:<span style="float:right"> <a class="mo_custom_api_setup_guide_button" href="https://plugins.miniorange.com/integrate-external-third-party-rest-api-endpoints-into-wordpress#step_3" target="_blank">Setup Guide</a> </span></p>
						<hr class="mo_custom_api_hr">
						<form id="custom_api_wp_sql" method="post">
							<?php wp_nonce_field( 'custom_api_wp_sql', 'custom_api_wp_sql_field' ); ?>
							<input type="hidden" name="option" value="custom_api_wp_sql">
							<div class=row>
								<div class=col-md-4>
									<label class="mo_custom_api_labels"> API Name</label>
								</div>
								<div class=col-md-6>
									<input type="text" class="mo_custom_api_custom_field" id="SQLApiName" name="SQLApiName" readonly value="<?php echo esc_attr( $api1 ); ?>">
								</div>
							</div>
							<br>
							<div class=row>
								<div class=col-md-4>
									<label class="mo_custom_api_labels"> Select Method</label>
								</div>
								<p>
									<div class=col-md-8>
										<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="MethodName" name="MethodName" readonly onchange="change_description(this)">
											<option value="GET" 
											<?php
											if ( 'GET' === $selectedsql['method'] ) {
												echo 'selected';}
											?>
												>GET</option>
											<option value="POST" 
											<?php
											if ( 'POST' === $selectedsql['method'] ) {
												echo 'selected';}
											?>
												>POST</option>
											<option value="PUT" 
											<?php
											if ( 'PUT' === $selectedsql['method'] ) {
												echo 'selected';}
											?>
												>PUT</option>
											<option value="DELETE" 
											<?php
											if ( 'DELETE' === $selectedsql['method'] ) {
												echo 'selected';}
											?>
												>DELETE</option>
										</select>&nbsp;&nbsp;
										<span style="margin-top:2px;margin-left:210px" class="mo_custom_api_method_description" id="method_description"> <?php echo esc_attr( $description ); ?> </span> 
									</div>
								</p>
							</div>
							<br>
							<div class=row>
								<div class=col-md-4>
									<label class="mo_custom_api_labels"> Enable custom query parameters:</label>
								</div>
								<div class=col-md-6 style="position: relative;left: 5px;">
									<input type="checkbox" class="mo_custom_api_SelectColumn" style="margin-top:5px;" id="QueryParameter" name="QueryParameter" value="1" 
									<?php
									if ( 1 === (int) $selectedsql['query_params'] ) {
										echo 'checked';}
									?>
										>
								</div>
							</div>
							<br>
							<div class=row>
								<div class=col-md-4>
									<label class="mo_custom_api_labels"> Enter SQL Query</label>
								</div>
								<div class=col-md-6>
									<textarea id="customsql" name="customsql" rows=10 class="mo_custom_api_txtarea"><?php echo esc_attr( $selectedsql['sql_query'] ); ?></textarea>                                  
								</div>
							</div>
							<hr class="mo_custom_api_hr">
							<input type="submit" class='mo_custom_api_create_update_btn' id="custom_api_wp_sql_submit" value="Update API">
						</form>
					</div>
					<?php
					contact_form();
					mo_custom_api_advertisement();
					?>
				</div>
			</div>
		</div>
	<?php
}

/**
 * Displays Custom SQL API List.
 */
function custom_api_wp_saved_sql_api() {
	if ( get_option( 'custom_api_wp_sql' ) ) {
		?>
		<div class="wrap mo_custom_api_page_layout_wrap" style="margin-left:18px">
					<div class="box-body" >
						<div class="row mo_custom_api_page_layout_row">
							<div class="col-md-8 mo_custom_api_page_layout" style="padding: 20px">
									<p style="margin: 0px 0px 10px 13px;" class="mo_custom_api_heading">Configured Custom SQL APIs:</p>
										<table id="tbldata" class="table table-hover" style="width: 75%">
											<thead>
												<tr class="header">
													<th style="display:none">RowId</th>
													<th>API NAME</th>
													<th>METHOD NAME</th>
													<th>ACTIONS</th>
												</tr>
											</thead>
											<tbody id="tbodyid">
												<?php
												if ( get_option( 'custom_api_wp_sql' ) ) {
													$list = get_option( 'custom_api_wp_sql' );

													foreach ( $list as $key => $value ) {
														echo '<tr>';
														echo " <td class='mo_custom_api_list_api_name'>" . esc_html( $key ) . '</td>';
														echo " <td class='mo_custom_api_list_api_name'>" . esc_html( $value['method'] ) . '</td>';
														echo "<td> <button class='mo_custom_api_ext_btn' onclick = 'custom_api_wp_edit_sql(this)'>Edit<i class='fas fa-user-edit'></i></button>&nbsp
                                                                        <button class='mo_custom_api_ext_btn' onclick ='custom_api_wp_delete_sql(this)'>Delete<i class='fas fa-user-edit'></i></button>&nbsp
                                                                        <button class='mo_custom_api_ext_btn' onclick ='custom_api_wp_view_sql(this)'>View<i class='fas fa-user-edit'></i></button>&nbsp
                                                                </td>";
													}
												}
												?>
											</tbody>

										</table>
										<p><strong>Notice: </strong><span style="color:red">*</span>With the current plan of the plugin you can create only one custom sql API, to create more, upgrade to <a href="admin.php?page=custom_api_wp_settings&action=license"><strong>Enterprise</strong></a> plan.</p>
									</div>
									<?php
									contact_form();
									mo_custom_api_advertisement();
									?>
								</div>
							</div>
						</div>
					<?php
	} else {
		?>
		<div class="wrap mo_custom_api_page_layout_wrap">
			<div class="box-body">
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="margin:0px 0px 0px 3px;padding:30px;padding-top: 20px;">
						<p class="mo_custom_api_heading">Configured Custom SQL APIs:</p>
						<hr>
						<h6 style="margin-bottom:18px;">You have not created any custom SQL API, to start <a  href="admin.php?page=custom_api_wp_settings&action=customsql"><button class="mo_custom_api_ext_btn">Click here</button></a></h6>
						<p><strong>Notice: </strong><span style="color:red">*</span>With the current plan of the plugin you can create only one custom sql API, to create more, upgrade to <a href="admin.php?page=custom_api_wp_settings&action=license"><strong>Enterprise</strong></a> plan.</p>
					</div>
			<?php
			contact_form();
			mo_custom_api_advertisement();
			?>
				</div>
			</div>
		</div>
			<?php
	}
}

/**
 * Displays External API Creation Form.
 */
function custom_api_wp_external_api_connection() {
	$external_api_configuration = get_option( 'custom_api_test_ExternalApiConfiguration' );

	$header_key   = '';
	$header_value = '';
	if ( isset( $external_api_configuration['ExternalHeaders'] ) && null !== $external_api_configuration['ExternalHeaders'] && $external_api_configuration['ExternalHeaders'] > 0 ) {

		$header_array = $external_api_configuration['ExternalHeaders'];
		$header_key   = array_key_first( $header_array );
		$header_value = $header_array[ $header_key ];
		if ( ! is_string( $header_key ) && 0 === (int) $header_key && false !== strpos( $header_value, ':' ) ) {
			$temp_header = array();
			foreach ( $header_array as $key_value ) {
				$key_val                    = explode( ':', $key_value, 2 );
				$temp_header[ $key_val[0] ] = $key_val[1];
			}
			$header_array = $temp_header;
			$header_key   = array_key_first( $header_array );
			$header_value = $header_array[ $header_key ];
		}
	}

	$body_array = array();
	$json_value = '';
	$bool       = isset( $external_api_configuration['ExternalApiPostFieldNew'] );
	if ( ! $bool ) {
		if ( isset( $external_api_configuration['ExternalApiPostField'] ) && null !== $external_api_configuration['ExternalApiPostField'] && 'x-www-form-urlencode' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
			$body_array = explode( '=', explode( '&', $external_api_configuration['ExternalApiPostField'] )[0] );
			$body_key   = $body_array[0];
			$body_value = $body_array[1];
		} elseif ( isset( $external_api_configuration['ExternalApiPostField'] ) && null !== $external_api_configuration['ExternalApiPostField'] && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
			$json_value = $external_api_configuration['ExternalApiPostField'];
		}
	} else {
		if ( isset( $external_api_configuration['ExternalApiPostFieldNew'] ) && null !== $external_api_configuration['ExternalApiPostFieldNew'] && 'x-www-form-urlencode' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
			$pos = strpos( $external_api_configuration['ExternalApiPostFieldNew'][0], ':' );
			if ( false !== $pos && substr_count( $external_api_configuration['ExternalApiPostFieldNew'][0], ':' ) > 1 ) {
				$bodyval    = substr_replace( $external_api_configuration['ExternalApiPostFieldNew'][0], '##mo_remove##', $pos, strlen( ':' ) );
				$body_array = explode( '##mo_remove##', $bodyval );
			} else {
				$body_array = explode( ':', $external_api_configuration['ExternalApiPostFieldNew'][0] );
			}

			$body_key   = $body_array[0];
			$body_value = $body_array[1];
		} elseif ( $external_api_configuration['ExternalApiPostFieldNew'] && null !== $external_api_configuration['ExternalApiPostFieldNew'] && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
			$json_value = $external_api_configuration['ExternalApiPostFieldNew'];
		}
	}

	?>
	<div class="wrap" style="margin-top:5px;">
		<div class="box-body">
			<div class="form-horizontal">
				<div class="box-body" style="margin-left: 16px;width:99%">
					<div class="row" style="padding: unset;">
						<div class="col-md-8 mo_custom_api_page_layout" style="padding:25px">
							<div style="margin-top:0px;">
								<p class="mo_custom_api_heading">External API: <span style="float:right;"> <a class="mo_custom_api_setup_guide_button" href="https://developers.miniorange.com/docs/rest-api-authentication/wordpress/developerhookscustom" target="_blank">Developer Docs</a>  <a class="mo_custom_api_setup_guide_button" href="https://plugins.miniorange.com/integrate-external-third-party-rest-api-endpoints-into-wordpress#step_2" target="_blank">Setup Guide</a> </span></p>
							</div>
							<hr style="margin-top:5px;">
							<form method="POST"><?php wp_nonce_field( 'CheckNonce', 'SubmitUser' ); ?>
								<div class=row>
									<div class=col-md-5>
										<label class="mo_custom_api_labels"> API Name</label>
									</div>
									<div class=col-md-6>
										<input class="mo_custom_api_custom_field" type="text" id="ExternalApiName"  name="ExternalApiName" value=<?php echo isset( $external_api_configuration['ExternalApiName'] ) ? esc_attr( $external_api_configuration['ExternalApiName'] ) : ''; ?> >
									</div>
								</div>
								<br>
								<div class=row>
									<div class=col-md-5>
										<label class="mo_custom_api_labels"> Select Method</label>
									</div>
									<div class=col-md-6>
										<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="MethodName" name="MethodName" >
											<option value="GET" 
											<?php
											if ( isset( $external_api_configuration['ExternalApiRequestType'] ) && 'GET' === $external_api_configuration['ExternalApiRequestType'] ) {
												echo 'Selected';}
											?>
												>GET</option>
											<option value="POST" 
											<?php
											if ( isset( $external_api_configuration['ExternalApiRequestType'] ) && 'POST' === $external_api_configuration['ExternalApiRequestType'] ) {
												echo 'Selected';}
											?>
												>POST</option>
											<option value="PUT" 
											<?php
											if ( isset( $external_api_configuration['ExternalApiRequestType'] ) && 'PUT' === $external_api_configuration['ExternalApiRequestType'] ) {
												echo 'Selected';}
											?>
												>PUT</option>
											<option value="DELETE" 
											<?php
											if ( isset( $external_api_configuration['ExternalApiRequestType'] ) && 'DELETE' === $external_api_configuration['ExternalApiRequestType'] ) {
												echo 'Selected';}
											?>
												>DELETE</option>
										</select>
									</div>
								</div>
								<br>
								<div class=row>
									<div class=col-md-5>
										<label class="mo_custom_api_labels"> External API</label>
									</div>

									<div class=col-md-6>
										<input type="text" id="ExternalApi" class="mo_custom_api_custom_field" name="ExternalApi"  placeholder="Enter External API" value=<?php echo isset( $external_api_configuration['ExternalEndpoint'] ) ? esc_attr( $external_api_configuration['ExternalEndpoint'] ) : ''; ?> >
									</div>
								</div>
								<br>
								<div class=row id="ExternalApiHeaders">
									<div class=col-md-2>
										<label class="mo_custom_api_labels"> Headers</label>
									</div>

									<div class=col-md-3 style="position: relative;right: 27px;">
										<input type="text" class="mo_custom_api_custom_field" id="ExternalHeaderKey"  name="ExternalHeaderKey" placeholder="Enter Key" value=<?php echo esc_attr( $header_key ); ?>>
									</div>

									<div class=col-md-4>
										<input type="text" id="ExternalHeaderValue" class="mo_custom_api_custom_field" name="ExternalHeaderValue" placeholder="Enter Value"  value=<?php echo "'" . esc_attr( $header_value ) . "'"; ?>>
									</div>

									<div class=col-md-3>
									<input type="button" style="width:50px;margin-left:0px;margin-top: 5px;" class="mo_custom_api_contact_us_submit_btn" value ="Add" onclick="add_header(' ',' ')">
									</div>
								</div>
								<br>
								<div class="row" id="ExternalApiBody">
									<div class=col-md-2 style="position: relative;right: 5px;">
										<label class="mo_custom_api_labels"> Request Body</label>
									</div>
									<div class=col-md-3 style="position: relative;right: 27px;">
										<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="RequestBodyType" name="RequestBodyType" onchange="RequestBodyTypeOnChange()" >
										<option value="x-www-form-urlencode" 
										<?php
										if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'x-www-form-urlencode' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
											echo 'Selected';}
										?>
											>x-www-form-urlencode</option>
										<option value="json" 
										<?php
										if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
											echo 'Selected';}
										?>
											>JSON</option>
										</select>
									</div>
									<div class=col-md-3 id = "DivRequestBodyKey" 
										<?php
										if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
											echo 'style="display: none; "';
										} else {
											echo 'style="display: block; "';
										}
										?>
									>
										<input type="text" id="RequestBodyKey" class="mo_custom_api_custom_field" name="RequestBodyKey" placeholder="Enter Key" value="<?php echo isset( $body_key ) ? esc_attr( $body_key ) : ''; ?>">
									</div>
									<div class=col-md-3 id = "DivRequestBodyValue" 
										<?php
										if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
											echo 'style="display: none; "';
										} else {
											echo 'style="display: block; "';}
										?>
									>
										<input type="text" id="RequestBodyValue" class="mo_custom_api_custom_field" name="RequestBodyValue" placeholder="Enter Value" value="<?php echo isset( $body_value ) ? esc_attr( $body_value ) : ''; ?>" >
									</div>
									<div class=col-md-1 id = "DivRequestBodyAddButton" 
										<?php
										if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
											echo 'style="display: none; "';
										} else {
											echo 'style="display: block; "';}
										?>
									>
										<input type="button" class="mo_custom_api_contact_us_submit_btn" style="margin-top: 5px;width:50px;" id="RequestBodyAddButton" onclick="add_request_body_param(' ',' ')" value="Add">
									</div>
									<div class=col-md-5 id="RequestBodyJsonTextArea" 
									<?php
									if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
										echo 'style="display: block; "';
									} else {
										echo 'style="display: none; "';}
									?>
									>
										<textarea id="RequestBodyJson" name="RequestBodyJson" style="height:123px;width:200px">
										<?php
										if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
											echo esc_html( $json_value );}
										?>
											</textarea>
									</div>
								</div>
								<br>
								<div class=row>
									<div class=col-md-5>
										<label class="mo_custom_api_labels">Select Dependent API</label>
									</div>
									<div class=col-md-6>
									<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="SelectedAPIsColumn" style="color:red;align-items:center" name="SelectedAPIscolumn">
									<option value="[ENTERPRISE]" selected disabled>[ENTERPRISE]</option>
									</select>
									</div>
								</div>
								<br>
								<div class="row">
								<div class=col-md-5>
										<label class="mo_custom_api_labels"> Response Data Type</label>
									</div>
									<div class=col-md-6>
									<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="responsebodytype" name="responsebodytype">
										<option value="xml" disabled="true" 
										<?php
										if ( isset( $external_api_configuration['ResponseBodyType'] ) && 'xml' === $external_api_configuration['ResponseBodyType'] ) {
											echo 'Selected';}
										?>
											disabled="true">XML  [ENTERPRISE]</option>
										<option value="json" 
										<?php
										if ( isset( $external_api_configuration['ResponseBodyType'] ) && 'json' === $external_api_configuration['ResponseBodyType'] ) {
											echo 'Selected';}
										?>
											>JSON</option>
									</select>
									</div>
								</div>
								<br>
								<div class=row>
									<div class=col-md-5>

										<label class="mo_custom_api_labels"> Select Response Fields</label>
									</div>
									<div class=col-md-6>
										<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="SelectedColumn" multiple="multiple" name="Selectedcolumn">


											<?php

											$data = get_option( 'ExternalApiResponseKey' );

											if ( false === $data ) {
												echo "<option value='custom_api_wp_getall' selected>Complete Response</option>";
											} elseif ( empty( $data ) ) {
												echo "<option value='custom_api_wp_getall' selected>Complete Response</option>";
												echo '<option >---Execute External API First---</option>';
											} else {
												$saved_external_api_configuration = get_option( 'custom_api_save_ExternalApiConfiguration' );

												$external_selected_response_key = array();
												if ( ! empty( $external_api_configuration ) ) {
													if ( ! empty( $saved_external_api_configuration[ $external_api_configuration['ExternalApiName'] ]['ExternalApiResponseDataKey'] ) ) {
														$external_selected_response_key = $saved_external_api_configuration[ $external_api_configuration['ExternalApiName'] ]['ExternalApiResponseDataKey'];
													}
												}
												echo "<option value='custom_api_wp_getall'>Complete Response</option>";
												foreach ( $data as $colu ) {

													echo "<option value='{" . esc_attr( $colu ) . "}'";

													echo isset( $external_selected_response_key ) && in_array( $colu, $external_selected_response_key, true ) ? "selected='selected'" : '';

													echo '>{' . esc_attr( $colu ) . '}</option>';
												}
											}
											?>

										</select>
									</div>
								</div>
								<hr style="margin-top:10px;">
								<input type="submit" value="Save Configuration" class="mo_custom_api_contact_us_submit_btn" name="ExternalApiConnectionSave" onclick="saveexternalapi()" > &nbsp;&nbsp;
								<input type="text" id="ExternalHeaderCount" name="ExternalHeaderCount" style="display: none;">
								<input type="text" id="ExternalResponseBodyCount" name="ExternalResponseBodyCount" style="display: none;">
								<input type="text" id="selected_column_all" name="selected_column_all" style="visibility: hidden;">
							</form>
						</div>
						<?php
						contact_form();
						mo_custom_api_advertisement();
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		function custom_api_test_execute(){
			var myWindow = window.open('<?php echo esc_url( site_url() ); ?>' + '/wp-admin/?customapiexternal=testexecute', "Test Attribute Configuration", "width=600, height=600");
		}
	</script>
	<input type="button" id="dynamic_external_ui" name="dynamic_external_ui" style="display:none;" onclick = '<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterOpen,Squiz.PHP.EmbeddedPhp.ContentBeforeOpen -- php code is embedded in between html code.
	if ( isset( $external_api_configuration['ExternalHeaders'] ) && ( isset( $external_api_configuration['ExternalApiPostFieldNew'] ) || isset( $external_api_configuration['ExternalApiPostField'] ) ) && isset( $external_api_configuration['ExternalApiBodyRequestType'] ) ) {
		echo 'add_dynamic_externalapi_ui(';
		echo esc_html( wp_json_encode( $external_api_configuration['ExternalHeaders'] ) );
		echo ',';
		if ( 'json' !== $external_api_configuration['ExternalApiBodyRequestType'] && $bool ) {
			echo esc_html( wp_json_encode( $external_api_configuration['ExternalApiPostFieldNew'] ) );
		} elseif ( 'json' !== $external_api_configuration['ExternalApiBodyRequestType'] && ! $bool ) {
			echo '"' . esc_html( $external_api_configuration['ExternalApiPostField'] ) . '"';
		} else {
			echo '"' . esc_html( $json_value ) . '"';
		}
		echo ',';
		echo '"' . esc_html( $external_api_configuration['ExternalApiBodyRequestType'] ) . '';
		echo ',';
		if ( empty( $external_api_configuration['ExternalApiPostFieldNew'] ) ) {
			echo '"0"';
		} else {
			echo '"1"';
		}
		echo ')';
	}
	?>
	'>

	<?php
}


/**
 * Displays External APIs List.
 */
function custom_api_wp_saved_external_api_connection() {
	update_option( 'custom_api_test_ExternalApiConfiguration', '' );

	if ( get_option( 'custom_api_save_ExternalApiConfiguration' ) ) {
		?>
		<div class="wrap mo_custom_api_page_layout_wrap" style="margin-left:18px">
			<div class="box-body" >
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="padding: 20px">
									<p style="margin: 0px 0px 10px 13px;" class="mo_custom_api_heading">Configured External APIs:</p>
										<table id="tbldata" class="table table-hover" style="width: 75%">
											<thead>
												<tr class="header">
													<th style="display:none">RowId</th>
													<th>API NAME</th>
													<th>METHOD NAME</th>
													<th>ACTIONS</th>
												</tr>
											</thead>
											<tbody id="tbodyid">
												<?php
												if ( get_option( 'custom_api_save_ExternalApiConfiguration' ) ) {
													$list = get_option( 'custom_api_save_ExternalApiConfiguration' );
													foreach ( $list as $key => $value ) {
														echo '<tr>';
														echo "<td class='mo_custom_api_list_api_name'>" . esc_html( $key ) . '</td>';
														echo " <td style='color:#36B37E;font-size:17px;font-weight:700'>" . esc_html( $value['ExternalApiRequestType'] ) . '</td>';
														echo "<td>  <button class='mo_custom_api_ext_btn' onclick = 'editexternalapi(this)'><b>Edit</b><i class='fas fa-user-edit'></i></button>&nbsp
                                                                        <button class='mo_custom_api_ext_btn' onclick ='deleteExternalapi(this)'><b>Delete</b><i class='fas fa-user-edit'></i></button>&nbsp
                                                                        
                                                                  </td>";
													}
												}
												?>
											</tbody>

										</table>
										<p><strong>Notice: </strong><span style="color:red">*</span>With the current plan of the plugin you can create only one external API connection, to create more, upgrade to <a href="admin.php?page=custom_api_wp_settings&action=license"><strong>Enterprise</strong></a> plan.</p>
									</div>
									<?php
									contact_form();
									mo_custom_api_advertisement();
									?>
								</div>
							</div>
						</div>
					<?php
	} else {
		?>
						<div class="wrap mo_custom_api_page_layout_wrap">
			<div class="box-body">
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="margin:0px 0px 0px 3px;padding:30px;padding-top: 20px;">
					<p class="mo_custom_api_heading">Configured External APIs:</p>
					<hr>
					<h6>You have not integrated any external API, to start integration <a href="admin.php?page=custom_api_wp_settings&action=externalapi"> <button class="mo_custom_api_ext_btn"> Click here</button></a></h6><br>
					<p><strong>Notice: </strong><span style="color:red">*</span>With the current plan of the plugin you can integrate only one External API, to integrate more upgrade to <a href="admin.php?page=custom_api_wp_settings&action=license"><strong>Enterprise</strong></a> plan.</p>
					</div>
		<?php
						contact_form();
						mo_custom_api_advertisement();
		?>
				</div>
			</div>
		</div>
					<?php
	}
}


/**
 * Displays External API Configuration.
 *
 * @param mixed $api1 Api name.
 */
function custom_api_wp_edit_externalapi( $api1 ) {
	$list                       = get_option( 'custom_api_save_ExternalApiConfiguration' );
	$external_api_configuration = $list[ $api1 ];

	$header_key   = '';
	$header_value = '';
	if ( isset( $external_api_configuration['ExternalHeaders'] ) && null !== $external_api_configuration['ExternalHeaders'] && $external_api_configuration['ExternalHeaders'] > 0 ) {

		$header_array = $external_api_configuration['ExternalHeaders'];
		$header_key   = array_key_first( $header_array );
		$header_value = $header_array[ $header_key ];
		if ( ! is_string( $header_key ) && 0 === (int) $header_key && false !== strpos( $header_value, ':' ) ) {
			$temp_header = array();
			foreach ( $header_array as $key_value ) {
				$key_val                    = explode( ':', $key_value, 2 );
				$temp_header[ $key_val[0] ] = $key_val[1];
			}
			$header_array = $temp_header;
			$header_key   = array_key_first( $header_array );
			$header_value = $header_array[ $header_key ];
		}
	}

	$bool       = isset( $external_api_configuration['ExternalApiPostFieldNew'] );
	$body_array = array();
	$json_value = '';
	if ( ! $bool ) {
		if ( isset( $external_api_configuration['ExternalApiPostField'] ) && null !== $external_api_configuration['ExternalApiPostField'] && 'x-www-form-urlencode' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
			$body_array = explode( '=', explode( '&', $external_api_configuration['ExternalApiPostField'] )[0] );
			$body_key   = $body_array[0];
			$body_value = $body_array[1];
		} elseif ( isset( $external_api_configuration['ExternalApiPostField'] ) && null !== $external_api_configuration['ExternalApiPostField'] && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
			$json_value = $external_api_configuration['ExternalApiPostField'];
		}
	} else {

		if ( isset( $external_api_configuration['ExternalApiPostFieldNew'] ) && null !== $external_api_configuration['ExternalApiPostFieldNew'] && 'x-www-form-urlencode' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
			$pos = strpos( $external_api_configuration['ExternalApiPostFieldNew'][0], ':' );
			if ( false !== $pos && substr_count( $external_api_configuration['ExternalApiPostFieldNew'][0], ':' ) > 1 ) {
				$bodyval    = substr_replace( $external_api_configuration['ExternalApiPostFieldNew'][0], '##mo_remove##', $pos, strlen( ':' ) );
				$body_array = explode( '##mo_remove##', $bodyval );
			} else {
				$body_array = explode( ':', $external_api_configuration['ExternalApiPostFieldNew'][0] );
			}
			$body_key   = $body_array[0];
			$body_value = $body_array[1];
		} elseif ( isset( $external_api_configuration['ExternalApiPostFieldNew'] ) && null !== $external_api_configuration['ExternalApiPostFieldNew'] && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
			$json_value = $external_api_configuration['ExternalApiPostFieldNew'];
		}
	}
	?>

		<div class="wrap" style="margin-top:5px;">
			<div class="form-horizontal">
				<div class="box-body" style="margin-left: 18px;width:99%">
					<div class="row" style="padding: unset;">
						<div class="col-md-8 mo_custom_api_page_layout" style="padding:25px">
							<div style="margin-top:0px;">
								<p class="mo_custom_api_heading">Update External API: <span style="float:right;"> <a class="mo_custom_api_setup_guide_button" href="https://developers.miniorange.com/docs/rest-api-authentication/wordpress/developerhookscustom" target="_blank">Developer Docs</a>  <a class="mo_custom_api_setup_guide_button" href="https://plugins.miniorange.com/integrate-external-third-party-rest-api-endpoints-into-wordpress#step_2" target="_blank">Setup Guide</a> </span></p> 
							</div>
							<hr style="margin-top:10px;">
							<form method="POST"><?php wp_nonce_field( 'CheckNonce', 'SubmitUser' ); ?>
								<div class=row>
									<div class=col-md-5>
										<label class="mo_custom_api_labels"> API Name</label>
									</div>
									<div class=col-md-6>
										<input type="text" id="ExternalApiName" class="mo_custom_api_custom_field" name="ExternalApiName" value=<?php echo isset( $external_api_configuration['ExternalApiName'] ) ? esc_html( $external_api_configuration['ExternalApiName'] ) : ''; ?> readonly>
									</div>
								</div>
								<br>
								<div class=row>
									<div class=col-md-5>
										<label class="mo_custom_api_labels"> Select Method</label>
									</div>
									<div class=col-md-6>
										<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="MethodName" name="MethodName" >
											<option value="GET" 
											<?php
											if ( isset( $external_api_configuration['ExternalApiRequestType'] ) && 'GET' === $external_api_configuration['ExternalApiRequestType'] ) {
												echo 'Selected';}
											?>
												>GET</option>
											<option value="POST" 
											<?php
											if ( isset( $external_api_configuration['ExternalApiRequestType'] ) && 'POST' === $external_api_configuration['ExternalApiRequestType'] ) {
												echo 'Selected';}
											?>
												>POST</option>
											<option value="PUT" 
											<?php
											if ( isset( $external_api_configuration['ExternalApiRequestType'] ) && 'PUT' === $external_api_configuration['ExternalApiRequestType'] ) {
												echo 'Selected';}
											?>
												>PUT</option>
											<option value="DELETE" 
											<?php
											if ( isset( $external_api_configuration['ExternalApiRequestType'] ) && 'DELETE' === $external_api_configuration['ExternalApiRequestType'] ) {
												echo 'Selected';}
											?>
												>DELETE</option>
										</select>
									</div>
								</div>
								<br>
								<div class=row>
									<div class=col-md-5>
										<label class="mo_custom_api_labels"> External API</label>
									</div>
									<div class=col-md-6>
										<input type="text" id="ExternalApi" class="mo_custom_api_custom_field" name="ExternalApi" placeholder="Enter External API" value=<?php echo isset( $external_api_configuration['ExternalEndpoint'] ) ? esc_html( $external_api_configuration['ExternalEndpoint'] ) : ''; ?> >
									</div>
								</div>
								<br>
								<div class=row id="ExternalApiHeaders">
									<div class=col-md-2>
										<label class="mo_custom_api_labels"> Headers</label>
									</div>
									<div class=col-md-3 style="position: relative;right: 27px;" >
										<input type="text" class="mo_custom_api_custom_field" id="ExternalHeaderKey"  name="ExternalHeaderKey" placeholder="Enter Key" value=<?php echo esc_attr( $header_key ); ?>>
									</div>
									<div class=col-md-4>
										<input type="text" class="mo_custom_api_custom_field" id="ExternalHeaderValue"  name="ExternalHeaderValue" placeholder="Enter Value"  value=<?php echo "'" . esc_attr( $header_value ) . "'"; ?>>
									</div>
									<div class=col-md-3>
									<input type="button" style="width:50px;margin-left:0px;margin-top: 5px;" class="mo_custom_api_contact_us_submit_btn" value ="Add" onclick="add_header(' ',' ')">
									</div>
								</div>
								<br>
								<div class=row id="ExternalApiBody" >
									<div class=col-md-2 style="position: relative;right: 5px;">
										<label class="mo_custom_api_labels"> Request Body</label>
									</div>
									<div class=col-md-3 style="position: relative;right: 27px;">
										<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="RequestBodyType" name="RequestBodyType" onchange="RequestBodyTypeOnChange()" >
										<option value="x-www-form-urlencode" 
										<?php
										if ( ! empty( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'x-www-form-urlencode' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
											echo 'Selected';}
										?>
										>x-www-form-urlencode</option>
										<option value="json" 
										<?php
										if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
											echo 'Selected';}
										?>
											>JSON</option>
										</select>
									</div>
									<div class=col-md-3 id = "DivRequestBodyKey" 
									<?php
									if ( ! empty( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
										echo 'style="display: none; "';
									} else {
										'style="display: block; "';}
									?>
									>
									<input type="text" id="RequestBodyKey" class="mo_custom_api_custom_field" name="RequestBodyKey" placeholder="Enter Key" value="<?php echo isset( $body_key ) ? esc_html( $body_key ) : ''; ?>">
									</div>
									<div class=col-md-3 id = "DivRequestBodyValue" 
									<?php
									if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
										echo 'style="display: none; "';
									} else {
										echo 'style="display: block; "';}
									?>
									>
									<input type="text" id="RequestBodyValue" class="mo_custom_api_custom_field" name="RequestBodyValue" placeholder="Enter Value" value="<?php echo isset( $body_value ) ? esc_html( $body_value ) : ''; ?>" >
									</div>
									<div class=col-md-1 id = "DivRequestBodyAddButton" 
									<?php
									if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
										echo 'style="display: none; "';
									} else {
										echo 'style="display: block; "';}
									?>
									>
									<input type="button" class="mo_custom_api_contact_us_submit_btn" style="margin-top: 5px;width:50px;" id="RequestBodyAddButton" onclick="add_request_body_param(' ',' ')" value="Add">
									</div>
									<div class=col-md-5 id="RequestBodyJsonTextArea" 
									<?php
									if ( ! empty( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
										echo 'style="display: block; "';
									} else {
										echo 'style="display: none; "';}
									?>
									>
										<textarea id="RequestBodyJson" name="RequestBodyJson" style="height:123px;width:200px">
										<?php
										if ( isset( $external_api_configuration['ExternalApiBodyRequestType'] ) && 'json' === $external_api_configuration['ExternalApiBodyRequestType'] ) {
											echo esc_html( $json_value );}
										?>
											</textarea>
									</div>
								</div>
								<br>
								<div class="row">
									<div class=col-md-5>
										<label class="mo_custom_api_labels"> Response Data Type</label>
									</div>
									<div class=col-md-6>
										<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="responsebodytype" name="responsebodytype">
											<option value="xml" disabled="true" 
											<?php
											if ( isset( $external_api_configuration['ResponseBodyType'] ) && 'xml' === $external_api_configuration['ResponseBodyType'] ) {
												echo 'Selected';}
											?>
												>XML  [ENTERPRISE]</option>
											<option value="json" 
											<?php
											if ( isset( $external_api_configuration['ResponseBodyType'] ) && 'json' === $external_api_configuration['ResponseBodyType'] ) {
												echo 'Selected';}
											?>
												>JSON</option>
										</select>
									</div>
								</div>
								<br>
								<div class=row>
									<div class=col-md-5>
										<label class="mo_custom_api_labels">Select Dependent API</label>
									</div>
									<div class=col-md-3>
									<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="SelectedAPIsColumn" style="color:red;align-items:center" name="SelectedAPIscolumn" readonly>
									<option value="[ENTERPRISE]" selected disabled>[ENTERPRISE]</option>
									</select>
									<label class="mo_custom_api_labels" ></label>
									</div>
									<div class=col-md-1>
									</div>
								</div>
								<br>
								<div class=row>
									<div class=col-md-5>
										<label class="mo_custom_api_labels"> Select Response Fields</label>
									</div>
									<div class=col-md-6>
										<select class="mo_custom_api_SelectColumn mo_custom_api_custom_field" id="SelectedColumn" multiple="multiple" name="Selectedcolumn">
											<?php

											$data = get_option( 'ExternalApiResponseKey' ) ? get_option( 'ExternalApiResponseKey' ) : array();
											$data = is_array( $data ) ? get_option( 'ExternalApiResponseKey' ) : array();

											if ( false === $data ) {
												echo "<option value='custom_api_wp_getall' selected>Complete Response</option>";
											} elseif ( empty( $data ) ) {
												echo "<option value='custom_api_wp_getall' selected>Complete Response</option>";
												echo '<option >---Execute External API First---</option>';
											} else {
												$list                             = get_option( 'custom_api_save_ExternalApiConfiguration' );
												$saved_external_api_configuration = $list[ $api1 ];

												$external_selected_response_key = array();
												if ( isset( $saved_external_api_configuration['ExternalApiResponseDataKey'] ) ) {
													$external_selected_response_key = $saved_external_api_configuration['ExternalApiResponseDataKey'];
												}
												echo "<option value='custom_api_wp_getall'";
												echo in_array( 'custom_api_wp_getall', $external_selected_response_key, true ) ? "selected='selected'" : '';
												echo ' >Complete Response</option>';
												foreach ( $data as $colu ) {
													echo "<option value='" . esc_attr( $colu ) . "'";

													echo in_array( $colu, $external_selected_response_key, true ) ? "selected='selected'" : '';

													echo '>' . esc_attr( $colu ) . '</option>';
												}
											}
											?>
										</select>
									</div>
								</div>
								<hr style="margin-top:10px;">
								<input type="submit" value="Save Configuration" class="mo_custom_api_contact_us_submit_btn" name="ExternalApiConnectionSave" onclick="saveexternalapi()" > &nbsp;&nbsp;
								<input type="submit" value="Execute" class="mo_custom_api_contact_us_submit_btn" name="ExternalApiConnection" onclick="console.log(document.getElementById('ExternalResponseBodyCount').value)" >
								<input type="text" id="ExternalHeaderCount" name="ExternalHeaderCount" style="display: none;">
								<input type="text" id="ExternalResponseBodyCount" name="ExternalResponseBodyCount" style="display: none;">
								<input type="text" id="selected_column_all" name="selected_column_all" style="visibility: hidden;">
							</form>
						</div>
						<?php
						contact_form();
						mo_custom_api_advertisement();
						?>
					</div>
				</div>
			</div>
		</div>
	<script>
	function custom_api_test_execute(){
		var myWindow = window.open('<?php echo esc_url( site_url() ); ?>' + '/wp-admin/?customapiexternal=testexecute', "Test Attribute Configuration", "width=600, height=1000");
	}
	</script>

	<input type="button" id="dynamic_external_ui" name="dynamic_external_ui" style="display:none;" onclick = '<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterOpen,Squiz.PHP.EmbeddedPhp.ContentBeforeOpen -- php code is embedded in between html code.
	if ( isset( $external_api_configuration['ExternalHeaders'] ) && ( isset( $external_api_configuration['ExternalApiPostFieldNew'] ) || isset( $external_api_configuration['ExternalApiPostField'] ) ) && isset( $external_api_configuration['ExternalApiBodyRequestType'] ) ) {
		echo 'add_dynamic_externalapi_ui(';
		echo esc_html( wp_json_encode( $header_array ) );
		echo ',';
		if ( 'json' !== $external_api_configuration['ExternalApiBodyRequestType'] && isset( $external_api_configuration['ExternalApiPostFieldNew'] ) ) {
			echo esc_html( wp_json_encode( $external_api_configuration['ExternalApiPostFieldNew'] ) );
		} elseif ( 'json' !== $external_api_configuration['ExternalApiBodyRequestType'] && ! isset( $external_api_configuration['ExternalApiPostFieldNew'] ) ) {
			echo '"' . esc_html( $external_api_configuration['ExternalApiPostField'] ) . '"';
		} else {
			if ( null !== json_decode( $json_value ) ) {
				echo esc_html( $json_value );
			} else {
				echo '"' . esc_html( $json_value ) . '"';
			}
		}
		echo ',';
		echo '"' . esc_html( $external_api_configuration['ExternalApiBodyRequestType'] ) . '"';
		echo ',';
		if ( empty( $external_api_configuration['ExternalApiPostFieldNew'] ) ) {
			echo '"0"';
		} else {
			echo '"1"';
		}
		echo ')';
	}
	?>
	'>

	<?php
}


/**
 * Displays Integration List Page.
 */
function custom_api_integration_page() {
	?>
		<div class="wrap mo_custom_api_page_layout_wrap" style="margin-left:18px">
			<div class="box-body">
				<div class="row mo_custom_api_page_layout_row">
					<div class="col-md-8 mo_custom_api_page_layout" style="padding:30px;padding-top: 15px;background-color: #f5f5f5;">
						<p class="mo_custom_api_heading">Check out all our integration and use cases-</p>
						<hr style="height: 5px;background: #1f3668;margin-top: 9px;border-radius: 30px;">

						<div class="mo_custom_api_intg_cards">
							<h4 class="mo_custom_api_intg_head">Woocommerce Sync products from External API.</h4>
							<p class="mo_custom_api_intg_para">If you have a Woocommerce store having a lot of products and want to sync/import products from external inventory, supplier via APIs. Then this can be acheived using this plugin along with Woocommerce sync add-on.</p>
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/woo-3.png' ); ?>" class="mo_cusotm_api_intg_logo" alt=" Image">
							<span class="mo_custom_api_intg_rect"></span>
							<span class="mo_custom_api_intg_tri"></span>
							<a class="mo_custom_api_intg_readmore" href="https://plugins.miniorange.com/woocommerce-api-product-sync-with-woocommerce-rest-apis" target="_blank">Learn More</a>
						</div>				
						<div class="mo_custom_api_intg_cards">
							<h4 class="mo_custom_api_intg_head">Integrate external API in WordPress.</h4>
							<p class="mo_custom_api_intg_para">If you are looking to connect your WordPress site with External APIs in order to fetch data from there and display in WordPress or want to use that data further or want to update data from WordPress to third-party app via thier APIs, then it can be acheived with our plugin's Connect to External API feature. </p>
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/ex3.png' ); ?>" class="mo_cusotm_api_intg_logo" alt=" Image">
							<span class="mo_custom_api_intg_rect"></span>
							<span class="mo_custom_api_intg_tri"></span>
							<a class="mo_custom_api_intg_readmore" href="https://plugins.miniorange.com/integrate-external-third-party-rest-api-endpoints-into-wordpress" target="_blank">Learn More</a>
						</div>

						<div class="mo_custom_api_intg_cards">
							<h4 class="mo_custom_api_intg_head">Create API with custom SQL query.</h4>
							<p class="mo_custom_api_intg_para">If you want to create the custom API endpoints in WordPress using your own complex custom SQL queries which will provide you with full control over what operations you want to perform. Then, Custom SQL API feature is what you need.</p>
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/sql-1.jpg' ); ?>" class="mo_cusotm_api_intg_logo" alt=" Image">
							<span class="mo_custom_api_intg_rect"></span>
							<span class="mo_custom_api_intg_tri"></span>
							<a class="mo_custom_api_intg_readmore" href="https://plugins.miniorange.com/integrate-external-third-party-rest-api-endpoints-into-wordpress#step_3" target="_blank">Learn More</a>
						</div>

						<div class="mo_custom_api_intg_cards">
							<h4 class="mo_custom_api_intg_head">Zoho Webhooks and API integration in WordPress.</h4>
							<p class="mo_custom_api_intg_para">If you are using Zoho product like Zoho subscription, CRM, Campaign ,Creator, Inventory etc and wants to connect it with your WordPress site for purposes of real-time data sync via Zoho Webhooks and Zoho REST APIs. Then we can  provide you with the customized solution for that. For more information contact us at apisupport@xecurify.com.</p>
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/zoho.png' ); ?>" class="mo_cusotm_api_intg_logo" alt=" Image">
							<span class="mo_custom_api_intg_rect"></span>
							<span class="mo_custom_api_intg_tri"></span>
							<!-- <a class="mo_custom_api_intg_readmore" href="https://plugins.miniorange.com/wordpress-user-provisioning" target="_blank">Learn More</a> -->
						</div>

						<div class="mo_custom_api_intg_cards">
							<h4 class="mo_custom_api_intg_head">AliDropship Sync products from External API.</h4>
							<p class="mo_custom_api_intg_para"> If you have Alidropship products store having a lot of products and want to sync/import products from external inventory, supplier via APIs. Then this can be acheived using this plugin along with Alidropship sync add-on.</p>
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/alidropship.png' ); ?>" class="mo_cusotm_api_intg_logo" alt=" Image">
							<span class="mo_custom_api_intg_rect"></span>
							<span class="mo_custom_api_intg_tri"></span>
							<!-- <a class="mo_custom_api_intg_readmore" href="https://plugins.miniorange.com/wordpress-user-provisioning" target="_blank">Learn More</a> -->
						</div>

						<div class="mo_custom_api_intg_cards">
							<h4 class="mo_custom_api_intg_head">Connect External API on Woocommerce events.</h4>
							<p class="mo_custom_api_intg_para">If you have a Woocommerce store and want to call the external/3rd-party provider APIs on various Woocommerce events like product purchase, order created, order status update, order whishlisted, user registered etc, then using the plugin's Connect to External API feature and developer hooks, this can be integrated.</p>
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/woo-3.png' ); ?>" class="mo_cusotm_api_intg_logo" alt=" Image">
							<span class="mo_custom_api_intg_rect"></span>
							<span class="mo_custom_api_intg_tri"></span>
							<a class="mo_custom_api_intg_readmore" href="https://plugins.miniorange.com/woocommerce-events-integration-on-webhooks" target="_blank">Learn More</a>
						</div>
						<div class="mo_custom_api_intg_cards">
							<h4 class="mo_custom_api_intg_head">Connect Google sheet to Woocommerce.</h4>
							<p class="mo_custom_api_intg_para">If you are looking to connect your WordPress or Woocommerce with Google sheet such that data can sync between these platforms on real-time events, then the plugin can be extened to acheive that.</p>
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/Google_Sheet.png' ); ?>" class="mo_cusotm_api_intg_logo" alt=" Image">
							<span class="mo_custom_api_intg_rect"></span>
							<span class="mo_custom_api_intg_tri"></span>
							<!-- <a class="mo_custom_api_intg_readmore" href="https://plugins.miniorange.com/woocommerce-api-product-sync-with-woocommerce-rest-apis" target="_blank">Learn More</a> -->
						</div>
						<div class="mo_custom_api_intg_cards" style="width:97.35%;height:75px;">
							<p style="font-size: 15px;font-weight: 500;padding:26px;">If you want custom features in the plugin, just drop an email at <a href="mailto:apisupport@xecurify.com?subject=Custom API for WP - Custom Requirement">apisupport@xecurify.com</a>.</p>  
						</div>
					</div>
					<?php
					contact_form();
					mo_custom_api_advertisement();
					?>
				</div>
			</div>
		</div>
	<?php
}

