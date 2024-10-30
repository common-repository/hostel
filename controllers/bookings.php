<?php
class WPHostelBookings {
	static function manage() {
		global $wpdb;
		$_booking = new WPHostelBooking();

		switch(@$_GET['do']) {
			case 'add':
				if(!empty($_POST['ok'])) {
					$_POST['from_date'] = $_POST['fromyear'].'-'.$_POST['frommonth'].'-'.$_POST['fromday'];
					$_POST['to_date'] = $_POST['toyear'].'-'.$_POST['tomonth'].'-'.$_POST['today'];
					$_POST['status'] = 'active';
					$_booking -> add($_POST);
					wphostel_redirect("admin.php?page=wphostel_bookings&type=".sanitize_text_field($_GET['type']));
				}
							
				// select rooms for the dropdown
				$rooms = $wpdb->get_results("SELECT * FROM ".WPHOSTEL_ROOMS." ORDER BY title");
				if(@file_exists(get_stylesheet_directory().'/wphostel/booking.html.php')) include get_stylesheet_directory().'/wphostel/booking.html.php';
				else include(WPHOSTEL_PATH."/views/booking.html.php");
			break;
			
			case 'edit':
				$_GET['id'] = intval($_GET['id']);
				if(!empty($_POST['del']) and check_admin_referer('wphostel_booking')) {
					$_booking->delete($_GET['id']);
					$_GET['type'] = sanitize_text_field($_GET['type']);
					$_GET['offset'] = intval($_GET['offset']);
					wphostel_redirect("admin.php?page=wphostel_bookings&type=$_GET[type]&offset=$_GET[offset]");				
				}				

				if(!empty($_POST['ok']) and check_admin_referer('wphostel_booking')) {
					$_POST['from_date'] = $_POST['fromyear'].'-'.$_POST['frommonth'].'-'.$_POST['fromday'];
					$_POST['to_date'] = $_POST['toyear'].'-'.$_POST['tomonth'].'-'.$_POST['today'];
					$_booking -> edit($_POST, $_GET['id']);
					$_GET['type'] = sanitize_text_field($_GET['type']);
					$_GET['offset'] = intval($_GET['offset']);
					wphostel_redirect("admin.php?page=wphostel_bookings&type=$_GET[type]&offset=$_GET[offset]");
				}			
			
				// select booking
				$booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WPHOSTEL_BOOKINGS." WHERE id=%d", (int)$_GET['id']));

				// select rooms for the dropdown
				$rooms = $wpdb->get_results("SELECT * FROM ".WPHOSTEL_ROOMS." ORDER BY title");
				if(@file_exists(get_stylesheet_directory().'/wphostel/booking.html.php')) include get_stylesheet_directory().'/wphostel/booking.html.php';
				else include(WPHOSTEL_PATH."/views/booking.html.php");
			break;
			
