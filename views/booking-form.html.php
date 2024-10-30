<?php if(!empty($_GET['booking_mode'])):?>
	<p><a href="<?php echo get_permalink($post->ID)?>"><?php _e('Back to the listing of rooms', 'wphostel')?></a></p>
<?php endif;?>

<div class="wrap wphostel-box" id="WPHostelBooking<?php echo $shortcode_id?>">
		<form class='wphostel-form-simple' method="post" onsubmit="return WPHostelValidateBooking(this);" id="WPHostelBooking<?php echo $shortcode_id?>">
			<p><label><?php _e('Select room:', 'wphostel')?></label> <br />
			<select name="room_id" onchange="WPHostelChangeRoom(this.value, this.form);">
				<?php foreach($rooms as $room):?>
					<option value="<?php echo $room->id?>" <?php if(!empty($_GET['room_id']) and $_GET['room_id'] == $room->id) echo 'selected'?>><?php echo stripslashes($room->title);?></option>
				<?php endforeach;?>
			</select></p>
			
			<p><label><?php _e('No. beds to book:', 'wphostel')?></label> <br /><input type="text" name="beds" value="<?php echo empty($booking->beds) ? 1 : $booking->beds?>" size="4"></p>
			<p><label><?php _e('Arriving:', 'wphostel')?></label> <br /><input type="text" size="10" value="<?php echo date($dateformat, strtotime($from_date))?>"  class="wphostelDatePicker" id="wphostelFromDate<?php echo $shortcode_id?>">
			<input type="hidden" name="from_date" id="alt_wphostelFromDate<?php echo $shortcode_id?>" value="<?php echo $from_date?>" ></p>
			<p><label><?php _e('Leaving:', 'wphostel')?></label> <br /><input type="text" size="10" value="<?php echo date($dateformat, strtotime($to_date))?>" class="wphostelDatePicker" id="wphostelToDate<?php echo $shortcode_id?>">
						<input type="hidden" name="to_date" id="alt_wphostelToDate<?php echo $shortcode_id?>" value="<?php echo $to_date?>"></p>
			
			<p><label><?php _e('Contact name:', 'wphostel')?></label> <br /><input type="text" name="contact_name" value="<?php echo empty($booking->contact_name) ? '' : $booking->contact_name?>"></p>
			<p><label><?php _e('Contact email:', 'wphostel')?></label> <br /><input type="text" name="contact_email" value="<?php echo empty($booking->contact_email) ? '' : $booking->contact_email?>"></p>
			<p><label><?php _e('Contact phone:', 'wphostel')?></label> <br /><input type="text" name="contact_phone" value="<?php echo empty($booking->contact_phone) ? '' : $booking->contact_phone?>"></p>
			<p><label><?php _e('You are:', 'wphostel')?></label> <br /><select name="contact_type">
			<option value="male"><?php _e('Male(s)', 'wphostel')?></option>
			<option value="female"><?php _e('Female(s)', 'wphostel')?></option>
			<option value="couple"><?php _e('Couple', 'wphostel')?></option>
			<option value="mixed"><?php _e('Mixed', 'wphostel')?></option>
			</select></p>
					
			<p align="center">
				<input type="button" class="wphostel-book-button" value="<?php _e('Make Reservation', 'wphostel')?>" onclick="WPHostelValidateBooking(this.form);">
			</p>
			<input type="hidden" name="wphostel_book" value="1">
			<input type="hidden" name="action" value="wphostel_ajax">
			<input type="hidden" name="type" value="book">
			<input type="hidden" name="shortcode_id" value="<?php echo $shortcode_id?>">
		</form>
	</div>
	
<script type="text/javascript" >
jQuery(document).ready(function() {    
    jQuery('.wphostelDatePicker').datepicker({
        dateFormat : '<?php echo dateformat_PHP_to_jQueryUI($dateformat);?>',        
        altFormat : "yy-mm-dd",       
        minDate: '<?php echo $min_date?>',        
        maxDate: '+<?php echo WPHOSTEL_MAX_DATE?>',  
    });
    
    jQuery(".wphostelDatePicker").each(function (idx, el) { 
	    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
	});
});	
</script>