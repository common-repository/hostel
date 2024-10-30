<form method="post">
	<p><?php _e('Arriving:', 'wphostel')?> <input type="text" size="10" value="<?php echo date($dateformat, strtotime($datefrom))?>"  class="wphostelDatePicker" id="wphostelFromDate<?php echo $shortcode_id?>"> <?php if(empty($atts['form_horizontal'])):?></p>
	<p><?php endif; // end if form not horizontal?><?php _e('Leaving:', 'wphostel')?> <input type="text" size="10" value="<?php echo date($dateformat, strtotime($dateto))?>" class="wphostelDatePicker" id="wphostelToDate<?php echo $shortcode_id?>"> <?php if(empty($atts['form_horizontal'])):?></p>
	<p><?php endif; // end if form not horizontal ?><input type="button" value="<?php _e('Show availability', 'wphostel')?>" onclick="validateHostelForm(this.form);" ></p>
	<input type="hidden" name="wphostel_from" value="<?php echo $datefrom?>" id="alt_wphostelFromDate<?php echo $shortcode_id?>">
	<input type="hidden" name="wphostel_to" value="<?php echo $dateto?>" id="alt_wphostelToDate<?php echo $shortcode_id?>">
</form>

<div id="wphostelRoomsTable<?php echo $shortcode_id?>" <?php if(empty($show_table)):?>style="display:none;"<?php endif;?>>
	<?php echo WPHostelRooms :: availability_table($shortcode_id, $atts);?>
</div>

<script type="text/javascript">
function validateHostelForm(frm) {
	startParts = frm.wphostel_from.value.split('-');
	var startDate = new Date(startParts[0], startParts[1], startParts[2]);
	endParts = frm.wphostel_to.value.split('-');
	var endDate = new Date(endParts[0], endParts[1], endParts[2]);
	
	daydiff = (endDate - startDate) / (1000*60*60*24);
	
	if(daydiff > <?php echo $max_days?>) {
		 alert("<?php printf(__('Please select up to %d days interval.', 'wphostel'), $max_days)?>");
		 return false;
	}
	
	<?php if(!empty($min_stay)):?>
	if(daydiff < <?php echo intval($min_stay)?>) {
		alert("<?php printf(__('Minimum stay of %d days is required', 'wphostel'), $min_stay);?>");
		return false;
	}
	<?php endif;?>
   jQuery('#wphostelRoomsTable<?php echo $shortcode_id?>').html("<?php _e('Please wait...', 'wphostel');?>");
	jQuery('#wphostelRoomsTable<?php echo $shortcode_id?>').show();
	data = {'action': 'wphostel_ajax', 'type': 'list_rooms', 'wphostel_from' : frm.wphostel_from.value, 
		'wphostel_to' : frm.wphostel_to.value, 'show_titles': '<?php echo $show_titles?>', 'shortcode_id' : '<?php echo $shortcode_id?>'};
	jQuery.post(wphostel_i18n.ajax_url, data, function(msg){
			jQuery('#wphostelRoomsTable<?php echo $shortcode_id?>').html(msg);
		});	
}

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