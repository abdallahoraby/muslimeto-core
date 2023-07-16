<?php


define("BOOKLY_WEEK_DAYS_INDEX", [
    1 => 'sun',
    2 => 'mon',
    3 => 'tue',
    4 => 'wed',
    5 => 'thu',
    6 =>'fri',
    7 => 'sat'
]);

// default week start with mon
define("WEEK_DAYS_INDEX", [
    1 => 'mon',
    2 => 'tue',
    3 => 'wed',
    4 => 'thu',
    5 =>'fri',
    6 => 'sat',
    7 => 'sun',
]);

define("SUN_WEEK_DAYS_INDEX", [
    0 => 'sun',
    1 => 'mon',
    2 => 'tue',
    3 => 'wed',
    4 => 'thu',
    5 =>'fri',
    6 => 'sat'
]);

// define bookly custom status
define("BOOKLY_CUSTOM_STATUS", [
    'approved' => '',
    'attended' => 'Attended',
    'attended-sl' => 'Attended (Student Late)',
    'attended-tl' => 'Attended (Teacher Late)',
    'no-show-s' => 'No Show Student',
    'extended-no-show-s' => 'Extended No Show Student',
    'cancelled' => 'Excused Student',
    'vacation-s' => 'Vacation Student',
    'no-show-t' => 'No Show Teacher',
    'excused-t' => 'Excused Teacher',
    'vacation-t' => 'Vacation Teacher',
    'holiday' => 'Holiday',
    'pending' => 'Pending',
    'rejected' => 'Rejected'
]);

function pre_dump($arr)
{

    echo '<pre>';
    var_dump($arr);
    echo '</pre>';

}

// function to display json in pretty way in html
function json_dump($json, $data_random_id = 1){
    ?>
    <pre style="background-color: #252532; margin: 2rem; border-radius: 10px; padding: 2rem; width: auto;" class="json_dump"><code class="json-container-<?= $data_random_id ?>" style="color: #30bdb6;"></code></pre>
    <script>

        var data_<?= $data_random_id ?> = <?= json_encode($json) ?>
        // document.getElementByClassName('json-container').innerHTML = JSON.stringify(data, null, 2);
        jQuery('.json-container-<?= $data_random_id ?>').html(JSON.stringify(data_<?= $data_random_id ?>, null, 2));

    </script>
    <?php
}

function convertToHoursMins($time) {

    $hours    = floor($time / 60);
    $minutes  = ($time % 60);


    if($minutes == 0){

        if($hours == 1){

            $output_format = '%02d hour ';

        }else{

            $output_format = '%02d hours ';
        }


//        $hoursToMinutes = sprintf($output_format, $hours);

        $hoursToMinutes = array(
            'hours' => (int) $hours,
            'minutes' => (int) $minutes
        );

    }else if($hours == 0){

        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }

        if($minutes == 1){

            $output_format  = ' %02d minute ';

        }else{

            $output_format  = ' %02d minutes ';
        }

//        $hoursToMinutes = sprintf($output_format,  $minutes);

        $hoursToMinutes = array(
            'hours' => (int) $hours,
            'minutes' => (int) $minutes
        );

    }else {

        if($hours == 1){

            $output_format = '%02d hour %02d minutes';

        }else{

            $output_format = '%02d hours %02d minutes';
        }

//        $hoursToMinutes = sprintf($output_format, $hours, $minutes);

        $hoursToMinutes = array(
            'hours' => (int) $hours,
            'minutes' => (int) $minutes
        );
    }

    return $hoursToMinutes;

}

function convertFullTimetoHoursMinutes ($full_time) {
//    $sTime   = '21:00:00';
    $sTime = $full_time;
    $oTime   = new DateTime($sTime);
    $aOutput = array();
    if ($oTime->format('G') > 0) {
//        $aOutput[] = $oTime->format('G') . ' hours';
        $aOutput['hours'] = $oTime->format('G');
    }
//    $aOutput[] = $oTime->format('i') . ' minutes';
    $aOutput['minutes'] = $oTime->format('i');
//    $aOutput[] = $oTime->format('s') . ' seconds';
//    echo implode(', ', $aOutput);
    return $aOutput;
}

function show_date($value, $key) {
    //echo '<br>' . $key, ': ', date('r', $value), PHP_EOL ;
}

function checkTimeOverlap($booking_stored_start_date, $booking_stored_end_date, $booking_user_start_date, $booking_user_end_date){

    $stored_booking_date_record = array('stored_start' => strtotime($booking_stored_start_date), 'stored_end' => strtotime($booking_stored_end_date));
    $user_booking_date_record = array('user_start' => strtotime($booking_user_start_date), 'user_end' => strtotime($booking_user_end_date));

//    array_walk($stored_booking_date_record, 'show_date');
//    array_walk($user_booking_date_record, 'show_date');

//    echo '<br>User_start: '. date('r', $user_booking_date_record['user_start']) ;
//    echo '<br>User_end: '. date('r', $user_booking_date_record['user_end']) ;
//    echo '<br>appointment_start: '. date('r', $stored_booking_date_record['stored_start']) ;
//    echo '<br>appointment_end: '. date('r', $stored_booking_date_record['stored_end']) ;


    $overlap_status = false;
    if( ($user_booking_date_record['user_start'] >= $stored_booking_date_record['stored_start'] ) && ( $user_booking_date_record['user_end'] <= $stored_booking_date_record['stored_end'] ) ){
        echo '<br> <span class="alert"> Conflict handling in appointment </span> <br>';
        $overlap_status = true;
    } else {
        echo '<br> No Conflict OK <br>';
        $overlap_status = false;
    }

    return $overlap_status;

}

function checkTeacherSchedule ($bookly_class_days, $schedule_start_dates, $schedule_end_dates, $user_start_time, $user_end_time ) {

    $overlap_status = [];

    $schedule_start_dates_keys = array_keys($schedule_start_dates);


    foreach ( $bookly_class_days as $bookly_class_day ):

        $search_day_index[] = array_search( $bookly_class_day, $schedule_start_dates_keys );


    endforeach;


    foreach ( $search_day_index as $day_index ):

        $day_name = $schedule_start_dates_keys[$day_index];

        for( $i=0; $i<count($schedule_start_dates[$day_name]); $i++ ):


            // get closest time for user_search_time in $schedule_start_dates for teacher and get it's index
            $timestamp = strtotime($user_start_time);
            $diff = null;
            $index = null;

            foreach ($schedule_start_dates[$day_name] as $key => $time) {
                $currDiff = abs($timestamp - strtotime($time));
                if (is_null($diff) || $currDiff < $diff) {
                    $index = $key;
                    $diff = $currDiff;
                }
            }

            $closest_start_time = $schedule_start_dates[$day_name][$index];
            $closes_end_time = $schedule_end_dates[$day_name][$index];

            $stored_booking_date_record = array('stored_start' => strtotime($closest_start_time), 'stored_end' => strtotime($closes_end_time));
            $user_booking_date_record = array('user_start' => strtotime($user_start_time), 'user_end' => strtotime($user_end_time));

            array_walk($stored_booking_date_record, 'show_date');
            array_walk($user_booking_date_record, 'show_date');

            if ( ( $user_booking_date_record['user_start'] >= $stored_booking_date_record['stored_start'] ) && ( $user_booking_date_record['user_end'] <= $stored_booking_date_record['stored_end'] ) )  {
                //echo '<br> No Conflict OK <br>';
                $overlap_status[] = false;
            } else {
                //echo '<br> <span class="alert"> Conflict handling in teacher schedule </span> <br>';
                $overlap_status[] = true;
            }

        endfor;


    endforeach;


    if (array_search(true, $overlap_status) !== false) {
        // then I found something
        return true;
    } else {
        // I did not find anything
        return false;
    }



}

// sort times ASC
function sortTimeArray ( $time_array ) {

    $time_array = array_unique( $time_array );
    foreach ( $time_array as $item ){
        $new_time_strtotime[$item] = strtotime( $item );
    }

    sort($new_time_strtotime);

    foreach ( $new_time_strtotime as $item ){
        $sorted_time_array[] = date ("H:i:s", $item);
    }

    return $sorted_time_array;

}

// sort dates ASC
function sortDateTimeArrayASC ( $date_time_array ) {

    $time_array = array_unique( $date_time_array );
    foreach ( $time_array as $item ){
        $new_time_strtotime[$item] = strtotime( $item );
    }

    sort($new_time_strtotime);

    foreach ( $new_time_strtotime as $item ){
        $sorted_time_array[] = date ("Y-m-d H:i:s", $item);
    }

    return $sorted_time_array;

}

// sort dates ASC with keys
function sortDateTimeArrayASCwithKeys ( $date_time_array ) {

    // Sort the array
    usort($date_time_array, function ($element1, $element2) {
        $datetime1 = strtotime($element1['start_date']);
        $datetime2 = strtotime($element2['start_date']);
        return $datetime1 - $datetime2;
    });

    return $date_time_array;

}

// sort dates DESC
function sortDateTimeArrayDESC ( $date_time_array ) {

    $time_array = array_unique( $date_time_array );
    foreach ( $time_array as $item ){
        $new_time_strtotime[$item] = strtotime( $item );
    }

    rsort($new_time_strtotime);

    foreach ( $new_time_strtotime as $item ){
        $sorted_time_array[] = date ("Y-m-d H:i:s", $item);
    }

    return $sorted_time_array;

}

// function to find closest date_time in a given array of date_time
function find_closest($array, $date){
    if( empty($array) ) return [];
    foreach($array as $day)
    {
        $interval[] = abs(strtotime($date) - strtotime($day));
    }
    asort($interval);
    $closest = key($interval);
    return $array[$closest];
}

// function to convert given date-time to wordpress timezone and calculate daylight hours
function convertTimeZoneDaylight ( $dateTime, $timezone ) {

    // get wp timezone
    $wp_timezone = get_option('timezone_string');

    $date = new DateTime($dateTime, new DateTimeZone($timezone));
    //echo $date->format('H:i:s') . "<br>";

    $date->setTimezone(new DateTimeZone($wp_timezone));
    $date_time = date('Y-m-d H:i:s' , strtotime( $date->format('Y-m-d H:i:s') ) );

    $currentYear = date('Y', strtotime( $date_time ) );
    $start_daylight_change = $currentYear . '-03-13 00:00:00';
    $end_daylight_change = $currentYear . '-04-03 00:00:00';


    if( ( strtotime($date_time) >= strtotime($start_daylight_change) ) && ( strtotime($date_time) <= strtotime($end_daylight_change) ) ):
        $date_time = date('Y-m-d H:i:s',strtotime('-1 hour',strtotime($date_time)));
    else:
        $date_time = date('Y-m-d H:i:s',strtotime($date_time));
    endif;

    return $date_time;

}

// function to convert given date-time to wordpress timezone
function convertTimeZone ( $dateTime, $timezone ) {

    // get wp timezone
    $wp_timezone = get_option('timezone_string');

    $date = new DateTime($dateTime, new DateTimeZone($timezone));
    //echo $date->format('H:i:s') . "<br>";

    $date->setTimezone(new DateTimeZone($wp_timezone));
    return $date->format('Y-m-d H:i:s');

}

// function to convert given date-time to  UTC timezone
function convertTimeZoneToUTC ( $dateTime, $timezone ) {


    $date = new DateTime($dateTime, new DateTimeZone($timezone));
    //echo $date->format('H:i:s') . "<br>";

    $date->setTimezone(new DateTimeZone('UTC'));
    return $date->format('Y-m-d H:i:s');

}

// function to convert given date-time to  UTC timezone for stored appointments
function convertTimeZoneToUTCforStored ( $dateTime, $timezone ) {

    $date = new DateTime($dateTime, new DateTimeZone($timezone));
    //echo $date->format('H:i:s') . "<br>";

    $date->setTimezone(new DateTimeZone('UTC'));
    $converted_to_utc = date('Y-m-d H:i:s' , strtotime( $date->format('Y-m-d H:i:s') ) );

    $currentYear = date('Y', strtotime( $converted_to_utc ) );
    $start_daylight_change = $currentYear . '-03-13 02:00:00';
    $end_daylight_change = $currentYear . '-11-06 02:00:00';


    if( ( strtotime($converted_to_utc) >= strtotime($start_daylight_change) ) && ( strtotime($converted_to_utc) <= strtotime($end_daylight_change) ) ):
        $converted_to_utc = date('Y-m-d H:i:s',strtotime('+1 hour',strtotime($converted_to_utc)));
    else:
        $converted_to_utc = date('Y-m-d H:i:s',strtotime($converted_to_utc));
    endif;

    return $converted_to_utc;

}

// function to convert given date-time to given user timezone
function convertToUserTimeZone ( $dateTime, $timezone ) {
    // get wp timezone
    $wp_timezone = get_option('timezone_string');
    date_default_timezone_set($wp_timezone);
    $datetime = new DateTime($dateTime);
    $la_time = new DateTimeZone($timezone);
    $datetime->setTimezone($la_time);
    return $datetime->format('Y-m-d H:i:s');
}

// function to convert given date-time from timezone 1 to timezone 2
function convertTimezone1ToTimezone2 ( $dateTime, $timezone1, $timezone2 ) {
    $date_timezone1 = new DateTime($dateTime, new DateTimeZone($timezone1));
    $date_timezone1->setTimezone( new DateTimeZone($timezone2));
    return $date_timezone1->format('Y-m-d H:i:s');
}

/**
 * What is the overlap, in minutes, of two time periods?
 *
 * @param $startDate1   string
 * @param $endDate1     string
 * @param $startDate2   string
 * @param $endDate2     string
 * @returns int     Overlap in minutes
 */
function overlapInMinutes($startDate1, $endDate1, $startDate2, $endDate2)
{
    // Figure out which is the later start time
    $lastStart = $startDate1 >= $startDate2 ? $startDate1 : $startDate2;
    // Convert that to an integer
    $lastStart = strtotime($lastStart);

    // Figure out which is the earlier end time
    $firstEnd = $endDate1 <= $endDate2 ? $endDate1 : $endDate2;
    // Convert that to an integer
    $firstEnd = strtotime($firstEnd);

    // Subtract the two, divide by 60 to convert seconds to minutes, and round down
    $overlap = floor( ($firstEnd - $lastStart) / 60 );

    // If the answer is greater than 0 use it.
    // If not, there is no overlap.

    $overlap_in_minutes = $overlap > 0 ? $overlap : 0;
    return $overlap_in_minutes;
}

function getReccurringDates($start_date, $number_of_repeats, $return_format, $user_timezone){
    // get recurring dates start from that day
    // Array for dates
    $dates = [];

    // Get next thursday
    $date = strtotime($start_date);
    $dates[] = $date;
    $currentYear = date('Y', $date);
    $currentMonth = date('m', $date);

//    if( $currentMonth > 6 && $currentMonth > 12){
//        $end_recurring_date = strtotime( '06/30/' . $currentYear );
//    } else{
//        $end_recurring_date = strtotime('06/30/' . ($currentYear + 1) );
//    }



    // stop after 6 months fromm starting date
    $end_recurring_date = strtotime( date("m/d/Y", strtotime("+6 months", strtotime($start_date))) );
    // we should get end history value from plugin setting page
    //$end_recurring_date = strtotime('06/30/2022' );


    // Get the next four
    for ($i = 0; $i < $number_of_repeats; $i++)
    {
        $date = strtotime('+1 week', $date);
        if( $date <= $end_recurring_date){
            $dates[] = $date;
        }

    }


    // Echo dates
    foreach ($dates as $date)
        $nextDays[] = convertTimeZoneToUTC( date($return_format, $date), $user_timezone);

    unset($nextDays[0]);
    return $nextDays;
}

function getReccurringDatesforCheck($start_date, $number_of_repeats, $return_format, $user_timezone){
    // get recurring dates start from that day
    // Array for dates
    $dates = [];

    // Get next thursday
    $date = strtotime($start_date);
    $dates[] = $date;
    $currentYear = date('Y', $date);
    $currentMonth = date('m', $date);


    // stop after 6 months fromm starting date
    $end_recurring_date = strtotime( date("m/d/Y", strtotime("+6 months", strtotime($start_date))) );
    // we should get end history value from plugin setting page
    //$end_recurring_date = strtotime('06/30/2022' );


    // Get the next four
    for ($i = 0; $i < $number_of_repeats; $i++)
    {
        $date = strtotime('+1 week', $date);
        if( $date <= $end_recurring_date){
            $dates[] = $date;
        }

    }


    // Echo dates
    foreach ($dates as $date)
        $nextDays[] = convertTimeZone( date($return_format, $date) , $user_timezone); // convert timezone heer

    unset($nextDays[0]);
    return $nextDays;
}

// get recurring days from starting day until end day given
function getReccurringDatesUntil($start_date, $end_date, $return_format ){
    // get recurring dates start from that day
    // Array for dates
    $dates = [];

    // Get next thursday
    $date = strtotime($start_date);
    $dates[] = $date;
    $currentYear = date('Y', $date);
    $currentMonth = date('m', $date);

    // stop after 6 months fromm starting date
    $end_date = date("m/d/Y", strtotime($end_date));
    // we should get end history value from plugin setting page
    $end_recurring_date = strtotime($end_date );


    // Get the next four
    for ($i = 0; $i < 999; $i++)
    {
        $date = strtotime('+1 week', $date);
        if( $date <= $end_recurring_date){
            $dates[] = $date;
        }

    }


    // Echo dates
    foreach ($dates as $date)
        $nextDays[] = date($return_format, $date);

    unset($nextDays[0]);
    return $nextDays;
}

// get recurring days from starting day until end day given
function getReccurringDatesUntilinTimezone($start_date, $end_date, $return_format , $user_timezone){
    // get recurring dates start from that day
    // Array for dates
    $dates = [];

    // Get next thursday
    $date = strtotime($start_date);
    $dates[] = $date;
    $currentYear = date('Y', $date);
    $currentMonth = date('m', $date);

    // stop after 6 months fromm starting date
    $end_date = date("m/d/Y", strtotime($end_date));
    // we should get end history value from plugin setting page
    $end_recurring_date = strtotime($end_date );


    // Get the next four
    for ($i = 0; $i < 999; $i++)
    {
        $date = strtotime('+1 week', $date);
        if( $date <= $end_recurring_date){
            $dates[] = $date;
        }

    }


    // Echo dates
    foreach ($dates as $date)
        $nextDays[] = convertTimeZoneToUTC( date($return_format, $date) , $user_timezone);

    unset($nextDays[0]);
    return $nextDays;
}

// get week number for given date
function getweekNumber ($date) {
    // get week number
    $dateTime = new DateTime($date);
    $weekNum = $dateTime->format("W");
    return $weekNum;
}

// get day date next to certain date
function getNextDayDate ($refrence_date, $day_name) {

    $timestamp = strtotime($refrence_date);

    return date('Y-m-d',
        strtotime("next " . $day_name . date('Y-m-d', $timestamp), $timestamp)
    );
}

// get day date prev to certain date
function getPrevDayDate ($refrence_date, $day_index) {

    $timestamp = strtotime($refrence_date);

    return date("Y-m-d", strtotime($day_index ." day", $timestamp));
}

// function to check teacher schedule using teacher_id
function checkTeacherIDSchedule( $bookly_teacher_id, $new_effective_start_time, $new_effective_end_time, $new_effective_day_name ){



    global $wpdb;

    $effective_week_number = date("W", strtotime( date('Y-m-d', time()) ));
    $effective_year_number = date('Y', strtotime( date('Y-m-d', time()) ));


    // get staff timezone
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $staff_results = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_table WHERE id = {$bookly_teacher_id}"
    );
    $wpdb->flush();

    $staff_timezone = $staff_results[0]->time_zone;



    // check for time overlap with teacher schedule items time
    $bookly_staff_schedules_table = $wpdb->prefix . 'bookly_staff_schedule_items';
    $staff_schedule_results = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_schedules_table WHERE staff_id = {$bookly_teacher_id}"
    );
    $wpdb->flush();

    foreach ($staff_schedule_results as $staff_schedule_result): // loop schedule items

        if( !is_null( $staff_schedule_result->start_time ) OR !is_null( $staff_schedule_result->end_time ) ):

            $schedule_item_id = (int) $staff_schedule_result->id;
            $day_name = BOOKLY_WEEK_DAYS_INDEX[$staff_schedule_result->day_index];

            // insert start schedule and end schedule time in arrays
            $start_schedule_after_breaks[$day_name][] = convertTimeZone( $day_name.' '.$staff_schedule_result->start_time, $staff_timezone );

            // new line
            $start_schedule_after_breaks_new[] = convertTimeZone( $day_name.' '.$staff_schedule_result->start_time, $staff_timezone );
            $start_datetime[] = $day_name . ' ' . $staff_schedule_result->start_time;
            $start_time_His[] =  $staff_schedule_result->start_time;

            // get breaks item for each schedule_item_id
            $bookly_staff_schedules_breaks_table = $wpdb->prefix . 'bookly_schedule_item_breaks';
            $staff_schedule_breaks_results = $wpdb->get_results(
                "SELECT * FROM $bookly_staff_schedules_breaks_table WHERE staff_schedule_item_id = {$schedule_item_id}"
            );
            $wpdb->flush();


            if( !empty($staff_schedule_breaks_results) ):
                foreach ( $staff_schedule_breaks_results as $staff_schedule_breaks_result ):
                    $breaks_start[$day_name][] = $staff_schedule_breaks_result->start_time;
                    $breaks_end[$day_name][] = $staff_schedule_breaks_result->end_time;
                endforeach;


                $sorted_breaks_start = sortTimeArray($breaks_start[$day_name]);
                $sorted_breaks_end = sortTimeArray($breaks_end[$day_name]);


                for( $i=0; $i<count($sorted_breaks_start); $i++ ):
                    $start_schedule_after_breaks[$day_name][] = convertTimeZone( $day_name.' '.$sorted_breaks_end[$i], $staff_timezone);
                    $end_schedule_after_breaks[$day_name][] = convertTimeZone( $day_name.' '.$sorted_breaks_start[$i], $staff_timezone);

                    //new line
                    $start_schedule_after_breaks_new[] = convertTimeZone( $day_name.' '.$sorted_breaks_end[$i], $staff_timezone);
                    $start_datetime[] = $day_name.' '.$sorted_breaks_end[$i];
                    $start_time_His[] = $sorted_breaks_end[$i];

                    // new line
                    $end_schedule_after_breaks_new[] = convertTimeZone( $day_name.' '.$sorted_breaks_start[$i], $staff_timezone);
                    $end_datetime[] = $day_name.' '.$sorted_breaks_start[$i];
                    $end_time_His[] = $sorted_breaks_start[$i];
                endfor;


            endif;



            // get end_schedule_item_hours
            $end_schedule_item_hour = (int) explode(':', $staff_schedule_result->end_time)[0];
            if( $end_schedule_item_hour >= 24 ):
                $end_schedule_after_breaks[$day_name][] = convertTimeZone( $day_name. ' 23:59:00', $staff_timezone);
                //new lines
                // if $end_schedule_item_hour >= 24 ===> get $day_name index and increase +1 ===> then get new day_name
                $day_name_index = (int) array_search( $day_name, BOOKLY_WEEK_DAYS_INDEX);
                $next_day_name = BOOKLY_WEEK_DAYS_INDEX[$day_name_index + 1];
                $end_schedule_after_breaks_new[] = convertTimeZone( $next_day_name. ' 00:00:00', $staff_timezone);
                $end_datetime[] = $next_day_name. ' 00:00:00';
                $end_time_His[] = '00:00:00';
            else:
                $end_schedule_after_breaks[$day_name][] = convertTimeZone( $day_name. ' ' .$staff_schedule_result->end_time, $staff_timezone);
                // new lines
                $end_schedule_after_breaks_new[] = convertTimeZone( $day_name. ' ' .$staff_schedule_result->end_time, $staff_timezone);
                $end_datetime[] = $day_name. ' ' .$staff_schedule_result->end_time;
                $end_time_His[] = $staff_schedule_result->end_time;
            endif;


        endif;

    endforeach;

    ///////////////////////////// NEW LOOOOP
    for ( $i=0; $i<count($start_datetime); $i++ ):

        $schedule_start_time = date( 'Y-m-d H:i:s', strtotime( convertTimeZone( date( 'Y-m-d H:i:s', strtotime( $start_datetime[$i] )  ), $staff_timezone) ) );
        $schedule_end_time =  date( 'Y-m-d H:i:s', strtotime( convertTimeZone( date( 'Y-m-d H:i:s', strtotime( $end_datetime[$i] )  ), $staff_timezone ) ) );
        $start_day = strtolower( date( 'D', strtotime( $start_datetime[$i] ) ) );
        $end_day = strtolower( date( 'D', strtotime( $end_datetime[$i] ) ) );
        $week_start_day_index = array_search($start_day, SUN_WEEK_DAYS_INDEX);
        $week_end_day_index = array_search($end_day, SUN_WEEK_DAYS_INDEX);

        $gendate_start = new DateTime();
        $gendate_end = new DateTime();
        $gendate_start->setISODate($effective_year_number,$effective_week_number,$week_start_day_index); //year , week num , day
        $gendate_end->setISODate($effective_year_number,$effective_week_number,$week_end_day_index); //year , week num , day
        $start_day_date = $gendate_start->format('Y-m-d');
        $end_day_date = $gendate_end->format('Y-m-d');

        $user_day_index = array_search($new_effective_day_name, SUN_WEEK_DAYS_INDEX);
        $gendate_start_user = new DateTime();
        $gendate_start_user->setISODate( $effective_year_number, $effective_week_number, $user_day_index );
        $gendate_start_date_user = $gendate_start_user->format('Y-m-d');
        $user_start_date =  date( 'Y-m-d H:i:s', strtotime( $gendate_start_date_user . ' ' . $new_effective_start_time ) );
        $user_end_date = date( 'Y-m-d H:i:s', strtotime( $gendate_start_date_user . ' ' . $new_effective_end_time ) );


        $user_start_datetime = date('Y-m-d H:i:s', strtotime( $start_day_date . ' ' . $new_effective_start_time ) );
        $user_end_datetime = date('Y-m-d H:i:s', strtotime( $start_day_date . ' ' . $new_effective_end_time ) );

        $teacher_start_datetime = convertTimeZone( date( 'Y-m-d H:i:s', strtotime( $start_day_date . ' ' . $start_time_His[$i] ) ), $staff_timezone );
        $teacher_end_datetime = convertTimeZone( date( 'Y-m-d H:i:s', strtotime( $end_day_date . ' ' . $end_time_His[$i] ) ), $staff_timezone );


        //echo  'start ' . $teacher_start_datetime . ' end ' . $teacher_end_datetime . ' - teacher: ' . $bookly_teacher_id .'<br>';
        //echo 'start ' . $user_start_date . ' end ' . $user_end_date .' user <br>';


        if( (  strtotime($user_start_date) ) >= strtotime( $teacher_start_datetime) && strtotime($user_end_date) <= ( strtotime( $teacher_end_datetime)  ) ):
            //echo 'No Conflict for teacher '. $bookly_teacher_id .' from '. $new_effective_start_time . ' to ' . $new_effective_end_time .'<hr>';
            $timeslot_fit[] = true;
        else:
            //echo ' <span class="alert"> booking doesnt fit in teacher '. $bookly_teacher_id .' schedule on '. $new_effective_start_time . ' to ' . $new_effective_end_time .' </span> <hr>';
            $timeslot_fit[] = false;
        endif;


    endfor;

    /////////////////////////// END NEW LOOP


//    for ( $i=0; $i<count($start_schedule_after_breaks_new); $i++ ):
//        $start_day = strtolower( date( 'D', strtotime( $start_schedule_after_breaks_new[$i] ) ) );
//        $schedule_start_time = date( 'Y-m-d H:i:s', strtotime( $start_schedule_after_breaks_new[$i] ) );
//        $schedule_end_time = date( 'Y-m-d H:i:s', strtotime( $end_schedule_after_breaks_new[$i] ) );
//
//        $user_start_date = date('Y-m-d H:i:s', strtotime( $new_effective_day_name . ' ' . $new_effective_start_time) );
//        $user_end_date = date('Y-m-d H:i:s', strtotime( $new_effective_day_name . ' ' . $new_effective_end_time) );
//
//        //echo 'teacher: ' . $bookly_teacher_id . ' on: '. $start_day .' -- start: ' . $schedule_start_time . ' --- end: '. $schedule_end_time .'<br>';
//        //echo 'user request on: ' . $new_effective_day_name . ' -- start: ' . $user_start_date . ' --- end: ' . $user_end_date .'<br>';
//        if( (  strtotime($user_start_date) ) >= strtotime( $schedule_start_time) && strtotime($user_end_date) <= ( strtotime( $schedule_end_time)  ) ):
//            //echo 'No Conflict for teacher '. $bookly_teacher_id .' from '. $new_effective_start_time . ' to ' . $new_effective_end_time .'<hr>';
//            $timeslot_fit[] = true;
//        else:
//            //echo ' <span class="alert"> booking doesnt fit in teacher '. $bookly_teacher_id .' schedule on '. $new_effective_start_time . ' to ' . $new_effective_end_time .' </span> <hr>';
//            $timeslot_fit[] = false;
//        endif;
//
//    endfor;


