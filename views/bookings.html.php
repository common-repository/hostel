<style type="text/css">
<?php wphostel_resp_table_css(700);?>
</style>

<div class="wrap">
	<h1><?php _e('Manage Bookings / Reservations', 'wphostel')?></h1>
	
	<div class="postbox-container" style="width:73%;margin-right:2%;">
	
	<p><?php _e('Showing', 'wphostel')?> <select onchange="window.location='admin.php?page=wphostel_bookings&type='+this.value;">
		<option value="upcoming" <?php if($type == 'upcoming') echo 'selected'?>><?php _e('Upcoming', 'wphostel')?></option>
		<option value="past" <?php if($type == 'past') echo 'selected'?>><?php _e('Past', 'wphostel')?></option>		
	</select> <?php _e('bookings', 'wphostel')?></p>
	<p><a href="admin.php?page=wphostel_bookings&do=add&type=<?php echo $type?>&offset=<?php echo $offset?>"><?php _e('Click here to manually add a new booking', 'wphostel')?></a></p>
	
	<?php if(!sizeof($bookings)):?>
		<p><?php _e('There are no bookings to show at the moment.', 'wphostel')?></p>
	<?php return false; 
	endif;?>
	<table class="widefat wphostel-table">
		<thead>
			<tr><th><a href="admin.php?page=wphostel_bookings&type=<?php echo $type?>&ob=tB.id&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('ID', 'wphostel');?></a></th><th><?php _e('Room/beds', 'wphostel')?></th><th><a href="admin.php?page=wphostel_bookings&type=<?php echo $type?>&ob=tB.contact_name&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Contact name', 'wphostel')?></a></th><th><a href="admin.php?page=wphostel_bookings&type=<?php echo $type?>&ob=tB.contact_email&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Contact email', 'wphostel')?></a></th>
			<th><a href="admin.php?page=wphostel_bookings&type=<?php echo $type?>&ob=tB.from_date&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Booking dates', 'wphostel')?></a></th>
			<th><a href="admin.php?page=wphostel_bookings&type=<?php echo $type?>&ob=tB.amount_paid&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Amount paid/due', 'wphostel')?></a></th>
			<th><a href="admin.php?page=wphostel_bookings&type=<?php echo $type?>&ob=tB.status&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Status', 'wphostel')?></a></th><th><?php _e('Action', 'wphostel')?></th></tr>
		</thead>
		<tbody>
		<?php foreach($bookings as $booking):
		$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>"><td><?php echo $booking->id?></td>
			<td><?php printf(__('%d beds in %s', 'wphostel'), $booking->beds, stripslashes($booking->room));?></td>
			<td><?php echo htmlspecialchars(stripslashes($booking->contact_name))?></td><td><?php echo $booking->contact_email?></td>
			<td><?php echo date(get_option('date_format'), strtotime($booking->from_date)).' - '.date(get_option('date_format'), strtotime($booking->to_date))?></td>
			<td><?php echo WPHOSTEL_CURRENCY." ".$booking->amount_paid." / ".WPHOSTEL_CURRENCY.' '.$booking->amount_due;?></td>
			<td><?php switch($booking->status):
			case 'active': _e('Active', 'wphostel'); break;
			case 'pending': _e('Pending', 'wphostel'); break;
			case 'cancelled': _e('Cancelled', 'wphostel'); break;
			endswitch;?></td>
			<td nowrap="true"><input type="button" value="<?php _e('Edit', 'wphostel')?>" onclick="window.location='admin.php?page=wphostel_bookings&do=edit&id=<?php echo $booking->id?>&type=<?php echo $type?>&offset=<?php echo $offset?>';">
			<?php if($booking->amount_due > 0 or $booking->status != 'active'):?>
				<input type="button" value="<?php _e('Mark as paid', 'wphostel');?>" onclick="wpHostelMarkPaid(<?php echo $booking->id?>);" class="button button-primary">
				<?php if($email_options['do_email_admin'] or $email_options['do_email_user']):?>
					<br> <input type="checkbox" id="bookingEmais<?php echo $booking->id?>"> <?php _e('Send emails when marking paid.', 'wphostel');?>
				<?php endif;?>
			<?php endif;?></td></tr>
		<?php endforeach;?>
		</tbody>
	</table>
	
	<p align="center"><?php if($offset > 0):?>
		<a href="admin.php?page=wphostel_bookings&type=<?php echo $type?>&offset=<?php echo $offset - $page_limit?>&ob=<?php echo @$_GET['ob']?>"><?php _e('[previous page]', 'wphostel')?></a>
	<?php endif;?> 
	<?php if($count > ($page_limit + $offset)):?>
		<a href="admin.php?page=wphostel_bookings&type=<?php echo $type?>&offset=<?php echo $offset + $page_limit?>&ob=<?php echo @$_GET['ob']?>"><?php _e('[next page]', 'wphostel')?></a>
	<?php endif;?>	
	</p>
	
	</div>
	
	<div id="wphsotel-sidebar">
				<?php require(WPHOSTEL_PATH."/views/sidebar.html.php");?>
	</div>	
</div>

<script type="text/javascript">
function wpHostelMarkPaid(id) {
	if(confirm("<?php _e('Are you sure?', 'wphostel')?>")) {
		var notice_str = '';
		if(jQuery('#bookingEmais' + id).length && jQuery('#bookingEmais' + id).is(':checked')) {
			notice_str = "&send_emails=1";
		}
		window.location = 'admin.php?page=wphostel_bookings&type=<?php echo $type?>&offset=<?php echo $offset;?>&mark_paid=1&id='+id + notice_str;
	}
}
<?php wphostel_resp_table_js();?>
</script>