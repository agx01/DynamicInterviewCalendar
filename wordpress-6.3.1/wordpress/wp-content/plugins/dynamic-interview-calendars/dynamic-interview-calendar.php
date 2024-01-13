<?php
/**
 * Plugin Name: Dynamic Interview Calendars
 * Description: Generate the Interview calendars from Google sheets
 * Author: Arijit Ganguly
 * Author URI: arijit.ganguly@jerseystem.org
 * Version: 2.0.0
 * Text Domain: dynamic-interview-calendars
 * Change Log: Updated the script so that the Google sheets is only called when the page is loaded.
 */

 if(!defined('ABSPATH')){
    exit;
 }

 define( 'PLUGIN_WITH_CLASSES__FILE__', __FILE__ );
 include_once('sql-interface.php');

 class DynamicInterviewCalendar{

    public function __construct(){

        //Create the required tables in as part of the plugin activation
        register_activation_hook( PLUGIN_WITH_CLASSES__FILE__, array($this, 'plugin_create_db'));

        // //Load the plugin
        // add_action('init', array($this, 'initialize_plugin'));

        //Add assets (js, css, stc)
        add_action('wp_enqueue_scripts', array($this, 'load_assets'));

        //Load javascript
        add_action('wp_footer', array($this, 'load_scripts'));

        //Add shortcode
        add_shortcode( 'dynamic-interview-cals', array($this, 'load_shortcode'));

        //Add shortcode2
        add_shortcode( 'iframe-links', array($this, 'load_links_table'));

        //Delete the table on deactivation
        register_uninstall_hook(PLUGIN_WITH_CLASSES__FILE__,'DynamicInterviewCalendar::plugin_remove_db');

    }

    public function plugin_create_db(){

        $sql_interafce = new SQLInterface;

        $sql_interafce->create_required_tables();
    }

    public function load_assets(){

        wp_enqueue_style(
            'dynamic-interview-calendar',
            plugin_dir_url(__FILE__). 'css/dynamic-interview-calendars.css',
            array(),
            1,
            'all' 
        );

        // wp_enqueue_scripts(
        //     'dynamic-interview-calendar',
        //     plugin_dir_url(__FILE__).'js/dynamic-interview-calendars.js',
        //     array(),
        //     1,
        //     true
        // );

    }

    public function load_scripts(){
    
    }

    private function get_links_from_sheet(){
        $sql_interface = new SQLInterface;
        $sheetid = "1w7KtDoPt8adBIqRK8g1ua5aOWhV7_zMHJ2hGMvXt8uE";
        $sheetname = "interviewsheet";
        $header = "true";
        $request = "https://script.google.com/macros/s/AKfycbyHY8u3kmsQFc6xgaP7saJsZcQ6VyP4hVyDlmrXFikwsUOr1hJ5FZKTSJp0PGx6ogJ4Mg/exec?".
                "spreadsheetid=".$sheetid.
                "&sheetname=".$sheetname.
                "&header=".$header."";

        $response = wp_remote_get($request);
        if (is_array($response) && !is_wp_error( $response )){
            $data = json_decode($response['body'], true);
            $data = $data['data'];

            $sql_interface->insert_records_appt($data);
        }
        else{
            echo 'Error fetching data from Google Apps Script web app.';
            echo json_decode(wp_remote_retrieve_body( $response ), true);
        }
    }

    public function get_data_from_sheets(){

        $sql_interface = new SQLInterface;
        $sheetid = "1w7KtDoPt8adBIqRK8g1ua5aOWhV7_zMHJ2hGMvXt8uE";
        $sheetname = "Nabil";
        $header = "true";
        $request = "https://script.google.com/macros/s/AKfycbyHY8u3kmsQFc6xgaP7saJsZcQ6VyP4hVyDlmrXFikwsUOr1hJ5FZKTSJp0PGx6ogJ4Mg/exec?".
                "spreadsheetid=".$sheetid.
                "&sheetname=".$sheetname.
                "&header=".$header."";

        $response = wp_remote_get($request);

        if (is_array($response) && !is_wp_error( $response )){
            $data = json_decode($response['body'], true);
            $data = $data['data'];

            $sql_interface->insert_records_iframes($data);
        }
        else{
            echo 'Error fetching data from Google Apps Script web app.';
            echo json_decode(wp_remote_retrieve_body( $response ), true);
        }

    }

    public function load_shortcode(){

        // $this->get_data_from_sheets();

        $sql_interface = new SQLInterface;

        if(!isset($_GET['expression']) && empty($_GET['expression'])){
            $results = $sql_interface->retrieve_links();
        }
        else{
            $results = $sql_interface->retrieve_links($_GET['expression']);
        }
        
        $num_cols = 2;

        $col_counter = 0;

        $html_string = "";

        $table_start = "<table class='table' border='1px solid black'>";
        $table_end = "</table>";
        $row_start = "<tr>";
        $row_end = "</tr>";

        
        $num_cells = count($results);

        $html_string = $html_string.$table_start;
        $record_ctr = 0;

        for($i = 0; $i < $num_cells/$num_cols; $i++){
            $html_string = $html_string.$row_start;
            for($col_counter=0; $col_counter < $num_cols; $col_counter++){
                
                $html_string = $html_string."<td><iframe src=" . $results[$record_ctr]->apptlink . " style='border-width:0; width:800px; height: 600px;'></iframe></td>";
                $record_ctr ++;
            }
            $html_string = $html_string.$row_end;
        }
        $html_string = $html_string . $table_end;

        return $html_string;
    }


    public function load_links_table(){
        $this->get_data_from_sheets();

        $sql_interface = new SQLInterface;

        if(!isset($_GET['expression']) && empty($_GET['expression'])){
            $results = $sql_interface->retrieve_links();
        }
        else{
            $results = $sql_interface->retrieve_links($_GET['expression']);
        }
        
        $num_cols = 2;

        $col_counter = 0;

        $html_string = "";

        $table_start = "<table class='table' border='1px solid black'>";
        $table_end = "</table>";
        $row_start = "<tr>";
        $row_end = "</tr>";

        
        $num_cells = count($results);

        $html_string = $html_string.$table_start;
        $record_ctr = 0;

        for($i = 0; $i < $num_cells/$num_cols; $i++){
            $html_string = $html_string.$row_start;
            for($col_counter=0; $col_counter < $num_cols; $col_counter++){
                
                $html_string = $html_string."<td><a href='".$results[$record_ctr]->iframe_link."'>".$results[$record_ctr]->iframe_link."</a><iframe src=" . $results[$record_ctr]->iframe_link . " style='border-width:0; width:800px; height: 600px;'></iframe></td>";
                $record_ctr ++;
            }
            $html_string = $html_string.$row_end;
        }
        $html_string = $html_string . $table_end;

        return $html_string;
    }

    public function filter_valid_links($results){

        foreach($results as $record){
            $response = wp_remote_get( $record->apptlink );
            if(gettype(wp_remote_retrieve_response_code($response)) === gettype("")){
                echo "Invalid link";
            }
        }

        return $results;
    }

    public static function plugin_remove_db(){
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $db_table_name = $wpdb->prefix.'google_appts';
    
        //Check to see if the table already exists
        if($wpdb->get_var("show tables like '$db_table_name'") == $db_table_name){
            
            $sql = "DROP TABLE $db_table_name $charset_collate;";
    
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta( $sql);
            // add_option('test_db_version', $test_db_version);
        }
    }
 }

 new DynamicInterviewCalendar;

?>