//    foreach ( $start_schedule_after_breaks as $key=>$value ):
//
//        if( $key === $new_effective_day_name ):
//            for( $i=0; $i<count($value); $i++ ):
//                $day_schedule_start = $value[$i];
//                $day_schedule_start = date('H:i:s', strtotime($day_schedule_start));
//                $day_schedule_end = $end_schedule_after_breaks[$key][$i];
//                $day_schedule_end = date('H:i:s', strtotime($day_schedule_end));
//
//
////                echo ' User: ------- ';
////                echo 'new_effective_start_time: ' . date('Y-m-d H:i:s', strtotime($new_effective_day_name . ' ' . $new_effective_start_time)) .'<br>';
////                echo 'new_effective_end_time: ' . date('Y-m-d H:i:s', strtotime($new_effective_day_name . ' ' . $new_effective_end_time)) .'<br>';
////                echo 'Teacher: -------' . $bookly_teacher_id ;
////                echo 'day_schedule_start: ' .  date('Y-m-d H:i:s', strtotime($new_effective_day_name . ' ' . $day_schedule_start)) .'<br>';
////                echo 'day_schedule_end: ' .  date('Y-m-d H:i:s', strtotime($new_effective_day_name . ' ' . $day_schedule_end) ) .'------- <br>';
//
//
//
//                if( (  strtotime($new_effective_day_name . ' ' . $new_effective_start_time) ) >= strtotime( $new_effective_day_name . ' ' .$day_schedule_start) && strtotime($new_effective_day_name . ' ' . $new_effective_end_time) <= ( strtotime( $new_effective_day_name . ' ' .$day_schedule_end)  ) ):
//                    //echo 'No Conflict for teacher '. $bookly_teacher_id .' on '. $new_effective_day_name . ' ' . $new_effective_start_time .'<br>';
//                    $timeslot_fit[] = true;
//                else:
//                    //echo ' <span class="alert"> booking doesnt fit in teacher '. $bookly_teacher_id .' schedule on '. $new_effective_day_name . ' ' . $new_effective_start_time .' </span> <br>';
//                    $timeslot_fit[] = false;
//                endif;
//
//
//            endfor;
//        else:
//            $timeslot_fit[] = false;
//           // echo 'teacher: '. $bookly_teacher_id . '--' .$key .'!==' .$new_effective_day_name .'<br>';
//        endif;
//
//
//
//    endforeach;


    if (array_search(true, $timeslot_fit) !== false): // teacher schedule has at least one fit
        return true;
    else:
        return false;
    endif;



}

/**
 * Generate unique value for entity field.
 */
function generateUniqueToken( )
{
    $token = md5( uniqid( time(), true ) );
    return $token;
}

// Bulk inserts records into a table using WPDB.  All rows must contain the same keys.
// Returns number of affected (inserted) rows.
function wpdb_bulk_insert($table, $rows) {
    global $wpdb;

    // Extract column list from first row of data
    $columns = array_keys($rows[0]);
    asort($columns);
    $columnList = '`' . implode('`, `', $columns) . '`';

    // Start building SQL, initialise data and placeholder arrays
    $sql = "INSERT INTO `$table` ($columnList) VALUES\n";
    $placeholders = array();
    $data = array();

    // Build placeholders for each row, and add values to data array
    foreach ($rows as $row) {
        ksort($row);
        $rowPlaceholders = array();

        foreach ($row as $key => $value) {
            $data[] = $value;
            $rowPlaceholders[] = is_numeric($value) ? '%d' : '%s';
        }

        $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
    }

    // Stitch all rows together
    $sql .= implode(",\n", $placeholders);

    // Run the query.  Returns number of affected rows.
    return $wpdb->query($wpdb->prepare($sql, $data));
}

// function to update row with array of data
function updateRecord($table_name, $data_array_to_update) {
    global $wpdb;
    if( empty($table_name) || empty($data_array_to_update) ):
        return false;
    endif;

    $primary_key = key(array_slice($data_array_to_update, 0, 1));
    $id_to_update = $data_array_to_update[$primary_key];
    $wpdb->update($table_name, $data_array_to_update, array('id'=>$id_to_update));
}

// function to get timezone offset in minutes with a negative sign for given user timezone
function getNowTimeZoneOffset ($user_timezone) {
    $dtz = new DateTimeZone($user_timezone);
    $bookly_user_datetime = new DateTime('now', $dtz);
    return -1 * ( $dtz->getOffset( $bookly_user_datetime )/60 ); // offset in minutes reversed in negative sign e.g: if ( 300 will be -300 )
}

// function to get wpdb errors
function my_print_error(){

    global $wpdb;

    if($wpdb->last_error !== '') :

        $str   = htmlspecialchars( $wpdb->last_result, ENT_QUOTES );
        $query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );

        print "<div id='error'>
        <p class='wpdberror'><strong>WordPress database error:</strong> [$str]<br />
        <code>$query</code></p>
        </div>";

    endif;

}

// user-defined comparison function
// based on timestamp
function compareByTimeStamp($time1, $time2)
{
    if (strtotime($time1) > strtotime($time2))
        return 1;
    else if (strtotime($time1) < strtotime($time2))
        return -1;
    else
        return 0;
}

function redux_global_var () {
    if( class_exists( 'Redux' ) ):
        return get_option('muslimeto');
    else:
        return array(
            'SP_PARENT_FORM_ID' => 0,
            'SCHEDULE_FORM_ID' => 0,
            'LEARNERS_FORM_ID' => 0
        );
    endif;
}

// single program parent form id
function SP_PARENT_FORM_ID () {
    $muslimeto = redux_global_var();
    if( empty($muslimeto) ):
        return 0;
    else:
        return (int) $muslimeto['SP_PARENT_FORM_ID'];
    endif;
}

// schedules form id ( child of single program form )
function SCHEDULE_FORM_ID () {
    $muslimeto = redux_global_var();
    if( empty($muslimeto) ):
        return 0;
    else:
        return (int) $muslimeto['SCHEDULE_FORM_ID'];
    endif;
}

// learners form id ( child of single program form )
function LEARNERS_FORM_ID () {
    $muslimeto = redux_global_var();
    if( empty($muslimeto) ):
        return 0;
    else:
        return (int) $muslimeto['LEARNERS_FORM_ID'];
    endif;
}

// staff form id
function STAFF_FORM_ID () {
    $muslimeto = redux_global_var();
    if( empty($muslimeto) ):
        return 0;
    else:
        return (int) $muslimeto['STAFF_FORM_ID'];
    endif;
}

// get gravity form consumer key
function GF_CONSUMER_KEY () {
    $muslimeto = redux_global_var();
    if( empty($muslimeto) ):
        return 0;
    else:
        return $muslimeto['gf_rest_api']['consumer_key'];
    endif;
}

// get gravity form secret key
function GF_CONSUMER_SECRET () {
    $muslimeto = redux_global_var();
    if( empty($muslimeto) ):
        return 0;
    else:
        return $muslimeto['gf_rest_api']['consumer_secret'];
    endif;
}

// set GF rest header
function REST_HEADERS(){
    $REST_USERNAME = GF_CONSUMER_KEY();
    $REST_PASSWORD = GF_CONSUMER_SECRET();
    return array( 'Authorization' => 'Basic ' . base64_encode( "{$REST_USERNAME}:{$REST_PASSWORD}" ) ) ;
}

function deleteBBgroup($bb_group_id){

    $delete_group = groups_delete_group( $bb_group_id );
    return $delete_group;

}

function deleteLDgroup ($linked_ld_group_id) {
    if( !empty($linked_ld_group_id) ):
        $delete_ld_group = wp_delete_post( $linked_ld_group_id, true );
        if( !empty( $delete_ld_group ) ):
            return true;
        else:
            return false;
        endif;
    else:
        return false;
    endif;
}

// check if user has certain role
function user_has_role($user_id, $role_name)
{
    $user_meta = get_userdata($user_id);
    $user_roles = $user_meta->roles;
    if( !empty($user_roles) ):
        return in_array($role_name, $user_roles);
    else:
        return false;
    endif;
}

// function to check if staff id in same team leader for this teacher
function get_team_teachersX($staff_id){
    // get teacher role from GF entries
    global $wpdb;
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $staff_form_id = STAFF_FORM_ID();
    $user_obj = get_user_by('id', $staff_id);
    $user_email = $user_obj->user_email;



    // search entries for user with email
    // get entry from GF staff form where email equal to teacher email
    $gf_results = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE meta_key = 4 AND meta_value LIKE '%{$user_email}%' AND form_id = {$staff_form_id}"
    );
    $wpdb->flush();

    $staff_entry_id = '';
    foreach($gf_results as $single_entry):
        // check if entry is active and exist on gr_entry
        $check_staff_entry_id = $single_entry->entry_id;
        $gf_check_results = $wpdb->get_results(
            "SELECT * FROM $gf_entry_table WHERE id = {$check_staff_entry_id} AND status = 'active'"
        );
        $wpdb->flush();
        if( !empty($gf_check_results)):
            $staff_entry_id =  $single_entry->entry_id;
        endif;

    endforeach;


    if( !empty($staff_entry_id) && $staff_entry_id !== '' ):
        // get role
        $gf_role_results = $wpdb->get_results(
            "SELECT * FROM $gf_meta_table WHERE meta_key = 33 AND entry_id = {$staff_entry_id} AND form_id = {$staff_form_id}"
        );
        $wpdb->flush();
        if( !empty($gf_role_results) ):
            $staff_role = $gf_role_results[0]->meta_value;
        endif;
    else:
        return false;
    endif;

    $user_is_team_leader = user_has_role(get_current_user_id(), 'team_leader');

    if( $user_is_team_leader ):
        // user can show list of his team
        // get joined team
        $gf_team_results = $wpdb->get_results(
            "SELECT * FROM $gf_meta_table WHERE meta_key = 43 AND entry_id = {$staff_entry_id} AND form_id = {$staff_form_id}"
        );
        $wpdb->flush();
        $staff_team = $gf_team_results[0]->meta_value;

        // get all teachers has same team name
        $gf_teachers_results = $wpdb->get_results(
            "SELECT * FROM $gf_meta_table WHERE meta_key = 43 AND meta_value = '{$staff_team}' AND form_id = {$staff_form_id}"
        );
        $wpdb->flush();



        if( !empty($gf_teachers_results) ):

            foreach ( $gf_teachers_results as $gf_teachers_result ):
                $entry_id = $gf_teachers_result->entry_id;
                // check if entry status is active
                $gf_status_results = $wpdb->get_results(
                    "SELECT * FROM $gf_entry_table WHERE id = {$entry_id}"
                );
                $wpdb->flush();
                $entry_status = $gf_status_results[0]->status;

                if( $entry_status === 'active' ):

                    // get teacher email from this entry
                    $gf_emails_results = $wpdb->get_results(
                        "SELECT * FROM $gf_meta_table WHERE meta_key = 4 AND entry_id = {$entry_id} AND form_id = {$staff_form_id}"
                    );
                    $wpdb->flush();
                    $staff_emails[] = $gf_emails_results[0]->meta_value;


                endif;
            endforeach;

            // make a list options from these emails
            if( !empty($staff_emails) ):
                foreach ( $staff_emails as $staff_email ):
                    // get teacher id from staff table
                    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
                    $bookly_staff_results = $wpdb->get_results(
                        "SELECT * FROM $bookly_staff_table WHERE email = '{$staff_email}'"
                    );
                    $wpdb->flush();
                    $bookly_staff_id = (int) $bookly_staff_results[0]->id;
                    $bookly_staff_full_name = $bookly_staff_results[0]->full_name;
                    $team_teachers[] = array(
                        'email' => $staff_email,
                        'bookly_staff_id' => $bookly_staff_id,
                        'bookly_full_name' => $bookly_staff_full_name
                    );
                endforeach;
            endif;

        endif;

    endif;
    if( !empty($team_teachers) ):
        return $team_teachers;
    else:
        return false;
    endif;
}

// function to get teachers under team leader from HR component ( reporting to )
function get_team_teachers($wp_user_id = null)
{
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $wphr_hr_employees_table = $wpdb->prefix . 'wphr_hr_employees';
    // wp_user_id is team leader id
    if( empty($wp_user_id) ):
        $wp_user_id = get_current_user_id();
    endif;

    $team_teachers = [];

    // get all employees

    $wphr_hr_employees_result = $wpdb->get_results(
        "SELECT user_id, reporting_to FROM $wphr_hr_employees_table WHERE reporting_to = {$wp_user_id}"
    );
    $wpdb->flush();


    if( empty($wphr_hr_employees_result) ) return false;

    foreach ( $wphr_hr_employees_result as $employee_id ):
        // get teacher id from staff table

        $bookly_staff_results = $wpdb->get_results(
            "SELECT id, wp_user_id, full_name, email FROM $bookly_staff_table WHERE wp_user_id = {$employee_id->user_id}"
        );
        $wpdb->flush();
        $bookly_staff_id = (int) $bookly_staff_results[0]->id;
        $bookly_staff_full_name = $bookly_staff_results[0]->full_name;
        $staff_email = $bookly_staff_results[0]->email;
        $team_teachers[] = array(
            'email' => $staff_email,
            'bookly_staff_id' => $bookly_staff_id,
            'bookly_full_name' => $bookly_staff_full_name
        );
    endforeach;


    if( !empty($team_teachers) ):
        return $team_teachers;
    else:
        return false;
    endif;

}

// function to get BB group type from GF entries
function getBBgroupType($bb_group_id) {
    // get group type from GF entries
    global $wpdb;
    $sp_form_id = SP_PARENT_FORM_ID();
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT entry_id FROM $gf_meta_table WHERE meta_key = 7 AND meta_value = {$bb_group_id} AND form_id ={$sp_form_id}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        $sp_entry_id = $gf_result[0]->entry_id;

        if( !empty($sp_entry_id) ):
            // get group type
            $gf_type_result = $wpdb->get_results(
                "SELECT * FROM $gf_meta_table WHERE meta_key = 9 AND entry_id = {$sp_entry_id} AND form_id ={$sp_form_id}"
            );
            $wpdb->flush();

            $group_type = $gf_type_result[0]->meta_value;
        else:
            $group_type = false;
        endif;
    else:
        $group_type = false;
    endif;

    return $group_type;
}

// function to get bookly staff_ids assigned to this customer
function getLearnerStaff($wp_user_id){
    // get learner staff
    global $wpdb;
    $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
    $customer_result = $wpdb->get_results(
        "SELECT id FROM $bookly_customers_table WHERE wp_user_id = {$wp_user_id}"
    );
    $wpdb->flush();
    $customer_id = $customer_result[0]->id;

    // get all staff ids from appointments_customer table
    $bookly_appointments_customers_table = $wpdb->prefix . 'bookly_customer_appointments';
    $appointments_results = $wpdb->get_results(
        "SELECT appointment_id FROM $bookly_appointments_customers_table WHERE customer_id = {$customer_id}"
    );
    $wpdb->flush();

    foreach ( $appointments_results as $appointments_result ):
        $appointment_id = $appointments_result->appointment_id;
        $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
        $staff_results = $wpdb->get_results(
            "SELECT staff_id FROM $bookly_appointments_table WHERE id = {$appointment_id}"
        );
        $wpdb->flush();

        $staff_ids[] = $staff_results[0]->staff_id;


    endforeach;
    if( !empty($staff_ids) ):
        return array_unique($staff_ids);
    else:
        return [];
    endif;


}

// function to get bookly customer_id from wp_user_id
function getcustomerID($wp_user_id){
    global $wpdb;
    $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
    $customer_result = $wpdb->get_results(
        "SELECT id FROM $bookly_customers_table WHERE wp_user_id = {$wp_user_id}"
    );
    $wpdb->flush();
    if( empty($customer_result) ) return false;
    $customer_id = $customer_result[0]->id;

    return $customer_id;
}

// function to get customer wp_user_id from bookly user id
function getBooklyWpUserId($bookly_customer_id){
    global $wpdb;
    $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
    $customer_result = $wpdb->get_results(
        "SELECT * FROM $bookly_customers_table WHERE id = {$bookly_customer_id}"
    );
    $wpdb->flush();
    if( empty($customer_result) ) return false;
    $wp_user_id = $customer_result[0]->wp_user_id;

    return $wp_user_id;

}

// function to return bookly customer appointment data with appointment_id
function getBooklyCA($appointment_id){
    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $customer_appointments_results = $wpdb->get_results(
        "SELECT * FROM $bookly_customer_appointments_table WHERE appointment_id = {$appointment_id}"
    );
    $wpdb->flush();

    if( !empty($customer_appointments_results) ):
        return $customer_appointments_results;
    else:
        return false;
    endif;

}

// function to get customer full name ( his name / parent name )
function getCustomerFullName($wp_user_id) {
    $first_name = get_user_meta($wp_user_id, 'first_name' ,true);
    $last_name =  get_user_meta($wp_user_id, 'last_name' ,true);
    $user_obj = get_user_by('id', $wp_user_id);
    $email = $user_obj->user_email;

    $memb_ReferralCode_chld = get_user_meta($wp_user_id ,'account_code', true);
    $memb_ReferralCode_chld = substr($memb_ReferralCode_chld, 5);
    $memb_ReferralCode_prnt = 'prnt-' . $memb_ReferralCode_chld;
    $parent_user = get_user_by_meta_data('account_code', $memb_ReferralCode_prnt);
    if( empty($parent_user) ) return false;
    $parent_user_id = (int) $parent_user->data->ID;
    $parent_user_display_name = $parent_user->data->display_name;
    if( !empty($parent_user_display_name) ):
        $child_last_name = $last_name . ' / ' . $parent_user_display_name;
    else:
        $child_last_name = $last_name ;
    endif;
    $full_name = $first_name . ' ' . $child_last_name;

    return $full_name;
}

// function to get customer name only ( not full name )
function getCustomerName($wp_user_id) {
    $first_name = get_user_meta($wp_user_id, 'first_name' ,true);
    $last_name =  get_user_meta($wp_user_id, 'last_name' ,true);
    $user_obj = get_user_by('id', $wp_user_id);
    $email = $user_obj->user_email;

    $full_name = $first_name . ' ' . $last_name;

    return $full_name;
}

// function to get customer full name ( his name <br>> parent name )
function getCustomerFullName2lines($wp_user_id) {
    $first_name = get_user_meta($wp_user_id, 'first_name' ,true);
    $last_name =  get_user_meta($wp_user_id, 'last_name' ,true);
    $user_obj = get_user_by('id', $wp_user_id);
    $email = $user_obj->user_email;

    $memb_ReferralCode_chld = get_user_meta($wp_user_id ,'account_code', true);
    $memb_ReferralCode_chld = substr($memb_ReferralCode_chld, 5);
    $memb_ReferralCode_prnt = 'prnt-' . $memb_ReferralCode_chld;
    $parent_user = get_user_by_meta_data('account_code', $memb_ReferralCode_prnt);
    $parent_user_id = (int) $parent_user->data->ID;
    $parent_user_display_name = $parent_user->data->display_name;
    if( !empty($parent_user_display_name) ):
        $child_last_name = $last_name . ' <br> P: ' . $parent_user_display_name;
    else:
        $child_last_name = $last_name ;
    endif;
    $full_name = $first_name . ' ' . $child_last_name;

    return $full_name;

}

// function to get parent child(s) as assigned from keap referral code
function getParentChilds($wp_user_id){
    $memb_ReferralCode_parent = get_user_meta($wp_user_id ,'account_code', true);
    $memb_ReferralCode = substr($memb_ReferralCode_parent, 5);
    // get all users has meta starts with 'chld-'.$referral-code
    // Query for users based on the meta data

    if(substr($memb_ReferralCode_parent, 0, 4) === "prnt"): // is parent
        $user_query = new WP_User_Query(
            array(
                'meta_key'	  =>	'account_code',
                'meta_value'	=>	'chld-'.$memb_ReferralCode
            )
        );

        // Get the results from the query, returning the first user
        $childs_users = $user_query->get_results();

        if( !empty($childs_users) ):
            $child_user_ids = array_unique(array_column($childs_users,'id'));
        endif;
    endif;

    if( !empty($child_user_ids) ):
        return $child_user_ids;
    else:
        return [];
    endif;
}

// function to get parent Active child(s) as assigned from keap referral code ( childs has programs with active status & programs linked to active subscription )
function getParentActiveChilds($wp_user_id){
    $memb_ReferralCode_parent = get_user_meta($wp_user_id ,'account_code', true);

    if( empty($memb_ReferralCode_parent) ) return false;
    $memb_ReferralCode = substr($memb_ReferralCode_parent, 5);
    // get all users has meta starts with 'chld-'.$referral-code
    // Query for users based on the meta data
    $active_childs = [];
    if(substr($memb_ReferralCode_parent, 0, 4) === "prnt"): // is parent
        $user_query = new WP_User_Query(
            array(
                'meta_key'	  =>	'account_code',
                'meta_value'	=>	'chld-'.$memb_ReferralCode
            )
        );

        // Get the results from the query, returning the first user
        if( empty($user_query) ) return false;
        $childs_users = $user_query->get_results();

        $child_user_ids[] = (int) $wp_user_id;

        if( !empty($childs_users) ):
            foreach ( $childs_users as $childs_user ):
                $child_user_ids[] = (int) $childs_user->data->ID;
            endforeach;
        endif;

        if( empty($child_user_ids) ) return false;

        // get user programs
        foreach ( $child_user_ids as $child_user_id ):
            $user_groups = groups_get_user_groups($child_user_id);
            if( !empty($user_groups['groups']) ):
                foreach ( $user_groups['groups'] as $bb_group_id ):
                    // get sp entry group status
                    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
                    if( !empty($sp_entry_id) ):
                        $program_status = getGFentryMetaValue($sp_entry_id, 26);
                        if( !empty($program_status) ):
                            $status = $program_status[0]->meta_value;
                            if( $status === 'Active' ):
                                $active_childs[] = $child_user_id;
                            endif;
                        endif;
                    endif;
                endforeach;
            endif;
        endforeach;

    endif;

    if( !empty($active_childs) ):
        return array_values(array_unique($active_childs));
    else:
        return [];
    endif;
}

// function to get parent from customer wp user id
function getParentID($child_user_id){
    $memb_ReferralCode_chld = get_user_meta($child_user_id ,'account_code', true);
    if( empty($memb_ReferralCode_chld) ) return false;
    $memb_ReferralCode_chld = substr($memb_ReferralCode_chld, 5);
    $memb_ReferralCode_prnt = 'prnt-' . $memb_ReferralCode_chld;
    $parent_user = get_user_by_meta_data('account_code', $memb_ReferralCode_prnt);
    if( empty($parent_user) ) return false;
    $parent_user_id = (int) $parent_user->data->ID;
    if( !empty($parent_user_id) ):
        return $parent_user_id;
    else:
        return false;
    endif;

}

// function to check if current user is parent using wp user id
function checkIfParent($wp_user_id){
    $memb_ReferralCode = get_user_meta($wp_user_id ,'account_code', true);

    if( !empty( $memb_ReferralCode ) ):
        $memb_ReferralCode = substr($memb_ReferralCode, 0, 4);
        if( !empty($memb_ReferralCode) && $memb_ReferralCode === 'prnt' ):
            $is_parent = true;
        else:
            $is_parent = false;
        endif;
    else:
        $is_parent = false;
    endif;

    return $is_parent;


}

// function to check if user is bookly staff
function checkIfBooklyStaff($wp_user_id){
    if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ):
        return true;
    else:
        return false;
    endif;
}

// function to check if current user is child using wp user id
function checkIfChild($wp_user_id){
    $memb_ReferralCode = get_user_meta($wp_user_id ,'account_code', true);
    if( !empty( $memb_ReferralCode ) ):
        $memb_ReferralCode = substr($memb_ReferralCode, 0, 4);
        if( !empty($memb_ReferralCode) && $memb_ReferralCode === 'chld' ):
            $is_parent = true;
        else:
            $is_parent = false;
        endif;
    else:
        $is_parent = false;
    endif;

    return $is_parent;

}

// function to get wp_user_id from bookly customrt id
function getWPuserIDfromBookly($bookly_customer_id){
    global $wpdb;
    $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
    $customer_result = $wpdb->get_results(
        "SELECT wp_user_id FROM $bookly_customers_table WHERE id = {$bookly_customer_id}"
    );
    $wpdb->flush();
    $wp_user_id = $customer_result[0]->wp_user_id;

    return $wp_user_id;
}

