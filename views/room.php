<h1><?php _e('Create/Edit Hostel Room', 'wphostel')?></h1>

<div class="wrap wphostel-box postbox">
	<form class='wphostel-form' onsubmit="return validateWPHostelForm(this);" method="post">
		<div><label><?php _e('Room title*', 'wphostel')?></label> <input type="text" name="title" value="<?php echo esc_attr(stripslashes($room->title ?? ''))?>" size="40">
		<div class="wphostel-help"><?php _e('For management purposes', 'wphostel')?></div></div>	
		<div><label><?php _e('Room type', 'wphostel')?></label> <select name="rtype">
			<option value="private" <?php if(!empty($room->rtype) and $room->rtype=='private') echo 'selected'?>><?php _e('Private', 'wphostel')?></option>		
			<option value="dorm" <?php if(!empty($room->rtype) and $room->rtype=='dorm') echo 'selected'?>><?php _e('Dorm', 'wphostel')?></option>
		</select></div>
		<div><label><?php _e('Number of beds', 'wphostel')?></label> <input type="text" name="beds" size="4" value="<?php echo @$room->beds?>"></div>
		<div><label><?php _e('Bathroom', 'wphostel')?></label> <select name="bathroom">
			<option value="ensuite" <?php if(!empty($room->bathroom) and $room->bathroom=='ensuite') echo 'selected'?>><?php _e('Ensuite', 'wphostel')?></option>		
			<option value="shared" <?php if(!empty($room->bathroom) and $room->bathroom=='shared') echo 'selected'?>><?php _e('Shared', 'wphostel')?></option>
		</select></div>
			<div><label><?php _e('Price:', 'wphostel')?></label> <?php echo WPHOSTEL_CURRENCY?> <input type="text" name="price" size="6" value="<?php echo @$room->price?>"> &nbsp;
			<input type="radio" name="price_type" value="per-bed" <?php if(empty($room->price_type) or $room->price_type == 'per-bed') echo 'checked'?>> <?php _e('Per person per night', 'wphostel')?>
			<input type="radio" name="price_type" value="per-room" <?php if(!empty($room->price_type) and $room->price_type == 'per-room') echo 'checked'?>> <?php _e('Per night for the whole room', 'wphostel')?>
		</div>
		<div><label><?php _e('Room description (optional):', 'wphostel')?></label> <?php wp_editor(stripslashes(@$room->description), 'description')?></div>
		<p><label><?php _e('Import external calendar (optional):', 'wphostel')?></label> <textarea name="ical_import" rows="5" cols="50"><?php echo empty($room->ical_import) ? '' : $room->ical_import;?></textarea> <?php _e('URLs of iCal / ics files, one per line', 'wphostel');?></p>
		<div><input type="submit" value="<?php _e('Save room details', 'wphostel')?>" class="button button-primary"></div>
		<input type="hidden" name="ok" value="1">
		<?php wp_nonce_field('wphostel_room');?>
	</form>
</div>

<script type="text/javascript" >
function validateWPHostelForm(frm) {
	if(frm.title.value=="") {
		alert("<?php _e('Please enter room title. This is important so you can recognize the room when editing it and when viewing its bookings', 'wphostel')?>");
		frm.title.focus();
		return false;
	}

	if(frm.beds.value=="" || isNaN(frm.beds.value)) {
		alert("<?php _e('Please enter number of beds in the room. Use only numbers.', 'wphostel')?>");
		frm.beds.focus();
		return false;
	}	
	
	if(frm.price.value=="" || isNaN(frm.price.value)) {
		alert("<?php _e('Please enter room price, numbers only', 'wphostel')?>");
		frm.beds.focus();
		return false;
	}	
	
	return true;
}
</script>
