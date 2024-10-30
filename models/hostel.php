<?php
// main model containing general config and UI functions
class WPHostel {
   static function install($update = false) {
   	global $wpdb;	
   	$wpdb -> show_errors();
   	
   	if(!$update) self::init();
	  
	   // rooms
   	if($wpdb->get_var("SHOW TABLES LIKE '".WPHOSTEL_ROOMS."'") != WPHOSTEL_ROOMS) {        
			$sql = "CREATE TABLE `" . WPHOSTEL_ROOMS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `title` VARCHAR(100) NOT NULL DEFAULT 'room',
				  `rtype` VARCHAR(100) NOT NULL DEFAULT 'dorm',
				  `beds` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				  `bathroom` VARCHAR(100) NOT NULL DEFAULT 'standard' /* ensuite, shared bathroom, etc goes here */,
				  `price` DECIMAL(8,2) NOT NULL DEFAULT '0.00',
				  `description` TEXT
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
	  	
	  	// bookings - will also contain unavailable dates which admin will store as bookings too			
		if($wpdb->get_var("SHOW TABLES LIKE '".WPHOSTEL_BOOKINGS."'") != WPHOSTEL_BOOKINGS) {        
				$sql = "CREATE TABLE `" . WPHOSTEL_BOOKINGS . "` (
					  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					  `room_id` INT UNSIGNED NOT NULL DEFAULT 0,
					  `from_date` DATE NOT NULL DEFAULT '2000-01-01',
					  `to_date` DATE NOT NULL DEFAULT '2000-01-01',
					  `beds` TINYINT UNSIGNED NOT NULL DEFAULT 1,
					  `amount_paid` DECIMAL(8,2) NOT NULL DEFAULT '0.00',
					  `amount_due` DECIMAL(8,2) NOT NULL DEFAULT '0.00',
					  `is_static` TINYINT UNSIGNED NOT NULL DEFAULT 0 /* When 1 means admin just disabled these dates */,
					  `contact_name` VARCHAR(255) NOT NULL DEFAULT '',
					  `contact_email` VARCHAR(255) NOT NULL DEFAULT '',
					  `contact_phone` VARCHAR(255) NOT NULL DEFAULT '',
					  `contact_type` VARCHAR(255) NOT NULL DEFAULT '' /* how many people & male/female/couple/mixed */,
					  `timestamp` DATETIME /* When the reservation is made */,
					  `status` VARCHAR(100) NOT NULL DEFAULT 'active' /* pending, active or cancelled */					  
					) DEFAULT CHARSET=utf8;";
				
				$wpdb->query($sql);
		  }
		  
		  // payment records	  
	  	if($wpdb->get_var("SHOW TABLES LIKE '".WPHOSTEL_PAYMENTS."'") != WPHOSTEL_PAYMENTS) {        
			$sql = "CREATE TABLE `" . WPHOSTEL_PAYMENTS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `booking_id` INT UNSIGNED NOT NULL DEFAULT 0,				  
				  `date` DATE NOT NULL DEFAULT '2001-01-01',
				  `amount` DECIMAL(8,2),
				  `status` VARCHAR(100) NOT NULL DEFAULT 'failed',
				  `paycode` VARCHAR(100) NOT NULL DEFAULT '',
				  `paytype` VARCHAR(100) NOT NULL DEFAULT 'paypal'
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  
	  
	   // this is email log of all the messages sent in the system 
	  if($wpdb->get_var("SHOW TABLES LIKE '".WPHOSTEL_EMAILLOG."'") != WPHOSTEL_EMAILLOG) {	  
			$sql = "CREATE TABLE `" . WPHOSTEL_EMAILLOG . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `sender` VARCHAR(255) NOT NULL DEFAULT '',
				  `receiver` VARCHAR(255) NOT NULL DEFAULT '',
				  `subject` VARCHAR(255) NOT NULL DEFAULT '',
				  `date` DATE,
				  `datetime` TIMESTAMP,
				  `status` VARCHAR(255) NOT NULL DEFAULT 'OK'				  
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }	 
		  
		// if there's no currency, default it to USD
		$currency = get_option('wphostel_currency');
		if(empty($currency)) update_option('wphostel_currency', 'USD');
		
		// add new fields
		wphostel_add_db_fields(array(
			array("name" => 'price_type', "type" => "VARCHAR(100) NOT NULL DEFAULT 'per-bed'"),
			array("name" => 'ical_import', "type" => "TEXT"),  /* URL of external iCal to sync with */
		 ),
		 WPHOSTEL_ROOMS);  	  
		 
		wphostel_add_db_fields(array(
			array('name' => 'timestamp', 'type'=> 'DATETIME'),
			array("name" => "ical_uid", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"), /* ID of externally imported iCal booking */
		), 
		WPHOSTEL_BOOKINGS); 
		
		$old_version = get_option('wphostel_version');
		if( version_compare( $old_version , '0.76') == -1 ) {
            // Indexes in version 0.76
            $sql = "CREATE INDEX title ON ".WPHOSTEL_ROOMS." (title) ";
            $wpdb->query($sql);
            
            $sql = "CREATE INDEX price ON ".WPHOSTEL_ROOMS." (price) ";
            $wpdb->query($sql);            
             
            $sql = "CREATE INDEX room_id ON ".WPHOSTEL_BOOKINGS." (room_id) ";
            $wpdb->query($sql);
            
            $sql = "CREATE INDEX room_and_dates ON ".WPHOSTEL_BOOKINGS." (room_id, from_date, to_date) ";
            $wpdb->query($sql);
		}
		 
		update_option('wphostel_version', '0.76'); 
   }
   
   // main menu
   static function menu() {
   	// we use 'hostelpro_manage' for consistency with the pro version
		$wphostel_caps = current_user_can('manage_options') ? 'manage_options' : 'hostelpro_manage';     	
   	
   	add_menu_page(__('Hostel', 'wphostel'), __('Hostel', 'wphostel'), $wphostel_caps, "wphostel_options", 
   		array(__CLASS__, "options"));
   	add_submenu_page('wphostel_options', __('Settings', 'wphostel'), __('Settings', 'wphostel'), $wphostel_caps, "wphostel_options", 
   		array(__CLASS__, "options"));
		add_submenu_page('wphostel_options', __("Manage Rooms", 'wphostel'), __("Manage Rooms", 'wphostel'), $wphostel_caps, 'wphostel_rooms', array('WPHostelRooms', "manage"));
		add_submenu_page('wphostel_options', __("Manage Bookings", 'wphostel'), __("Manage Bookings", 'wphostel'), $wphostel_caps, 'wphostel_bookings', array('WPHostelBookings', "manage")); 
		add_submenu_page('wphostel_options', __("Unavailable Dates", 'wphostel'), __("Unavailable Dates", 'wphostel'), $wphostel_caps, 'wphostel_unavailable', array('WPHostelBookings', "unavailable")); 
		add_submenu_page('wphostel_options', __("Email Log", 'wphostel'), __("Email Log", 'wphostel'), $wphostel_caps, 'wphostel_emaillog', array('WPHostelHelp', "email_log"));
   	add_submenu_page('wphostel_options', __("Help", 'wphostel'), __("Help", 'wphostel'), $wphostel_caps, 'wphostel_help', array('WPHostelHelp', "index")); 	
		
	}
	
	// CSS and JS
	static function scripts() {
		// CSS
		wp_register_style( 'wphostel-css', WPHOSTEL_URL.'css/main.css?v=1');
	  wp_enqueue_style( 'wphostel-css' );
   
   	wp_enqueue_script('jquery');
	   
	   // Namaste's own Javascript
		wp_register_script(
				'wphostel-common',
				WPHOSTEL_URL.'js/common.js',
				false,
				'0.1.0',
				false
		);
		wp_enqueue_script("wphostel-common");
		
		$translation_array = array('ajax_url' => admin_url('admin-ajax.php'),
			'enter_name' =>  __('Please enter name!', 'wphostel'),
			'enter_email' => __('Please enter email address!', 'wphostel'),
			'beds_required' => __('Please enter number of beds, numbers only', 'wphostel'),
			'from_date_required' => __('Please enter arrival date', 'wphostel'),
			'to_date_required' => __('Please enter date of leaving', 'wphostel'),
			'from_date_atleast_today' => __('Date of arrival cannot be in the past', 'wphostel'),
			'from_date_before_to' => __('Date of arrival cannot be after date of leave', 'wphostel'),);	
		wp_localize_script( 'wphostel-common', 'wphostel_i18n', $translation_array );	
		
		// jQuery Validator
		wp_enqueue_script(
				'jquery-validator',
				'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js',
				false,
				'0.1.0',
				false
		);
	}
	
	// initialization
	static function init() {
		global $wpdb;
		
		if(get_option('wphostel_debug_mode'))  {			
			$wpdb->show_errors();
			if(!defined('DIEONDBERROR')) define( 'DIEONDBERROR', true );
		}			
		
		load_plugin_textdomain( 'wphostel', false, WPHOSTEL_RELATIVE_PATH."/languages/" );
		if (!session_id()) @session_start();
		
		// define table names 
		define( 'WPHOSTEL_ROOMS', $wpdb->prefix. "wphostel_rooms");
		define( 'WPHOSTEL_BOOKINGS', $wpdb->prefix. "wphostel_bookings");
		define( 'WPHOSTEL_PAYMENTS', $wpdb->prefix. "wphostel_payments");
		define( 'WPHOSTEL_EMAILLOG', $wpdb->prefix. "wphostel_emaillog");
	
		define( 'WPHOSTEL_VERSION', get_option('wphostel_version'));
		
		// if there's no currency, default it to USD
		$currency = get_option('wphostel_currency');
		if(empty($currency)) update_option('wphostel_currency', 'USD');
		define( 'WPHOSTEL_CURRENCY', get_option('wphostel_currency'));
		
		// shortcodes
		add_shortcode('wphostel-booking', array("WPHostelShortcodes", "booking"));
		add_shortcode('wphostel-list', array("WPHostelShortcodes", "list_rooms"));
		add_shortcode('wphostel-book', array("WPHostelShortcodes", "book"));
		
		// Paypal IPN
		add_filter('query_vars', array(__CLASS__, "query_vars"));
		add_action('parse_request', array("WPHostelPayment", "parse_request"));
		
		// cleanup unconfirmed bookings
		if(get_option('wphostel_booking_mode') == 'paypal') {
			$cleanup_hours = get_option('wphostel_cleanup_hours');
			if(!empty($cleanup_hours) and is_numeric($cleanup_hours)) {
				 $wpdb->query("DELETE FROM ".WPHOSTEL_BOOKINGS." WHERE
				 	timestamp < NOW() - INTERVAL $cleanup_hours HOUR	
				 	AND amount_paid = 0 AND status != 'active'");
			}
		}
		
		// default datepicker CSS
		if(get_option('wphostel_datepicker_css') == '') {
			update_option('wphostel_datepicker_css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		}
		
		// cleanup email logs		
		$cleanup_raw_log = get_option('hostelpro_cleanup_email_log');
		if(empty($cleanup_raw_log)) $cleanup_raw_log = 7;
		if($wpdb->get_var("SHOW TABLES LIKE '".WPHOSTEL_EMAILLOG."'") == WPHOSTEL_EMAILLOG) {			
			$wpdb->query($wpdb->prepare("DELETE FROM ".WPHOSTEL_EMAILLOG." WHERE date < CURDATE() - INTERVAL %d DAY", $cleanup_raw_log));				
		}	
		
		// handle Paypal PDT payment
		if(!empty($_GET['wphostel_pdt'])) WPHostelPayment::paypal_ipn(); // process PDT payment if any		
		
		// ical
		add_action('template_redirect', array("WPHostelSync", "ical"));	
		
		// max date for the datepicker. Defaults to 1 year
		$max_date = get_option('wphostel_max_date');
		if(empty($max_date)) {
			update_option('wphostel_max_date', '1y');
			$max_date = '1y';
		}	
		define("WPHOSTEL_MAX_DATE", $max_date);
		
		$old_version = get_option('wphostel_version');
		if( version_compare( $old_version , '0.76') == -1 ) self :: install(true);		
	}
	
	// handle Hostel vars in the request
	static function query_vars($vars) {
		$new_vars = array('wphostel');
		$vars = array_merge($new_vars, $vars);
	   return $vars;
	} 	
		
	// parse Namaste vars in the request
	static function template_redirect() {		
		global $wp, $wp_query, $wpdb;
		$redirect = false;		
		 
	  if($redirect) {
	   	if(@file_exists(TEMPLATEPATH."/".$template)) include TEMPLATEPATH."/namaste/".$template;		
			else include(WPHOSTEL_PATH."/views/templates/".$template);
			exit;
	  }	   
	}	
			
	// manage general options
	static function options() {
		global $wpdb, $wp_roles;
		$is_admin = current_user_can('administrator');			
		
		if(!empty($_POST['ok']) and check_admin_referer('wphostel_options')) {
			if(empty($_POST['currency'])) $_POST['currency'] = sanitize_text_field($_POST['custom_currency']);
			update_option('wphostel_currency', sanitize_text_field($_POST['currency']));
			update_option('wphostel_booking_mode', sanitize_text_field($_POST['booking_mode']));
			update_option('wphostel_email_options', array("do_email_admin"=>empty($_POST['do_email_admin']) ? 0 : 1, 
				"admin_email"=>sanitize_email($_POST['admin_email']), "do_email_user"=>empty($_POST['do_email_user']) ? 0 : 1, 
				"email_admin_subject" => sanitize_text_field($_POST['email_admin_subject']), 
				"email_admin_message" => wphostel_strip_tags($_POST['email_admin_message']),
				"email_user_subject" => sanitize_text_field($_POST['email_user_subject']), 
				"email_user_message" => wphostel_strip_tags($_POST['email_user_message'])));
			update_option('wphostel_paypal', sanitize_text_field($_POST['paypal']));
			update_option('wphostel_cleanup_hours', intval($_POST['cleanup_hours']));
			// update_option('wphostel_booking_url', $_POST['booking_url']);		
			update_option('wphostel_min_stay', intval($_POST['min_stay']));
			update_option('wphostel_debug_mode', empty($_POST['debug_mode']) ? 0 : 1);
			update_option('wphostel_use_pdt', empty($_POST['use_pdt']) ? 0 : 1);
			update_option('wphostel_pdt_token', sanitize_text_field($_POST['pdt_token']));
			update_option('wphostel_max_date', intval($_POST['max_date_num']).sanitize_text_field($_POST['max_date_unit']));
			update_option('wphostel_booking_start', sanitize_text_field($_POST['booking_start']));
		}		
		
		if(!empty($_POST['datepicker_settings'])  and check_admin_referer('wphostel_datepicker')) {
			// these will be the same for PRO and free versions
			// datepicker locale and CSS
			update_option('wphostel_locale_url', esc_url_raw($_POST['locale_url']));
			update_option('wphostel_datepicker_css', esc_url_raw($_POST['datepicker_css']));
		}
		
		if(!empty($_POST['role_settings']) and $is_admin and check_admin_referer('role_settings')) {
			$roles = $wp_roles->roles;			
			
			foreach($roles as $key=>$r) {
				if($key == 'administrator') continue;
				
				$role = get_role($key);
	
				// manage Hostel(& Pro) - allow only admin change this
				if($is_admin) {
					if(@in_array($key, $_POST['manage_roles'])) {					
	    				if(!$role->has_cap('hostelpro_manage')) $role->add_cap('hostelpro_manage');
					}
					else $role->remove_cap('hostelpro_manage');
				}	// end if can_manage_options
			} // end foreach role 
		}
		
		$roles = $wp_roles->roles;
		
		$currency = get_option('wphostel_currency');
		$currencies=array('USD'=>'$', "EUR"=>"&euro;", "GBP"=>"&pound;", "JPY"=>"&yen;", "AUD"=>"AUD",
		   "CAD"=>"CAD", "CHF"=>"CHF", "CZK"=>"CZK", "DKK"=>"DKK", "HKD"=>"HKD", "HUF"=>"HUF",
		   "ILS"=>"ILS", "MXN"=>"MXN", "NOK"=>"NOK", "NZD"=>"NZD", "PLN"=>"PLN", "SEK"=>"SEK",
		   "SGD"=>"SGD");
		$currency_keys = array_keys($currencies);  
		   
		$booking_mode = get_option('wphostel_booking_mode');   
		$email_options = get_option('wphostel_email_options');
		$paypal = get_option('wphostel_paypal');
		$cleanup_hours = get_option('wphostel_cleanup_hours');
		$min_stay = get_option('wphostel_min_stay');
		$booking_start = get_option('wphostel_booking_start');
		$use_pdt = get_option('wphostel_use_pdt');
		
		$max_date = get_option('wphostel_max_date');		
		$max_date_num = substr($max_date, 0, 1);
		$max_date_unit = substr($max_date, 1, 1);
		
		$payment_errors = get_option('wphostel_errorlog');   	
		if(@file_exists(get_stylesheet_directory().'/wphostel/options.php')) include get_stylesheet_directory().'/wphostel/options.php';
		else include(WPHOSTEL_PATH."/views/options.php");		
	}	
	
	static function help() {
		if(@file_exists(get_stylesheet_directory().'/wphostel/help.php')) include get_stylesheet_directory().'/wphostel/help.php';
		else include(WPHOSTEL_PATH."/views/help.php");
	}	
	
	static function register_widgets() {
		// register_widget('WPHostelWidget');
	}
}
