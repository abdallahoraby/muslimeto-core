<?php


add_shortcode('test', 'test');
function test() {


    ?>



    <br><br><br>
    <br><br><br>

    <a href="#" class="test-ajax button"> test </a>
    <div class="test-ajax-result"></div>

<?php


    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $appointments_customer_results = $wpdb->get_results(
        "SELECT * FROM $bookly_appointments_table WHERE id = 404949"
    );
    $wpdb->flush();

    $booking_stored_start_date = $appointments_customer_results[0]->start_date;
    $booking_stored_end_date = $appointments_customer_results[0]->end_date;

    $start_day = '2022-10-05 23:00:00';
    $end_day = '2022-10-06 00:00:00';
    $overlap_check = overlapInMinutes($start_day, $end_day, $booking_stored_start_date, $booking_stored_end_date);

    //pre_dump($overlap_check);



    $bb_group_id = 202;
    $start_date = '2022-01-01';

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
                ORDER BY $bookly_appointments_table.start_date ASC "
    );
    $wpdb->flush();

    /*echo " SELECT $bookly_appointments_table.start_date, $bookly_appointments_table.end_date , $bookly_customer_appointments_table.appointment_id,
                $bookly_customer_appointments_table.custom_fields, $bookly_customer_appointments_table.id as ca_id, $bookly_customer_appointments_table.series_id
                FROM $bookly_appointments_table INNER JOIN $bookly_customer_appointments_table 
                ON $bookly_customer_appointments_table.appointment_id = $bookly_appointments_table.id 
                HAVING $bookly_appointments_table.start_date >= '{$start_date}' 
                AND $bookly_customer_appointments_table.custom_fields LIKE '%{$custom_fields}%' 
                ORDER BY $bookly_appointments_table.start_date ASC ";*/

    //pre_dump(  $appointments_data );



    try {
        pre_dump( regenerateAppointmentsFor2Months(202) );
    } catch(Error $e) {
        echo "The error was created on line: " . $e->getLine() . "in file " . $e->getFile();
    }







}




function test_ajax() {

    echo 'test';

    wp_die();

}
add_action('wp_ajax_test_ajax', 'test_ajax');
add_action( 'wp_ajax_nopriv_text_ajax', 'test_ajax' );



// register new menu location
//register_nav_menus( array(
//    'test_menu' => __( 'Test Menu', 'muslimeto-bb' ),
//) );










