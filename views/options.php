<div class="wrap">
	<h1><?php _e("Hostel Options", 'wphostel')?></h1>
	<p><b><?php printf(__('This plugin is a light version of <a href="%s" target="_blank">Hostel PRO</a>', 'wphostel'), 'http://wp-hostel.com')?></b></p>
	
	<div class="postbox-container" style="width:73%;margin-right:2%;">
	
	<form method="post" class="wphostel-form">
		<div class="postbox wphostel-box">
			<div><label><?php _e("Currency:", 'wphostel');?></label>
				<select name="currency" onchange="this.value ? jQuery('#customCurrency').hide() : jQuery('#customCurrency').show(); ">
				<?php foreach($currencies as $key=>$val):
	            if($key==$currency) $selected='selected';
	            else $selected='';?>
	        		<option <?php echo $selected?> value='<?php echo $key?>'><?php echo $val?></option>
	         <?php endforeach; ?>
	         	<option value="" <?php if(!in_array($currency, $currency_keys)) echo 'selected'?>><?php _e('Custom', 'wphostel')?></option>
				</select> <input type="text" id="customCurrency" name="custom_currency" style='display:<?php echo in_array($currency, $currency_keys) ? 'none' : 'inline';?>' value="<?php echo $currency?>"></div>
			<div><label><?php _e('Booking mode:', 'wphostel')?></label> <select name="booking_mode" onchange="changeBookingMode(this.value);">
				<option value="none" <?php if($booking_mode == 'none') echo 'selected'?>><?php _e('No booking', 'wphostel')?></option>		
				<option value="manual" <?php if($booking_mode == 'manual') echo 'selected'?>><?php _e('Manual / No Payment', 'wphostel')?></option>
				<option value="paypal" <?php if($booking_mode == 'paypal') echo 'selected'?>><?php _e('Paypal', 'wphostel')?></option>
				</select>
				<div class="wphostel-help">
					<p><strong><?php _e('No booking', 'wphostel')?></strong> <?php _e('- In this mode your site will only show the information for the rooms and will not let the visitors book rooms', 'wphostel')?></p>
					
					<p><strong><?php _e('Manual / No Payment', 'wphotel')?></strong> <?php _e('- In this mode your visitors will be able to request booking by clicking on button and filling their information in the booking form. You as admin will approve or reject the booking manually in the admin panel.', 'wphostel')?></p>
					<p><strong><?php _e('Paypal', 'wphotel')?></strong> <?php _e('- In this mode your visitors will be able to book and get their bookings activated instantly by paying by Paypal', 'wphostel')?></p>
				</div>		
			</div>	
			
			<?php if(!empty($payment_errors)):?>
				<p><a href="#" onclick="jQuery('#hostelErrorlog').toggle();return false;"><?php _e('View payments errorlog', 'wphostel')?></a></p>
				<div id="hostelErrorlog" style="display:none;"><?php echo nl2br($payment_errors)?></div>
			<?php endif;?>	
					
			<div id="wphostelPaypal" style='display:<?php echo ($booking_mode=='paypal')?'block':'none'?>'>
				<label><?php _e('Your Paypal Email:', 'wphostel')?></label> <input type="text" name="paypal" value="<?php echo @$paypal?>">
				<p><b><?php _e('Note: Paypal IPN will not work if your site is behind a "htaccess" login box or running on localhost. Your site must be accessible from the internet for the IPN to work. In cases when IPN cannot work you need to use Paypal PDT.', 'wphostel')?></b></p>
			
				<p><input type="checkbox" name="use_pdt" value="1" <?php if($use_pdt == 1) echo 'checked'?> onclick="this.checked ? jQuery('#paypalPDTToken').show() : jQuery('#paypalPDTToken').hide();"> <?php printf(__('Use Paypal PDT instead of IPN (<a href="%s" target="_blank">Why and how</a>)', 'wphostel'), 'http://blog.calendarscripts.info/using-paypal-payment-data-transfer-pdt-instead-of-ipn-in-hostel-and-hostelpro-plugins');?></p>
				
				<div id="paypalPDTToken" style='display:<?php echo ($use_pdt == 1) ? 'block' : 'none';?>'>
					<p><label><?php _e('Paypal PDT Token:', 'wphostel');?></label> <input type="text" name="pdt_token" value="<?php echo get_option('wphostel_pdt_token');?>" size="60"></p>
				</div>				
				
				
				<p><?php _e('Automatically cleanup unconfirmed (unpaid) bookings after', 'wphostel')?> <input type="text" name="cleanup_hours" value="<?php echo $cleanup_hours?>" size="4"> <?php _e('hours. (Leave blank for no automated cleanup.)', 'wphostel')?> </p>
			</div>
			
			<div id="wphostelMinStay" style='display:<?php echo ($booking_mode!='none')?'block':'none'?>'>
				<p><label><?php _e('Require minimum stay of:', 'wphostel')?></label> <input type="text" name="min_stay" value="<?php echo $min_stay?>" size="3"> <?php _e('days', 'wphostel');?></p>	
				<p><label><?php _e('Guests can book rooms from:', 'wphostel');?></label> <select name="booking_start">
					<option value="tomorrow" <?php if($booking_start == 'tomorrow') echo 'selected'?>><?php _e('Next day', 'wphostel');?></option>
					<option value="today" <?php if($booking_start == 'today') echo 'selected'?>><?php _e('Same day', 'wphostel');?></option>
				</select></p>			
				<p><label><?php _e('Limit bookings to', 'wphostel');?></label> <input type="text" size="4" name="max_date_num" value="<?php echo $max_date_num?>"> 
					<select name="max_date_unit">
						<option value="m" <?php if($max_date_unit == 'm') echo 'selected';?>><?php echo _e('months', 'wphostel');?></option>
						<option value="y" <?php if($max_date_unit == 'y') echo 'selected';?>><?php echo _e('years', 'wphostel');?></option>
					</select> <?php _e('in the future', 'wphostel');?></p>			
			</div>
			
				<div><input type="checkbox" name="do_email_admin" value="1" <?php if(!empty($email_options['do_email_admin'])) echo 'checked'?> onclick="jQuery('#emailAdminOptions').toggle();"> <?php _e('Send me email with booking details when someone makes or requests a booking','wphostel')?> </div>
			
			<div id="emailAdminOptions" style='display:<?php echo empty($email_options['do_email_admin'])? 'none' : 'block'?>;margin-left:100px;'>
					<div><label><?php _e('Email address to receive the notice:', 'wphostel')?></label> <input type="text" name="admin_email" value="<?php echo empty($email_options['admin_email']) ? get_option('admin_email') : $email_options['admin_email']?>"></div>		
					<div><label><?php _e('Email subject:', 'wphostel')?></label> <input type="text" name="email_admin_subject" value="<?php echo $email_options['email_admin_subject']?>" size="40"></div>
					<div><label><?php _e('Email message:', 'wphostel')?></label> <?php echo wp_editor(stripslashes(@$email_options['email_admin_message']), 'email_admin_message')?></div>
					<p><?php _e('You can use the following variables:', 'wphostel')?> <b>{{from-date}}</b>, <b>{{to-date}}</b>, <b>{{url}}</b> <?php _e('(The URL to see the booking details in admin)','wphostel')?>, <b>{{room-type}}</b>, <b>{{room-name}}</b>, <b>{{num-beds}}</b>, <b>{{contact-name}}</b>, <b>{{contact-email}}</b>, <b>{{contact-phone}}</b>, <b>{{timestamp}}</b> <?php _e('(Date/time when reservation is made)','wphostel')?></p>
			</div>
			
			<div><input type="checkbox" name="do_email_user" value="1" <?php if(!empty($email_options['do_email_user'])) echo 'checked'?> onclick="jQuery('#emailUserOptions').toggle();"> <?php _e('Send confirmation email to user when booking is made','wphostel')?> </div>
			
				
			<div id="emailUserOptions" style='display:<?php echo empty($email_options['do_email_user'])? 'none' : 'block'?>;margin-left:100px;'>					
					<div><label><?php _e('Email subject:', 'wphostel')?></label> <input type="text" name="email_user_subject" value="<?php echo $email_options['email_user_subject']?>" size="40"></div>
					<div><label><?php _e('Email message:', 'wphostel')?></label> <?php echo wp_editor(stripslashes(@$email_options['email_user_message']), 'email_user_message')?></div>
					<p><?php _e('You can use the following variables:', 'wphostel')?> <b>{{from-date}}</b>, <b>{{to-date}}</b>, <b>{{amount-paid}}</b>, 
					<b>{{amount-due}}</b>, <b>{{room-type}}</b>, <b>{{room-name}}</b>, <b>{{num-beds}}</b>, <b>{{timestamp}}</b> <?php _e('(Date/time when reservation is made)','wphostel')?></p>
			</div>
			
			<h2><?php _e('Other Technical Settings', 'hostelpro');?></h2>
			
			<p><input type="checkbox" name="debug_mode" value="1" <?php if(get_option('wphostel_debug_mode')) echo 'checked'?> /> <?php _e('Enable debug mode to see SQL errors. (Useful in case you have any problems)', 'wphostel')?></p>
			
			<p><input type="submit" value="<?php _e('Save Options', 'wphostel')?>" class="button button-primary"></p>
			<input type="hidden" name="ok" value="1">
				</div>
				<?php wp_nonce_field('wphostel_options');?>
	</form>
	
		<h2><?php _e('Datepicker Localization and Theming','wphostel')?></h2>
		<form method="post" class="wphostel-form">
			<div class="postbox wphostel-box">
				<p><?php printf(__('Here you can specify localization and theme files for your datepicker. Please do read <a href="%s" target="_blank">this article</a> for more information.', 'wphostel'), 'http://blog.calendarscripts.info/localization-and-styling-of-the-datepicker-in-hostelpro/')?></p>
				<p><label><?php _e('Localization  file URL:', 'wphostel')?></label> <input type="text" name="locale_url" value="<?php echo get_option('wphostel_locale_url');?>" size="80"></p>
				<p><label><?php _e('CSS Theme URL:', 'wphostel')?></label> <input type="text" name="datepicker_css" value="<?php echo get_option('wphostel_datepicker_css');?>" size="80"></p>
				<input type="submit" value="<?php _e('Save Datepicker Settings', 'wphostel')?>" name="datepicker_settings" class="button button-primary">
			</div>
			<?php wp_nonce_field('wphostel_datepicker');?>
		</form>
		
		<?php if($is_admin):?>
		<h2><?php _e('Role Management','wphostel')?></h2>
		<form method="post" class="wphostel-form">
			<div class="postbox wphostel-box">
				<h2><?php _e('Wordpress roles that can administrate the plugin', 'wphostel')?></h2>
		
				<p><?php _e('By default this is only the blog administrator. Here you can enable any of the other roles as well', 'wphostel')?></p>
				
				<p><?php foreach($roles as $key=>$r):
					if($key=='administrator') continue;
					$role = get_role($key);?>
					<input type="checkbox" name="manage_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('hostelpro_manage')) echo 'checked';?>> <?php _e($role->name, 'wphostel')?> &nbsp;
				<?php endforeach;?></p>
				<p><input type="submit" value="<?php _e('Save Role Management Settings', 'wphostel')?>" name="role_settings" class="button button-primary"></p>
			</div>
			<?php wp_nonce_field('role_settings');?>
		</form>
	<?php endif;?>	
	
		<p><?php printf(__('Your feedback is most welcome! Please <a href="%s" target="_blank">let us know</a> what features and improvements you would like to see in the plugin.', 'wphostel'), 'http://wordpress.org/support/plugin/hostel')?></p>
	</div>
	
	<div id="wphsotel-sidebar">
				<?php require(WPHOSTEL_PATH."/views/sidebar.html.php");?>
	</div>	
</div>	

<script type="text/javascript" >
function changeBookingMode(val) {
	jQuery('#wphostelPaypal').hide();
	jQuery('#wphostelMinStay').hide();
	if(val=='paypal') jQuery('#wphostelPaypal').show();
	if(val=='paypal' || val == 'manual') jQuery('#wphostelMinStay').show();
}
</script>