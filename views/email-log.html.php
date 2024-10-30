<div class="wrap">
	<h1><?php _e('Email Log', 'wphostel');?></h1>
	
	<div class="postbox-container" style="width:73%;margin-right:2%;">
		<p><?php _e('This page shows what booking notification emails have been sent on each date. The last column will show the message status, i.e. response from the mailing server. If emails are not delivered or you see errors there you should contact your hosting support.', 'wphostel');?> </p>
	
		<div class="postbox wp-admin" style="padding:20px;">
			<form method="post">
				<p><label><?php _e('Log date:', 'wphostel')?></label> <input type="text" name="date" class="wphostelDatePicker" value="<?php echo $date?>">
				<input type="submit" value="<?php _e('Show log', 'wphostel')?>" class="button button-primary">
				&nbsp;			
				<?php _e('Automatically cleanup old logs after', 'wphostel')?> <input type="text" size="4" name="cleanup_days" value="<?php echo $cleanup_raw_log?>"> <?php _e('days', 'wphostel')?> <input type="submit" name="cleanup" value="<?php _e('Set Cleanup', 'wphostel')?>" class="button button-primary"> </p>
			</form>		
			
			<?php if(!sizeof($emails)):?>
				<p><?php _e('No emails have been sent on the selected date.', 'wphostel')?></p>
			<?php else:?>
				<table class="widefat">
					<tr><th><?php _e('Time', 'wphostel')?></th><th><?php _e('Sender', 'wphostel')?></th><th><?php _e('Receiver', 'wphostel')?></th>
	<th><?php _e('Subject', 'wphostel')?></th><th><?php _e('Response from the mailing server', 'wphostel')?></th></tr>
					<?php foreach($emails as $email):
						$class = ('alternate' == @$class) ? '' : 'alternate';?>
						<tr class="<?php echo $class?>"><td><?php echo date('H:i', strtotime($email->datetime))?></td>
						<td><?php echo htmlentities(stripslashes($email->sender))?></td>
						<td><?php echo stripslashes($email->receiver)?></td>
						<td><?php echo stripslashes($email->subject)?></td>
						<td><?php echo $email->status?></td></tr>
					<?php endforeach;?>
				</table>
			<?php endif;?>
		</div>
	
	</div>
	
	<div id="wphsotel-sidebar">
				<?php require(WPHOSTEL_PATH."/views/sidebar.html.php");?>
	</div>	
</div>	

<script type="text/javascript" >
jQuery(function(){
	jQuery('.wphostelDatePicker').datepicker({dateFormat: "yy-m-d"});
});
</script>