<?php
/**
 * Plugin Name: Nepse Stock Web Scraper
 * Plugin URI:
 * Description: This plugin shows live nepse data.
 * Version: 1.0.0
 * Author: Dharma Raj Thapa
 * Author URI:
 *
 */
define('__ROOT__', dirname(__FILE__));
// require_once(__ROOT__.'/config.php');
if(!class_exists('NpLiveNepse'))
{
    class NpLiveNepse
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
        	add_action('admin_init', array(&$this, 'admin_init'));
        	add_action('admin_menu', array(&$this, 'add_menu'));
			// add_action('admin_enqueue_scripts',array(&$this,'np_plugin_scripts'));
            require_once (__ROOT__.'/inc/simplehtmldom/simple_html_dom.php');
			require_once (__ROOT__.'/inc/shortcodes.php');
			// echo "<pre>";
			// print_r(plugin_dir_url( __FILE__ ));
			// die;



            // register actions
        } // END public function __construct

        public function np_plugin_scripts(){
	    wp_register_script('np_plugin_script_js',plugin_dir_url( __FILE__ ).'js/bootstrap.js');
	    wp_register_script('np_plugin_script_min_js',plugin_dir_url( __FILE__ ).'js/bootstrap.min.js');
	    wp_register_script('np_plugin_script_jquery_js',plugin_dir_url( __FILE__ ).'js/jquery.js');
         wp_enqueue_style('plugin_bootstrap-theme.min', plugin_dir_url( __FILE__ ).'css/bootstrap.min.css', array(), '0.2');
         wp_enqueue_style('plugin_font-awesome.min', plugin_dir_url( __FILE__ ).'css/font-awesome/font-awesome.min.css', array(), '0.2');
	    wp_enqueue_script('np_plugin_script_js');
	    wp_enqueue_script('np_plugin_script_jquery_js');
	    wp_enqueue_script('np_plugin_script_min_js');
		}

    	public function admin_init()
    	{

    	}
    	public function add_menu()
    	{
    		add_options_page('Nepse Live Setting', 'Live Nepse', 'manage_options', 'NpLiveNepse', array(&$this, 'plugin_settings_page'));
		} // END public function add_menu()

    	public function plugin_settings_page()
		{
		    if(!current_user_can('manage_options'))
		    {
		        wp_die(__('You do not have sufficient permissions to access this page.'));
		    }

            // echo date('i');
            echo "<h3> Paste this shortcode <i>[get_live_stock_marquee]</i> to get live data like this</h3>";
                    echo do_shortcode('[get_live_stock_marquee]' );


            echo "<h3> Paste this shortcode <i> [get_live_stock_table] </i> to get live data like this in table</h3>";


                    echo do_shortcode('[get_live_stock_table]' );



		}
        public function get_live_nepse()
        {
            ini_set( 'default_socket_timeout', 3000 );
            set_time_limit( 3000 );

        //$this->load->database();
        $CRAWL_URL='http://nepalstock.com/stocklive/';
        $html = new simple_html_dom();

        $html->load_file($CRAWL_URL);
        $table = $html->find('table',0);

        $trows = $table->find("tr");
        //container for heading
        $heading = array();
        //container for the data rows
        $rowData = array();
        //determine the heading row

        $key_map = array(
          1 => 'Symbol',
          2 => 'LTP',
          3 => 'LTV',
          4 => 'PointChange',
          5 => 'PercentChange',
          6 => 'Open',
          9 => 'Volume',
        );

        $data_key = array(1,2,3,4,5,6,9);



        if( $trows[0]->find('th')) {
            $heading_row = $trows[0]->find('th');
        }else {
            $heading_row = $trows[0]->find('td');
        }

        //set the heading
        foreach ($data_key as $data_heading_key) {
            $heading[$data_heading_key] = $key_map[$data_heading_key];//$heading_row[$data_heading_key]->plaintext;
        }

        //set the data rows
        foreach($trows as $key => $row) {
            if($key == 0) continue;
            // initialize array to store the cell data from each row
            $flight = array();

            $data_row = $row->find('td');

            foreach ($data_key as $data_heading_key) {
                $flight[$data_heading_key] = $data_row[$data_heading_key]->plaintext;
            }

            $rowData[] = $flight;
        }

            $live_data= array();

            foreach ($rowData as $row => $tr) {
                foreach ($tr as $key => $td) {
                    $live_data[$row][$heading[$key]] = $td;
                }
            }

            /**
             * Caption
             */
            $tableWrapper =  $table->parent();
            $captionTemp = str_get_html($tableWrapper);
            function remove_td($element) {
                if ($element->tag=='thead' || $element->tag=='tr' ||  $element->tag=='td' ) {
                    $element->outertext = '';
                }
            }
            $captionTemp->set_callback('remove_td');
                    // $caption = new simple_html_dom($captionTemp);
            // $caption = str_get_html($captionTemp);
            // $caption = $caption->plaintext;
            $caption = trim(strip_tags($captionTemp));



            /**
             * NEPSE
             */
            $NEPSE = array();
            $nepseWrapper = $html->find('#market-watch',0);
            $NEPSE['Date'] =  $nepseWrapper->find('.panel-heading',0)->innertext;
            $NEPSE['CurrentIndex'] =  trim($nepseWrapper->find('.current-index',0)->innertext);
            $NEPSE['PointIndex'] =  trim($nepseWrapper->find('.point-change',0)->innertext);
            $NEPSE['PercentIndex'] =  trim($nepseWrapper->find('.percent-change',0)->innertext);

            $final_data = array(
                'Caption' => $caption,
                'NEPSE' => $NEPSE,
                'LiveTrading' => $live_data,

            );
               if (!empty($final_data['LiveTrading'])) {
                $live_data = json_encode($final_data);
                update_option( 'live_nepse', $live_data );
               }


        }

         // EN
        /**
         * Activate the plugin
         */
        public static function activate()
        {
             if (!get_option('live_nepse')) {
                    add_option('live_nepse', $live_data);
                }

                wp_schedule_event( time(), '1minute', 'set_cron_job' );
            // Do nothing
        } // END public static function activate


        /**
         * Deactivate the plugin
         */
        public static function deactivate()
        {
            wp_clear_scheduled_hook('set_cron_job');
            // Do nothing
        } // END public static function deactivate

        public function np_live_nepse_fetch()
        {
            $time = date('H');
            $day = date('D');
            if ($day != 'Fri' && $day != 'Sat' && $time >= 12 && $time < 15 ) {
                $this->get_live_nepse();
            }
        }

       public function add_minutely_cron_schedule( $schedules ) {
            $schedules['1minute'] = array(
                'interval' => 1, //  seconds
                'display'  => __( 'Once per 5 minute' ),
            );

            return $schedules;
        }

        public function schedule_my_cron(){
            wp_schedule_event(time(), '1minute', 'set_cron_job');
        }


    } // END class NpLiveNepse

    if(class_exists('NpLiveNepse'))
	{
	    // Installation and uninstallation hooks
	    register_activation_hook(__FILE__, array('NpLiveNepse', 'activate'));
	    register_deactivation_hook(__FILE__, array('NpLiveNepse', 'deactivate'));

         date_default_timezone_set("Asia/Kathmandu");

	    // instantiate the plugin class
        $np_live_nepse = new NpLiveNepse();

        add_filter( 'cron_schedules',  array($np_live_nepse,'add_minutely_cron_schedule' ) );

        add_action('set_cron_job', array($np_live_nepse,'np_live_nepse_fetch') );

        if(!wp_get_schedule('set_cron_job')){

            add_action('init', array($np_live_nepse,'schedule_my_cron'));
        }



	}
} // END if(!class_exists('NpLiveNepse'))
