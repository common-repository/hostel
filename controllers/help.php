<?php
class WPHostelHelp {
	static function index() {
		if(@file_exists(get_stylesheet_directory().'/wphostel/help.html.php')) include get_stylesheet_directory().'/wphostel/help.html.php';
		else include(WPHOSTEL_PATH."/views/help.html.php");
	}
	
	static function email_log() {
		global $wpdb;
		$date = empty($_POST['date']) ? date('Y-m-d') : $_POST['date'];
		if(!empty($_POST['cleanup'])) update_option('hostelpro_cleanup_email_log', $_POST['cleanup_days']);
		
		$emails = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WPHOSTEL_EMAILLOG." WHERE date=%s ORDER BY id", $date));
		
		$cleanup_raw_log = get_option('hostelpro_cleanup_email_log');
		if(empty($cleanup_raw_log)) $cleanup_raw_log = 7;
		
		wphostel_enqueue_datepicker();
		if(@file_exists(get_stylesheet_directory().'/wphostel/email-log.html.php')) include get_stylesheet_directory().'/wphostel/email-log.html.php';
		else include(WPHOSTEL_PATH."/views/email-log.html.php");		
	}
}