function reGenerateMissingAppointments($wp_user_id, $bb_group_id ){
//
//    bookly_teacher_id ok
//    bookly_effective_date ok
//    bookly_start_hours ok
//    bookly_start_minutes ok
//    bookly_class_duration ok
//    bookly_class_days ok
//    bookly_user_timezone ok
//    bookly_service_id
//    bookly_student_id ok
//    bookly_category_id
//    bb_group_id ok

    $catch_error = '';
    global $wpdb;

    $bookly_effective_date = '01/01/2022';
    $bookly_effective_day = strtolower( date('D', strtotime($bookly_effective_date)) );
    $bookly_teacher_id = getStaffId($wp_user_id);

    // check if this group has records in GF entries
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);

    // get schedule entry for sp entry
    $schedule_entry_ids = getScheduleEntryID($sp_entry_id);

    // get learners entry for sp entry
    $learners_entry_id = getLearnersEntryID($sp_entry_id);


    // get learners wp_user_ids
    $learners_list = getLearnersWPuserIds($learners_entry_id);
    $learners_ids_string = substr( $learners_list, 1, -1 );
    $learners_ids_array = explode(',', $learners_ids_string);
    foreach ( $learners_ids_array as $learner_id ):
        $learner_ids = (int) preg_replace("/[^0-9]/","",$learner_id);
        $bookly_customer_ids[] = getcustomerID($learner_id);
    endforeach;

    $bookly_user_timezone = getSPentryTimezone($sp_entry_id);
    $bookly_user_timezone_offset = getNowTimeZoneOffset($bookly_user_timezone);
    $bookly_service_id = getBooklyServiceId($sp_entry_id);

    // loop schedules entry
    foreach ( $schedule_entry_ids as $schedule_entry_id ):
        $bookly_class_days[] = getClassDays($schedule_entry_id);
        $start_time = getStartTime($schedule_entry_id);
        $bookly_start_hours[] = explode(':', $start_time)[0];
        $bookly_start_minutes[] = explode(':', $start_time)[1];
        $bookly_class_duration[] = getClassDuration($schedule_entry_id);
        // get series is for schedules
        $series_id[] = getBooklySeriesId($schedule_entry_id);
    endforeach; // end schedules loop


    foreach ( $bookly_class_duration as $duration ):
        $units[] = ( (int) $duration ) / 15 ;
    endforeach;


    if(
        empty($bookly_teacher_id) ||
        empty($bookly_customer_ids) ||
        empty($bookly_user_timezone) ||
        empty($bookly_start_hours) ||
        empty($bookly_start_minutes) ||
        empty($bookly_class_duration) ||
        empty($bookly_effective_date) ||
        empty($bookly_class_days) ||
        empty($bookly_service_id)
    ):
        return 'empty-fields';
    endif;


    // get week number and year number to generate effective dates for each row
    $effective_week_number = date("W", strtotime($bookly_effective_date));
    $effective_year_number = date('Y', strtotime($bookly_effective_date));
    $effective_month_number = (int) date('m', strtotime($bookly_effective_date));

    // fix wrong week number if in day in last week of previous year
    if( $effective_month_number === 1 && (int) $effective_week_number > 50):
        $effective_week_number = 1;
    endif;


    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $created_at = $current_date_object->format('Y-m-d H:i:s');

    // get effective start datetime
    for ( $i=0; $i<count($bookly_start_hours); $i++):
        $bookly_end_minutes[] = convertToHoursMins( (int) $bookly_start_minutes[$i] + (int) $bookly_class_duration[$i] )['minutes'];
        $bookly_end_hours[] = (int) $bookly_start_hours[$i] + convertToHoursMins( (int) $bookly_start_minutes[$i] + (int) $bookly_class_duration[$i] )['hours'];
        $string_start_date[] = strtotime( $bookly_effective_date . ' ' . (int) $bookly_start_hours[$i] . ':' . (int) $bookly_start_minutes[$i] ); // mm/dd/yyyy H:m
    endfor;

    foreach ( $string_start_date as $start_date ):
        $booking_user_start_date[] = convertTimeZone( date ("Y-m-d H:i:s", $start_date), $bookly_user_timezone);
    endforeach;


    // get effective end datetime
    for( $i=0; $i<count($bookly_end_hours); $i++ ):
        if( $bookly_end_hours[$i] == 24 ){
            $string_end_date = strtotime( $bookly_effective_date . ' 23:59:00'  ); // mm/dd/yyyy H:m
        } else {
            $string_end_date = strtotime( $bookly_effective_date . ' ' . $bookly_end_hours[$i] . ':' . $bookly_end_minutes[$i] ); // mm/dd/yyyy H:m
        }
        $booking_user_end_date[] = convertTimeZone( date ("Y-m-d H:i:s", $string_end_date), $bookly_user_timezone );
    endfor;


    // get new effective start and end dates for each booking row
    $effective_day_index = array_search($bookly_effective_day, WEEK_DAYS_INDEX);

    for( $i=0; $i<count($bookly_class_days); $i++ ):
        if( $bookly_end_hours[$i] >= 24 ){
            $bookly_end_hours[$i] = 23;
            $bookly_end_minutes[$i] = 59;
        }
        foreach( $bookly_class_days[$i] as $bookly_class_day ):
            $day_index = array_search($bookly_class_day, WEEK_DAYS_INDEX);
            $week_day_index = array_search($bookly_class_day, SUN_WEEK_DAYS_INDEX);
            $gendate = new DateTime();
            $gendate->setISODate($effective_year_number,$effective_week_number,$week_day_index); //year , week num , day
            $row_effective_start_dates[$i][] =  $gendate->format('Y-m-d '. $bookly_start_hours[$i] . ':' . $bookly_start_minutes[$i] . ':00' );
            $row_effective_end_dates[$i][] = $gendate->format('Y-m-d '. $bookly_end_hours[$i] . ':' . $bookly_end_minutes[$i] . ':00');

        endforeach;
    endfor;


    // check if effective date has days before it in schedule, and get previous dates to skip from recurring
    $bookly_effective_day_index =  array_search($bookly_effective_day, WEEK_DAYS_INDEX);


    foreach ( $row_effective_start_dates[0] as $row_effective_start_date ):
        $class_day_name = strtolower( date('D', strtotime($row_effective_start_date)) );
        $class_day_index = array_search($class_day_name, WEEK_DAYS_INDEX);
        if( $class_day_index < $bookly_effective_day_index ){
            $skip_start_previous_days[] = $row_effective_start_date;
        }
    endforeach;

    foreach ( $row_effective_end_dates[0] as $row_effective_end_date ):
        $class_day_name = strtolower( date('D', strtotime($row_effective_end_date)) );
        $class_day_index = array_search($class_day_name, WEEK_DAYS_INDEX);
        if( $class_day_index < $bookly_effective_day_index ){
            $skip_end_previous_days[] = $row_effective_end_date;
        }
    endforeach;


    // get recurring days for each row_start_date
    for( $i=0; $i<count($row_effective_start_dates); $i++ ):

        foreach ( $row_effective_start_dates[$i] as $row_effective_start_date ):
            // get reccurring dates for each start and end date
            $recurringDatesStartArray = getReccurringDates( date('m/d/Y H:i:s', strtotime($row_effective_start_date)) ,500, 'Y-m-d H:i:s');
            $recurringDatesStartArray[] = $row_effective_start_date;
            $rowReccurringStartDates[$i][] = $recurringDatesStartArray;
        endforeach;

        foreach ( $row_effective_end_dates[$i] as $row_effective_end_date ):
            // get reccurring dates for each start and end date
            $recurringDatesEndArray = getReccurringDates( date('m/d/Y H:i:s', strtotime($row_effective_end_date)) ,500, 'Y-m-d H:i:s');
            $recurringDatesEndArray[] = $row_effective_end_date;
            $rowReccurringEndDates[$i][] = $recurringDatesEndArray;
        endforeach;

    endfor;


    // skip previous dates from recurring start for booking row 1 only
//    foreach ( $rowReccurringStartDates[0] as $key=>$rowReccurringStartDate):
//        // loop for each class date
//        // check if date in skip
//        foreach ( $skip_start_previous_days as $skip_start_day ):
//            if( $skip_key = array_search($skip_start_day, $rowReccurringStartDate) ):
//                unset( $rowReccurringStartDates[0][$key][$skip_key] );
//            endif;
//        endforeach;
//    endforeach;

    // skip previous dates from recurring end for booking row 1 only
//    foreach ( $rowReccurringEndDates[0] as $key=>$rowReccurringEndDate):
//        // loop for each class date
//        // check if date in skip
//        foreach ( $skip_end_previous_days as $skip_end_day ):
//            if( $skip_key = array_search($skip_end_day, $rowReccurringEndDate) ):
//                unset( $rowReccurringEndDates[0][$key][$skip_key] );
//            endif;
//        endforeach;
//    endforeach;




    //set bookly custom fields data for new records
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $custom_fields = json_encode(
        array(
            array(
                'id' => $bb_custom_field_id,
                'value' => strval($bb_group_id)
            )
        )
    );


    // create appointments records
    for( $i=0; $i<count($rowReccurringStartDates); $i++ ):
        //echo '------------------ Row ' . $i . ' ----------------------- <br>';
        // for each booking row create new series id
        // insert new record in bookly series table
//        $bookly_series_table = $wpdb->prefix . 'bookly_series';
//
//        $insert_new_series = $wpdb->insert($bookly_series_table,
//            array(
//                'repeat' => '',
//                'token' => generateUniqueToken(),
//            )
//        );
//
//        if( $insert_new_series ):
//            // get last series id from bookly_series to attach to new schedule
//            $series_results = $wpdb->get_results(
//                "SELECT id FROM $bookly_series_table ORDER BY id DESC LIMIT 1"
//            );
//            $wpdb->flush();
//
//            if( !empty($series_results) ):
//                $series_id = (int) $series_results[0]->id;
//            else:
//                $catch_error .= 'Error: bookly series id not found<br>';
//            endif;
//
//
//        else:
//            $wpdb->show_errors();
//            $catch_error .= 'Error: insert new bookly series record. ' . $wpdb->last_error.'<br>';
//        endif;

        $series_id = $series_id[$i];

        // start inserting bookly_appointments table

        for( $x=0; $x<count($rowReccurringStartDates[$i]); $x++ ):
            //echo '==== Day ' . $x .' ====<br>';
            for( $d=1; $d<=count($rowReccurringStartDates[$i][$x]); $d++ ):
                //echo 'start: ' . $rowReccurringStartDates[$i][$x][$d]. ' ---  end: ' . $rowReccurringEndDates[$i][$x][$d] .'<br>';

                $booking_start_date = $rowReccurringStartDates[$i][$x][$d];
                $booking_end_date   = $rowReccurringEndDates[$i][$x][$d];

                // for each class_day in row get recurring dates ( start and end ) -> create appointment record
                // insert appointments into bookly_appointments table
                $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
                $appointments = array(
                    array(
                        'staff_id' => $bookly_teacher_id,
                        'staff_any' => 0,
                        'service_id' => $bookly_service_id,
                        'start_date' => $booking_start_date,
                        'end_date' => $booking_end_date,
                        'extras_duration' => 0,
                        'internal_note' => '',
                        'created_from' => 'bookly',
                        'created_at' => $created_at,
                        'updated_at' => $created_at
                    ),
                );


                if( wpdb_bulk_insert($bookly_appointments_table, $appointments) === 1 ):
                    //if record true, get appointment id
                    $appointment_id = $wpdb->insert_id;

                    // get appointment record id to use it in customer_appointments table and use social group id as custom_fields
                    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';

                    // start inserting bookly_customer_appointments table
                    foreach ( $bookly_customer_ids as $key=>$bookly_customer_id ):

                        $customer_appointments_array[$key] = array(
                            'series_id' => $series_id,
                            'customer_id' => $bookly_customer_id,
                            'appointment_id' => $appointment_id,
                            'number_of_persons' => 1,//count( $bookly_customer_ids ),
                            'units' => $units[$i],
                            'extras' => [],
                            'extras_multiply_nop' => 1,
                            'extras_consider_duration' => 1,
                            'custom_fields' => $custom_fields,
                            'status' => 'approved',
                            'token' => generateUniqueToken(),
                            'time_zone' => $bookly_user_timezone,
                            'time_zone_offset' => $bookly_user_timezone_offset,
                            'created_from' => 'frontend',
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        );

                    endforeach;

                    $customer_appointments = $customer_appointments_array;

                    if( wpdb_bulk_insert($bookly_customer_appointments_table, $customer_appointments) !== 1 ):
                        // do nothing, insert success
                        //$catch_error .= 'Error in inserting customer appointment <br>';
                    endif;
                else:
                    $wpdb->show_errors();
                    $catch_error .= 'Error in inserting bookly appointments table: '.$wpdb->print_error().'<br>';
                endif;

            endfor;
        endfor;
    endfor;



    if( empty($catch_error) ):
        return true;
    else:
        return $catch_error;
    endif;


}

// function to get BB group entry id in Single program form
function getBBgroupGFentryID ($bb_group_id) {
    global $wpdb;
    $bb_group_id = (int) $bb_group_id;
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT entry_id FROM $gf_meta_table WHERE meta_key = 7 AND meta_value = {$bb_group_id} AND form_id ={$SP_PARENT_FORM_ID}"
    );
    $wpdb->flush();

    // check if entry is active
    if( !empty($gf_result) ):
        foreach ( $gf_result as $entry ):
            $entry_id = $entry->entry_id;

            $gf_entry_result = $wpdb->get_results(
                "SELECT id FROM $gf_entry_table WHERE id = {$entry_id} AND status = 'active'"
            );
            $wpdb->flush();

            if( !empty($gf_entry_result) ):
                return (int) $entry_id;
            endif;

        endforeach;
    else:
        return false;
    endif;
}

// function to get schedule entry id from sp parent entry id
function getScheduleEntryID($sp_entry_id) {
    if( empty($sp_entry_id) ):
        return false;
    endif;
    global $wpdb;
    $SCHEDULE_FORM_ID = SCHEDULE_FORM_ID();
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $parent_meta_key = 'workflow_parent_form_id_'. $SP_PARENT_FORM_ID .'_entry_id';
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT entry_id FROM $gf_meta_table WHERE meta_key = '{$parent_meta_key}' AND meta_value = {$sp_entry_id} AND form_id ={$SCHEDULE_FORM_ID}"
    );
    $wpdb->flush();


    if( !empty($gf_result) ):
        // check if entry is active
        foreach ( $gf_result as $entry ):
            $entry_id = $entry->entry_id;
            $gf_entry_result = $wpdb->get_results(
                "SELECT * FROM $gf_entry_table WHERE id = {$entry_id} AND status = 'active'"
            );
            $wpdb->flush();

            if( !empty($gf_entry_result) ):
                $schedule_entries[] = $entry_id;
            endif;

        endforeach;
    else:
        return false;
    endif;

    if( !empty($schedule_entries) ):
        return array_unique( $schedule_entries );
    else:
        return false;
    endif;

}

// function to get learners entry id from sp parent entry id
function getLearnersEntryID($sp_entry_id) {
    global $wpdb;
    $catch_error = '';
    $LEARNERS_FORM_ID = LEARNERS_FORM_ID();
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $parent_meta_key = 'workflow_parent_form_id_'. $SP_PARENT_FORM_ID .'_entry_id';
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT entry_id FROM $gf_meta_table WHERE meta_key = '{$parent_meta_key}' AND meta_value = {$sp_entry_id} AND form_id = {$LEARNERS_FORM_ID}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        // check if entry is active
        foreach ( $gf_result as $entry ):
            $entry_id = $entry->entry_id;
            $gf_entry_result = $wpdb->get_results(
                "SELECT * FROM $gf_entry_table WHERE id = {$entry_id} AND status = 'active'"
            );
            $wpdb->flush();
            if( !empty($gf_entry_result) ):
                $learner_entry_id = $entry_id;
            endif;

        endforeach;
    else:
        $catch_error .= 'Error: no gf learner(s) found. <br>';
    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return $learner_entry_id;
    endif;

}

// function to get learners user ids from learners entry id
function getLearnersWPuserIds($learners_entry_id) {
    global $wpdb;
    $LEARNERS_FORM_ID = LEARNERS_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE meta_key = 3 AND entry_id = {$learners_entry_id} AND form_id = {$LEARNERS_FORM_ID}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;
}

// function to get class days from schedule entry id
function getClassDays($schedule_entry_id) {
    global $wpdb;
    $SCHEDULE_FORM_ID = SCHEDULE_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE meta_key LIKE '%1.%' AND entry_id = {$schedule_entry_id} AND form_id = {$SCHEDULE_FORM_ID}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        foreach ( $gf_result as $item ):
            $class_days[] = $item->meta_value;
        endforeach;
        return $class_days;
    else:
        return false;
    endif;
}

// function to get user timezone from entry id for sp parent entry id
function getSPentryTimezone($sp_entry_id) {
    if( empty($sp_entry_id) ):
        return false;
    endif;
    global $wpdb;
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT meta_value FROM $gf_meta_table WHERE entry_id = {$sp_entry_id} AND meta_key = 3 AND form_id ={$SP_PARENT_FORM_ID}"
    );
    $wpdb->flush();


    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;


}

// function to get program status from entry id for sp parent entry id
function getSPentryStatus($sp_entry_id) {
    global $wpdb;
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE entry_id = {$sp_entry_id} AND meta_key = 10 AND form_id ={$SP_PARENT_FORM_ID}"
    );
    $wpdb->flush();


    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;

}

// function to get bookly teacher id from entry id for sp parent entry id
function getSPentryStaffId($sp_entry_id) {
    if( empty($sp_entry_id) ) return false;
    global $wpdb;
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT meta_value FROM $gf_meta_table WHERE entry_id = {$sp_entry_id} AND meta_key = 8 AND form_id ={$SP_PARENT_FORM_ID}"
    );
    $wpdb->flush();


    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;

}

// function to get bookly xTeacher id from entry id for sp parent entry id
function getSPentryxStaffId($sp_entry_id) {
    global $wpdb;
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE entry_id = {$sp_entry_id} AND meta_key = 25 AND form_id ={$SP_PARENT_FORM_ID}"
    );
    $wpdb->flush();


    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;


}

// function to get bookly teacher id from entry id for schedule entry id
function getScheduleEntryStaffId($schedule_entry_id) {
    global $wpdb;
    $SCHEDULE_FORM_ID = SCHEDULE_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE entry_id = {$schedule_entry_id} AND meta_key = 10 AND form_id ={$SCHEDULE_FORM_ID}"
    );
    $wpdb->flush();


    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;

}

// function to get start time from schedule entry id
function getStartTime($schedule_entry_id) {
    global $wpdb;
    $SCHEDULE_FORM_ID = SCHEDULE_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE meta_key = 5 AND entry_id = {$schedule_entry_id} AND form_id = {$SCHEDULE_FORM_ID}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;

}

// function to get duration from schedule entry id
function getClassDuration($schedule_entry_id) {
    global $wpdb;
    $SCHEDULE_FORM_ID = SCHEDULE_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE meta_key = 6 AND entry_id = {$schedule_entry_id} AND form_id = {$SCHEDULE_FORM_ID}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;

}

// function to get series id from schedule entry id
function getBooklySeriesId($schedule_entry_id) {
    global $wpdb;
    $SCHEDULE_FORM_ID = SCHEDULE_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE meta_key = 7 AND entry_id = {$schedule_entry_id} AND form_id = {$SCHEDULE_FORM_ID}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;

}

// function to get start date from schedule entry id
function getBooklyStartDate($schedule_entry_id) {
    global $wpdb;
    $SCHEDULE_FORM_ID = SCHEDULE_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE meta_key = 9 AND entry_id = {$schedule_entry_id} AND form_id = {$SCHEDULE_FORM_ID}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;
}

// function to get end date from schedule entry id
function getBooklyEndDate($schedule_entry_id) {
    global $wpdb;
    $SCHEDULE_FORM_ID = SCHEDULE_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE meta_key = 8 AND entry_id = {$schedule_entry_id} AND form_id = {$SCHEDULE_FORM_ID}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;
}

// function to get created by from gf entry id
function getGFentryCreatedBy($entry_id) {
    global $wpdb;
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_entry_table WHERE id = {$entry_id}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        return $gf_result[0]->created_by;
    else:
        return false;
    endif;

}

// function to get created on date from gf entry id
function getGFentryCreatedOn($entry_id) {
    global $wpdb;
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_entry_table WHERE id = {$entry_id}"
    );
    $wpdb->flush();

    if( !empty($gf_result) ):
        return $gf_result[0]->date_created;
    else:
        return false;
    endif;
}

// function to get bookly service id from entry id for sp parent entry id
function getBooklyServiceId($sp_entry_id) {
    global $wpdb;
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE entry_id = {$sp_entry_id} AND meta_key = 6 AND form_id ={$SP_PARENT_FORM_ID}"
    );
    $wpdb->flush();


    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;

}

// function to get bookly category id from entry id for sp parent entry id
function getBooklyCategoryId($sp_entry_id) {
    global $wpdb;
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE entry_id = {$sp_entry_id} AND meta_key = 5 AND form_id ={$SP_PARENT_FORM_ID}"
    );
    $wpdb->flush();


    if( !empty($gf_result) ):
        return $gf_result[0]->meta_value;
    else:
        return false;
    endif;

}

// function to get BB groups that has no appointments records in bookly_customer_appointments table
function getMissingBBgroups($wp_user_id){
    global $wpdb;
    $missing_bb_groups = [];
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
    $user_groups = groups_get_user_groups($wp_user_id);
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    if( !empty($user_groups) ):
        $current_groups = $user_groups['groups'];
        foreach ( $current_groups as $bb_group_id ):
            // get group type
            $group_type = bp_groups_get_group_type($bb_group_id);
            if( $group_type === 'class' ):
                // get entries in customer appointments table, if not found that is missing
                $custom_field = '[{"id":'. $bb_custom_field_id .',"value":"'.$bb_group_id.'"}]';
                // get all teachers
                $ca_results = $wpdb->get_results(
                    "SELECT * FROM $bookly_appointments_customer_table WHERE `custom_fields` LIKE '%{$custom_field}%'"
                );
                $wpdb->flush();

                if( empty($ca_results) ):
                    $missing_bb_groups[] = $bb_group_id;
                endif;

            endif;

        endforeach;

        return $missing_bb_groups;

    endif;

    return;

}

// function to delete appointments for teachers has missing ca records
function deleteMissingAppointments ($wp_user_id) {
    $missing_apointments = getMissingAppointments($wp_user_id);
    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    if( !empty($missing_apointments) ):
        // delete these appointments from bookly_appointments table
        $appts_ids = implode(",", $missing_apointments);
        if( ! $wpdb->query("DELETE FROM $bookly_appointments_table WHERE id IN ($appts_ids)") ):
            $delete_error = 'Error in deleting bookly_appointments_table <br>';
        endif;
    else:
        return false;
    endif;

    if( !empty($delete_error) ):
        return $delete_error;
    else:
        return true;
    endif;

}

// function to get appointments for teachers has missing ca records
function getMissingAppointments ($wp_user_id) {
    // get staff appointments
    global $wpdb;
    $teachers_id = getStaffId($wp_user_id);
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
    $appointments_results = $wpdb->get_results(
        "SELECT * FROM $bookly_appointments_table WHERE staff_id = {$teachers_id}"
    );
    $wpdb->flush();
    foreach ( $appointments_results as $appointments_result ):
        $appointment_id = $appointments_result->id;
        // get customer appointments for this id
        $customer_appointment_results = $wpdb->get_results(
            "SELECT * FROM $bookly_appointments_customer_table WHERE appointment_id = {$appointment_id}"
        );
        $wpdb->flush();
        if( empty($customer_appointment_results) ):
            $empty_records[] = $appointment_id;
        endif;
    endforeach;

    if( !empty($empty_records) ):
        return $empty_records;
    else:
        return false;
    endif;



}

// function to get staff id from wp_user_id
function getStaffId($wp_user_id){
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $staff_results = $wpdb->get_results(
        "SELECT id FROM $bookly_staff_table WHERE wp_user_id = {$wp_user_id}"
    );
    $wpdb->flush();
    if( !empty($staff_results) ):
        $staff_id = $staff_results[0]->id;
    else:
        $staff_id = false;
    endif;
    return $staff_id;
}

// function to get staff wp_user_id from staff_id
function getStaffwp_user_id($staff_id){
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $staff_results = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_table WHERE id = {$staff_id}"
    );
    $wpdb->flush();
    if( !empty($staff_results) ):
        $staff_wp_user_id = $staff_results[0]->wp_user_id;
    else:
        $staff_wp_user_id = false;
    endif;
    return $staff_wp_user_id;
}

// get teachers bb_groups assigned to him
function getStaffBBGroups($staff_id) {
    global $wpdb;
    $wp_user_id = getStaffwp_user_id($staff_id);
    $staff_bb_groups = [];
    $user_groups = groups_get_user_groups($wp_user_id);

    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    if( !empty($user_groups) ):
        $current_groups = $user_groups['groups'];
        foreach ( $current_groups as $bb_group_id ):
            // get group type
            $group_type = bp_groups_get_group_type($bb_group_id);
            if( $group_type === 'class' ):
                $staff_bb_groups[] = $bb_group_id;
            endif;

        endforeach;

        return $staff_bb_groups;

    endif;
    return false;
}

// function to get staff full name from bookly_user_id
function getStaffFullName($bookly_user_id){
    if( empty($bookly_user_id) ) return false;
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $staff_results = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_table WHERE id = {$bookly_user_id}"
    );
    $wpdb->flush();
    if( !empty($staff_results) ):
        $staff_name = $staff_results[0]->full_name;
    else:
        $staff_name = false;
    endif;
    return $staff_name;

}

// function to get staff timezone from staff_id
function getStaffTimezone($staff_id){
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $staff_results = $wpdb->get_results(
        "SELECT time_zone FROM $bookly_staff_table WHERE id = {$staff_id}"
    );
    $wpdb->flush();
    if( !empty($staff_results) ):
        $staff_timezone = $staff_results[0]->time_zone;
    else:
        $staff_timezone = false;
    endif;
    return $staff_timezone;

}

// function to get all bookly staff where staff found in HR table and status is (Active, Active Vacation, Active Not Accepting, Active Hold) and department = 8
function getAllBooklyStaffx() {
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $staff_results = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_table"
    );
    $wpdb->flush();
    if( !empty($staff_results) ):
        return $staff_results;
    else:
        return false;
    endif;
}

function getAllBooklyStaff(){
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $wphr_hr_employees_table = $wpdb->prefix . 'wphr_hr_employees';

    // get all employees
//    $wphr_hr_employees_result = $wpdb->get_results(
//        "SELECT user_id FROM $wphr_hr_employees_table WHERE department = 8 AND status IN('active', 'act_vac', 'act_hold', 'act_nac')"
//    );
//    $wpdb->flush();
//
//
//    if( empty($wphr_hr_employees_result) ) return false;
//
//    $staff_wp_user_ids = implode(',', array_column($wphr_hr_employees_result, 'user_id'));

    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $staff_results = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_table 
            where wp_user_id IN( SELECT user_id FROM $wphr_hr_employees_table 
                                    WHERE department = 8 AND status IN('active', 'act_vac', 'act_hold', 'act_nac') )"
    );
    $wpdb->flush();
    if( !empty($staff_results) ):
        return $staff_results;
    else:
        return false;
    endif;
}

// function to get bookly staff full name
function getBooklyStaffFullName($bookly_user_id) {
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $staff_results = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_table WHERE id = {$bookly_user_id}"
    );
    $wpdb->flush();
    if( !empty($staff_results) ):
        return $staff_results[0]->full_name;
    else:
        return false;
    endif;
}

// function to get total hrs for teacher monthly
function getMonthlyStaffTotalHrs($staff_id, $start_date, $end_date) {
    global $wpdb;
//    $current_date_object = new DateTime('now', new DateTimeZone('America/New_York'));
//    $created_at = $current_date_object->format('Y-m-d H:i:s');
//    $current_datetime = $current_date_object->format('Y-m-d H:i:s');
//    $current_month = (int) date('m', strtotime($current_datetime));
//    $current_year = (int) date('y', strtotime($current_datetime));
    $start_date = $start_date . ' 00:00:00';
    $end_date = $end_date . ' 00:00:00';

    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $staff_appointments_results = $wpdb->get_results(
        "SELECT * FROM $bookly_appointments_table WHERE staff_id = {$staff_id} AND (start_date BETWEEN '{$start_date}' AND '{$end_date}')"
    );

    $wpdb->flush();
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    if( !empty($staff_appointments_results) ):
        // get ca appts for each record
        foreach ( $staff_appointments_results as $staff_appointments_result ):
            $appointmnet_id = $staff_appointments_result->id;
            $ca_record = getBooklyCA($appointmnet_id);
            if( !empty($ca_record) ):
                $attendance_status = $ca_record[0]->status;

                $stored_bb_group_custom_field = json_decode($ca_record[0]->custom_fields);
                foreach ( $stored_bb_group_custom_field as $field_data ):
                    $custom_field_id = (int) $field_data->id;

                    if( $field_data->id === 2583 ): // actual mins value
                        $stored_actual_min = $field_data->value;
                        $stored_actual_minarr[$attendance_status][] = $field_data->value;
                    endif;

                    if( $field_data->id === 95778 ): // late mins value
                        $stored_late_mins = $field_data->value;
                    endif;

                endforeach;

                if( $attendance_status === 'attended' || $attendance_status === 'attended-tl' || $attendance_status === 'no-show-s' || $attendance_status === 'holiday' ):
                    $total_hrs = (int) $stored_actual_min + $total_hrs;
                elseif ( $attendance_status === 'attended-sl' ):
                    $total_hrs = (int) $stored_actual_min + (int) $stored_late_mins + $total_hrs;
                endif;

            endif;
        endforeach;
    endif;
    if( !empty($total_hrs) ):
        return number_format( $total_hrs / 60 , 1);
    else:
        return 0;
    endif;

}

// function to get bookly service name from service id
function getBooklyServiceName($service_id) {
    global $wpdb;
    $bookly_services_table = $wpdb->prefix . 'bookly_services';
    $service_results = $wpdb->get_results(
        "SELECT * FROM $bookly_services_table WHERE id = {$service_id}"
    );
    $wpdb->flush();
    if( !empty($service_results) ):
        return $service_results[0]->title;
    else:
        return false;
    endif;
}

// function to get bookly service categoty id from service id
function getBooklyServiceCategoryId($service_id) {
    global $wpdb;
    $bookly_services_table = $wpdb->prefix . 'bookly_services';
    $service_results = $wpdb->get_results(
        "SELECT * FROM $bookly_services_table WHERE id = {$service_id}"
    );
    $wpdb->flush();
    if( !empty($service_results) ):
        return $service_results[0]->category_id;
    else:
        return false;
    endif;
}

// function to get bookly categoty name from category id
function getBooklyServiceCategoryName($category_id) {
    global $wpdb;
    $bookly_categories_table = $wpdb->prefix . 'bookly_categories';
    $categories_results = $wpdb->get_results(
        "SELECT * FROM $bookly_categories_table WHERE id = {$category_id}"
    );
    $wpdb->flush();
    if( !empty($categories_results) ):
        return $categories_results[0]->name;
    else:
        return false;
    endif;

}

