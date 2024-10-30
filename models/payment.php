<?php
class WPHostelPayment {
	static $pdt_mode = false;	
	static $pdt_response = '';		
	
	// handle Paypal IPN request
	static function parse_request($wp) {
		
		// only process requests with "namaste=paypal"
	   if (array_key_exists('wphostel', $wp->query_vars) 
	            && $wp->query_vars['wphostel'] == 'paypal') {
	        self::paypal_ipn($wp);
	   }	
	}
	
	// process paypal IPN
	static function paypal_ipn($wp = null) {
		global $wpdb;
		echo "<!-- WPHOSTEL paypal IPN -->";
		
	   $paypal_email = get_option("wphostel_paypal");
	   $paypal_sandbox = 0; // this is fixed to false for the moment
	   $test_mode = false;
	   
	   $pdt_mode = false;
	   if(!empty($_GET['tx']) and !empty($_GET['wphostel_pdt']) and get_option('wphostel_use_pdt')==1) {
			// PDT			
			$req = 'cmd=_notify-synch';
			$tx_token = strtoupper($_GET['tx']);
			$auth_token = get_option('wphostel_pdt_token');
			$req .= "&tx=$tx_token&at=$auth_token";
			$pdt_mode = true;
			$success_responce = "SUCCESS";
		}
		else {	
			// IPN		
			$req = 'cmd=_notify-validate';
			foreach ($_POST as $key => $value) { 
			  $value = urlencode(stripslashes($value)); 
			  $req .= "&$key=$value";
			}
			$success_responce = "VERIFIED";
		}		
		
		self :: $pdt_mode = $pdt_mode;	
		
		$paypal_host = "ipnpb.paypal.com";
		if($paypal_sandbox == '1') $paypal_host = 'ipnpb.sandbox.paypal.com';
		
		// post back to PayPal system to validate
		$paypal_host = "https://".$paypal_host;
		
		// wp_remote_post
		$response = wp_remote_post($paypal_host, array(
			    'method'      => 'POST',
			    'timeout'     => 45,
			    'redirection' => 5,
			    'httpversion' => '1.0',
			    'blocking'    => true,
			    'headers'     => array(),
			    'body'        => $req,
			    'cookies'     => array()
		    ));
		
		if ( is_wp_error( $response ) ) {
		    $error_message = $response->get_error_message();
			 return self::log_and_exit("Can't connect to Paypal: $error_message");
		} 
		
		if (strstr ($response['body'], $success_responce) or $paypal_sandbox == '1') self :: paypal_ipn_verify($response['body']);
		else return self::log_and_exit("Paypal result is not VERIFIED: ".$response['body']);			
		exit;
	}
		
	static function paypal_ipn_verify($pp_response) {
		global $wpdb, $user_ID, $post;
		
		$test_mode = false;
		
		// when we are in PDT mode let's assign all lines as POST variables
		if(self :: $pdt_mode) {
			 $lines = explode("\n", $pp_response);	
				if (strcmp ($lines[0], "SUCCESS") == 0) {
				for ($i=1; $i<count($lines);$i++){
					if(strstr($lines[$i], '=')) list($key,$val) = explode("=", $lines[$i]);
					$_POST[urldecode($key)] = urldecode($val);
				}
			 }
			 
			 $_GET['user_id'] = $user_ID;
			 self :: $pdt_response = $pp_response;
		} // end PDT mode transfer from lines to $_POST	 				
					
		// check the payment_status is Completed
      // check that txn_id has not been previously processed
      // check that receiver_email is your Primary PayPal email
      // process payment
	   $payment_completed = false;
	   $txn_id_okay = false;
	   $receiver_okay = false;
	   $payment_currency_okay = false;
	   $payment_amount_okay = false;
	   $paypal_email = get_option("wphostel_paypal");
	   $_POST['txn_id'] = sanitize_text_field($_POST['txn_id']);
	   
	   if(@$_POST['payment_status']=="Completed" or $test_mode) {
	   	$payment_completed = true;
	   } 
	   else return self::log_and_exit("Payment status: $_POST[payment_status]");
	   
	   // check txn_id
	   $txn_exists = $wpdb->get_var($wpdb->prepare("SELECT paycode FROM ".WPHOSTEL_PAYMENTS."
		   WHERE paytype='paypal' AND paycode=%s", $_POST['txn_id']));
		if(empty($txn_exists)) $txn_id_okay = true;
		else {
			// in PDT mode just redirect to the post because existing txn_id isn't a problem.
			// but of course we shouldn't insert second payment			
			if( self :: $pdt_mode) wphostel_redirect(get_permalink(@$post->ID));
			return self::log_and_exit("TXN ID exists: $txn_exists");
		}  
		
		// check receiver email
		if($_POST['business']==$paypal_email or $_POST['receiver_id'] == $paypal_email or $test_mode) {
			$receiver_okay = true;
		}
		else return self::log_and_exit("Business email is wrong: $_POST[business]");
		
		// check payment currency
		if($_POST['mc_currency']==get_option("wphostel_currency") or $test_mode) {
			$payment_currency_okay = true;
		}
		else return self::log_and_exit("Currency is $_POST[mc_currency]"); 
		
		// check amount
		$_GET['bid'] = intval($_GET['bid']);
		$booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WPHOSTEL_BOOKINGS." WHERE id=%d", $_GET['bid']));
		$fee = $booking->amount_due;
		if($_POST['mc_gross']>=$booking->amount_due) {
			$payment_amount_okay = true;
		}
		else self::log_and_exit("Wrong amount: paid $_POST[mc_gross] when price is $fee"); 
		
		// everything OK, insert payment and enroll
		if($payment_completed and $txn_id_okay and $receiver_okay and $payment_currency_okay 
				and $payment_amount_okay) {					
									
			$wpdb->query($wpdb->prepare("INSERT INTO ".WPHOSTEL_PAYMENTS." SET 
				booking_id=%d, date=CURDATE(), amount=%s, status='completed', paycode=%s, paytype='paypal'", 
				$_GET['bid'], $fee, $_POST['txn_id']));
				
			// activate booking and move amount due in amount paid
			$wpdb->query($wpdb->prepare("UPDATE ".WPHOSTEL_BOOKINGS." SET status='active', amount_paid = amount_due, amount_due = 0
				WHERE id=%d", $_GET['bid']));						
			
			$_booking = new WPHostelBooking();
			// send email if you have to
			$_booking->email($_GET['bid']);
			exit;
		}
	}
	
	// log paypal errors
	static function log_and_exit($msg) {
		// log
		$errorlog=get_option("wphostel_errorlog");
		$errorlog = date(get_option('date_format').' '.get_option('time_format')).": ".$msg."\n".$errorlog;
		update_option("wphostel_errorlog",$errorlog);
		
		// throw exception as there's no need to contninue
		exit;
	}
}