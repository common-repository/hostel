<?php
// manage hostel rooms controller
class WPHostelRooms {
	static function manage() {
		$_room = new WPHostelRoom();
		
		$action = empty($_GET['action'])?'list':$_GET['action'];
		switch($action) {
			case 'add':
				if(!empty($_POST['ok']) and check_admin_referer('wphostel_room')) {
					$_room -> add($_POST);
					$success = __('Room added.', 'wphostel');
					wphostel_redirect("admin.php?page=wphostel_rooms&action=list");
				}			
			
				if(@file_exists(get_stylesheet_directory().'/wphostel/room.php')) include get_stylesheet_directory().'/wphostel/room.php';
				else include(WPHOSTEL_PATH."/views/room.php");
			break;
			
			case 'edit':
				$_GET['id'] = intval($_GET['id']);
				if(!empty($_POST['ok']) and check_admin_referer('wphostel_room')) {
					$_room->edit($_POST, $_GET['id']);
					$success = __('Room details saved.', 'wphostel');
					wphostel_redirect("admin.php?page=wphostel_rooms&action=list");
				}
				
				$room = $_room->get($_GET['id']);
				
				if(@file_exists(get_stylesheet_directory().'/wphostel/room.php')) include get_stylesheet_directory().'/wphostel/room.php';
				else include(WPHOSTEL_PATH."/views/room.php");
			break;
			
			case 'delete':
				if (!isset($_GET['nonce'])) wp_die("");
				$nonce = $_GET['nonce'];
				if (!wp_verify_nonce($nonce, 'delete_room')) {
					// Nonce verification failed
					wp_die('Nonce verification failed');
				}
				$_GET['id'] = intval($_GET['id']);
				$_room->delete($_GET['id']);
				$success = __("Room deleted.", 'wphostel');
				wphostel_redirect("admin.php?page=wphostel_rooms&action=list");
			break;			
			
			case 'list':
			default:
				$rooms = $_room->find();
				if(@file_exists(get_stylesheet_directory().'/wphostel/rooms.php')) include get_stylesheet_directory().'/wphostel/rooms.php';
				else include(WPHOSTEL_PATH."/views/rooms.php");
			break;
		}
	}
	
	// ajax called function that returns the default number of beds for the booking form:
	// for dorm rooms and "per room" price return 1
	// for private rooms return max beds
	// outputs also 0 or 1 after the | to show whether the user can change or not the number of rooms
	static function default_beds() {
		global $wpdb;
		
		// select room
		$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WPHOSTEL_ROOMS." WHERE id=%d", intval($_POST['room_id'])));
		
		if($room->rtype == 'dorm' or $room->price_type == 'per-room') {
			echo "1";
			if($room->rtype == 'dorm') echo "|1";
			else echo "|0";
		}
		else echo $room->beds.'|0';		
		
		exit;
	}
	
	// displays the availability table of all rooms by given dates
	static function availability_table($shortcode_id, $atts = null) {
		global $wpdb;
		
		$_room = new WPHostelRoom();
		$_booking = new WPHostelBooking();
		$dateformat = get_option('date_format');
		$booking_mode = get_option('wphostel_booking_mode');
		$min_stay = get_option('wphostel_min_stay');
		$booking_start = get_option('wphostel_booking_start');
		if(empty($booking_start)) $booking_start = 'tomorrow';
		$book_to_date = ($booking_start == 'tomorrow') ? '+2 days' : 'tomorrow';
		$show_titles = empty($atts['show_titles']) ? 0 : $atts['show_titles'];
				
		// the dropdown defaults to "from tomorrow to 1 day after"
		$default_dateto_diff = $min_stay ? strtotime("+ ".(intval($min_stay)+1)." days") : strtotime($book_to_date);
		$datefrom = empty($_POST['wphostel_from']) ? date("Y-m-d", strtotime($booking_start)) : sanitize_text_field($_POST['wphostel_from']);
		$dateto = empty($_POST['wphostel_to']) ? date("Y-m-d", $default_dateto_diff) : sanitize_text_field($_POST['wphostel_to']);
		
		// select all rooms
		$rooms = $wpdb->get_results("SELECT * FROM ".WPHOSTEL_ROOMS." ORDER BY price", ARRAY_A);
		
		// select all bookings in the given period
		$bookings = $_booking->select_in_period($datefrom, $dateto);
		
		$datefrom_time = strtotime($datefrom);
		$dateto_time = strtotime($dateto);		
		$numdays = ($dateto_time   -  $datefrom_time) / (24 * 3600);
		
		// match bookings to rooms so for each date we know if the room is booked or not
		foreach($rooms as $cnt=>$room) {
			$rooms[$cnt] = $_room->availability($room, $bookings, $datefrom, $dateto, $numdays, $datefrom_time, $dateto_time);			
		} // end foreach room
		
		if(@file_exists(get_stylesheet_directory().'/wphostel/partial/rooms-table.html.php')) include get_stylesheet_directory().'/wphostel/partial/rooms-table.html.php';
		else include(WPHOSTEL_PATH."/views/partial/rooms-table.html.php");
	} // end availability table
}