// function to change status of gf entry (  1 approved, 2 disapproved, 3 pending )
function changeGFentryStatus( $entry_id, $update_status ){
    global $wpdb;
    $entry_id = (int) $entry_id;
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_result = $wpdb->get_results(
        "SELECT * FROM $gf_meta_table WHERE entry_id = {$entry_id} AND meta_key = 'is_approved'"
    );
    $wpdb->flush();
    $catch_error = '';
    $meta_id = $gf_result[0]->id;
    // update is_approved to new value
    $gf_meta_entry_update = $wpdb->query(
        "UPDATE $gf_meta_table SET meta_value = {$update_status} WHERE id = {$meta_id}"
    );
    if( ! $gf_meta_entry_update ):
        $wpdb->show_errors();
        $catch_error .= 'Error: in updating gf entry. <br>' .$wpdb->print_error();
    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;


}

// function to set created by user for GF entry
function setGFentryCreatedBy( $entry_id, $wp_user_id ){
    global $wpdb;
    $entry_id = (int) $entry_id;
    if( (int) $wp_user_id === (int) get_current_user_id() ):
        return  true;
    endif;

    $catch_error = '';
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    // update is_approved to new value
    $gf_entry_update = $wpdb->query(
        "UPDATE $gf_entry_table SET created_by = '{$wp_user_id}' WHERE id = {$entry_id}"
    );
    if( ! $gf_entry_update ):
        $wpdb->show_errors();
        $catch_error .= 'Error: in updating created by gf entry. <br>' .$wpdb->print_error();
    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;
}

