BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
CALSCALE:GREGORIAN
<?php 
foreach($bookings as $booking):?>
BEGIN:VEVENT
DTEND:<?php echo self :: dateToCal(strtotime($booking->to_date.' 12:00:00'))."\r\n"; ?>
UID:<?php echo $booking->id.'-'.$site_uid."\r\n"; ?>
DTSTAMP:<?php echo self :: dateToCal(time())."\r\n"; ?>
DESCRIPTION:<?php echo ($booking->is_static ? __('Unavailable dates', 'wphostel') : __('Booking', 'wphostel'))."\r\n" ?>
SUMMARY:<?php echo ($booking->is_static ? __('Unavailable dates', 'wphostel') : __('Booking', 'wphostel'))."\r\n" ?>
DTSTART:<?php echo self :: dateToCal(strtotime($booking->from_date.' 12:00:00'))."\r\n"; ?>
END:VEVENT
<?php endforeach;
?>END:VCALENDAR