<?php
// class to sync with other services, for example through iCal format
class WPHostelSync {
	// generates iCal from all future bookings of a given room
	static function ical() {
		global $wpdb;
		
		if(empty($_GET['wphostel_ical'])) return true;
		
		// select bookings
		$bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WPHOSTEL_BOOKINGS."
			WHERE room_id=%d AND to_date >= ".date('Y-m-d', current_time('timestamp'))." ORDER BY id", intval($_GET['room_id'])));
		
		if(!empty($_GET['download'])) {
			header('Content-type: text/calendar; charset=utf-8');
			header('Content-Disposition: attachment; filename=calendar-' . intval($_GET['room_id']).'.ics');
		}	
		
		$site_uid = substr(md5(get_option('admin_email')), 0, 10);
		
		if(@file_exists(get_stylesheet_directory().'/wphostel/ical.html.php')) include get_stylesheet_directory().'/wphostel/ical.html.php';
		else include(WPHOSTEL_PATH."/views/ical.html.php");
		exit;			
	} // end ical
	
	static function dateToCal($timestamp) {
	  return date('Ymd\THis\Z', $timestamp);
	}
	// Escapes a string of characters
	static function escapeString($string) {
	  return preg_replace('/([\,;])/','\\\$1', $string);
	}
	
	// import bookings from external calendar
	static function import($room, $datefrom, $dateto, $available_beds, $times_in_epoch = false) {
		global $wpdb;
		$now = current_time('mysql');
		
		// in case the ical import contains several URLs, execute the function once for each and return
		if(strstr($room->ical_import, PHP_EOL)) {
			$ical_imports = explode(PHP_EOL, $room->ical_import);
			foreach($ical_imports as $ical_import) {
				$room->ical_import = $ical_import;
				self :: import($room, $datefrom, $dateto, $available_beds, $times_in_epoch);
			}
			
			return true;
		} 		
		
		$events = self :: icsToArray($room->ical_import);
		if(empty($events)) return false;		
		
		if($times_in_epoch) {
			$sync_start = $datefrom;
			$sync_end = $dateto;
		}
		else {
			$sync_start = strtotime($datefrom);
			$sync_end = strtotime($dateto);
		}			
		
		foreach($events as $event) {
			// check if event is within the selected date range. If not, continue
			if(trim($event['BEGIN']) != 'VEVENT') continue;
			$event_start_date = $event_end_date = date('Y-m-d'); // initialize just in case. To avoid errors
			
			foreach($event as $key=>$val) {
				if(strstr($key, 'DTSTART')) $event_start_date = substr($val, 0, 4).'-'.substr($val, 4, 2).'-'.substr($val, 6,2);
				if(strstr($key, 'DTEND')) $event_end_date = substr($val, 0, 4).'-'.substr($val, 4, 2).'-'.substr($val, 6,2);
			}
			
			$event_start = strtotime($event_start_date);
			$event_end = strtotime($event_end_date);
			if($event_start > $sync_end or $event_end < $sync_start) continue;
			
			// if event with this ID already exists, continue
			$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".WPHOSTEL_BOOKINGS." 
				WHERE ical_uid=%s AND room_id=%d", $event['UID'], $room->id));
				
			if($exists) continue;	
			
			// import event
			$wpdb->query($wpdb->prepare("INSERT INTO ".WPHOSTEL_BOOKINGS." SET
				 room_id=%d, from_date=%s, to_date=%s, amount_paid=%s, amount_due=%s,
				 is_static=%d, contact_name=%s, created_time='$now', status='active', beds=%d, ical_uid=%s", 
				 $room->id, $event_start_date, $event_end_date, 0, 0, 0,
				 @$event['SUMMARY'], $available_beds, $event['UID']));
		}
	} 
	
	// thanks to http://stackoverflow.com/questions/4757061/which-ics-parser-written-in-php-is-good
	static function icsToArray($paramUrl) {
		 $paramUrl = trim($paramUrl);
		 ob_start();
	    $icsFile = file_get_contents($paramUrl);
		
	    $error = ob_get_clean();
	    if(!empty($error)) {
	    	// log error
	    	$msg = "Importing iCal events failed at ".date(get_option('date_format'), current_time('timestamp'))." with message: $error";
	    	update_option('wphostel_ical_import_error', $msg);
	    }
	    if(empty($icsFile)) {
           // try curl
			 $curl = curl_init();
	        curl_setopt($curl, CURLOPT_URL, $paramUrl);
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	        //curl_setopt($curl, CURLOPT_HEADER, false);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
	        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	        $icsFile = curl_exec($curl);
	        curl_close($curl);
			//echo $icsFile;
	    }

	    if(empty($icsFile)) return false;
	
	    $icsData = explode("BEGIN:", $icsFile);
		 	
	    foreach($icsData as $key => $value) {
	        $icsDatesMeta[$key] = explode("\n", $value);
	    }
	
	    foreach($icsDatesMeta as $key => $value) {
	        foreach($value as $subKey => $subValue) {	     
	        	
	            if ($subValue != "") {
	                if ($key != 0 && $subKey == 0) {
	                    $icsDates[$key]["BEGIN"] = $subValue;
	                } else {
	                	
	                    $subValueArr = explode(":", $subValue, 2);
	                    $icsDates[$key][$subValueArr[0]] = $subValueArr[1];
	                }
	            }
	        }
	    }
		
	    return $icsDates;
	} // end icsToArray
}