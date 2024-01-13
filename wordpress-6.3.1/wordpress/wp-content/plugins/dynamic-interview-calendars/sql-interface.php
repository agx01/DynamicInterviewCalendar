<?php

// path of the log file where errors need to be logged 
$log_file = "my-errors.log"; 

// setting error logging to be active 
ini_set("log_errors", TRUE); 

// setting the logging file in php.ini 
ini_set('error_log', $log_file);

if(!defined('ABSPATH')){
    exit;
 }

class SQLInterface{

    private $wpdb;
    private $appts_table_name;
    private $iframe_links_table;
    private $metadata_table_name;
    private $charset_collate;
    private $log_file;

    public function __construct(){

        //Setting up the global wpdb variables
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->charset_collate = $this->wpdb->get_charset_collate();
        $this->log_file = "my-errors.log";

        //Setting up the names for the tables required
        $this->appts_table_name = $this->wpdb->prefix.'google_appts';
        $this->iframe_links_table = $this->wpdb->prefix.'iframe_tables';
        $this->metadata_table_name = $this->wpdb->prefix.'appts_tables_meta';
        
    }

    public function get_iframes_tablename(){
        return $this->iframe_links_table;
    }

    public function get_appts_tablename(){
        return $this->appts_table_name;
    }

    public function create_required_tables(){

        //Check to see if the table already exists
        if($this->wpdb->get_var("show tables like '$this->appts_table_name'") != $this->appts_table_name){
            $this->create_appts_table();
        }

        //Check to see if the table already exists
        if($this->wpdb->get_var("show tables like '$this->iframe_links_table'") != $this->iframe_links_table){
            $this->create_links_table();
        }

        //Check to see if the table already exists
        if($this->wpdb->get_var("show tables like '$this->metadata_table_name'") != $this->metadata_table_name){
            $this->create_metadata_table();
        }

    }

    private function create_appts_table(){
           
        $sql = "CREATE TABLE $this->appts_table_name(
            department varchar(5),
            firstname varchar(20),
            lastname varchar(20),
            email varchar(20),
            apptlink TEXT,
            valid_link Boolean,
            PRIMARY KEY (firstname, lastname, email)
            ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta( $sql);

        
   }

   private function create_links_table(){
        $sql = "CREATE TABLE $this->iframe_links_table(
            expression varchar(20),
            category varchar(20),
            iframe_link TEXT,
            PRIMARY KEY (expression)
            ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta( $sql);
   }

   private function create_metadata_table(){
           
        $sql = "CREATE TABLE $this->metadata_table_name(
            table_name varchar(20),
            last_updated DATETIME,
            num_records INT,
            PRIMARY KEY (table_name)
            ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql);
   }
   

   public function retrieve_links($expression=null){
        $results = NULL;

        if(is_null($expression)){
            $results = $this->wpdb->get_results("SELECT * FROM $this->iframe_links_table");
        }
        else{
            $results = $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "SELECT * FROM $this->iframe_links_table expression REGEXP '%s'",
                    $expression
                )
            );
        }
        
        return $results;
   }

   public function  retrieve_appt_links($department=NULL){
        $results = NULL;

        if(is_null($department)){
            $results = $this->wpdb->get_results("SELECT * FROM $this->appts_table_name");
        }
        else{
            $results = $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "SELECT * FROM $this->appts_table_name WHERE department=%s",
                    $department
                )
            );
        }

        return $results;

   }

   public function insert_records_iframes($data){
        foreach($data as $item){
            try{
                $this->wpdb->insert(
                    $this->iframe_links_table,
                    array(
                        'expression' => $item['Expression'],
                        'category' => $item['Category'],
                        'iframe_link' => $item['Information']
                    )
                );
            }
            catch(Exception $e){
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        }
   }

   public function insert_records_appt($data){
        foreach($data as $item){
            try{
                $this->wpdb->insert(
                    $this->appts_table_name,
                    array(
                        'department' => $item['department'],
                        'firstname' => $item['firstname'],
                        'lastname' => $item['lastname'],
                        'apptlink' => $item['appointmentlink']
                    )
                    );
            }
            catch(Exception $e){
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        }
   }

   private function check_num_records($table_name){

        $results = $this->wpdb->get_results("SELECT COUNT(*) FROM $table_name");
        echo "<script>alert('".$this->wpdb->last_error."')</script>";
        // logging error message to given log file 
        error_log($this->wpdb->last_error, 3, $this->log_file); 
   }

}

?>