// function to change end date of schedule gf entry
function updateGFentryEndDate( $entry_id, $end_date ){
    global $wpdb;
    $catch_error = '';
    $entry_id = (int) $entry_id;
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    // update is_approved to new value
    if( gform_update_meta( $entry_id, 8, $end_date ) === 1 ):
        return true;
    else:

        $gf_meta_entry_get = $wpdb->get_results(
            "SELECT * FROM $gf_meta_table WHERE entry_id = {$entry_id} AND meta_key = 8 "
        );
        $wpdb->flush();

        $id = $gf_meta_entry_get[0]->id;

        if( $gf_meta_entry_get[0]->meta_value === $end_date ):
            return true;
        else:
            $gf_meta_entry_update = $wpdb->query(
                "UPDATE $gf_meta_table SET meta_value = '{$end_date}' WHERE id = {$id}"
            );
            if( ! $gf_meta_entry_update ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating end date for gf entry entry: '. $entry_id . '<br>' .$wpdb->print_error();
            endif;

        endif;

    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;


}

// function to change status of program single program gf entry
function updateGFentryProgramStatus( $entry_id, $status ){
    global $wpdb;
    $catch_error = '';
    $entry_id = (int) $entry_id;
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    // update is_approved to new value
    if( gform_update_meta( $entry_id, 26, $status ) === 1 ):
        return true;
    else:

        $gf_meta_entry_get = $wpdb->get_results(
            "SELECT * FROM $gf_meta_table WHERE entry_id = {$entry_id} AND meta_key = 26 "
        );
        $wpdb->flush();

        $id = $gf_meta_entry_get[0]->id;

        if( $gf_meta_entry_get[0]->meta_value === $status ):
            return true;
        else:
            $gf_meta_entry_update = $wpdb->query(
                "UPDATE $gf_meta_table SET meta_value = '{$status}' WHERE id = {$id}"
            );
            if( ! $gf_meta_entry_update ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating program status for SP gf entry : '. $entry_id . '<br>' .$wpdb->print_error();
            endif;

        endif;

    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;

}

// function to change xTeacher of program single program gf entry
function updateGFentryxTeacher( $entry_id, $xTeacherId ){
    global $wpdb;
    $catch_error = '';
    $entry_id = (int) $entry_id;
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    // update is_approved to new value
    if( gform_update_meta( $entry_id, 25, $xTeacherId ) === 1 ):
        return true;
    else:

        $gf_meta_entry_get = $wpdb->get_results(
            "SELECT * FROM $gf_meta_table WHERE entry_id = {$entry_id} AND meta_key = 25 "
        );
        $wpdb->flush();

        $id = $gf_meta_entry_get[0]->id;

        if( $gf_meta_entry_get[0]->meta_value === $xTeacherId ):
            return true;
        else:
            $gf_meta_entry_update = $wpdb->query(
                "UPDATE $gf_meta_table SET meta_value = '{$xTeacherId}' WHERE id = {$id}"
            );
            if( ! $gf_meta_entry_update ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating xTeacher for SP gf entry : '. $entry_id . '<br>' .$wpdb->print_error();
            endif;

        endif;

    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;


}

// function to change Teacher of program single program gf entry
function updateGFentryTeacher( $entry_id, $TeacherId ){
    global $wpdb;
    $catch_error = '';
    $entry_id = (int) $entry_id;
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    // update is_approved to new value
    if( gform_update_meta( $entry_id, 8, $TeacherId ) === 1 ):
        return true;
    else:

        $gf_meta_entry_get = $wpdb->get_results(
            "SELECT * FROM $gf_meta_table WHERE entry_id = {$entry_id} AND meta_key = 8 "
        );
        $wpdb->flush();

        $id = $gf_meta_entry_get[0]->id;

        if( $gf_meta_entry_get[0]->meta_value === $TeacherId ):
            return true;
        else:
            $gf_meta_entry_update = $wpdb->query(
                "UPDATE $gf_meta_table SET meta_value = '{$TeacherId}' WHERE id = {$id}"
            );
            if( ! $gf_meta_entry_update ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating Teacher for SP gf entry : '. $entry_id . '<br>' .$wpdb->print_error();
            endif;

        endif;

    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;


}

// function to change teacher id of schedule gf entry
function updateGFentryTeacherId( $entry_id, $teacher_id ){
    global $wpdb;
    $catch_error = '';
    $entry_id = (int) $entry_id;
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    // update is_approved to new value
    if( gform_update_meta( $entry_id, 10, $teacher_id ) === 1 ):
        return true;
    else:

        $gf_meta_entry_get = $wpdb->get_results(
            "SELECT * FROM $gf_meta_table WHERE entry_id = {$entry_id} AND meta_key = 10 "
        );
        $wpdb->flush();

        $id = $gf_meta_entry_get[0]->id;

        if( (int) $gf_meta_entry_get[0]->meta_value === (int) $teacher_id ):
            return true;
        else:
            $gf_meta_entry_update = $wpdb->query(
                "UPDATE $gf_meta_table SET meta_value = '{$teacher_id}' WHERE id = {$id}"
            );
            if( ! $gf_meta_entry_update ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating teacher for gf entry entry: '. $entry_id . '<br>' .$wpdb->print_error();
            endif;

        endif;

    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;



}

// function to get teacher timezone
function getTeacherTimeZone($bookly_id)
{

    $staff = Bookly\Lib\Entities\Staff::query()->where( 'id', $bookly_id )->findOne();
    if ( $staff ) {
        $staff_tz = $staff->getTimeZone();
        if ( $staff_tz ) {
            return $staff_tz;
        }
    }

    // Use WP time zone by default
    return Bookly\Lib\Config::getWPTimeZone();
}

// function to get bb group name
function getBBgroupName($bb_group_id) {
    $bb_group_id = (int) $bb_group_id;
    $bb_group_obj = groups_get_group( $bb_group_id );
    if( !empty($bb_group_obj) ):
        return $bb_group_obj->name;
    else:
        return false;
    endif;

}

// function to get GF meta_value record
function getGFentryMetaValue($entry_id, $meta_key) {
    if( empty($entry_id) || empty($meta_key) ):
        return false;

    endif;

    global $wpdb;
    $gf_entry_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_results = $wpdb->get_results(
        "SELECT * FROM $gf_entry_meta_table WHERE meta_key = '{$meta_key}' AND entry_id = {$entry_id}"
    );
    $wpdb->flush();
    if( !empty($gf_results) ):
        return $gf_results;
    else:
        return false;
    endif;

}

// function to calculate total hours for BB group id bases on gf entries schedule
function getProgramTotalHours($bb_group_id){
    $bb_group_id = (int) $bb_group_id;
    $catch_error = [];
    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $now_date_time = $current_date_object->format('Y-m-d');
    // get SP entry id
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
    if( empty($sp_entry_id) ):
        $catch_error[] = 'Error: bb group id: ' . $bb_group_id . ' has no sp entry. <br>';
    else:
        // get schedules(s) entries
        $schedule_entry_ids = getScheduleEntryID($sp_entry_id);
        if( empty($schedule_entry_ids) ):
            $catch_error[] = 'Error: sp entry id: ' . $sp_entry_id . ' has no schedule entry. <br>';
        else:
            // loop schedules entry
            $total_current_mins = 0;
            $total_stopping_mins = 0;
            $total_future_mins = 0;

            $total_hrs = 0;
            $total_stopping_hrs = 0;
            $total_future_hrs = 0;

            foreach ( $schedule_entry_ids as $schedule_entry_id ):
                // get class days
                $bookly_class_days = getClassDays($schedule_entry_id);

                // get duration
                $bookly_class_duration = (int) getClassDuration($schedule_entry_id);

                // get start date
                $start_date = getGFentryMetaValue($schedule_entry_id, 9);
                if( !empty($start_date) ):
                    $start_date = date('Y-m-d', strtotime($start_date[0]->meta_value));
                endif;

                // get end date
                $end_date = getGFentryMetaValue($schedule_entry_id, 8);
                if( !empty($end_date) ):
                    $end_date = date('Y-m-d', strtotime($end_date[0]->meta_value));
                endif;

                if(
                    // Consider Schedule (Start Date < Now) && (End Date Empty or > Now) => current
                    (  strtotime($start_date) <= strtotime($now_date_time) ) && ( strtotime($end_date) > strtotime($now_date_time)  && !empty($start_date) && !empty($end_date) ) ||
                    // start date < now && no end date
                    ( strtotime($start_date) <= strtotime($now_date_time) ) && empty($end_date)
                ):
                    // calculate totals
                    $total_current_mins += $bookly_class_duration * count($bookly_class_days) * 4;
                endif;


                // calculate stopping mins
                if(
                    // Consider Schedule (Start Date < Now) && (End Date Empty or < Now) => in the past
                    strtotime($start_date) <= strtotime($now_date_time) &&  strtotime($end_date) > strtotime($now_date_time)  && !empty($start_date) && !empty($end_date) ) :
                    $total_stopping_mins += $bookly_class_duration * count($bookly_class_days) * 4;
                endif;

                // calculate future mins
                if(
                    // Consider Schedule (Start Date > Now) => in the future
                    ( strtotime($now_date_time) < strtotime($start_date) ) && !empty($start_date)
                ):
                    $total_future_mins += $bookly_class_duration * count($bookly_class_days) * 4;
                endif;

            endforeach; // end schedules loop

            if( $total_current_mins > 0 ):
                $total_hrs = round($total_current_mins / 60, 2);
            endif;

            if( $total_stopping_mins > 0 ):
                $total_stopping_hrs = round($total_stopping_mins / 60, 2);
            endif;

            if( $total_future_mins > 0 ):
                $total_future_hrs = round($total_future_mins / 60, 2);
            endif;

            // total hours = duration * count(days)
            $total_program_hours = array(
                $total_hrs,
                $total_stopping_hrs,
                $total_future_hrs
            );

            // active => currrent > 0
            if( $total_hrs > 0 ):
                //update gf entry with data ( current )
                gform_update_meta( $sp_entry_id, 26, 'Active' );
            // future => current = 0 && future > 0
            elseif ( $total_hrs === 0 && $total_future_hrs > 0 ):
                gform_update_meta( $sp_entry_id, 26, 'Future' );
            // inactive => current = 0 && future = 0
            elseif ( $total_hrs === 0 && $total_future_hrs === 0 ):
                gform_update_meta( $sp_entry_id, 26, 'Inactive' );
            endif;


        endif;
    endif;

    if( !empty($catch_error) ):
        // log error to activity
        addLog(
            array(
                'event_title' => "Error: in getting program total hrs for group $bb_group_id",
                'event_desc' => json_encode($catch_error),
                'user_id' => get_current_user_id()
            )
        );
        return false;
    else:
        return array(
            'current_total_hrs' => $total_hrs,
            'stopping_total_hrs' => $total_stopping_hrs,
            'starting_total_hrs' => $total_future_hrs
        );
    endif;

}

// function to get paid hours for parent
function getParentPaidHrs($wp_user_id){
    // get parent user subs
    $subs_qty = 0;
    $user_subs = get_active_subs_for_parent_user($wp_user_id);
    if( empty($user_subs['data']) ):
        return false;
    else:
        foreach ( $user_subs['data'] as $sub ):
            // check if sub is active
            if( $sub->active === true ):
                // get sub qty
                $subs_qty += $sub->quantity;
            endif;
        endforeach;
        return $subs_qty;
    endif;

}

// function to update GF sp entry program with total hours
function updateProgramTotalHours($bb_group_id) {
    $bb_group_id = (int) $bb_group_id;
    $group_total_hrs = getProgramTotalHours($bb_group_id);


    if( empty($group_total_hrs) ):
        $group_total_hrs = array(
            'current_total_hrs' => 0,
            'stopping_total_hrs' => 0,
            'starting_total_hrs' => 0
        );
    endif;

    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
    if( !empty($sp_entry_id) ):

        //update gf entry with data ( current )
        gform_update_meta( $sp_entry_id, 27, $group_total_hrs['current_total_hrs'] );

        // update gf entry with data ( stopping )
        gform_update_meta( $sp_entry_id, 28, $group_total_hrs['stopping_total_hrs'] );

        // update gf entry with data ( upcoming )
        gform_update_meta( $sp_entry_id, 29, $group_total_hrs['starting_total_hrs'] );

    endif;

}

// add/update user billing indicator ( Good, MH, Md, Inactive ) based on his subscriptions
function updateUserBillingIndicator($wp_user_id ,$user_email) {

    $catch_error = '';
    if( !empty($user_email) ):
        $wp_user_obj = get_user_by( 'email', $user_email );
        $wp_user_id = $wp_user_obj->data->ID;
    endif;

    $keap_data = get_parent_stats_from_keap($wp_user_id); // in future call from user meta not keap api


    if( !empty($keap_data) ):


        $balance_due = $keap_data['balance_due'];
        $last_payment = $keap_data['get_last_payment_on'];
        $renews_on = $keap_data['get_renews_on'];
        $cancelled_amount = $keap_data['inactive_total_subs'];
        $paid_amount = $keap_data['active_total_subs'];
        $user_subs = $keap_data['active_subs'];



        $parent_total_hours = getParentTotalHours($wp_user_id,'');


        if( !empty($last_payment) ):
            $last_payment_date = date('Y-m-d', strtotime($last_payment));
        endif;


        $parent_stats['id'] = '';
        $parent_stats['parent_wp_user_id'] = $wp_user_id;
        $contact_id = wpf_get_contact_id($wp_user_id);
        $parent_stats['keap_user_id'] = $contact_id;

        $user_tickets = user_tickets_count($wp_user_id);
        if( empty($user_tickets) ):
            $user_tickets = null;
        else:
            $user_tickets = json_encode($user_tickets);
        endif;


        $user_happiness_rate = get_user_happiness_rate($wp_user_id);
        if( empty($user_happiness_rate) ):
            $user_happiness_rate = '';
        else:
            $user_happiness_rate = json_encode(
                array(
                    'badPercentage' => $user_happiness_rate->badPercentage,
                    'okPercentage' => $user_happiness_rate->okPercentage,
                    'goodPercentage' => $user_happiness_rate->goodPercentage
                )
            );

        endif;

        $users_list[] = $wp_user_id;
        // check if parent
        // get childs for parent
        //$childs = getParentActiveChilds($wp_user_id);

        $childs = getParentActiveChilds($wp_user_id);
        if( ! empty($childs) ):
            $users_list = array_merge($users_list, $childs);
        else:
            $childs = [];
        endif;



        foreach ( $users_list as $user_id ):
            $user_id = (int) $user_id;
            $user_groups[$user_id] = groups_get_user_groups($user_id);
        endforeach;

        // get parent user subs
        $subs_qty = 0;
        $all_cancelled = true; // this flag is true when no one of user subs is inactive
        $non_renewal_indicator = true; // this flag is true when user will not renewing his subs
        //    $user_subs = get_active_subs_for_parent_user($wp_user_id);

        if( empty($user_subs) ):
            $non_renewal_indicator = false;
            $all_cancelled = false;
            $catch_error .= "Error: user $wp_user_id with contact id ($contact_id) has no subs ";
        else:
            foreach ( $user_subs as $sub ):
                // check if sub is active
                if( $sub->active === true ):
                    $all_cancelled = false;
                    // get sub qty
                    $subs_qty += $sub->quantity;
                endif;


                if( $sub->active === true && empty($sub->end_date) ):
                    $non_renewal_indicator = false;
                endif;

            endforeach;

        endif;

        // update mslm_non_renewal_indicator
        update_field('mslm_non_renewal_indicator', $non_renewal_indicator, 'user_'.$wp_user_id);


        $total_group_future_hrs = 0;
        $total_group_current_hrs = 0;
        $cids = [];
        foreach ( $user_groups as $user_group ):
            // get total hours
            if( !empty($user_group['groups']) ):

                foreach ( $user_group['groups'] as $group_id ):
                    $cids[] = $group_id;
                    updateProgramTotalHours($group_id);
                    // get SP GF entry id
                    $sp_entry_id = getBBgroupGFentryID($group_id);
                    // get current total hrs
                    $current_total_hrs = getGFentryMetaValue($sp_entry_id, 27);
                    if( !empty($current_total_hrs) ) $current_total_hrs = $current_total_hrs[0]->meta_value;
                    // get stopping total hrs
                    $stopping_total_hrs = getGFentryMetaValue($sp_entry_id, 28);
                    if( !empty($stopping_total_hrs) ) $stopping_total_hrs = $stopping_total_hrs[0]->meta_value;
                    // get starting total hrs
                    $starting_total_hrs = getGFentryMetaValue($sp_entry_id, 29);
                    if( !empty($starting_total_hrs) ) $starting_total_hrs = $starting_total_hrs[0]->meta_value;

                    $total_group_future_hrs += $current_total_hrs + $starting_total_hrs - $stopping_total_hrs;
                    $total_group_current_hrs += $current_total_hrs;

                    // echo 'bb_group_id: '. $group_id . '  -- current: ' . $current_total_hrs . ' -- stopping: ' . $stopping_total_hrs . ' -- starting: ' . $starting_total_hrs .'<br>';

                endforeach;  // end loop single user groups

            endif;

        endforeach; // end loop users groups

        $cids = array_merge($cids);
        $unique_cids = array_unique($cids);




        //echo '<hr> Total future: ' . $total_group_future_hrs . ' ***** Total current: ' . $total_group_current_hrs .' Total subs: '. $subs_qty .'<br>';

        // set account status meta (mslm_account_status)
        $mslm_account_status = 'No Data';
        // OnVacation => on vacation tag is found for parent
        if( wpf_has_tag( 394 , $wp_user_id ) ):
            $mslm_account_status = 'on_vacation';
        // Trialing => if parent has trialing tag
        elseif( wpf_has_tag( 380 , $wp_user_id ) ):
            $mslm_account_status = 'trialing';
        // Inactive => if has tag "all cancelled"
        elseif( wpf_has_tag( 362 , $wp_user_id ) ):
            $mslm_account_status = 'inactive';
        // Active => if has tag "active subscriptions"
        elseif( wpf_has_tag( 256 , $wp_user_id ) ):
            $mslm_account_status = 'active';
        endif;

        // update account status meta (mslm_account_status)
        update_field('mslm_account_status', $mslm_account_status, 'user_'.$wp_user_id);


        // set mslm_billing_indicator
        $mslm_billing_indicator = 'No Data';
        if( $total_group_future_hrs !== $subs_qty && $subs_qty > 0 ):
            $mslm_billing_indicator = 'mismatch';
        elseif( $balance_due > 0 ):
            $mslm_billing_indicator = 'overdue';
        else:
            $mslm_billing_indicator = 'good';
        endif;

        // update mslm_billing_indicator
        update_field('mslm_billing_indicator', $mslm_billing_indicator, 'user_'.$wp_user_id);



        // update parent_stats table

        global $wpdb;
        $parent_stats_table = $wpdb->prefix . 'muslimeto_parent_stats';
        $parent_stats_results = $wpdb->get_results(
            "SELECT * FROM $parent_stats_table WHERE parent_wp_user_id = {$wp_user_id}"
        );
        $wpdb->flush();

        $update_status_zoho_flag = false;
        $update_status_keap_flag = false;

        // check if parent not found on table insert
        if( empty( $parent_stats_results ) ):
            unset($parent_stats['id']);
            $parent_stats['cids'] = json_encode( array_merge($unique_cids) );
            $parent_stats['active_childs'] = json_encode( $childs );
            $parent_stats['total_hours'] = json_encode( $parent_total_hours ); // total_current_hrs, total_stopping_hrs, total_starting_hrs
            $parent_stats['support_tickets'] = $user_tickets;
            $parent_stats['happiness_rate'] = $user_happiness_rate;

            // check for keap values, if empty dont store or update
            if( $balance_due >= 0 ):
                $parent_stats['due_balance'] = $balance_due;
                $parent_stats['renew_on'] = $renews_on;
                $parent_stats['paid_amount'] = $paid_amount;
                $parent_stats['cancelled_amount'] = $cancelled_amount;
                $parent_stats['paid_hours'] = $subs_qty;
                $parent_stats['last_payment'] = $last_payment_date;
            endif;


            if( wpdb_bulk_insert( $parent_stats_table, array($parent_stats) ) !== 1 ):
                $catch_error .= ' Error: in inserting parent stats record. <br>';
            endif;


        // else update its record, on in case of one column has error from API dont update row
        else:

            $parent_stats_record = $parent_stats_results[0]->id;
            $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
            $updated_at = $current_date_object->format('Y-m-d H:i:s');
            $parent_stats['id'] = $parent_stats_record;


            // Update internal info:  cids, active_childs, parent_total_hrs
            if( !empty($unique_cids) && !empty($childs) && !empty($parent_total_hours) ):
                $parent_stats['cids'] = json_encode( array_merge($unique_cids) );
                $parent_stats['active_childs'] = json_encode( $childs );
                $parent_stats['total_hours'] = json_encode( $parent_total_hours ); // total_current_hrs, total_stopping_hrs, total_starting_hrs
                updateRecord($parent_stats_table, $parent_stats);
            endif;

            // Update zoho data: support_tickets, happiness_rate ( Flag update_status )
            if( !empty($user_tickets) && !empty($user_happiness_rate) ):
                $parent_stats['support_tickets'] = $user_tickets;
                $parent_stats['happiness_rate'] = $user_happiness_rate;
                updateRecord($parent_stats_table, $parent_stats);
                $update_status_zoho_flag = true;
            endif;

            // update keap info:  due_balance, renew_on, paid_amount, cancalled_amount, paid_hours, last_payment
            // old condition  !empty($balance_due) && !empty($last_payment) && !empty($user_subs)

            if( $balance_due >= 0 ):

                $parent_stats['due_balance'] = $balance_due;
                $parent_stats['renew_on'] = $renews_on;
                $parent_stats['paid_amount'] = $paid_amount;
                $parent_stats['cancelled_amount'] = $cancelled_amount;
                $parent_stats['paid_hours'] = $subs_qty;
                $parent_stats['last_payment'] = $last_payment_date;

                updateRecord($parent_stats_table, $parent_stats);
                $update_status_keap_flag = true;

            endif;

            // last updated: update this column only updated_at if $update_status_flag == true
            if( $update_status_zoho_flag == true && $update_status_keap_flag == true ):
                $parent_stats['update_status'] = 'full';
                $parent_stats['updated_at'] = $updated_at;
                updateRecord($parent_stats_table, $parent_stats);

            elseif( $update_status_zoho_flag == true || $update_status_keap_flag == true ):

                $parent_stats['update_status'] = 'partial';
                $parent_stats['updated_at'] = $updated_at;
                updateRecord($parent_stats_table, $parent_stats);
                addLog( array(
                    'event_title' => "Warning: Parent stats partial update",
                    'event_desc' => "Partial update for userID: $wp_user_id",
                ) );

            else:
                $parent_stats['update_status'] = 'limited';
                $parent_stats['updated_at'] = $updated_at;
                updateRecord($parent_stats_table, $parent_stats);
                addLog( array(
                    'event_title' => "Warning: Parent stats limited update",
                    'event_desc' => "Limited update for userID: $wp_user_id",
                ) );
            endif;

        endif;

    else:
        addLog( array(
            'event_title' => "Error: No Keap Data",
            'event_desc' => "empty keap data for userID: $wp_user_id",
        ) );
    endif;


}

// function to get group members names
function getBBgroupMembersNames($bb_group_id){
    $args = array(
        'group_id' => $bb_group_id,
        'max' => 999,
        'exclude_admins_mods' => true,
        'exclude_banned' => true,
    );
    $catch_error = '';
    $members = groups_get_group_members( $args );

    if( !empty($members) ):
        $members_id = array_column($members['members'], 'ID');
        foreach ( $members_id as $member_id ):
            // get user full name
            $user_full_name[] = getCustomerFullName($member_id);
        endforeach;

        return $user_full_name;
    else:
        return false;
    endif;

}

// function to get group schedule(s)
function getBBgroupSchedules($bb_group_id) {
    $catch_error = '';
    $schedules_data = [];
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
    if( empty($sp_entry_id) ) $catch_error .= 'Error: bb group id: ' . $bb_group_id . ' has no SP entry. <br>';

    // get schedule entry for sp entry
    $schedule_entry_ids = getScheduleEntryID($sp_entry_id);
    if( empty($schedule_entry_ids) ):
        $catch_error .= 'Error: sp entry id: ' . $sp_entry_id . ' has no Schedule entry. <br>';
    else:
        foreach ( $schedule_entry_ids as $schedule_entry_id ):
            $end_date_meta = getGFentryMetaValue($schedule_entry_id, 8); // get end_date value
            // if schedule has end_date exclude it
            if( $end_date_meta == false ):
                $class_days = getClassDays($schedule_entry_id);
                $start_time = getStartTime($schedule_entry_id);
                $schedules_data[] = array(
                    $class_days,
                    $start_time
                );
            endif;
        endforeach;

    endif;

    if( !empty($catch_error) ):
        return false;
    else:
        return $schedules_data;
    endif;




}

// function to get group schedules as text
function getBBgroupSchedulesAsText($bb_group_id, $timezone1 = false, $timezone2 = false){
    $schedules_data = getBBgroupSchedules($bb_group_id);
    if( empty($schedules_data) ) return false;
    foreach( $schedules_data as $key=>$schedule_data ):
        foreach ( $schedule_data[0] as $small_day ):
            $start_days[] = ucfirst($small_day);
        endforeach;
        //$start_days = $schedule_data[0];
        $start_time = date('h:i a', strtotime($schedule_data[1]));
        $schedule_text[] = $start_time . ' ' . implode(', ', $start_days);
        if( !empty($timezone1) && !empty($timezone2) ):
            // convert start time from timezone1 to timezone2
            foreach ( $start_days as $start_day ):
                $date_time = date('Y-m-d h:i a', strtotime($start_day. ' ' . $schedule_data[1]));
                $start_time_con = convertTimezone1ToTimezone2 ( $date_time, $timezone1, $timezone2 );
                $start_time_converted[$key] = date('h:i a', strtotime($start_time_con));
                $start_day_converted[$key][] = date('D', strtotime($start_time_con));
            endforeach;
        endif;
    endforeach;

    if( !empty($start_time_converted) && !empty($start_day_converted) ):
        foreach ( $start_time_converted as $key=>$start_time ):
            $schedule_text_converted[] = $start_time . ' ' . implode(', ', $start_day_converted[$key]);
        endforeach;
    endif;
    if( !empty($timezone1) && !empty($timezone2) ):
        return array(
            'schedule_text' => $schedule_text,
            'schedule_text_converted' => $schedule_text_converted,
        );
    else:
        return $schedule_text;
    endif;
}

//get_active_subs_for_parent_user
function get_active_subs_for_parent_user($wp_user_id){
    $ContactId  = wpf_get_contact_id($wp_user_id);
    if( empty($ContactId) ) return false;
//    $token = get_option('keap_access_token');
    $token = get_token_from_dev();
    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/subscriptions?contact_id=".$ContactId
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    $actives=array();

    if(isset($data->subscriptions)):
        $subscriptions = $data->subscriptions;
        foreach ($subscriptions as $subscription) {
            if($subscription->active){
                $actives[]= $subscription;
            }
        }
        return array('data'=> $actives);
    else:
        return array('error'=>"parent has no subscriptions.");
    endif;
}

// function to get all parent users
function getAllParents() {
    $args = array(
        'role__in'       => 'parent',
        'orderby'    => 'ID',
        'order'      => 'ASC',
    );
    $parents = get_users( $args );
    if( !empty($parents) ):
        return $parents;
    else:
        return false;
    endif;

}

// function to get parent and his active childs total hours
function getParentTotalHours($wp_user_id ,$user_email) {
    $catch_error = '';
    if( !empty($user_email) ):
        $wp_user_obj = get_user_by('email', $user_email);
        $wp_user_id = $wp_user_obj->data->ID;
    endif;
    $users_list[] = $wp_user_id;

    // check if parent
    if( checkIfParent($wp_user_id) !== true ):
        $catch_error .= 'Error: user is not a parent. <br>';
    else:
        // get childs for parent
        //$childs = getParentActiveChilds($wp_user_id);
        $childs = getParentChilds($wp_user_id);

        if( ! empty($childs) ):
            $users_list = array_merge($users_list, $childs);
        endif;

        foreach ( $users_list as $user_id ):
            $user_id = (int) $user_id;
            $user_groups[$user_id] = groups_get_user_groups($user_id);
        endforeach;

        $total_current_hrs = 0;
        $total_stopping_hrs = 0;
        $total_starting_hrs = 0;

        foreach ( $user_groups as $user_id=>$user_group ):
            // get total hours
            if( !empty($user_group['groups']) ):

                foreach ( $user_group['groups'] as $group_id ):
                    // get SP GF entry id
                    $sp_entry_id = getBBgroupGFentryID($group_id);
                    // get current total hrs
                    $current_total_hrs = getGFentryMetaValue($sp_entry_id, 27);
                    if( !empty($current_total_hrs) ) $current_total_hrs = (int) $current_total_hrs[0]->meta_value;
                    // get stopping total hrs
                    $stopping_total_hrs = getGFentryMetaValue($sp_entry_id, 28);
                    if( !empty($stopping_total_hrs) ) $stopping_total_hrs = (int) $stopping_total_hrs[0]->meta_value;
                    // get starting total hrs
                    $starting_total_hrs = getGFentryMetaValue($sp_entry_id, 29);
                    if( !empty($starting_total_hrs) ) $starting_total_hrs = (int) $starting_total_hrs[0]->meta_value;

                    //echo 'bb_group_id: '. $group_id . ' -- user_id: '. $user_id .'  -- current: ' . $current_total_hrs . ' -- stopping: ' . $stopping_total_hrs . ' -- starting: ' . $starting_total_hrs .'<br>';

                    $total_current_hrs += $current_total_hrs;
                    $total_stopping_hrs += $stopping_total_hrs;
                    $total_starting_hrs += $starting_total_hrs;

                endforeach;  // end loop single user groups

            endif;

        endforeach; // end loop users groups



    endif; // end if user is parent

    if( !empty($catch_error) ):
        return array(
            'total_current_hrs' => 0,
            'total_stopping_hrs' => 0,
            'total_starting_hrs' => 0
        );
    else:
        return array(
            'total_current_hrs' => $total_current_hrs,
            'total_stopping_hrs' => $total_stopping_hrs,
            'total_starting_hrs' => $total_starting_hrs
        );
    endif;


}

// function to get post by meta vale
function getPostbyMetavalue($meta_value) {
    global $wpdb;
    $post_meta_table = $wpdb->prefix . 'postmeta';
    $meta_results = $wpdb->get_results(
        "SELECT * FROM $post_meta_table WHERE meta_value = '{$meta_value}'"
    );
    $wpdb->flush();
    if( !empty( $meta_results ) ):
        return $meta_results;
    endif;

    return false;

}

// function to delete recurring appointments after given date for given bb group id
function deleteBooklyAppointmentsInCancelMode($bb_group_id, $new_effective_date) {
    if( empty($new_effective_date) || empty($bb_group_id) ):
        return 'Error: Empty Fields when call deleteBooklyAppointmentsInCancelMode()';
    endif;
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $custom_fields = json_encode(
        array(
            'id' => $bb_custom_field_id,
            'value' => strval($bb_group_id)
        )
    );
    $catch_error = '';
    $new_effective_date = date('Y-m-d H:i:s', strtotime($new_effective_date));
    // get all CA records with bb_group_id from custom_fields and new effective from
    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $customer_appointments_results = $wpdb->get_results(
        "SELECT * FROM $bookly_customer_appointments_table WHERE custom_fields LIKE '%{$custom_fields}%'"
    );
    $wpdb->flush();

    
    if( !empty($customer_appointments_results) ):
        foreach ( $customer_appointments_results as $ca_result ):
            $appointment_id = $ca_result->appointment_id;
            $appointment_status = $ca_result->status;
            if( $appointment_status === 'approved' ):
                // get start date from appontment_table

                $appointments_results = $wpdb->get_results(
                    "SELECT * FROM $bookly_appointments_table WHERE id = {$appointment_id}"
                );
                $wpdb->flush();

                $start_date = $appointments_results[0]->start_date;
                $test_data[$appointment_id] = $appointment_status;
                if( strtotime($start_date) >= strtotime($new_effective_date) ):
                    $delete_appointments_data[$appointment_id] = $start_date;
                    $delete_appointments_ids[] = $appointment_id;
                    $delete_ca_appointments_ids[] = $ca_result->id;
                endif;

            endif;

        endforeach;

        // delete appointments id and ca_ids, and make schedule entry status = disapproved

        if( !empty($delete_appointments_ids) ):
            $appts_ids = implode(",", $delete_appointments_ids);
            if( $wpdb->query("DELETE FROM $bookly_appointments_table WHERE id IN ($appts_ids)") === false ):
                $catch_error .= 'Error in deleting bookly_appointments_table. <br>';
            endif;

            $ca_appts_ids = implode(",", $delete_ca_appointments_ids);
            if( $wpdb->query("DELETE FROM $bookly_customer_appointments_table WHERE id IN ($ca_appts_ids)") === false ):
                $catch_error .= 'Error in deleting bookly_ca_appointments_table. <br>';
            endif;
        endif;

    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;

}

// function to delete recurring appointments after given date ( for fixing timezone only )
function deleteBooklyAppointmentsAfterDate($stored_bookly_series_id, $new_effective_date, $appointment_ids_has_no_ca) {

    if( empty($new_effective_date) || empty($stored_bookly_series_id) ):
        return 'Error: Empty Fields when call deleteBooklyAppointmentsInCancelMode() fix timezone';
    endif;

    $catch_error = '';
    $new_effective_date = date('Y-m-d', strtotime($new_effective_date));
    // get all CA records with series_id and new effective from
    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $customer_appointments_results = $wpdb->get_results(
        "SELECT * FROM $bookly_customer_appointments_table WHERE series_id = {$stored_bookly_series_id}"
    );
    $wpdb->flush();

    if( !empty($customer_appointments_results) ):
        foreach ( $customer_appointments_results as $ca_result ):
            $appointment_id = $ca_result->appointment_id;
            $appointment_status = $ca_result->status;

            // get start date from appontment_table

            $appointments_results = $wpdb->get_results(
                "SELECT * FROM $bookly_appointments_table WHERE id = {$appointment_id}"
            );
            $wpdb->flush();

            $start_date = $appointments_results[0]->start_date;

            if( strtotime($start_date) >= strtotime($new_effective_date) ):
                $delete_appointments_data[$appointment_id] = $start_date;
                $delete_appointments_ids[] = $appointment_id;
                $delete_ca_appointments_ids[] = $ca_result->id;
            endif;


        endforeach;


        if( !empty($delete_appointments_ids) && !empty($appointment_ids_has_no_ca) ):
            array_merge($delete_appointments_ids, $appointment_ids_has_no_ca);
        endif;


        // delete appointments id and ca_ids, and make schedule entry status = disapproved

        if( !empty($delete_appointments_ids) ):
            $appts_ids = implode(",", $delete_appointments_ids);
            if( $wpdb->query("DELETE FROM $bookly_appointments_table WHERE id IN ($appts_ids)") === false ):
                $catch_error .= 'Error in deleting bookly_appointments_table. <br>';
            endif;

            if( !empty($delete_ca_appointments_ids) ):
                $ca_appts_ids = implode(",", $delete_ca_appointments_ids);
                if( $wpdb->query("DELETE FROM $bookly_customer_appointments_table WHERE id IN ($ca_appts_ids)") === false ):
                    $catch_error .= 'Error in deleting bookly_ca_appointments_table. <br>';
                endif;
            endif;
        endif;

    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;

}

function reGenerateAppointmentsfromGF($bookly_teacher_id, $bb_group_id ){

    $catch_error = '';
    global $wpdb;

    $bookly_effective_date = '03/21/2022';


    // check if this group has records in GF entries
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);


    if( $sp_entry_id !== false ):

        // get schedule entry for sp entry
        $schedule_entry_ids = getScheduleEntryID($sp_entry_id);

        // get learners entry for sp entry
        $learners_entry_id = getLearnersEntryID($sp_entry_id);

        if( empty($schedule_entry_ids) ):
            $catch_error .= 'Error: Group: ' . $bb_group_id . ' has no schedule(s) entries. <br>';
        elseif( empty($learners_entry_id) ):
            $catch_error .= 'Error: Group: ' . $bb_group_id . ' has no learners entry. <br>';
        else:
            // do recreate appointmnets
            // get learners wp_user_ids
            $learners_list = getLearnersWPuserIds($learners_entry_id);
            $learners_ids_string = substr( $learners_list, 1, -1 );
            $learners_ids_array = explode(',', $learners_ids_string);
            foreach ( $learners_ids_array as $learner_id ):
                $learner_ids = (int) preg_replace("/[^0-9]/","",$learner_id);
                $bookly_customer_ids[] = getcustomerID($learner_id);
            endforeach;



            $bookly_user_timezone = getSPentryTimezone($sp_entry_id);
            $bookly_user_timezone_offset = 0;
            $bookly_service_id = getBooklyServiceId($sp_entry_id);

            // loop schedules entry
            foreach ( $schedule_entry_ids as $schedule_entry_id ):
                $bookly_class_days[] = getClassDays($schedule_entry_id);
                $start_time = getStartTime($schedule_entry_id);
                $bookly_start_hours[] = explode(':', $start_time)[0];
                $bookly_start_minutes[] = explode(':', $start_time)[1];
                $bookly_class_duration[] = getClassDuration($schedule_entry_id);
                // get series is for schedules
                $single_series_id = getBooklySeriesId($schedule_entry_id);
                $series_ids[] = $single_series_id;


                // get start date for schedule entry and end date
                $start_date = getGFentryMetaValue($schedule_entry_id, 9);
                $end_date = getGFentryMetaValue($schedule_entry_id, 8);

                if( !empty($start_date) ):
                    $start_date = $start_date[0]->meta_value;
                endif;

                if( !empty($end_date) ):
                    $end_date = $end_date[0]->meta_value;
                    $end_date_to_compare[$single_series_id] = $end_date;
                endif;


                // if effective date >= end_date, ignore and don't regenrate appointmnets
                if( ( strtotime($bookly_effective_date) >= strtotime($end_date) ) && !empty($end_date) ):
                    $bookly_effective_date = false;
                elseif( ( strtotime($bookly_effective_date) <= strtotime($start_date) ) && !empty($start_date) ):
                    // if effective date <= start_date, make effective_date = start_date and regenrte starting from it
                    $bookly_effective_date = date('m/d/Y', strtotime($start_date));
                elseif ( ( strtotime($bookly_effective_date) > strtotime($start_date) ) && !empty($start_date) ):
                    // if schedule has start_date in the past and no end_date regenerate starting from effective_date
                    $bookly_effective_date = $bookly_effective_date;
                endif;


            endforeach; // end schedules loop


            if( !empty($bookly_effective_date) && isset($bookly_effective_date) ):

                $bookly_effective_day = strtolower( date('D', strtotime($bookly_effective_date)) );

                foreach ( $bookly_class_duration as $duration ):
                    $units[] = ( (int) $duration ) / 15 ;
                endforeach;



                if(
                    empty($bookly_teacher_id) ||
                    empty($bookly_customer_ids) ||
                    empty($bookly_user_timezone) ||
                    empty($bookly_start_hours) ||
                    empty($bookly_start_minutes) ||
                    empty($bookly_class_duration) ||
                    empty($bookly_effective_date) ||
                    empty($bookly_class_days) ||
                    empty($bookly_service_id)
                ):
                    return 'empty-fields';
                endif;


                // get week number and year number to generate effective dates for each row
                $effective_week_number = date("W", strtotime($bookly_effective_date));
                $effective_year_number = date('Y', strtotime($bookly_effective_date));
                $effective_month_number = (int) date('m', strtotime($bookly_effective_date));

                // fix wrong week number if in day in last week of previous year
                if( $effective_month_number === 1 && (int) $effective_week_number > 50):
                    $effective_week_number = 1;
                endif;


                $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
                $created_at = $current_date_object->format('Y-m-d H:i:s');

                // get effective start datetime
                for ( $i=0; $i<count($bookly_start_hours); $i++):
                    $bookly_end_minutes[] = convertToHoursMins( (int) $bookly_start_minutes[$i] + (int) $bookly_class_duration[$i] )['minutes'];
                    $bookly_end_hours[] = (int) $bookly_start_hours[$i] + convertToHoursMins( (int) $bookly_start_minutes[$i] + (int) $bookly_class_duration[$i] )['hours'];
                    $string_start_date[] = strtotime( $bookly_effective_date . ' ' . (int) $bookly_start_hours[$i] . ':' . (int) $bookly_start_minutes[$i] ); // mm/dd/yyyy H:m
                endfor;


                foreach ( $string_start_date as $start_date ):
                    $booking_user_start_date[] = date ("Y-m-d H:i:s", $start_date);
                endforeach;




                // get effective end datetime
                for( $i=0; $i<count($bookly_end_hours); $i++ ):
                    if( $bookly_end_hours[$i] == 24 ){
                        $string_end_date = strtotime( $bookly_effective_date . ' 23:59:00'  ); // mm/dd/yyyy H:m
                    } else {
                        $string_end_date = strtotime( $bookly_effective_date . ' ' . $bookly_end_hours[$i] . ':' . $bookly_end_minutes[$i] ); // mm/dd/yyyy H:m
                    }
                    $booking_user_end_date[] = date ("Y-m-d H:i:s", $string_end_date);
                endfor;

                // get new effective start and end dates for each booking row
                $effective_day_index = array_search($bookly_effective_day, WEEK_DAYS_INDEX);

                for( $i=0; $i<count($bookly_class_days); $i++ ):
                    if( $bookly_end_hours[$i] >= 24 ){
                        $bookly_end_hours[$i] = 23;
                        $bookly_end_minutes[$i] = 59;
                    }
                    foreach( $bookly_class_days[$i] as $bookly_class_day ):
                        $day_index = array_search($bookly_class_day, WEEK_DAYS_INDEX);
                        $week_day_index = array_search($bookly_class_day, SUN_WEEK_DAYS_INDEX);
                        $gendate = new DateTime();
                        $gendate->setISODate($effective_year_number,$effective_week_number,$week_day_index); //year , week num , day
                        $row_effective_start_dates[$i][] =  $gendate->format('Y-m-d '. $bookly_start_hours[$i] . ':' . $bookly_start_minutes[$i] . ':00' );
                        $row_effective_end_dates[$i][] = $gendate->format('Y-m-d '. $bookly_end_hours[$i] . ':' . $bookly_end_minutes[$i] . ':00');

                    endforeach;
                endfor;



                // check if effective date has days before it in schedule, and get previous dates to skip from recurring
                $bookly_effective_day_index =  array_search($bookly_effective_day, WEEK_DAYS_INDEX);


                foreach ( $row_effective_start_dates[0] as $row_effective_start_date ):
                    $class_day_name = strtolower( date('D', strtotime($row_effective_start_date)) );
                    $class_day_index = array_search($class_day_name, WEEK_DAYS_INDEX);
                    if( $class_day_index < $bookly_effective_day_index ){
                        $skip_start_previous_days[] = $row_effective_start_date;
                    }
                endforeach;

                foreach ( $row_effective_end_dates[0] as $row_effective_end_date ):
                    $class_day_name = strtolower( date('D', strtotime($row_effective_end_date)) );
                    $class_day_index = array_search($class_day_name, WEEK_DAYS_INDEX);
                    if( $class_day_index < $bookly_effective_day_index ){
                        $skip_end_previous_days[] = $row_effective_end_date;
                    }
                endforeach;

                // get recurring days for each row_start_date
                for( $i=0; $i<count($row_effective_start_dates); $i++ ):
                    // if schedule end_date > new_effective_date, run recurring stop date
                    $series_id = $series_ids[$i];
                    if( strtotime( $end_date_to_compare[$series_id] ) > strtotime($bookly_effective_date) ):
                        $end_date_to_stop = $end_date_to_compare[$series_id];
                        // set recurring start and stop to stop at end_date
                        foreach ( $row_effective_start_dates[$i] as $row_effective_start_date ):
                            // get reccurring dates for each start and end date
                            $recurringDatesStartArray = getReccurringDatesUntilinTimezone(date('m/d/Y H:i:s', strtotime($row_effective_start_date)), $end_date_to_stop, 'Y-m-d H:i:s' , $bookly_user_timezone);
                            $recurringDatesStartArray[] = convertTimeZoneToUTC( $row_effective_start_date , $bookly_user_timezone);
                            $rowReccurringStartDates[$i][] = $recurringDatesStartArray;
                        endforeach;

                        foreach ( $row_effective_end_dates[$i] as $row_effective_end_date ):
                            // get reccurring dates for each start and end date
                            $recurringDatesEndArray = getReccurringDatesUntilinTimezone( date('m/d/Y H:i:s', strtotime($row_effective_end_date)), $end_date_to_stop, 'Y-m-d H:i:s' , $bookly_user_timezone);
                            $recurringDatesEndArray[] = convertTimeZoneToUTC( $row_effective_end_date , $bookly_user_timezone);
                            $rowReccurringEndDates[$i][] = $recurringDatesEndArray;
                        endforeach;
                    else:
                        // set recurring to be +6 months
                        foreach ( $row_effective_start_dates[$i] as $row_effective_start_date ):
                            // get reccurring dates for each start and end date
                            $recurringDatesStartArray = getReccurringDates( date('m/d/Y H:i:s', strtotime($row_effective_start_date)) ,500, 'Y-m-d H:i:s', $bookly_user_timezone);
                            $recurringDatesStartArray[] = convertTimeZoneToUTC( $row_effective_start_date , $bookly_user_timezone);
                            $rowReccurringStartDates[$i][] = $recurringDatesStartArray;
                        endforeach;

                        foreach ( $row_effective_end_dates[$i] as $row_effective_end_date ):
                            // get reccurring dates for each start and end date
                            $recurringDatesEndArray = getReccurringDates( date('m/d/Y H:i:s', strtotime($row_effective_end_date)) ,500, 'Y-m-d H:i:s', $bookly_user_timezone);
                            $recurringDatesEndArray[] = convertTimeZoneToUTC( $row_effective_end_date , $bookly_user_timezone);
                            $rowReccurringEndDates[$i][] = $recurringDatesEndArray;
                        endforeach;
                    endif;

                endfor;






                //set bookly custom fields data for new records
                $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
                $custom_fields = json_encode(
                    array(
                        array(
                            'id' => $bb_custom_field_id,
                            'value' => strval($bb_group_id)
                        )
                    )
                );


                // create appointments records
                for( $i=0; $i<count($rowReccurringStartDates); $i++ ):
                    //echo '------------------ Row ' . $i . ' ----------------------- <br>';

                    $series_id = $series_ids[$i];

                    // start inserting bookly_appointments table

                    for( $x=0; $x<count($rowReccurringStartDates[$i]); $x++ ):
                        //echo '==== Day ' . $x .' ====<br>';
                        for( $d=1; $d<=count($rowReccurringStartDates[$i][$x]); $d++ ):
                            //echo 'start: ' . $rowReccurringStartDates[$i][$x][$d]. ' ---  end: ' . $rowReccurringEndDates[$i][$x][$d] .'<br>';

                            $booking_start_date = $rowReccurringStartDates[$i][$x][$d];
                            $booking_end_date   = $rowReccurringEndDates[$i][$x][$d];

                            // for each class_day in row get recurring dates ( start and end ) -> create appointment record
                            // insert appointments into bookly_appointments table
                            $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
                            $appointments = array(
                                array(
                                    'staff_id' => $bookly_teacher_id,
                                    'staff_any' => 0,
                                    'service_id' => $bookly_service_id,
                                    'start_date' => $booking_start_date,
                                    'end_date' => $booking_end_date,
                                    'extras_duration' => 0,
                                    'internal_note' => '',
                                    'created_from' => 'bookly',
                                    'created_at' => $created_at,
                                    'updated_at' => $created_at
                                ),
                            );


                            if( wpdb_bulk_insert($bookly_appointments_table, $appointments) === 1 ):
                                //if record true, get appointment id
                                $appointment_id = $wpdb->insert_id;

                                // get appointment record id to use it in customer_appointments table and use social group id as custom_fields
                                $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';

                                // start inserting bookly_customer_appointments table
                                foreach ( $bookly_customer_ids as $key=>$bookly_customer_id ):

                                    $customer_appointments_array[$key] = array(
                                        'series_id' => (int) $series_id,
                                        'customer_id' => $bookly_customer_id,
                                        'appointment_id' => $appointment_id,
                                        'number_of_persons' => 1,//count( $bookly_customer_ids ),
                                        'units' => $units[$i],
                                        'extras' => [],
                                        'extras_multiply_nop' => 1,
                                        'extras_consider_duration' => 1,
                                        'custom_fields' => $custom_fields,
                                        'status' => 'approved',
                                        'token' => generateUniqueToken(),
                                        'time_zone' => 'UTC',
                                        'time_zone_offset' => $bookly_user_timezone_offset,
                                        'created_from' => 'frontend',
                                        'created_at' => $created_at,
                                        'updated_at' => $created_at
                                    );

                                endforeach;


                                $customer_appointments = $customer_appointments_array;

                                if( wpdb_bulk_insert($bookly_customer_appointments_table, $customer_appointments) !== 1 ):
                                    // do nothing, insert success
                                    //$catch_error .= 'Error in inserting customer appointment <br>';
                                    $wpdb->show_errors();
                                endif;
                            else:
                                $wpdb->show_errors();
                                $catch_error .= 'Error in inserting bookly appointments table: '.$wpdb->print_error().'<br>';
                            endif;

                        endfor;
                    endfor;
                endfor;

            endif;


        endif;

    else:
        $catch_error .= 'Error: bb_group_id: ' . $bb_group_id . ' has no SP entry id. <br>';
    endif;

    if( empty($catch_error) ):
        return true;
    else:
        return $catch_error;
    endif;

}

function regenerateSchedulefromGFentries($staff_id) {
    // get teachers appointments
    global $wpdb;
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_ca_table = $wpdb->prefix . 'bookly_customer_appointments';
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $parent_meta_key = 'workflow_parent_form_id_'. $SP_PARENT_FORM_ID .'_entry_id';
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $appts_results = $wpdb->get_results(
        "SELECT * FROM $bookly_appts_table WHERE staff_id ={$staff_id}"
    );
    $wpdb->flush();
    if( !empty($appts_results) ):

        foreach ( $appts_results as $appt_result ):
            $appointment_ids[] = $appt_result->id;

            // get series id for each appt_id
            $ca_results = $wpdb->get_results(
                "SELECT * FROM $bookly_ca_table WHERE appointment_id ={$appt_result->id}"
            );
            $wpdb->flush();

            if( !empty($ca_results) ):
                $series_ids[] = (int) $ca_results[0]->series_id;
            else:
                $appointment_ids_has_no_ca[] = $appt_result->id;
            endif;

            $stored_bb_group_custom_field = json_decode($ca_results[0]->custom_fields);
            foreach ( $stored_bb_group_custom_field as $field_data ):
                $custom_field_id = (int) $field_data->id;
                if( $custom_field_id === $bb_custom_field_id ):
                    $stored_bb_group_id[] = (int) $field_data->value;
                endif;

            endforeach;



        endforeach;

        $catch_error = '';

        $series_ids = array_unique($series_ids);
        $stored_bb_group_id = array_unique($stored_bb_group_id);
        $bookly_effective_date = '03/21/2022';


        foreach ( $stored_bb_group_id as $bb_group_id ):
            // update teachre for schedule entry if has no teacher assigned
            $update_schedule_teacher = setGFscheduleEntryTeacher($bb_group_id);
            if( $update_schedule_teacher !== true ):
                $catch_error .= $update_schedule_teacher;
            endif;

            // update start date for eschedule entry if not exist
            $update_schedule_start_date = setGFScheduleStartDate($bb_group_id);
            if( $update_schedule_start_date !== true ):
                $catch_error .= $update_schedule_start_date;
            endif;
        endforeach;


        foreach ( $series_ids as $series_id ):
            // delete future appointments starting from start effective date
            $delete_old_appts = deleteBooklyAppointmentsAfterDate($series_id, $bookly_effective_date, $appointment_ids_has_no_ca);
            if( $delete_old_appts !== true ):
                $catch_error .= 'Error in deleting appointments for series_id: ' . $series_id . '<br>';
                $catch_error .= $delete_old_appts;
            endif;
        endforeach;


        foreach ( $stored_bb_group_id as $bb_group_id ):
            // create recurring array
            $reGenerateAppts = reGenerateAppointmentsfromGF($staff_id, $bb_group_id);
            if ( $reGenerateAppts !== true ):
                $catch_error .=  $reGenerateAppts;
            endif;
        endforeach;

    endif;

    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;

}

// function to get memory usage and peak memory usage
function print_mem()
{
    /* Currently used memory */
    $mem_usage = memory_get_usage();

    /* Peak memory usage */
    $mem_peak = memory_get_peak_usage(true)/1024/1024;
    //echo 'The script is now using: <strong>' . round($mem_usage / 1024 / 1024) . 'MB</strong> of memory.<br>';
    echo 'Peak usage: <strong>' .$mem_peak . 'MB </strong> of memory.';
}

// function to set start date for CID ( used to fix schedule entries that has no start_time )
function setGFScheduleStartDate($bb_group_id) {
    global $wpdb;
    $gf_entry_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_ca_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $catch_error = '';
    $custom_fields = json_encode(
        array(
            array(
                'id' => $bb_custom_field_id,
                'value' => strval($bb_group_id)
            )
        )
    );


    if( empty($bb_group_id) ) return false;
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);

    $ca_results = $wpdb->get_results(
        "SELECT * FROM $bookly_ca_table WHERE custom_fields LIKE '%{$custom_fields}%'"
    );
    $wpdb->flush();

    if( !empty( $ca_results ) ):
        foreach ($ca_results as $ca_result):
            $appt_id = $ca_result->appointment_id;
            $appts_results = $wpdb->get_results(
                "SELECT * FROM $bookly_appts_table WHERE id ={$appt_id}"
            );
            $wpdb->flush();
            if( !empty($appts_results) ):
                $start_dates_arr[] = $appts_results[0]->start_date;
            else:
                $catch_error = false;
            endif;
        endforeach;

        if( !empty($start_dates_arr) ):
            $start_date_to_update = date('m/d/Y' ,strtotime(sortDateTimeArrayASC($start_dates_arr)[0]));
            if( !empty($sp_entry_id) ):
                $schedule_entry = getScheduleEntryID($sp_entry_id);
                if( !empty($schedule_entry) ):
                    foreach ( $schedule_entry as $schedule_entry_id ):
                        // get start date value
                        $start_date = getGFentryMetaValue($schedule_entry_id, 9);
                        if( empty($start_date) ):
                            // if start date not found, gform_update_meta( $entry_id, $key, $value )
                            if( gform_update_meta( $schedule_entry_id, 9, $start_date_to_update ) === 1 ):
                                return true;
                            else:
                                return false;
                            endif;
                        else:
                            // if start date has a value do noting
                            return true;
                        endif;
                    endforeach;
                else:
                    return false;
                endif;
            else:
                return false;
            endif;
        else:
            return false;
        endif;

    else:
        return false;
    endif;

}

// function to update teacher from SP entry to Schedule entry ( fix for new schedule fields added )
function setGFscheduleEntryTeacher($bb_group_id) {
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
    if( empty($sp_entry_id) ) return false;

    $sp_entry_teacher = getGFentryMetaValue($sp_entry_id, 8);
    if( !empty($sp_entry_teacher) ):
        // update schedule entry with teacher id
        $bookly_teacher_id = $sp_entry_teacher[0]->meta_value;
        $schedule_entry_ids = getScheduleEntryID($sp_entry_id);
        if( empty($schedule_entry_ids) ) return false;
        foreach ($schedule_entry_ids as $schedule_entry_id):
            // get teacher value in schedule entry
            $schedule_entry_teacher = getGFentryMetaValue($schedule_entry_id, 10);
            if( !empty($schedule_entry_teacher) ):
                // schedule entry teacher has value do nothing
                return true;
            else:
                // update meta with teacher id
                if( gform_update_meta( $schedule_entry_id, 10, $bookly_teacher_id ) === 1 ):
                    return true;
                else:
                    return false;
                endif;
            endif;
        endforeach;

    else:
        return false;
    endif;

}

// function to fix if no GF learners entry found for sp entry
function fixGFmissingLearnersEntry($bb_group_id) {

    global $wpdb;
    $catch_error = '';
    // get sp entry for group id
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
    if( empty($sp_entry_id) ):
        $catch_error .= 'Error: bb group id: ' . $bb_group_id . ' has no SP entry. <br>';
    else:
        $learners_entry_id = getLearnersEntryID($sp_entry_id);
        if( empty($learners_entry_id) ):
            // get members joined to bb group
            $args = array(
                'group_id' => $bb_group_id,
                'max' => 999,
                'exclude_admins_mods' => true
            );

            $group_members = groups_get_group_members($args);
            if( empty($group_members) ):
                $catch_error .= 'Error: bb group id: ' . $bb_group_id . ' has no members. <br>';
            else:

                foreach ( $group_members['members'] as $member ):
                    $learners_list[] = $member->ID;
                endforeach;

                // insert entry in learners form
                $learners_url = rest_url( 'gf/v2/forms/'. LEARNERS_FORM_ID() .'/submissions' );
                $learners_form_data = array(
                    "input_3" => $learners_list,
                    "field_values" => "",
                    "source_page" => 1,
                    "target_page" =>  1
                );

                $learners_form_response = wp_remote_post( $learners_url, array(
                    'body'    => $learners_form_data,
                    'headers' => REST_HEADERS(),
                ) );

                // Check the response code.
                if (  empty( wp_remote_retrieve_body( $learners_form_response ) )  ) {
                    // If not a 200, HTTP request failed.
                    $catch_error .= 'Error in submitting Learners(s) Gravity Form entry. There was an error attempting to access the API.<br>';
                } else {
                    $learners_form_entry_status = json_decode($learners_form_response['body'])->is_valid;
                }

                if( $learners_form_entry_status !== true && !empty($learners_form_entry_status) ):
                    $catch_error .= 'Error: Learner(s) entry insertion not Completed<br>';
                else:
                    // change status to sp entry to approved
                    $learners_url_entries = rest_url( 'gf/v2/entries/?form_ids[0]='. LEARNERS_FORM_ID() . '&sorting[key]=id&sorting[direction]=DESC&sorting[is_numeric]=true&paging[page_size]=1' );
                    $learners_entries_response = wp_remote_get( $learners_url_entries, array(
                        'headers' => REST_HEADERS(),
                    ) );

                    $learners_last_entry_obj =json_decode($learners_entries_response['body']);
                    // approve entry
                    if( !empty($learners_last_entry_obj) ):
                        $learners_last_entry_id = $learners_last_entry_obj->entries[0]->id;

                        if( ! gform_update_meta( $learners_last_entry_id, 'is_approved', 1 ) ):
                            $catch_error .= 'Error in updating Learner(s) gravity form to Approved<br>';
                        endif;

                        // update created by current user
                        if ( ! setGFentryCreatedBy($learners_last_entry_id, get_current_user_id() ) ):
                            $catch_error .= 'Error: in update Learners GF entry Created By';
                        endif;

                    else:
                        $catch_error .= 'Error in getting Learner(s) Gravity Form Entries<br>';
                    endif;


                    // link entry to SP entry parent form
                    $parent_meta_key = 'workflow_parent_form_id_' . SP_PARENT_FORM_ID() . '_entry_id';
                    if( ! gform_add_meta($learners_last_entry_id, $parent_meta_key, $sp_entry_id) ):
                        $catch_error .= 'Error in linking learners(s) form entry with Single program form entry<br>';
                    endif;
                endif;

                echo '<br> bb group id: ' . $bb_group_id . ' has been fixed <br>';

            endif;

        endif;
    endif;


    if( !empty($catch_error) ):
        return $catch_error;
    else:
        return true;
    endif;

}

// function to get trouble classes which parent has overdue
function getTroubleUpcomingClasses(){
    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $paren_stats_table = $wpdb->prefix . 'muslimeto_parent_stats';
    $now_date = gmdate("Y-m-d H:i:s");

    $start_query = date("Y-m-d H:i:s", strtotime('-1 hours', strtotime($now_date)));
    $end_query = date("Y-m-d H:i:s", strtotime('+8 hours', strtotime($now_date)));


    $upcoming_ppointments_results = $wpdb->get_results(
        "SELECT * FROM $bookly_appointments_table WHERE start_date >= '{$start_query}' AND end_date <= '{$end_query}' ORDER BY start_date ASC "
    );
    $wpdb->flush();



    if( !empty( $upcoming_ppointments_results ) ):
        $trouble_data = [];
        // get ca record
        foreach ( $upcoming_ppointments_results as $upcoming_ppointments_result ):
            $appointmnet_id = $upcoming_ppointments_result->id;
            $ca_record = getBooklyCA($appointmnet_id);
            $bb_group_id_data = json_decode($ca_record[0]->custom_fields);
            if( !empty($bb_group_id_data) ):
                $bb_group_id = $bb_group_id_data[0]->value;
            else:
                $bb_group_id = '';
            endif;
            $customer_id = $ca_record[0]->customer_id;
            // get child user id
            $child_wp_user_id = getBooklyWpUserId($customer_id);

            // get parent id
            $parent_id = getParentID($child_wp_user_id);

            if( !empty($parent_id) ):
                $parent_id = $child_wp_user_id;
            else:
                $parent_id = 0; // no parent found
            endif;

            // check if this parent has record and has overdue
            // search in parent stats table if this parent has record and has overdue
            $upcoming_trouble_results = $wpdb->get_results(
                "SELECT * FROM $paren_stats_table WHERE parent_wp_user_id = {$parent_id} AND due_balance > 0"
            );
            $wpdb->flush();


            if(!empty($upcoming_trouble_results)):
                $trouble_data[] = array(
                    'child_wp_user_id' => $child_wp_user_id,
                    'parent_wp_user_id' => $parent_id,
                    'event_start_date' => $upcoming_ppointments_result->start_date,
                    'event_end_date' => $upcoming_ppointments_result->end_date,
                    'staff_id' => $upcoming_ppointments_result->staff_id,
                    'bb_group_id' => $bb_group_id,
                    'last_payment' => $upcoming_trouble_results[0]->last_payment
                );
            endif;

        endforeach;

    endif;


    return $trouble_data;
}

// function to calculate upcoming time difference from now in UTC
function getTimeDiffinUTC($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $sign = $diff->format('%R');
    $hrs = $diff->h;
    $mins = $diff->i;
    $sec = $diff->s;


    if( $sign === '-' ) return false;


    if( $hrs === 0 && $mins > 0 )
        return $diff->format('0 hrs %i mins'); // return diff in xx mins xx sec

    if( $hrs > 0 )
        return $diff->format('%h hrs %i mins');

    if( $mins === 0 )
        return 'less than a minute';



    return $diff->format('0 hrs %i mins'); // return diff in xx mins xx sec

}

// function to get zoom meeting id for bb group , or for teacher in tutoring
function getZoomMeetingID($bb_group_id){
    $bb_group_id = (int) $bb_group_id;
    $bb_group_type = getBBgroupType($bb_group_id);
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
    if( empty($sp_entry_id) ) return false;
    global $wpdb;
    $gf_meta_table = $wpdb->prefix . 'gf_entry_meta';
    // if group is one-to-one || one-on-one => get zoom meeting id from employee profile wphr
    if( $bb_group_type === 'one-to-one' || $bb_group_type === 'one-on-one' ):
        // get teacher from sp_entry_id
        $teacher_bookly_id = getSPentryStaffId($sp_entry_id);
        if( empty($teacher_bookly_id) ) return false;
        $teacher_wp_user_id = (int) getStaffwp_user_id($teacher_bookly_id);
        $zoom_meeting_id = get_user_meta($teacher_wp_user_id, 'meeting_id', true);
        if( empty($zoom_meeting_id) ) return false;
        return $zoom_meeting_id;
    // if group is (mvs, family, open) get zoom meeting id from gf entry
    else:
        $zoom_meta_data = getGFentryMetaValue($sp_entry_id, 11);
        if( !empty($zoom_meta_data) ):
            return $zoom_meta_data[0]->meta_value;
        else:
            return false;
        endif;

    endif;

}

//function to return bookly CA events in a given period
function getCAeventsDuringPeriod($start_date, $end_date, $customers = []){
    if( empty($start_date) || empty($end_date) ) return false;
    // query bookly customers table where customer_id
    global $wpdb;
    $bookly_customer_appts_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';

    if( !empty($customers) ):
        $customers_list = implode(',', $customers);
        $query = "SELECT customer_id, status, appointment_id, custom_fields FROM $bookly_customer_appts_table WHERE appointment_id IN ( 
                    SELECT id FROM $bookly_appts_table WHERE start_date >= '{$start_date}' AND end_date <= '{$end_date}' ORDER by start_date ASC ) AND customer_id IN ({$customers_list})";
    else:
        $query = "SELECT customer_id, status, appointment_id, custom_fields FROM $bookly_customer_appts_table WHERE appointment_id IN ( 
                    SELECT id FROM $bookly_appts_table WHERE start_date >= '{$start_date}' AND end_date <= '{$end_date}' ORDER by start_date ASC )";
    endif;

    // get data from appointment_table
    $appointments_data = $wpdb->get_results($query);

    $wpdb->flush();


    return $appointments_data;


}

// function to calculate recorder hours for learner ( if parent for him and his childs)
function getRecordedHrs($wp_user_id,$events_data){
    if( empty($wp_user_id) || empty($events_data) ) return false;
    global $wpdb;
    $bookly_customer_appts_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';

    // if parent => get active childs appts
    $users_list = getParentActiveChilds($wp_user_id);

    if( empty($users_list) ):
        $users_list = [];
    endif;

    $family_total_events_hrs = 0;
    $family_total_recorded_hrs = 0;
    if( !empty($events_data) ):

        foreach ( $users_list as $user_id ):
            $events_duration_mins = 0;
            $recorded_duratuion_mins = 0;

            foreach ( $events_data as $ca_event ):

                // get bookly customer id
                $customer_id = getcustomerID($user_id);

                if( (int) $ca_event->customer_id === (int) $customer_id ):
                    $appointment_status = $ca_event->status;
                    $appointment_id = $ca_event->appointment_id;
                    // get data from appointment_table
                    $event_data = $wpdb->get_results(
                        "SELECT start_date, end_date FROM $bookly_appts_table WHERE id = {$appointment_id}"
                    );
                    $wpdb->flush();

                    // get start and end date
                    if( !empty($event_data) ):
                        $event_start_date = $event_data[0]->start_date;
                        $event_ene_date = $event_data[0]->end_date;
                        $events_duration_mins += ( strtotime($event_ene_date) - strtotime($event_start_date) ) / 60;
                    endif;

                    // get custom field  actual mins value as total hrs
                    $stored_custom_field = json_decode($ca_event->custom_fields);
                    foreach ( $stored_custom_field as $field_data ):
                        $custom_field_id = (int) $field_data->id;

                        // if status != approved => Recorded
                        if( $field_data->id === 2583 && $appointment_status !== 'approved' ): // actual mins value
                            $recorded_duratuion_mins += $field_data->value;
                            break;
                        endif;

                    endforeach;

                endif;

            endforeach;

            if( !empty($events_duration_mins) ):
                $total_events_hrs = round( $events_duration_mins / 60 , 1);
                $family_total_events_hrs += $total_events_hrs;
            else:
                $total_events_hrs = 0;
            endif;

            if( !empty($recorded_duratuion_mins) ):
                $recorded_hrs = round( $recorded_duratuion_mins / 60 , 1);
                $family_total_recorded_hrs += $recorded_hrs;
            else:
                $recorded_hrs = 0;
            endif;

            $recorded_hours['users'][$user_id] = array(
                'total_events_hrs' => $total_events_hrs,
                'recorded_hrs' => $recorded_hrs
            );

        endforeach;

        $recorded_hours['family_total_events_hrs'] = $family_total_events_hrs;
        $recorded_hours['family_total_recorded_hrs'] = $family_total_recorded_hrs;

    endif;

    return $recorded_hours;

}
function getRecordedHrsx($wp_user_id,$events_data){
    if( empty($wp_user_id) || empty($events_data) ) return false;
    global $wpdb;
    $bookly_customer_appts_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';

    $users_list = [];

    // if parent => get active childs appts
    $active_childs = getParentActiveChilds($wp_user_id);


    if( !empty($active_childs) ):
        $users_list = array_merge($users_list, $active_childs);
    endif;

    $events_duration_mins = 0;
    $recorded_duratuion_mins = 0;

    if( !empty($events_data) ):
        foreach ( $events_data as $ca_event ):

            foreach ( $users_list as $user_id ):
                // get bookly customer id
                $customer_id = getcustomerID($user_id);

                if( (int) $ca_event->customer_id === (int) $customer_id ):
                    $appointment_status = $ca_event->status;
                    $appointment_id = $ca_event->appointment_id;
                    // get data from appointment_table
                    $event_data = $wpdb->get_results(
                        "SELECT start_date, end_date FROM $bookly_appts_table WHERE id = {$appointment_id}"
                    );
                    $wpdb->flush();

                    // get start and end date
                    if( !empty($event_data) ):
                        $event_start_date = $event_data[0]->start_date;
                        $event_ene_date = $event_data[0]->end_date;
                        $events_duration_mins += ( strtotime($event_ene_date) - strtotime($event_start_date) ) / 60;
                    endif;

                    // get custom field  actual mins value as total hrs
                    $stored_custom_field = json_decode($ca_event->custom_fields);
                    foreach ( $stored_custom_field as $field_data ):
                        $custom_field_id = (int) $field_data->id;

                        // if status != approved => Recorded
                        if( $field_data->id === 2583 && $appointment_status !== 'approved' ): // actual mins value
                            $recorded_duratuion_mins += $field_data->value;


                            break;
                        endif;

                    endforeach;

                endif;

            endforeach;

        endforeach;
    endif;

    if( !empty($events_duration_mins) ):
        $total_events_hrs = round( $events_duration_mins / 60 , 1);
    else:
        $total_events_hrs = 0;
    endif;

    if( !empty($recorded_duratuion_mins) ):
        $recorded_hrs = round( $recorded_duratuion_mins / 60 , 1);
    else:
        $recorded_hrs = 0;
    endif;

    return array(
        'total_events_hrs' => $total_events_hrs,
        'recorded_hrs' => $recorded_hrs
    );



}

// function to count student late for learner ( if parent for him and his childs)
function getStudentLateTimes($wp_user_id,$events_data){
    global $wpdb;
    $bookly_customer_appts_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';

    $users_list = [];

    // if parent => get active childs appts
    $active_childs = getParentActiveChilds($wp_user_id);

    if( !empty($active_childs) ):
        $users_list = array_merge($users_list, $active_childs);
    endif;


    $family_student_late_times = 0;

    if( !empty($events_data) ):
        foreach ( $users_list as $user_id ):
            $times_student_late = 0;
            foreach ( $events_data as $ca_event ):

                // get bookly customer id
                $customer_id = getcustomerID($user_id);

                if( (int) $ca_event->customer_id === (int) $customer_id ):
                    $appointment_status = $ca_event->status;

                    if( $appointment_status === 'attended-sl' ):
                        $times_student_late++;
                        $family_student_late_times++;
                    endif;

                endif;

            endforeach;

            $learner_student_late['users'][$user_id] = $times_student_late;

        endforeach;
    endif;

    $learner_student_late['family_student_late_times'] = $family_student_late_times;

    return $learner_student_late;


}
function getStudentLateTimesx($wp_user_id,$events_data){
    global $wpdb;
    $bookly_customer_appts_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';

    $users_list = [];

    // if parent => get active childs appts
    $active_childs = getParentActiveChilds($wp_user_id);

    if( !empty($active_childs) ):
        $users_list = array_merge($users_list, $active_childs);
    endif;

    $times_student_late = 0;

    if( !empty($events_data) ):
        foreach ( $events_data as $ca_event ):

            foreach ( $users_list as $user_id ):
                // get bookly customer id
                $customer_id = getcustomerID($user_id);

                if( (int) $ca_event->customer_id === (int) $customer_id ):
                    $appointment_status = $ca_event->status;

                    if( $appointment_status === 'attended-sl' ):
                        $times_student_late++;
                    endif;

                endif;

            endforeach;

        endforeach;
    endif;

    return $times_student_late;


}

// function to count ( teacher no show, student cancalled, teacher cancalled) for learner ( if parent for him and his childs)
function getCancalledTimes($wp_user_id,$events_data){
    global $wpdb;
    $bookly_customer_appts_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';

    $users_list = [];

    // if parent => get active childs appts
    $active_childs = getParentActiveChilds($wp_user_id);

    if( !empty($active_childs) ):
        $users_list = array_merge($users_list, $active_childs);
    endif;


    $family_cancelled_times = 0;

    if( !empty($events_data) ):
        foreach ( $users_list as $user_id ):
            $times_cancelled = 0;
            foreach ( $events_data as $ca_event ):

                // get bookly customer id
                $customer_id = getcustomerID($user_id);

                if( (int) $ca_event->customer_id === (int) $customer_id ):
                    $appointment_status = $ca_event->status;
                    // if status ===  teacher no show OR student cancelled OR teacher cancelled => times++
                    if( $appointment_status === 'no-show-t' || $appointment_status === 'cancelled' || $appointment_status === 'excused-t' ):
                        $times_cancelled++;
                        $family_cancelled_times++;
                    endif;

                endif;

            endforeach;

            $total_cancelled['users'][$user_id] = $times_cancelled;

        endforeach;

    endif;

    $total_cancelled['family_cancelled_times'] = $family_cancelled_times;

    return $total_cancelled;


}
function getCancalledTimesx($wp_user_id,$events_data){
    global $wpdb;
    $bookly_customer_appts_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';

    $users_list = [];

    // if parent => get active childs appts
    $active_childs = getParentActiveChilds($wp_user_id);

    if( !empty($active_childs) ):
        $users_list = array_merge($users_list, $active_childs);
    endif;


    $times_cancalled = 0;

    if( !empty($events_data) ):
        foreach ( $events_data as $ca_event ):

            foreach ( $users_list as $user_id ):
                // get bookly customer id
                $customer_id = getcustomerID($user_id);

                if( (int) $ca_event->customer_id === (int) $customer_id ):
                    $appointment_status = $ca_event->status;
                    // if status ===  teacher no show OR student cancalled OR teacher cancalled => times++
                    if( $appointment_status === 'no-show-t' || $appointment_status === 'cancelled' || $appointment_status === 'excused-t' ):
                        $times_cancalled++;
                    endif;

                endif;

            endforeach;

        endforeach;
    endif;

    return $times_cancalled;


}

// function to count student no show for learner ( if parent for him and his childs)
function getStudentNoshowTimes($wp_user_id,$events_data){
    global $wpdb;
    $bookly_customer_appts_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';

    $users_list = [];

    // if parent => get active childs appts
    $active_childs = getParentActiveChilds($wp_user_id);

    if( !empty($active_childs) ):
        $users_list = array_merge($users_list, $active_childs);
    endif;

    $family_no_show_s = 0;


    if( !empty($events_data) ):
        foreach ( $users_list as $user_id ):
            $times_student_noshow = 0;

            foreach ( $events_data as $ca_event ):

                // get bookly customer id
                $customer_id = getcustomerID($user_id);

                if( (int) $ca_event->customer_id === (int) $customer_id ):
                    $appointment_status = $ca_event->status;
                    // if status === attended-sl => times++
                    if( $appointment_status === 'no-show-s' ):
                        $times_student_noshow++;
                        $family_no_show_s++;
                    endif;

                endif;

            endforeach;

            $total_noshow_learners['users'][$user_id] = $times_student_noshow;

        endforeach;
    endif;

    $total_noshow_learners['family_no_show_s'] = $family_no_show_s;

    return $total_noshow_learners;


}
function getStudentNoshowTimesx($wp_user_id,$events_data){
    global $wpdb;
    $bookly_customer_appts_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';

    $users_list = [];

    // if parent => get active childs appts
    $active_childs = getParentActiveChilds($wp_user_id);

    if( !empty($active_childs) ):
        $users_list = array_merge($users_list, $active_childs);
    endif;

    $times_student_noshow = 0;
    if( !empty($events_data) ):
        foreach ( $events_data as $ca_event ):

            foreach ( $users_list as $user_id ):
                // get bookly customer id
                $customer_id = getcustomerID($user_id);

                if( (int) $ca_event->customer_id === (int) $customer_id ):
                    $appointment_status = $ca_event->status;
                    // if status === attended-sl => times++
                    if( $appointment_status === 'no-show-s' ):
                        $times_student_noshow++;
                    endif;

                endif;

            endforeach;

        endforeach;
    endif;
    return $times_student_noshow;


}

// hook to sync customers on created or updated to bookly
//add_action( 'profile_update',  'on_user_update', 10, 1 );
//function on_user_update( $user_id ) {
//
//    global $wpdb;
//    $wp_user_id = $user_id;
//    $user_obj = get_user_by( 'id', $wp_user_id );
//    $first_name = $_POST['first_name'];
//    $last_name = $_POST['last_name'];
//    $email = $_POST['email'];
//
//    $memb_ReferralCode_chld = get_user_meta($wp_user_id ,'memb_ReferralCode', true);
//    if( !empty($memb_ReferralCode_chld) ):
//        $country = get_user_meta($wp_user_id ,'memb_Country', true);
//        $memb_ReferralCode_chld = substr($memb_ReferralCode_chld, 5);
//        $memb_ReferralCode_prnt = 'prnt-' . $memb_ReferralCode_chld;
//        $parent_user = get_user_by_meta_data('memb_ReferralCode', $memb_ReferralCode_prnt);
//        $parent_user_id = (int) $parent_user->data->ID;
//        $parent_user_display_name = $parent_user->data->display_name;
//        $child_last_name = $last_name . ' / ' . $parent_user_display_name;
//        $phone = get_user_meta($wp_user_id ,'memb_Phone1', true);
//        if( empty( $phone ) ):
//            // get parent phone
//            $phone = get_user_meta($parent_user_id ,'memb_Phone1', true);
//        endif;
//    else:
//        $child_last_name = $last_name;
//    endif;
//
//    $full_name = $first_name . ' ' . $child_last_name;
//
//
//    $current_date_object = new DateTime('now', new DateTimeZone('America/New_York'));
//    $created_at = $current_date_object->format('Y-m-d H:i:s');
//
//    $bookly_customers[0]['wp_user_id'] = $wp_user_id;
//    $bookly_customers[0]['full_name'] = $full_name;
//    $bookly_customers[0]['first_name'] = $first_name;
//    $bookly_customers[0]['last_name'] = $last_name;
//    $bookly_customers[0]['phone'] = $phone;
//    $bookly_customers[0]['email'] = $email;
//    $bookly_customers[0]['country'] = '';
//    $bookly_customers[0]['created_at'] = $created_at;
//
//
//
//    // insert into table bookly_customers
//    if( !empty($bookly_customers) ):
//        $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
//        wpdb_bulk_insert($bookly_customers_table, $bookly_customers);
//        wp_die();
//    endif;
//
//
//}

// get wp user id by meta_key
function get_user_by_meta_data( $meta_key, $meta_value ) {

    // Query for users based on the meta data
    $user_query = new WP_User_Query(
        array(
            'meta_key'	  =>	$meta_key,
            'meta_value'	=>	$meta_value
        )
    );

    // Get the results from the query, returning the first user
    $users = $user_query->get_results();

    if( empty($users) ) return false;
    return $users[0];

}

// function to calculate makeup balance after adjustment
function adjustMakeupBalance($parent_wp_user_id, $makeup_balance_amount){
    echo 'test';
}

// function to get last class reminder sent date_time
function classLastReminderDateTime($bb_group_id){
    global $wpdb;
    $notifications_table = 'notification_log';
    $notifications = $wpdb->get_results(
        "SELECT send_date FROM $notifications_table WHERE `cid` = '{$bb_group_id}' AND type = 'session_reminder' ORDER BY send_date DESC LIMIT 1"
    );
    $wpdb->flush();
    if( !empty($notifications) ):
        return $notifications[0]->send_date;
    else:
        return false;
    endif;

}

// function to get upcoming class for bb group id, where start_date >= current_day (Y-m-d)
function getBBgroupUpcomingClass($bb_group_id, $limit = 1){
    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $now_date_time = $current_date_object->format('Y-m-d');
    // get all records has same custom field bb group id
    global $wpdb;
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $custom_fields = json_encode(
        array(
            array(
                'id' => $bb_custom_field_id,
                'value' => strval($bb_group_id)
            )
        )
    );


    $bookly_appt_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_ca_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_upcoming_class = $wpdb->get_results(
        "SELECT * FROM {$bookly_appt_table} WHERE id IN 
                    (SELECT appointment_id  FROM {$bookly_ca_table} WHERE `custom_fields` LIKE '%{$custom_fields}%')
                    AND start_date >= '{$now_date_time}' ORDER BY start_date ASC LIMIT {$limit}"
    );
    $wpdb->flush();

    if( !empty($bookly_upcoming_class) ):
        return $bookly_upcoming_class;
    else:
        return false;
    endif;

}

// function to get upcoming class for wp_user_id in current_day (Y-m-d)
function getUserBBgroupUpcomingClasses($wp_user_id){
    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $today_date_time = $current_date_object->format('Y-m-d H:i:s');
    $tomorrow_date_time = date('Y-m-d H:i:s', strtotime($today_date_time . ' +1 day'));

    $start_date_to_search = $today_date_time;
    $end_date_to_search = $tomorrow_date_time;


    // get all records has same custom field bb group id
    global $wpdb;
    $bookly_appt_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_ca_table = $wpdb->prefix . 'bookly_customer_appointments';

    // if parent => get childs => get customer_id => push to customers_list
    if( checkIfParent($wp_user_id) == true ):
        $childs = getParentChilds($wp_user_id);
        $childs[] = $wp_user_id;
        foreach ( $childs as $child_id ):
            // get customer id
            $customer_ids[] = getcustomerID($child_id);
        endforeach;

    // if learner get customer_id => push to customers_list
    else:
        $customer_ids[] = getcustomerID($wp_user_id);
    endif;

    $customers_to_find = implode(',', $customer_ids);



    // get user time zone
    $user_timezone = getUserTimezone($wp_user_id);
    // convert start and end dates to search with new usertime_zone
    $start_date_to_search = convertTimezone1ToTimezone2 ( $start_date_to_search, 'UTC', $user_timezone );
    $end_date_to_search = convertTimezone1ToTimezone2 ( $end_date_to_search, 'UTC', $user_timezone );


    // check if staff => get classes -4 to +8 from now
    if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', $wp_user_id )->count() > 0 ):
        $start_date_to_search = date('Y-m-d H:i:s', strtotime($start_date_to_search . ' -4 hours'));
        $end_date_to_search = date('Y-m-d H:i:s', strtotime($start_date_to_search . ' +8 hours'));
        // get $staff_id
        $staff_id = getStaffId($wp_user_id);

        $bookly_upcoming_classes = $wpdb->get_results(
            "SELECT $bookly_appt_table.id AS appointment_id, $bookly_appt_table.start_date, $bookly_appt_table.end_date, $bookly_ca_table.id AS ca_id,
                    $bookly_ca_table.customer_id, $bookly_ca_table.custom_fields, $bookly_appt_table.staff_id , $bookly_ca_table.status, $bookly_ca_table.time_zone
                    FROM $bookly_appt_table JOIN $bookly_ca_table
                    ON $bookly_appt_table.id = $bookly_ca_table.appointment_id  
                    AND $bookly_appt_table.staff_id = {$staff_id}
                    AND $bookly_ca_table.status NOT IN('pending', 'rejected')
                    HAVING $bookly_appt_table.start_date BETWEEN '{$start_date_to_search}' AND '{$end_date_to_search}' ORDER BY $bookly_appt_table.end_date ASC;"
        );
    else:
        // user is not staff do other query
        $start_date_to_search = date('Y-m-d 00:00:00', strtotime($start_date_to_search));
        $end_date_to_search = date('Y-m-d 00:00:00', strtotime($end_date_to_search));
        $bookly_upcoming_classes = $wpdb->get_results(
            "SELECT $bookly_appt_table.id AS appointment_id, $bookly_appt_table.start_date, $bookly_appt_table.end_date, $bookly_ca_table.id AS ca_id,
                    $bookly_ca_table.customer_id, $bookly_ca_table.custom_fields, $bookly_appt_table.staff_id , $bookly_ca_table.status, $bookly_ca_table.time_zone
                    FROM $bookly_appt_table JOIN $bookly_ca_table
                    ON $bookly_appt_table.id = $bookly_ca_table.appointment_id  
                    AND $bookly_ca_table.customer_id IN ({$customers_to_find})
                    AND $bookly_ca_table.status NOT IN('pending', 'rejected')
                    HAVING $bookly_appt_table.start_date BETWEEN '{$start_date_to_search}' AND '{$end_date_to_search}' ORDER BY $bookly_appt_table.end_date ASC;"
        );
    endif;


    $wpdb->flush();


    if( !empty($bookly_upcoming_classes) ):

        foreach ( $bookly_upcoming_classes as $key=>$bookly_upcoming_class ):


            $upcoming_classes_data[$key]['number'] = $key+1;

            foreach (BOOKLY_CUSTOM_STATUS as $status=>$BOOKLY_STATUS):
                if( $status == $bookly_upcoming_class->status ):
                    $event_status = $BOOKLY_STATUS;
                    break;
                endif;
            endforeach;

            $upcoming_classes_data[$key]['status'] = $event_status;
            $upcoming_classes_data[$key]['ca_id'] = $bookly_upcoming_class->ca_id;

            //get bookly custom fields data
            $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
            $custom_fields = json_decode($bookly_upcoming_class->custom_fields);
            foreach ( $custom_fields as $field_data ):
                $custom_field_id = (int) $field_data->id;
                if( $custom_field_id === $bb_custom_field_id ):
                    $stored_bb_group_id = (int) $field_data->value;
                    $upcoming_classes_data[$key]['bb_group_id'] = $stored_bb_group_id;
                    // get group name
                    $group_obj = groups_get_group ( $stored_bb_group_id );
                    $group_full_title = $group_obj->name;
                    $upcoming_classes_data[$key]['name'] = $group_full_title;
                    // get zoom join url
                    $join_url = 'https://muslimeto.zoom.us/j/' . getZoomMeetingID($stored_bb_group_id);
                    $upcoming_classes_data[$key]['zoom_join'] = $join_url;
                else:
                    $upcoming_classes_data[$key]['name'] = '';
                    $upcoming_classes_data[$key]['zoom_join'] = '';
                endif;

                if( $field_data->id === 95778 ): // late mins value
                    $stored_late_mins = $field_data->value;
                    $upcoming_classes_data[$key]['late_mins'] = $stored_late_mins;
                else:
                    $upcoming_classes_data[$key]['late_mins'] = 0;
                endif;

                if( $field_data->id === 2583 ): // actual mins value
                    $stored_actual_min = $field_data->value;
                    $upcoming_classes_data[$key]['actual_mins'] = $stored_actual_min;
                else:
                    $upcoming_classes_data[$key]['actual_mins'] = 0;
                endif;

            endforeach; // end custom fileds loop

            $wp_tz = $bookly_upcoming_class->time_zone;


            $start_date_converted = convertTimezone1ToTimezone2 ( $bookly_upcoming_class->start_date, $wp_tz, $user_timezone );
            $end_date_converted = convertTimezone1ToTimezone2 ( $bookly_upcoming_class->end_date, $wp_tz, $user_timezone );

            $upcoming_classes_data[$key]['start'] = $start_date_converted;
            $upcoming_classes_data[$key]['end'] = $end_date_converted;


            // get teacher name
            $upcoming_classes_data[$key]['teacher'] = getStaffFullName($bookly_upcoming_class->staff_id);

            // get learner full name
            $bookly_wp_user_id = getBooklyWpUserId($bookly_upcoming_class->customer_id);
            $upcoming_classes_data[$key]['learner'] = getCustomerFullName($bookly_wp_user_id);


        endforeach; // end upcoming classes loop

        // if teacher, group classes by bb group id
        if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', $wp_user_id )->count() > 0 ):
            $teacher_upcoming_classes = array_values(array_column($upcoming_classes_data, null, 'bb_group_id'));
            foreach ( $teacher_upcoming_classes as $key=>$teacher_upcoming_class ):
                $teacher_upcoming_classes[$key]['number'] = $key + 1;
            endforeach;
            $upcoming_classes_data = $teacher_upcoming_classes;
        endif;


        return $upcoming_classes_data;

    else:
        return [];
    endif;

}

// cron loop
function cron_loop(){
    set_time_limit(20);
    // get last id in parent stats table
    global $wpdb;
    $parent_stats_table = $wpdb->prefix . 'muslimeto_parent_stats';
    // get all teachers
    $parent_stats_results = $wpdb->get_results(
        "SELECT * FROM $parent_stats_table ORDER BY id desc"
    );
    $wpdb->flush();

    $parents = getAllParents();
    $parent_ids = array_column($parents, 'id');

    // set counter you want to iterate over.
    $offset =0;
    $step = 10; // set the step value
    for($i = 0 ;$i< count($parent_ids) ;$i++):
        $sliced_parents = array_slice($parent_ids, $offset, $step);
        $offset = $offset + $step; // increment counter by to step position.
        foreach ( $sliced_parents as &$single_parent_id ):
            //update option with next parent id
            $updateUser = updateUserBillingIndicator($single_parent_id, '');

            if( $updateUser  !== true ):
                // insert into cron log
                $cron_log_table = $wpdb->prefix . 'muslimeto_error_log';
                $cron_log_data = array(
                    array(
                        'event_title' => 'cron_muslimeto_updateUserBillingIndicator_daily',
                        'event_desc' => 'user_id: ' . $single_parent_id . ' - error: ' .$updateUser
                    ),
                );

                wpdb_bulk_insert($cron_log_table, $cron_log_data);

            endif;

        endforeach;
        sleep(15);
    endfor;
}

// check if now < start_date_time of session with one hour
function checkSessionCancellationDate($start_date){
    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $now_datetime = $current_date_object->format('Y-m-d H:i:s');
    $remaining_hrs_to_session =  ( strtotime($start_date) - strtotime($now_datetime) ) / 3600 ;

    if(  $remaining_hrs_to_session >= 1 ):
        // The session's duration mins will be restored to balance
        return true;
    else:
        // the session will start less than one hour , the duration mins will not be restored
        return false;
    endif;
}

// function to get week start and end ( week starts Monday , ends Sunday )
function getWeekStartEnd($date) {
    $week=date("W",strtotime($date));
    $year=date("Y",strtotime($date));
    $dto = new DateTime();
    $result['start'] = $dto->setISODate($year, $week, 1)->format('Y-m-d');
    $result['end'] = $dto->setISODate($year, $week, 7)->format('Y-m-d');
    return $result;
}

// function to get session with cancelled status in ( week, same teacher, same bb_group_id )
function checkIfCancalledExceedLimits($week = false, $session_start_date = false, $customer_id = false, $staff_id = false, $bb_group_id = false){

    if( empty($customer_id) ) return 'Error: customer_id not found';
    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';

    if( $week == true ):
        // get start and end of week
        $week_start_end = getWeekStartEnd($session_start_date);
        if( !empty($week_start_end) ):
            $week_start_date = $week_start_end['start'];
            $week_end_date = $week_start_end['end'];

            // get sessions between these dates for this customer_id
            $cancalled_appointments_results = $wpdb->get_results(
                "SELECT id, status FROM $bookly_customer_appointments_table WHERE appointment_id IN (SELECT id FROM $bookly_appointments_table WHERE `start_date` BETWEEN '{$week_start_date}' AND '{$week_end_date}') AND customer_id = {$customer_id} AND status = 'cancelled'"
            );
            $wpdb->flush();

            // if cancalled sessions more than 2 during week
            if( !empty($cancalled_appointments_results) && count($cancalled_appointments_results) >= 2 ):
                // parent can cancel but makeup balance will not added
                return true;
            else:
                return false;
            endif;

        endif;

    elseif ( !empty($bb_group_id) ):
        // get all cancalled sessions for this customer and bb_group_id
        $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
        $custom_fields = json_encode(
            array(
                array(
                    'id' => $bb_custom_field_id,
                    'value' => strval($bb_group_id)
                )
            )
        );
        $cancalled_appointments_results = $wpdb->get_results(
            " SELECT id, status FROM $bookly_customer_appointments_table WHERE `customer_id` = {$customer_id} AND `status` = 'cancelled' AND custom_fields LIKE '%{$custom_fields}%' "
        );
        $wpdb->flush();


        if( !empty($cancalled_appointments_results) && count($cancalled_appointments_results) >= 2 ):
            // parent can cancel but makeup balance will not added
            return true;
        else:
            return false;
        endif;

    elseif ( !empty($staff_id) ):

        $cancalled_appointments_results = $wpdb->get_results(
            "SELECT id, staff_id FROM $bookly_appointments_table WHERE id IN (SELECT appointment_id  FROM $bookly_customer_appointments_table WHERE `customer_id` = {$customer_id} AND `status` = 'cancelled') AND staff_id ={$staff_id}"
        );
        $wpdb->flush();

        if( !empty($cancalled_appointments_results) && count($cancalled_appointments_results) >= 2 ):
            // parent can cancel but makeup balance will not added
            return true;
        else:
            return false;
        endif;

    endif;
}

// function to get user timezone ( staff, parent, learner )
function getUserTimezone($wp_user_id){
    if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', $wp_user_id )->count() > 0 ): // if staff get timezone from bookly staff table
        $staff_id = getStaffId($wp_user_id);
        $timezone = getStaffTimezone($staff_id);
        $user_role = 'teacher';
    else: // if parent, learner get timezone from meta_date
        $timezone = get_user_meta($wp_user_id, 'time_zone', true);
    endif;

    if( empty($timezone) ):
        $timezone = 'UTC';
    endif;

    return $timezone;
}

// function to get parent childs linked to bb_group_id
function getParentChildsinBBgroup($parent_wp_user_id, $bb_group_id, $allSelected = false){

    if( $allSelected == 'select-all' ):
        $selected = 'selected';
    else:
        $selected = '';
    endif;

    // get joined members in bb group
    $bb_args = array(
        'group_id' => $bb_group_id,
        'max' => 999,
        'exclude_admins_mods' => true
    );

    $group_members = groups_get_group_members($bb_args);

    if( !empty($group_members['members']) ):
        $members_wp_user_ids = array_column($group_members['members'], 'ID');
    endif;

    $customers_options = '';
    //check if parent has childs show filter
    $parent_childs = getParentChilds( $parent_wp_user_id );
    $parent_childs[] = $parent_wp_user_id;
    if( !empty($parent_childs) ):
        // user is a parent get his childs as bookly cutsomers
        foreach ($parent_childs as $parent_child):
            $customer_child_ids[] = getcustomerID($parent_child);
        endforeach;


        if( !empty($customer_child_ids) ):
            $selected_id = !empty($_GET['child_customer_id']) ? (int) $_GET['child_customer_id'] : '';

//            if( $args['return_wp_uid'] == true ):
//                $customer_child_ids = $parent_childs;
//            endif;

            // check this condition again
            if( count($members_wp_user_ids) > 1 && $selected != 'selected' ):
                $customers_options = '<option value="0" selected disabled> -- please select child -- </option>';
            endif;

            foreach ($customer_child_ids as $customer_child_id):

                // get email and full name for child
                $child_wp_user_id = getWPuserIDfromBookly($customer_child_id);

                if( in_array($child_wp_user_id, $members_wp_user_ids) ):
                    $child_user_obj = get_user_by('id', $child_wp_user_id);
                    $child_email = $child_user_obj->data->user_email;
                    $child_display_name = $child_user_obj->data->display_name;

                    // generate options for select
                    $customers_options .= '<option value="'. $customer_child_id .'" '. $selected .'> ' . $child_display_name  . ' ' . $child_email . ' </option>';
                endif;
            endforeach;

        endif;

    endif;

    return $customers_options;

}

// function to get parent childs linked to bb_group_id as text
function getParentChildsinBBgroupString($parent_wp_user_id, $bb_group_id){


    // get joined members in bb group
    $bb_args = array(
        'group_id' => $bb_group_id,
        'max' => 999,
        'exclude_admins_mods' => true
    );

    $group_members = groups_get_group_members($bb_args);

    if( !empty($group_members['members']) ):
        $members_wp_user_ids = array_column($group_members['members'], 'ID');
    endif;

    //check if parent has childs show filter
    $parent_childs = getParentChilds( $parent_wp_user_id );
    $parent_childs[] = $parent_wp_user_id;
    if( !empty($parent_childs) ):
        // user is a parent get his childs as bookly cutsomers
        foreach ($parent_childs as $parent_child):
            $customer_child_ids[] = getcustomerID($parent_child);
        endforeach;

        $customers_list = '';

        if( !empty($customer_child_ids) ):
            $selected_id = !empty($_GET['child_customer_id']) ? (int) $_GET['child_customer_id'] : '';

//            if( $args['return_wp_uid'] == true ):
//                $customer_child_ids = $parent_childs;
//            endif;


            foreach ($customer_child_ids as $customer_child_id):

                // get email and full name for child
                $child_wp_user_id = getWPuserIDfromBookly($customer_child_id);

                if( in_array($child_wp_user_id, $members_wp_user_ids) ):
                    $child_user_obj = get_user_by('id', $child_wp_user_id);
                    $child_email = $child_user_obj->data->user_email;
                    $child_display_name = $child_user_obj->data->display_name;

                    // generate options for select
                    $customers_list .= "<p> $child_display_name $child_email</p>";
                endif;
            endforeach;

        endif;

    endif;

    return $customers_list;

}

// send an email with token from bookly_customer_appointments event to confirm from parent side
function sendVerificationTokenScheduleMakeup($wp_user_id, $ca_id){
    // check if not parent, return do not complete
    if( checkIfParent($wp_user_id) !== true ) return false;

    // get token from bookly_customer_appointments where id = $ca_id
    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $token_result = $wpdb->get_results(
        "SELECT token FROM $bookly_customer_appointments_table WHERE id = {$ca_id}"
    );
    $wpdb->flush();

    if( empty($token_result) ) return false;

    $token = $token_result[0]->token;

    // get parent email
    $user_obj = get_user_by('id', $wp_user_id);
    $parent_email = $user_obj->data->user_email;

    // send an email with verification link
    $verify_url = site_url() . "/verify-schedule-makeup?verify_token=$token&uid=$wp_user_id";
    //$verify_url = site_url() . "/wp-json/validate_token?verify_token=$token";
    $to = $parent_email; // change to parent email
    $subject = '[Pending Confirmation] New makeup scheduled - Muslimeto™';
    $message = "Please verify schedule makeup session: $verify_url";
    $body = bp_email_core_wp_get_template($message);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $send_email = wp_mail( $to, $subject, $body, $headers );

    // return success if sent
    return $send_email;

}

// function to verify token for schedule makeup session
function validateVerificationTokenScheduleMakeup($token,$wp_user_id){

    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $makeup_session_result = $wpdb->get_results(
        "SELECT id, status, appointment_id, custom_fields FROM $bookly_customer_appointments_table WHERE token = '{$token}'"
    );
    $wpdb->flush();

    if( empty($token) || empty($makeup_session_result) ) return 'Error: No valid token found to confirm session. <br> Please contact support for help.';

    //get bookly custom fields data
    $stored_bb_group_custom_field = json_decode($makeup_session_result[0]->custom_fields);
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    foreach ( $stored_bb_group_custom_field as $field_data ):
        $custom_field_id = (int) $field_data->id;
        if( $custom_field_id === $bb_custom_field_id ):
            $stored_bb_group_id = (int) $field_data->value;
            break;
        endif;
    endforeach;

    if( empty($stored_bb_group_id) ) return 'Error: not able to retrieve class information. <br> Please contact support for help.';

    // query get all ca records with same group
    $custom_fields = json_encode(
        array(
            'id' => $bb_custom_field_id,
            'value' => strval($stored_bb_group_id)
        )
    );

    $sessions_to_approve_results = $wpdb->get_results(
        "SELECT id, status, appointment_id FROM $bookly_customer_appointments_table WHERE custom_fields LIKE '%{$custom_fields}%' AND status = 'pending'"
    );
    $wpdb->flush();


    // get start_date of appointment
    $appointment_result = $wpdb->get_results(
        "SELECT start_date, end_date, staff_id FROM $bookly_appointments_table WHERE id = '{$makeup_session_result[0]->appointment_id}'"
    );
    $wpdb->flush();

    if( empty($appointment_result) ) return 'Error: not able to retrieve class information. <br> Please contact support for help.';

    $start_date = $appointment_result[0]->start_date;
    $end_date = $appointment_result[0]->end_date;
    $appointment_id = $makeup_session_result[0]->appointment_id;
    $bookly_staff_id = $appointment_result[0]->staff_id;

    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $nowUTC = $current_date_object->format('Y-m-d H:i:s');

    $dates_diff = intval( (strtotime($start_date) - strtotime($nowUTC)) / 3600 );
    if( $dates_diff < 1  ) return "Error: we can not confirm a session starting in less than one hour, sorry for the inconvenience. <br> Your makeup balance was not deducted.";

    foreach ( $sessions_to_approve_results as $sessions_to_approve_result ):
        if( $sessions_to_approve_result->status !== 'pending' ) return 'All good! This appointment has already been confirmed earlier.';
    endforeach;

    // get group timezone
    $sp_entry_id = getBBgroupGFentryID($stored_bb_group_id);
    $class_timezone = getSPentryTimezone($sp_entry_id);

    if( empty($class_timezone) ) return 'Error: not able to retrieve class information. <br> Please contact support for help.';


    $class_duration_mins = ( strtotime($end_date) - strtotime($start_date) ) / 60;
    // convert start_date from utc to class_timezone
    $start_date_converted = convertTimezone1ToTimezone2 ( $appointment_result[0]->start_date, 'UTC', $class_timezone );


    // check if session start_date is not in past && session must be approved at least up to 1hr before start_date
    if( strtotime($start_date) < strtotime($nowUTC) ) return "Error: we can not confirm a session in the past, sorry for the inconvenience. <br> Your makeup balance was not deducted.";



    $makeup_balance = (int) get_field( 'mslm_makeup_balance', 'user_' . $wp_user_id );

    $current_time_to_display = date('h:i A', strtotime($nowUTC));
    $current_date_to_display = date('D, m-d-Y', strtotime($nowUTC));

    $makeup_hrs = intval($makeup_balance/60);
    $makeup_mins = $makeup_balance - ($makeup_hrs * 60);
    // check if parent makeup balance >= class_duration
    if( $makeup_balance < $class_duration_mins ) return "Error: Your account does not have enough makeup balance to confirm this session, sorry for the inconvenience. <br> Your current makeup balance is $makeup_hrs hrs $makeup_mins mins as of $current_time_to_display ($current_date_to_display).";

    // reduce parent makeup balance with class duration
    $new_makeup_balance = $makeup_balance - $class_duration_mins;
    update_field('mslm_makeup_balance', $new_makeup_balance, 'user_' . $wp_user_id);


    // if token found change session(s) status from "pending" to "approved"
    foreach ( $sessions_to_approve_results as $sessions_to_approve_result ):
        $data_array_to_update = [ 'status' => 'approved' ];
        $where = [ 'id' => $sessions_to_approve_result->id ];
        $updateSessionStatus[] = $wpdb->update( $bookly_customer_appointments_table, $data_array_to_update, $where );
    endforeach;

    // check if session(s) approved or not
    if( is_array($updateSessionStatus) ):

        $start_time_to_display = date('h:i A', strtotime($start_date_converted));
        $start_date_to_display = date('D, m-d-Y', strtotime($start_date_converted));
        $dateTime = new DateTime();
        $dateTime->setTimeZone(new DateTimeZone( $class_timezone ));
        $timezone_abbr = $dateTime->format('T');

        $confirmation_message = "[Confirmed] Makeup session scheduled at: ". $start_time_to_display . ' ' . $timezone_abbr .' ('. $start_date_to_display.')' ; // add EST, .. timezone short after time start
        // send notification to parent
        $parent_notification_args= array(
            "sender_id" => 'Scheduler',
            "receiver_id" => $wp_user_id,
            "cid" => $stored_bb_group_id,
            "aid" => $appointment_id,
            "type" => 'makeup_scheduled',
            "message" =>  $confirmation_message ,
        );
        send_notification_for_user($parent_notification_args);

        // send notification to teacher
        $teacher_notification_args= array(
            "sender_id" => 'Scheduler',
            "receiver_id" => getStaffwp_user_id($bookly_staff_id),
            "cid" => $stored_bb_group_id,
            "aid" => $appointment_id,
            "type" => 'makeup_scheduled',
            "message" =>  $confirmation_message ,
        );
        send_notification_for_user($teacher_notification_args);


        // create bb group activity so specific group
        $activity_args = array(
            'content' => '[Confirmed] Makeup session scheduled at: '. $start_time_to_display . ' ' . $timezone_abbr .' ('. $start_date_to_display.')',
            'group_id' => $stored_bb_group_id,
            'user_id' => getStaffwp_user_id($bookly_staff_id)
        );
        groups_post_update($activity_args);

        return array(
            'status' => true,
            'message' =>  "You have successfully confirmed new makeup session at: " . $start_time_to_display . ' ' . $timezone_abbr .' ('. $start_date_to_display.')'
        );
    else:
        return 'System Error: Please contact support for help.';
    endif;

}

// function to set class status from 'pending' to 'rejected'
function rejectBooklySession($bb_group_id){
    // check if group type == family-group or one-on-one or one-to-one
    $bb_group_type = getBBgroupType($bb_group_id);
    if( $bb_group_type == 'family-group' || $bb_group_type == 'one-on-one' || $bb_group_type == 'one-to-one' ):
        // get members for this group
        $args = array(
            'group_id' => $bb_group_id,
            'max' => 999,
            'exclude_admins_mods' => true,
            'exclude_banned' => true,
        );
        $group_members_data = groups_get_group_members( $args )['members'];
        $learners_uesr_ids = array_column($group_members_data, 'id');

        if( !empty($learners_uesr_ids) ):
            // get customer id for them
            foreach ( $learners_uesr_ids as $learners_uesr_id):
                $customer_ids[] = getcustomerID($learners_uesr_id);
            endforeach;

            //  select from ca table where customer_id in(customer_ids) and custom_fields like $bbgroup_field
            $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
            $custom_fields = json_encode(
                array(
                    'id' => $bb_custom_field_id,
                    'value' => strval($bb_group_id)
                )
            );

            $customer_list = implode(',', $customer_ids);

            // get CA record for this group id
            global $wpdb;
            $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';

            $ca_record_result = $wpdb->get_results(
                "SELECT status, appointment_id, id, custom_fields FROM $bookly_appointments_customer_table WHERE customer_id IN({$customer_list}) AND custom_fields LIKE '%{$custom_fields}%' AND status = 'pending' "
            );
            $wpdb->flush();


            if( !empty($ca_record_result) ):
                foreach ( $ca_record_result as $ca_record ):
                    $ca_id = $ca_record->id;
                    $wpdb->update($bookly_appointments_customer_table, array('id'=>$ca_id, 'status'=>'rejected'), array('id'=>$ca_id));
                endforeach;

                return true;
            else:
                return false;
            endif;

        else:
            return false;
        endif;

    else:
        return false;
    endif;
}

// function to get next session with status 'pending'
function getNextPendingSession($wp_user_id){
    global $wpdb;
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    // check if parent , get his childs
    $childs = getParentActiveChilds($wp_user_id);

    if( !empty($childs) ):
        // get groups assigned to each child
        foreach ( $childs as $child_id ):
            $customers_ids[] = getcustomerID($child_id);
        endforeach;

        if( empty($customers_ids) ) return false;

        $customers_list = implode(',', $customers_ids);

        $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
        $nowUTC = $current_date_object->format('Y-m-d H:i:s');

        // select * from bookly_appointment where id IN( select appointment_id from bookly_customer_appoitnmets where customer_id IN ($childs_customers_ids) AND status = 'pending' ) AND start_date >= nowUTC order by start_date ASC limit 1

        $pending_result = $wpdb->get_results(
            "SELECT id, start_date, end_date, staff_id FROM $bookly_appointments_table WHERE id 
               IN( SELECT appointment_id FROM $bookly_appointments_customer_table WHERE customer_id IN($customers_list) AND status = 'pending' ) 
                AND start_date >= '{$nowUTC}' ORDER BY start_date ASC LIMIT 1"
        );
        $wpdb->flush();

        if( !empty($pending_result) ):
            $appointment_id = $pending_result[0]->id;
            $start_date = $pending_result[0]->start_date;
            $end_date = $pending_result[0]->end_date;
            $staff_id = $pending_result[0]->staff_id;

            // get ca record data
            $ca_record = getBooklyCA($appointment_id);
            $ca_id = $ca_record[0]->id;
            $token = $ca_record[0]->token;
            $status = $ca_record[0]->status;
            $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
            $custom_fields = json_decode( $ca_record[0]->custom_fields );
            foreach ( $custom_fields as $key=>$custom_field ):
                if( $custom_field->id === $bb_custom_field_id ):
                    $stored_bb_group_id = (int) $custom_field->value;
                    break;
                endif;
            endforeach;

            return array(
                'appointment_id' => $appointment_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'staff_id' => $staff_id,
                'ca_id' => $ca_id,
                'stored_bb_group_id' => $stored_bb_group_id,
                'token' => $token,
                'status' => $status
            );
        else:
            return false;
        endif;

    else:
        return false;
    endif;

}

// function to get session data from token
function getSessionDatafromToken($token){

    global $wpdb;
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';

    $pending_result = $wpdb->get_results(
        "SELECT id, start_date, end_date, staff_id FROM $bookly_appointments_table WHERE id 
                    IN( SELECT appointment_id FROM $bookly_appointments_customer_table WHERE token = '{$token}' )"
    );
    $wpdb->flush();


    if( !empty($pending_result) ):
        $appointment_id = $pending_result[0]->id;
        $start_date = $pending_result[0]->start_date;
        $end_date = $pending_result[0]->end_date;
        $staff_id = $pending_result[0]->staff_id;

        // get ca record data
        $ca_record = getBooklyCA($appointment_id);
        $ca_id = $ca_record[0]->id;
        $token = $ca_record[0]->token;
        $status = $ca_record[0]->status;
        $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
        $custom_fields = json_decode( $ca_record[0]->custom_fields );
        foreach ( $custom_fields as $key=>$custom_field ):
            if( $custom_field->id === $bb_custom_field_id ):
                $stored_bb_group_id = (int) $custom_field->value;
                break;
            endif;
        endforeach;

        return array(
            'appointment_id' => $appointment_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'staff_id' => $staff_id,
            'ca_id' => $ca_id,
            'stored_bb_group_id' => $stored_bb_group_id,
            'token' => $token,
            'status' => $status
        );
    else:
        return false;
    endif;

}

// function to fix regenerate group appointments for next 2 months
function regenerateAppointmentsFor2Months($bb_group_id) {
    global $wpdb;
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_ca_table = $wpdb->prefix . 'bookly_customer_appointments';

    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $nowUTC = $current_date_object->format('Y-m-d');

    // get sp entry for group
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
    if( empty($sp_entry_id) ) $catch_error[] = "Error no sp entry found for $bb_group_id";

    // get learners entry for sp entry
    $learners_entry_id = getLearnersEntryID($sp_entry_id);
    if( empty($learners_entry_id) ) $catch_error[] = "Error no learners entry found for $bb_group_id";
    // get learners wp_user_ids
    $learners_list = getLearnersWPuserIds($learners_entry_id);
    $learners_ids_string = substr( $learners_list, 1, -1 );
    $learners_ids_array = explode(',', $learners_ids_string);
    foreach ( $learners_ids_array as $learner_id ):
        $learner_ids = (int) preg_replace("/[^0-9]/","",$learner_id);
        $bookly_customer_ids[] = getcustomerID($learner_id);
    endforeach;

    $bookly_user_timezone = getSPentryTimezone($sp_entry_id);
    $bookly_user_timezone_offset = 0;
    $bookly_service_id = getBooklyServiceId($sp_entry_id);

    // get schedules linked to sp entry
    $schedules_entries = getScheduleEntryID($sp_entry_id);
    if( empty($schedules_entries) ) $catch_error[] = "Error no schedules entries found for $sp_entry_id";

    // loop schedules to get end_date
    foreach ( $schedules_entries as $schedules_entry_id ):
        $end_date_meta = getGFentryMetaValue($schedules_entry_id, 8);
        // if end_date > now or no end_date, get this schedule
        if( $end_date_meta == false ): // no end date => $regenerate_schedule[] = $schedules_entry_id
            $regenerate_schedules[$schedules_entry_id] = false;
        else: // end date > now in future => $regenerate_schedule[] = $schedules_entry_id
            $end_date = $end_date_meta[0]->meta_value;
            if( strtotime($end_date) > strtotime($nowUTC) ):
                $regenerate_schedules[$schedules_entry_id] = $end_date;
            endif;
        endif;

    endforeach;


    if( !empty($regenerate_schedules) ):

        foreach ( $regenerate_schedules as $regenerate_schedule_id=>$schedule_end_date ):

            // get series id
            $series_id_meta = getGFentryMetaValue($regenerate_schedule_id, 7);
            if( !empty($series_id_meta) ):
                $series_id = $series_id_meta[0]->meta_value;
                $series_ids[] = $series_id;

                if( !empty($series_id) ):

                    // get end_date from bookly appointment table
                    $last_appt_end_date_result = $wpdb->get_results(
                        "SELECT end_date, staff_id FROM $bookly_appts_table WHERE id IN(SELECT appointment_id FROM $bookly_ca_table WHERE series_id ={$series_id}) ORDER BY end_date DESC LIMIT 1"
                    );
                    $wpdb->flush();



                    if( !empty($last_appt_end_date_result) ):
                        $appt_end_date = $last_appt_end_date_result[0]->end_date;
                        $bookly_teacher_id = $last_appt_end_date_result[0]->staff_id;

                        $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
                        $created_at = $current_date_object->format('Y-m-d H:i:s');

                        // get current month => add 2 months => $calculated_end_date = end of following month => $end_date_to_regenerate = $calculated_end_date
                        // 28/8/2022 => 28/10/2022 => $calculated_end_date = 31/10/2022
                        $current_date_to_compare = date('Y-m', strtotime($created_at)) . '-01';
                        $calculated_end_date = date('Y-m-d', strtotime($current_date_to_compare . ' +3 months - 1 day'));

                        $start_date_regenerate = date('Y-m-d', strtotime($appt_end_date . ' +1 day'));
                        $start_date_to_regenerate[] = $start_date_regenerate;


                        if( $schedule_end_date == false && strtotime($calculated_end_date) > strtotime($appt_end_date) ): // regenerate next date > end_date for 60 days && $calculated_end_date > $appt_end_date
                            $end_date_to_regenerate[] =  $calculated_end_date;
                        else: // // regenerate next date > end_date until schedule_end_date
                            $end_date_to_regenerate[] =  $schedule_end_date;
                        endif;



                        ////////////////////////

                        // do recreate appointmnets
                        $bookly_class_days = getClassDays($regenerate_schedule_id);
                        $start_time = getStartTime($regenerate_schedule_id);
                        $bookly_start_hours = explode(':', $start_time)[0];
                        $bookly_start_minutes = explode(':', $start_time)[1];
                        $bookly_class_duration = getClassDuration($regenerate_schedule_id);
                        $units = ( (int) $bookly_class_duration ) / 15 ;

                        if( !empty($start_date_regenerate) && isset($start_date_regenerate) ):

                            $bookly_effective_day = strtolower( date('D', strtotime($start_date_regenerate)) );

                            if(
                                empty($bookly_teacher_id) ||
                                empty($bookly_customer_ids) ||
                                empty($bookly_user_timezone) ||
                                empty($bookly_start_hours) ||
                                empty($bookly_start_minutes) ||
                                empty($bookly_class_duration) ||
                                empty($start_date_regenerate) ||
                                empty($bookly_class_days) ||
                                empty($bookly_service_id)
                            ):
                                return 'empty-fields';
                            endif;


                            // get week number and year number to generate effective dates for each row
                            $effective_week_number = date("W", strtotime($start_date_regenerate));
                            $effective_year_number = date('Y', strtotime($start_date_regenerate));
                            $effective_month_number = (int) date('m', strtotime($start_date_regenerate));

                            // fix wrong week number if in day in last week of previous year
                            if( $effective_month_number === 1 && (int) $effective_week_number > 50):
                                $effective_week_number = 1;
                            endif;


                            // get effective start datetime
                            $bookly_end_minutes = convertToHoursMins( (int) $bookly_start_minutes + (int) $bookly_class_duration )['minutes'];
                            $bookly_end_hours = (int) $bookly_start_hours + convertToHoursMins( (int) $bookly_start_minutes + (int) $bookly_class_duration )['hours'];
                            $string_start_date = strtotime( $start_date_regenerate . ' ' . (int) $bookly_start_hours . ':' . (int) $bookly_start_minutes ); // mm/dd/yyyy H:m

                            $booking_user_start_date = date ("Y-m-d H:i:s", $string_start_date);



                            // get effective end datetime
                            if( $bookly_end_hours == 24 ){
                                $string_end_date = strtotime( $start_date_regenerate . ' 23:59:00'  ); // mm/dd/yyyy H:m
                            } else {
                                $string_end_date = strtotime( $start_date_regenerate . ' ' . $bookly_end_hours . ':' . $bookly_end_minutes ); // mm/dd/yyyy H:m
                            }
                            $booking_user_end_date = date ("Y-m-d H:i:s", $string_end_date);


                            // get new effective start and end dates for each booking row
                            $effective_day_index = array_search($bookly_effective_day, WEEK_DAYS_INDEX);


                            if( $bookly_end_hours >= 24 ){
                                $bookly_end_hours = 23;
                                $bookly_end_minutes = 59;
                            }
                            foreach( $bookly_class_days as $bookly_class_day ):
                                $day_index = array_search($bookly_class_day, WEEK_DAYS_INDEX);
                                $week_day_index = array_search($bookly_class_day, SUN_WEEK_DAYS_INDEX);
                                $gendate = new DateTime();
                                $gendate->setISODate($effective_year_number,$effective_week_number,$week_day_index); //year , week num , day
                                $row_effective_start_dates[$regenerate_schedule_id][] =  $gendate->format('Y-m-d '. $bookly_start_hours . ':' . $bookly_start_minutes . ':00' );
                                $row_effective_end_dates[$regenerate_schedule_id][] = $gendate->format('Y-m-d '. $bookly_end_hours . ':' . $bookly_end_minutes . ':00');
                            endforeach;


                        endif;

                        //////////////////////////

                    endif;

                endif;
            else:
                $catch_error[] = 'Error no series id found for this group' . $bb_group_id;
            endif;


        endforeach; // loop each schedule


        if( !empty($row_effective_start_dates) && count($row_effective_start_dates) > 0 ):
            $row_effective_start_dates = array_values($row_effective_start_dates);
            $row_effective_end_dates = array_values($row_effective_end_dates);

            // get recurring days for each row_start_date
            for( $i=0; $i<count($row_effective_start_dates); $i++ ):
                // set recurring start and stop to stop at end_date
                foreach ( $row_effective_start_dates[$i] as $row_effective_start_date ):
                    // get reccurring dates for each start and end date
                    $rowReccurringStartDates[$i][] = getReccurringDatesUntilinTimezone(date('m/d/Y H:i:s', strtotime($row_effective_start_date)), $end_date_to_regenerate[$i], 'Y-m-d H:i:s' , $bookly_user_timezone);
                endforeach;

                foreach ( $row_effective_end_dates[$i] as $row_effective_end_date ):
                    // get reccurring dates for each start and end date
                    $rowReccurringEndDates[$i][] = getReccurringDatesUntilinTimezone( date('m/d/Y H:i:s', strtotime($row_effective_end_date)), $end_date_to_regenerate[$i], 'Y-m-d H:i:s' , $bookly_user_timezone);
                endforeach;
            endfor;

            //set bookly custom fields data for new records
            $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
            $custom_fields = json_encode(
                array(
                    array(
                        'id' => $bb_custom_field_id,
                        'value' => strval($bb_group_id)
                    )
                )
            );

            // create appointments records
            for( $i=0; $i<count($rowReccurringStartDates); $i++ ):

//                echo '------------------ Schedule ' . $i . ' ----------------------- <br>';

                $series_id = $series_ids[$i];

                // start inserting bookly_appointments table

                for( $x=0; $x<count($rowReccurringStartDates[$i]); $x++ ):
//                    echo '==== Day ' . $x .' ====<br>';
                    for( $d=1; $d<=count($rowReccurringStartDates[$i][$x]); $d++ ):
//                        echo 'start: ' . $rowReccurringStartDates[$i][$x][$d]. ' ---  end: ' . $rowReccurringEndDates[$i][$x][$d] .'<br>';

                        $booking_start_date = $rowReccurringStartDates[$i][$x][$d];
                        $booking_end_date   = $rowReccurringEndDates[$i][$x][$d];

                        // for each class_day in row get recurring dates ( start and end ) -> create appointment record
                        // insert appointments into bookly_appointments table
                        $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
                        $appointments = array(
                            array(
                                'staff_id' => $bookly_teacher_id,
                                'staff_any' => 0,
                                'service_id' => $bookly_service_id,
                                'start_date' => $booking_start_date,
                                'end_date' => $booking_end_date,
                                'extras_duration' => 0,
                                'internal_note' => '',
                                'created_from' => 'bookly',
                                'created_at' => $created_at,
                                'updated_at' => $created_at
                            ),
                        );


                        if( wpdb_bulk_insert($bookly_appointments_table, $appointments) === 1  ):
                            //if record true, get appointment id
                            $appointment_id = $wpdb->insert_id;

                            // get appointment record id to use it in customer_appointments table and use social group id as custom_fields
                            $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';

                            // start inserting bookly_customer_appointments table
                            foreach ( $bookly_customer_ids as $key=>$bookly_customer_id ):

                                $customer_appointments_array[$key] = array(
                                    'series_id' => (int) $series_id,
                                    'customer_id' => $bookly_customer_id,
                                    'appointment_id' => $appointment_id,
                                    'number_of_persons' => 1,//count( $bookly_customer_ids ),
                                    'units' => $units,
                                    'extras' => [],
                                    'extras_multiply_nop' => 1,
                                    'extras_consider_duration' => 1,
                                    'custom_fields' => $custom_fields,
                                    'status' => 'approved',
                                    'token' => generateUniqueToken(),
                                    'time_zone' => 'UTC',
                                    'time_zone_offset' => $bookly_user_timezone_offset,
                                    'created_from' => 'frontend',
                                    'created_at' => $created_at,
                                    'updated_at' => $created_at
                                );

                            endforeach;


                            $customer_appointments = $customer_appointments_array;

                            if( wpdb_bulk_insert($bookly_customer_appointments_table, $customer_appointments) != 1):
                                // show error if customer_appointment_record not inserted success
                                $wpdb->show_errors();
                                $catch_error[] = 'Warning: error in inserting customer appointment for appointment_id: ' . $appointment_id . $wpdb->print_error() .'<br>';
                            endif;
                        else: // show error if appointment_record not inserted success
                            $wpdb->show_errors();
                            $catch_error[] = 'Error in inserting bookly appointments table for group id: ' . $bb_group_id .$wpdb->print_error().'<br>';
                        endif;

                    endfor;
                endfor;
            endfor;
        else:
            $catch_error[] = "Error: no dates to regenerate for group $bb_group_id";
        endif;


    else:
        $catch_error[] = 'Error no active schedules found in group ' . $bb_group_id;
    endif;

    if( !empty($catch_error) ):

        // store in error log
        $cron_log_table = $wpdb->prefix . 'muslimeto_error_log';
        $cron_log_data = array(
            array(
                'event_title' => 'cron_muslimeto_regenerate_group_apointments',
                'event_desc' => json_encode($catch_error)
            ),
        );

        wpdb_bulk_insert($cron_log_table, $cron_log_data);
    else:

        // store in error log
        $cron_log_table = $wpdb->prefix . 'muslimeto_error_log';
        $cron_log_data = array(
            array(
                'event_title' => 'cron_muslimeto_regenerate_group_apointments',
                'event_desc' => "BB group $bb_group_id regeneration success."
            ),
        );

        wpdb_bulk_insert($cron_log_table, $cron_log_data);
    endif;

}

/* Add the HTML  tag attribute 'target' to the list
 * of allowed tags & attributes in BP's activity filters.
 */
function ufmn_add_target_attribute( $activity_allowedtags ) {
    $activity_allowedtags['video']['src']    = array();
    $activity_allowedtags['video']['width']    = array();
    $activity_allowedtags['video']['controls']    = array();
    $activity_allowedtags['source']['src']    = array();
    $activity_allowedtags['source']['type']    = array();
    return $activity_allowedtags;
}
add_filter( 'bp_activity_allowed_tags', 'ufmn_add_target_attribute', 1 );
add_filter( 'bp_forums_allowed_tags', 'ufmn_add_target_attribute', 1 );

// function to fix and remove _pda added to attachment files
function fix_pda_media(){

    // update meta _wp_attached_file
    global $wpdb;
    $post_meta_table = $wpdb->prefix . 'postmeta';
    $media_data = $wpdb->get_results(
        "SELECT * FROM $post_meta_table WHERE  meta_key = '_wp_attached_file' AND meta_value LIKE '%_pda/%' "
    );
    $wpdb->flush();

    if( !empty($media_data) ):
        foreach ( $media_data as $media ):
            $post_id = (int) $media->post_id;
            $meta_value = $media->meta_value;
            $search1 = '_pda/wpfd/_pda/' ;
            $search2 = '_wpfd/_pda/' ;
            $search3 = '_pda/' ;
            $new_meta_value = str_replace($search1, '', $meta_value) ;
            $new_meta_value = str_replace($search2, '', $new_meta_value) ;
            $new_meta_value = str_replace($search3, '', $new_meta_value) ;
            // update meta value with new filename
            update_post_meta( $post_id, '_wp_attached_file', $new_meta_value );
            echo 'file: ' . $post_id . ' updated successfully. <br>';
        endforeach;

        return true;

    else:
        return false;
    endif;


}

// function to insert in error log table
function addLog( $log_data ){
    // insert into cron log
    global $wpdb;
    $cron_log_table = $wpdb->prefix . 'muslimeto_error_log';
    $cron_log_data = array( $log_data );
    wpdb_bulk_insert($cron_log_table, $cron_log_data);
}

// function to remove ca_appts for customer and bb group id from start_date
function removeCustomerCAappts( $learner_wp_user_id, $bb_group_id, $start_date ){
    $customer_id = getcustomerID($learner_wp_user_id);
    // remove from bookly_customer_appointments table where appointmen_id in ( select id from bookly_appointments where start_date >= $start_date )
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $custom_fields = json_encode(
        array(
            'id' => $bb_custom_field_id,
            'value' => strval($bb_group_id)
        )
    );
    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $customer_appointments_results = $wpdb->get_results(
        "SELECT $bookly_appointments_table.id AS appt_id, $bookly_customer_appointments_table.appointment_id as ca_appt_id, $bookly_customer_appointments_table.id as ca_id , $bookly_appointments_table.start_date, $bookly_customer_appointments_table.customer_id, $bookly_customer_appointments_table.custom_fields, $bookly_customer_appointments_table.status 
                FROM $bookly_appointments_table 
                INNER JOIN $bookly_customer_appointments_table 
                ON $bookly_appointments_table.id = $bookly_customer_appointments_table.appointment_id 
                HAVING $bookly_appointments_table.id IN( SELECT appointment_id FROM $bookly_customer_appointments_table WHERE $bookly_customer_appointments_table.custom_fields LIKE '%$custom_fields%' ) 
                AND $bookly_customer_appointments_table.customer_id = {$customer_id} 
                AND $bookly_appointments_table.start_date >= '{$start_date}' 
                ORDER BY $bookly_appointments_table.start_date ASC;"
    );
    $wpdb->flush();

    if( !empty($customer_appointments_results) ):
        $ca_ids = array_column($customer_appointments_results, 'ca_id');
        $ca_ids = array_map(function($value) {
            return intval($value);
        }, $ca_ids);

        // remove customer appointments records
        $ca_ids_to_remove = implode(",", (array) $ca_ids);
        if( ! $wpdb->query("DELETE FROM $bookly_customer_appointments_table WHERE id IN ($ca_ids_to_remove)") ):
            $delete_error = 'Error in deleting bookly_appointments_table <br>';
            addLog( array(
                'event_title' => $delete_error,
                'event_desc' => "error in delete these ca_ids: $ca_ids_to_remove",
                'user_id' => get_current_user_id()
            ) );
        endif;

        return true;
    else:
        return false;
    endif;

}

// function to re-generate new customer appointments for new learner in group only
function regenerateAppointmentsForNewLearner($learner_wp_user_id, $bb_group_id, $start_date) {
    global $wpdb;
    $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_ca_table = $wpdb->prefix . 'bookly_customer_appointments';

    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $created_at = $current_date_object->format('Y-m-d H:i:s');

    $customer_id = getcustomerID($learner_wp_user_id);
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $custom_fields = json_encode(
        array(
            'id' => $bb_custom_field_id,
            'value' => strval($bb_group_id)
        )
    );

    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';

    // get appointments starting from start_date
    $customer_appointments_results = $wpdb->get_results(
        " SELECT $bookly_appointments_table.id AS appt_id, $bookly_customer_appointments_table.appointment_id as ca_appt_id, $bookly_customer_appointments_table.id as ca_id , $bookly_appointments_table.start_date, $bookly_customer_appointments_table.customer_id, $bookly_customer_appointments_table.custom_fields, $bookly_customer_appointments_table.status
                FROM $bookly_appointments_table
                INNER JOIN $bookly_customer_appointments_table
                ON $bookly_appointments_table.id = $bookly_customer_appointments_table.appointment_id
                HAVING $bookly_appointments_table.id 
                           IN( SELECT appointment_id FROM $bookly_customer_appointments_table 
                           WHERE $bookly_customer_appointments_table.custom_fields LIKE '%$custom_fields%' 
                           AND $bookly_customer_appointments_table.customer_id != {$customer_id} GROUP BY $bookly_customer_appointments_table.appointment_id )
                AND $bookly_appointments_table.start_date >= '{$start_date}'
                ORDER BY $bookly_appointments_table.start_date ASC "
    );
    $wpdb->flush();



    $appointment_ids_to_link = array_unique( array_column( $customer_appointments_results, 'appt_id' ) );

    $new_custom_field = $custom_fields = json_encode(
        array(
            array(
                'id' => $bb_custom_field_id,
                'value' => strval($bb_group_id)
            )
        )
    );

    if( !empty($appointment_ids_to_link) ):

        foreach ( $appointment_ids_to_link as $appointment_id_to_link ):

            // check if appointment to link to new learner not exist in bookly customers table => create new one
            $check_customer_appointments_results = $wpdb->get_results(
                " SELECT * FROM $bookly_customer_appointments_table WHERE customer_id = {$customer_id} AND appointment_id = {$appointment_id_to_link} "
            );
            $wpdb->flush();

            if( empty($check_customer_appointments_results) ):
                // get CA record for each one
                $ca_record = getBooklyCA($appointment_id_to_link);
                if( !empty($ca_record) ):
                    $new_ca_appts[] = array(
                        'series_id' => $ca_record[0]->series_id,
                        'customer_id' => $customer_id,
                        'appointment_id' => $appointment_id_to_link,
                        'number_of_persons' => $ca_record[0]->number_of_persons,
                        'units' => $ca_record[0]->units,
                        'extras' => [],
                        'extras_multiply_nop' => 1,
                        'extras_consider_duration' => 1,
                        'custom_fields' => $new_custom_field,
                        'status' => 'approved',
                        'token' => generateUniqueToken(),
                        'time_zone' => 'UTC',
                        'time_zone_offset' => $ca_record[0]->time_zone_offset,
                        'created_from' => 'frontend',
                        'created_at' => $created_at,
                        'updated_at' => $created_at
                    );
                endif;
            endif;

        endforeach;

        if( !empty($new_ca_appts) ):
            $creaet_new_learner_ca_record = wpdb_bulk_insert($bookly_customer_appointments_table, $new_ca_appts);

            if( $creaet_new_learner_ca_record == false ):
                // show error if customer_appointment_record not inserted success
                $wpdb->show_errors();
                $catch_error[] = 'Warning: error in inserting customer appointment for appointments with ids: ' . implode(',', $appointment_ids_to_link) . ' for learner: ' . $learner_wp_user_id . $wpdb->print_error() .'<br>';
            endif;
        endif;

    endif;

    if( !empty($catch_error) ):
        // store previous appointment in erro_log in case to recreate again
        $catch_error['ca_records'] = $new_ca_appts;
        // store in ctivity log
        $cron_log_table = $wpdb->prefix . 'muslimeto_error_log';
        $cron_log_data = array(
            array(
                'event_title' => "Error: in regenerateAppointmentsForNewLearner() for learner: $learner_wp_user_id in BB group $bb_group_id",
                'event_desc' => json_encode($catch_error)
            ),
        );

        wpdb_bulk_insert($cron_log_table, $cron_log_data);
        return false;
    else:
        return true;
    endif;

}

// function to remove learner from GF entry
function removeLearnerfromGF( $wp_user_id, $bb_group_id ){
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
    $learners_entry_id = getLearnersEntryID($sp_entry_id);
    $learners_ids = getLearnersWPuserIds($learners_entry_id);
    $learners_ids_string = substr( $learners_ids, 1, -1 );
    $learners_ids_array = explode(',', $learners_ids_string);
    $new_learners_list = [];
    foreach ( $learners_ids_array as $key=>$learner_id ):
        $learner_wp_user_id = (int) preg_replace("/[^0-9]/","",$learner_id);
        if( $learner_wp_user_id == $wp_user_id ):
            unset($learners_ids_array[$key]);
        else:
            $new_learners_list[] = $learner_id;
        endif;
    endforeach;


    $new_meta_value = '['. implode(',', $new_learners_list) .']';

    if( gform_update_meta( $learners_entry_id, 3, $new_meta_value ) == 1 ):
        return true;
    else:
        addLog( array(
            'event_title' => "Error: in updating GF learner entry",
            'event_desc' => "Error: in removing learner: $wp_user_id from group $bb_group_id from GF learners entry: $learners_entry_id",
            'user_id' => get_current_user_id()
        ) );
        return false;
    endif;
}

// function to add learner to GF entry
function addLearnerfromGF( $learner_wp_user_id, $bb_group_id ){
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
    $learners_entry_id = getLearnersEntryID($sp_entry_id);
    $learners_ids = getLearnersWPuserIds($learners_entry_id);
    $learners_ids_string = substr( $learners_ids, 1, -1 );
    $learners_ids_array = explode(',', $learners_ids_string);

    $stored_learner_wp_user_ids = [];

    foreach ( $learners_ids_array as $key=>$learner_id ):
        $stored_learner_wp_user_id = (int) preg_replace("/[^0-9]/","",$learner_id);
        if( !empty($stored_learner_wp_user_id) ):
            $stored_learner_wp_user_ids[] = $stored_learner_wp_user_id;
        endif;
    endforeach;

    if( ! in_array($learner_wp_user_id, $stored_learner_wp_user_ids) ):
        $stored_learner_wp_user_ids[] = $learner_wp_user_id;
    endif;


    $new_learners_ids = array_map(function($value) { return '"'. $value . '"'; }, $stored_learner_wp_user_ids);

    $new_meta_value = '['. implode(',', $new_learners_ids) .']';


    if( gform_update_meta( $learners_entry_id, 3, $new_meta_value ) == 1 ):
        return true;
    else:
        addLog( array(
            'event_title' => "Error: in updating GF learner entry",
            'event_desc' => "Error: in removing learner: $wp_user_id from group $bb_group_id from GF learners entry: $learners_entry_id",
            'user_id' => get_current_user_id()
        ) );
        return false;
    endif;

}

// function to check if not found in any mvs classes
function checkIflearnerHasMvs($wp_user_id)
{
    // search for learner in all mvs groups
    // * check if not found in any mvs classes => remove from 'MVS' group, => remove role 'student'
    $mvs_flag = false;
    $args = array(
        'user_id' => $wp_user_id,          // Pass a user_id to limit to only groups that this user is a member of.
        'group_type' => '',             // Array or comma-separated list of group types to limit results to.
        'per_page' => 999,             // The number of results to return per page.
    );
    $user_bb_groups_obj = groups_get_groups($args);
    $user_bb_groups = $user_bb_groups_obj['groups'];
    if (!empty($user_bb_groups)):
        foreach ($user_bb_groups as $user_bb_group):
            $bb_group_id = $user_bb_group->id;
            // get sp entry group status
            $sp_entry_id = getBBgroupGFentryID($bb_group_id);
            if( !empty($sp_entry_id) ):
                $program_status = getGFentryMetaValue($sp_entry_id, 26);
                if( !empty($program_status) ):
                    $status = $program_status[0]->meta_value;
                endif;
            endif;
            // check if group type is 'mvs' and group is active
            if (getBBgroupType($bb_group_id) == 'mvs' && !empty($status) && $status === 'Active' ):
                $mvs_flag = true;
            endif;
        endforeach;
    endif;

    return $mvs_flag;
}

// function to get user rank
function getGamipressUserRank($wp_user_id)
{
    if( empty($wp_user_id) ):
        $wp_user_id = get_current_user_id();
    endif;
    $rank_id = get_user_meta($wp_user_id, '_gamipress_levels_rank', true);
    if( !empty($rank_id) ):
        $rank_name = get_the_title($rank_id);
//        $rank_thumb = gamipress_get_rank_post_thumbnail($rank_id, 'gamipress-rank', 'gamipress-rank-thumbnail');
        $rank_thumb = get_the_post_thumbnail_url( $rank_id, 'gamipress-rank', array( 'class' => 'gamipress-rank-thumbnail' ) );
        if( empty($rank_thumb) ):
            $rank = get_page_by_path( gamipress_get_post_type( $user_rank['rank_id'] ), OBJECT, 'rank-type' );
            $rank_thumb = apply_filters( 'gamipress_default_rank_post_thumbnail', '', $rank, 'gamipress-rank' );
        endif;
        return array(
            'rank_id' => $rank_id,
            'rank_name' => $rank_name,
            'rank_thumb' => $rank_thumb
        );
    else:
        return false;
    endif;

}


// function to get bookly appointments from appointments and customer appointments table after start date
function getBooklyEventsAfter( $start_date, $bb_group_id = null, $series_id = null ){
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $custom_fields = json_encode(
        array(
            'id' => $bb_custom_field_id,
            'value' => strval($bb_group_id)
        )
    );
    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $appointments_data = $wpdb->get_results(
        " SELECT $bookly_appointments_table.start_date, $bookly_appointments_table.end_date , $bookly_customer_appointments_table.appointment_id,
                $bookly_customer_appointments_table.custom_fields, $bookly_customer_appointments_table.id as ca_id, $bookly_customer_appointments_table.series_id
                FROM $bookly_appointments_table INNER JOIN $bookly_customer_appointments_table 
                ON $bookly_customer_appointments_table.appointment_id = $bookly_appointments_table.id 
                HAVING $bookly_appointments_table.start_date >= '{$start_date}' 
                AND $bookly_customer_appointments_table.custom_fields LIKE '%{$custom_fields}%' 
                AND $bookly_customer_appointments_table.series_id = {$series_id}
                ORDER BY $bookly_appointments_table.start_date ASC "
    );
    $wpdb->flush();

    if( !empty($appointments_data) ):
        return $appointments_data;
    else:
        return false;
    endif;
}