			// view/print booking details. Will allow also to confirm/cancel
			case 'view':
				$_GET['id'] = intval($_GET['id']);
				// select booking and room details
				$booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WPHOSTEL_BOOKINGS." WHERE id=%d", $_GET['id']));
				$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WPHOSTEL_ROOMS." WHERE id=%d", $booking['room_id']));	
			
				if(@file_exists(get_stylesheet_directory().'/wphostel/view-booking.html.php')) include get_stylesheet_directory().'/wphostel/view-booking.html.php';
				else include(WPHOSTEL_PATH."/views/view-booking.html.php");				
			break;			
			
			// list bookings
			default:

				// which bookings to show - upcoming or past?
				$type = empty($_GET['type']) ? 'upcoming' : sanitize_text_field($_GET['type']);
				$offset = empty($_GET['offset']) ? 0 : intval($_GET['offset']);
				$dir = empty($_GET['dir']) ? 'ASC' : $_GET['dir'];
				if($dir != 'ASC' and $dir != 'DESC') $dir = 'ASC';
				$odir = ($dir == 'ASC') ? 'DESC' : 'ASC';
				
				// mark booking as fully paid	
				if(!empty($_GET['mark_paid'])) {
					$_booking->mark_paid($_GET['id']);

					if(!empty($_GET['send_emails'])) {
						$_booking->email($_GET['id']);
					}					
					
					wphostel_redirect("admin.php?page=wphostel_bookings&type=".$type."&offset=".$offset);
				}
				
				// define $where_sql and orderby depending on the $type		
				$curdate = date("Y-m-d", current_time('timestamp'));		
				if($type == 'upcoming') {
					$where_sql = "AND from_date >=  '$curdate' ";
					$orderby = "ORDER BY from_date";
					
				}
				else {
					$where_sql = "AND from_date < '$curdate' ";
					$orderby = "ORDER BY from_date DESC";
				}
				
				// define limit (as it's paginated)				
				$page_limit = 20;
				$limit_sql = empty($_GET['export']) ? $wpdb->prepare("LIMIT %d, %d", $offset, $page_limit) : ''; 
				
				// search filter
				if(!empty($_GET['contact_email'])) {
					$_GET['contact_email'] = sanitize_text_field($_GET['contact_email']);
					$where_sql .= " AND contact_email LIKE '%".$_GET['contact_email']."%' ";
				}
				if(!empty($_GET['contact_name'])) {
					$_GET['contact_name'] = sanitize_text_field($_GET['contact_name']);
					$where_sql .= " AND contact_name LIKE '%".$_GET['contact_name']."%' ";
				}
				if(!empty($_GET['room_id'])) {
					$_GET['room_id'] = intval($_GET['room_id']);
					$where_sql .= $wpdb->prepare(" AND room_id = %d ", $_GET['room_id']);
				}
				if(!empty($_GET['status'])) {
					$_GET['status'] = sanitize_text_field($_GET['status']);
					$where_sql .= $wpdb->prepare(" AND status = %s ", $_GET['status']);
				}				 
				if(!empty($_GET['booking_id'])) {
					$_GET['booking_id'] = intval($_GET['booking_id']);
					$where_sql .= $wpdb->prepare(" AND tB.id = %d ", $_GET['booking_id']);
				}
				if(!empty($_GET['contact_email']) or !empty($_GET['contact_name']) 
					or !empty($_GET['room_id']) or !empty($_GET['status']) or !empty($_GET['booking_id'])) $filters_apply = true;	
					
				if(!empty($_GET['ob'])) {					
					$orderby = "ORDER BY ".sanitize_text_field($_GET['ob']) . ' ' . $dir;
				}
				
				$bookings = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS tB.*, tR.title as room 
					FROM ".WPHOSTEL_BOOKINGS." tB JOIN ".WPHOSTEL_ROOMS." tR ON tR.id = tB.room_id
					WHERE is_static=0 $where_sql $orderby $limit_sql");
				$count = $wpdb->get_var("SELECT FOUND_ROWS()");	
				
				$email_options = get_option('wphostel_email_options');
				
				$filters_str = '';

				if(@file_exists(get_stylesheet_directory().'/wphostel/bookings.html.php')) include get_stylesheet_directory().'/wphostel/bookings.html.php';
				else include(WPHOSTEL_PATH."/views/bookings.html.php");				  
			break;
		}
	}
	
	// manage unavailable dates
	// they are entered as "static" booking. 
	// these bookings always have 1 DB record for each single date
	// from equals the date, to is the next day
	static function unavailable() {
		global $wpdb;
		$_booking = new WPHostelBooking();
		$_room = new WPHostelRoom();
		$dateformat = get_option('date_format');
		
		$date = empty($_POST['date']) ? date("Y-m-d") : sanitize_text_field($_POST['date']);
		$to_date = empty($_POST['to_date']) ? date("Y-m-d", strtotime($date) + 24*3600) : sanitize_text_field($_POST['to_date']); 
		
		// select all available rooms
		$rooms = $wpdb->get_results( "SELECT * FROM ".WPHOSTEL_ROOMS." ORDER BY title" );
		
		$unavailable_room_ids = (!empty($_POST['ids']) and is_array($_POST['ids'])) ? wphostel_int_array($_POST['ids']) : array(0);		
		if(!empty($_POST['set_dates']) and check_admin_referer('wphostel_unavailable')) {
			foreach($rooms as $room) {
				if(in_array($room->id, $unavailable_room_ids)) {
					// make sure there is no static booking for the room on this date
					$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".WPHOSTEL_BOOKINGS." 
						WHERE room_id=%d AND from_date<=%s AND to_date>=%s AND is_static=1", $room->id, $date, $to_date));
					if(!$exists) {
						$wpdb->query($wpdb->prepare("INSERT INTO ".WPHOSTEL_BOOKINGS." SET
							room_id=%d, from_date=%s, to_date=%s, is_static=1", $room->id, $date, $to_date));
					}	
				}
				else {
					// delete any static bookings for this room on this exact period
					$wpdb->query($wpdb->prepare("DELETE FROM ".WPHOSTEL_BOOKINGS." 
						WHERE is_static=1 AND from_date=%s AND to_date=%s AND room_id=%d", $date, $to_date, $room->id));
						
					// but in case there is period that overlaps partially we'll need to break it up on parts
					$overlap_both = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WPHOSTEL_BOOKINGS." 
						WHERE is_static=1 AND from_date<=%s AND to_date>=%s AND room_id=%d", $date, $to_date, $room->id));
						
					if(!empty($overlap_both->id)) {
						// delete the overlap and enter 2 other periods
						$wpdb->query($wpdb->prepare("DELETE FROM " . WPHOSTEL_BOOKINGS. " WHERE is_static=1 AND from_date<=%s AND to_date>=%s AND room_id=%d", 
						 $date, $to_date, $room->id));
						
						// 1st period
						$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".WPHOSTEL_BOOKINGS." 
							WHERE room_id=%d AND from_date<=%s AND to_date>=%s AND is_static=1", $room->id, $overlap_both->from_date, $date));						
						if(!$exists) {
							$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_BOOKINGS." SET
								room_id=%d, from_date=%s, to_date=%s, is_static=1", $room->id, $overlap_both->from_date, $date));
						}	
						
						// 2nd period
						$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".WPHOSTEL_BOOKINGS." 
							WHERE room_id=%d AND from_date<=%s AND to_date>=%s AND is_static=1", $room->id, $to_date, $overlap_both->to_date));						
						if(!$exists) {
							$wpdb->query($wpdb->prepare("INSERT INTO ".WPHOSTEL_BOOKINGS." SET
								room_id=%d, from_date=%s, to_date=%s, is_static=1", $room->id, $to_date, $overlap_both->to_date));
						}		
					}
				} // end unsetting
			}
		}
			
			
		// now select all static bookings on the given dates and feel new $unavailable_room_ids array
		$static_bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WPHOSTEL_BOOKINGS." 
			WHERE is_static=1 AND from_date<=%s AND to_date>=%s", $date, $to_date));
		$unavailable_room_ids = array();
		foreach($static_bookings as $booking) $unavailable_room_ids[] = $booking->room_id;
		
		// now select partially unavailable periods so we can show them
		$partially_unavailable = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WPHOSTEL_BOOKINGS." 
			WHERE is_static=1 AND ((from_date < %s AND to_date >=%s AND to_date < %s)
			 OR (from_date >=%s AND from_date <= %s AND to_date > %s)
			 OR (from_date >=%s AND from_date <= %s AND to_date > %s AND to_date <= %s))", $date, $date, $to_date,
			 	$date, $to_date, $to_date,
			 	$date, $to_date, $date, $to_date));
				
		wphostel_enqueue_datepicker();
		if(@file_exists(get_stylesheet_directory().'/wphostel/unavailable-dates.html.php')) include get_stylesheet_directory().'/wphostel/unavailable-dates.html.php';
		else include(WPHOSTEL_PATH."/views/unavailable-dates.html.php");				  
	}
	
	// do the booking
	static function book() {
		global $wpdb, $post;
		
		// insert booking details
			$_booking = new WPHostelBooking();
			$_room = new WPHostelRoom();
			
			$from_date = sanitize_text_field($_POST['from_date']);
			$to_date = sanitize_text_field($_POST['to_date']);
			
			// make sure it's not a duplicate
			$bid = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".WPHOSTEL_BOOKINGS."
				WHERE room_id=%d AND from_date=%s AND to_date=%s AND contact_email=%s",
				intval($_POST['room_id']), $from_date, $to_date, sanitize_email($_POST['contact_email'])));
				
			// select the room
			$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WPHOSTEL_ROOMS." WHERE id=%d", intval($_POST['room_id'])));	
			$check_room = (array)$room;	
			
			if($room->price_type == 'per-room') $_POST['beds'] = 1;
			$_POST['beds'] = intval($_POST['beds']);
				
			// calculate cost
			$datefrom_time = strtotime($from_date);
			$dateto_time = strtotime($to_date);		
			$numdays = ($dateto_time   -  $datefrom_time) / (24 * 3600);	
			
			$cost = $numdays * $_POST['beds'] * $room->price;	
			$_POST['amount_paid'] =  0;
			$_POST['amount_due'] = $cost;			
			$_POST['status'] = 'pending';
										
			if(empty($bid)) {
				// minimum stay required?
				$min_stay = get_option('wphostel_min_stay');
				if(!empty($min_stay) and $min_stay > $numdays) {
					return '<!--BOOKERROR-->'.sprintf(__('Minimum stay of %d days is required.', 'wphostel'), $min_stay);
				}						
				
				// if this is a private room, we cannot book less beds than the room has
				if($room->rtype == 'private' and $_POST['beds'] != $room->beds and $room->price_type != 'per-room') {
					return '<!--BOOKERROR-->'.sprintf(__('This is a private room. You have to book all the %d beds', 'wphostel'), $room->beds);
				}				
				
				// select all bookings in the given period
				$bookings = $_booking->select_in_period($from_date, $to_date);
								
				// make sure all dates are available
				$check_room = $_room->availability($check_room, $bookings, $from_date, $to_date, $numdays, $datefrom_time, $dateto_time);
				foreach($check_room['days']	as $day) {
					if(!$day['available_beds'] or $day['available_beds'] < $_POST['beds']) return '<!--BOOKERROR-->'. __('In your selection there are dates when the room is not available or there are not enough free beds. Please check your selection.','wphostel');
				}		
						
				$bid = $_booking->add($_POST);
			}
			
			// if paypal display payment button otherwise display success message
			if(get_option('wphostel_booking_mode') == 'paypal') {
				if(@file_exists(get_stylesheet_directory().'/wphostel/pay-paypal.html.php')) include get_stylesheet_directory().'/wphostel/pay-paypal.html.php';
				else include(WPHOSTEL_PATH."/views/pay-paypal.html.php");
			}
			else {
				echo "<p>".__('Thank you for your reservation request. We will get back to you when it is confirmed', 'wphostel')."</p>";
				
				// send email if you have to
				$_booking->email($bid);
			}
	}
}
