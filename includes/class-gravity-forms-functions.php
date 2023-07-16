<?php


// register data table scripts
function data_table_scripts_register() {

    wp_register_style( 'datatables-style', plugin_dir_url(__DIR__) . 'public/css/jquery.dataTables.min.css', array(), rand(), 'all' );
    wp_register_script( 'datatables-script', plugin_dir_url(__DIR__) . 'public/js/jquery.dataTables.min.js', array(), rand(), true );
}
add_action( 'wp_enqueue_scripts', 'data_table_scripts_register' );


// register bootstrap scripts
function modal_scripts_register() {

    wp_register_style( 'modal-style', plugin_dir_url(__DIR__) . 'public/css/jquery.modal.min.css', array(), rand(), 'all' );
    wp_register_script( 'modal-script', plugin_dir_url(__DIR__) . 'public/js/jquery.modal.min.js', array(), rand(), true );
}
add_action( 'wp_enqueue_scripts', 'modal_scripts_register' );


// get users and push to select in gf select
add_filter( 'gform_pre_render_'.SP_PARENT_FORM_ID(), 'populate_students' );
add_filter( 'gform_pre_validation_'.SP_PARENT_FORM_ID(), 'populate_students' );
add_filter( 'gform_pre_submission_filter_'.SP_PARENT_FORM_ID(), 'populate_students' );
add_filter( 'gform_admin_pre_render_'.SP_PARENT_FORM_ID(), 'populate_students' );
function populate_students( $form ) {

    foreach ( $form['fields'] as &$field ) {

        if ( $field->type != 'select' || strpos( $field->cssClass, 'populate-students' ) === false ) {
            continue;
        }

        $choices = array();


        // get students
        global $wpdb;
        $table_name = $wpdb->prefix . 'bookly_customers';
        $customers = $wpdb->get_results(
            "SELECT * FROM $table_name"
        );
        $wpdb->flush();
        foreach ( $customers as $customer ):
            $choices[] = array( 'text' => $customer->full_name . ' - ' . $customer->email, 'value' => $customer->wp_user_id );
        endforeach;

        // you can add additional parameters here to alter the posts that are retrieved
        // more info: http://codex.wordpress.org/Template_Tags/get_posts
        //$posts = get_posts( 'numberposts=-1&post_status=publish' );

//        foreach ( $posts as $post ) {
//            $choices[] = array( 'text' => $post->post_title, 'value' => $post->post_title );
//        }

        // update 'Select a Post' to whatever you'd like the instructive option to be
        $field->placeholder = 'Select a Student';
        $field->choices = $choices;

    }

    return $form;
}

//add_filter( 'gform_pre_render', 'populate_students_multi_select' );
//add_filter( 'gform_pre_validation', 'populate_students_multi_select' );
//add_filter( 'gform_pre_submission_filter', 'populate_students_multi_select' );
//add_filter( 'gform_admin_pre_render', 'populate_students_multi_select' );
//function populate_students_multi_select( $form ) {
//
//    //only populating drop down for form id 2
////    if ( $form['id'] != 2 ) {
////        return $form;
////    }
//    foreach ( $form['fields'] as &$field ) {
//        if( strpos( $field->cssClass, 'populate-students-multi' ) === false ){
//            return $form;
//        }
//    }
//
//
//    global $wpdb;
//    $table_name = $wpdb->prefix . 'bookly_customers';
//    $customers = $wpdb->get_results(
//        "SELECT * FROM $table_name"
//    );
//    $wpdb->flush();
//
//    //Creating item array.
//    $items = array();
//
//    //Add a placeholder to field id 8, is not used with multi-select or radio, will overwrite placeholder set in form editor.
//    //Replace 8 with your actual field id.
//    $fields = $form['fields'];
//    foreach( $form['fields'] as &$field ) {
//        if ( strpos( $field->cssClass, 'populate-students-multi' ) === true ) {
//            $field->placeholder = 'This is my placeholder';
//        }
//    }
//
//    //Adding post titles to the items array
//    foreach ( $customers as $customer ) {
//        $items[] = array( 'value' => $customer->wp_user_id, 'text' => $customer->full_name . ' - ' . $customer->email );
//    }
//
//    //Adding items to field id 8. Replace 8 with your actual field id. You can get the field id by looking at the input name in the markup.
//    foreach ( $form['fields'] as &$field ) {
//        if ( strpos( $field->cssClass, 'populate-students-multi' ) === true ) {
//            $field->choices = $items;
//        }
//    }
//
//    return $form;
//
//}



// get bookly categories
add_filter( 'gform_pre_render_'.SP_PARENT_FORM_ID(), 'populate_bookly_categories' );
add_filter( 'gform_pre_validation_'.SP_PARENT_FORM_ID(), 'populate_bookly_categories' );
add_filter( 'gform_pre_submission_filter_'.SP_PARENT_FORM_ID(), 'populate_bookly_categories' );
add_filter( 'gform_admin_pre_render_'.SP_PARENT_FORM_ID(), 'populate_bookly_categories' );
function populate_bookly_categories( $form ) {

    foreach ( $form['fields'] as &$field ) {

        if ( $field->type != 'select' || strpos( $field->cssClass, 'populate-bookly-categories' ) === false ) {
            continue;
        }

        $choices = array();

        // get categories
        global $wpdb;
        $table_name = $wpdb->prefix . 'bookly_categories';
        $categories = $wpdb->get_results(
            "SELECT * FROM $table_name"
        );

        foreach ( $categories as $category ):
            $choices[] = array( 'text' => $category->name, 'value' => $category->id );
        endforeach;


        // update 'Select a Post' to whatever you'd like the instructive option to be
        $field->placeholder = 'Select Category';
        $field->choices = $choices;

    }

    return $form;
}


// get bookly services
add_filter( 'gform_pre_render_'.SP_PARENT_FORM_ID(), 'populate_bookly_services' );
add_filter( 'gform_pre_validation_'.SP_PARENT_FORM_ID(), 'populate_bookly_services' );
add_filter( 'gform_pre_submission_filter_'.SP_PARENT_FORM_ID(), 'populate_bookly_services' );
add_filter( 'gform_admin_pre_render_'.SP_PARENT_FORM_ID(), 'populate_bookly_services' );
function populate_bookly_services( $form ) {

    foreach ( $form['fields'] as &$field ) {

        if ( $field->type != 'select' || strpos( $field->cssClass, 'populate-bookly-services' ) === false ) {
            continue;
        }

        $choices = array();

        // get categories
        global $wpdb;
        $table_name = $wpdb->prefix . 'bookly_services';
        $services = $wpdb->get_results(
            "SELECT * FROM $table_name"
        );

        foreach ( $services as $service ):
            $choices[] = array( 'text' => $service->title, 'value' => $service->id );
        endforeach;


        // update 'Select a Post' to whatever you'd like the instructive option to be
        $field->placeholder = 'Select Service';
        $field->choices = $choices;

    }

    return $form;
}

// get timezone and push to select in gf select
add_filter( 'gform_pre_render_'.SP_PARENT_FORM_ID(), 'populate_timezone' );
add_filter( 'gform_pre_validation_'.SP_PARENT_FORM_ID(), 'populate_timezone' );
add_filter( 'gform_pre_submission_filter_'.SP_PARENT_FORM_ID(), 'populate_timezone' );
add_filter( 'gform_admin_pre_render_'.SP_PARENT_FORM_ID(), 'populate_timezone' );
function populate_timezone( $form ) {

    require_once plugin_dir_path( __FILE__ ) . 'class-getTimeZones.php';

    foreach ( $form['fields'] as &$field ) {

        if ( $field->type != 'select' || strpos( $field->cssClass, 'populate-timezone' ) === false ) {
            continue;
        }

        $choices = array();

        // get timezones
        $timezones = new getTimeZones;
        $timeZonesList = $timezones->getTimeZoneValues();

        $countriesTimeZone = array_merge(
            $timeZonesList['Africa'],
            $timeZonesList['America'],
            $timeZonesList['Antarctica'],
            $timeZonesList['Arctic'],
            $timeZonesList['Asia'],
            $timeZonesList['Atlantic'],
            $timeZonesList['Australia'],
            $timeZonesList['Europe'],
            $timeZonesList['Indian'],
            $timeZonesList['Pacific']
        );

        foreach($countriesTimeZone as $key => $value):
            $timeZoneOffset = ( $timezones->getTimeZoneOffset($key) == 0 ) ? $timezones->getTimeZoneOffset($key) : sprintf("%+d",$timezones->getTimeZoneOffset($key));
            $choices[] = array( 'text' => $value. ' UTC/GMT / ' . $timeZoneOffset , 'value' => $key );
        endforeach;

        // update 'Select a Post' to whatever you'd like the instructive option to be
        $field->placeholder = 'Select your Timezone';
        $field->choices = $choices;

    }

    return $form;
}

// get bookly staff (teachers) and push to select in gf select
add_filter( 'gform_pre_render_'.SP_PARENT_FORM_ID(), 'populate_teachers' );
add_filter( 'gform_pre_validation_'.SP_PARENT_FORM_ID(), 'populate_teachers' );
add_filter( 'gform_pre_submission_filter_'.SP_PARENT_FORM_ID(), 'populate_teachers' );
add_filter( 'gform_admin_pre_render_'.SP_PARENT_FORM_ID(), 'populate_teachers' );
function populate_teachers( $form ) {

    foreach ( $form['fields'] as &$field ) {

        if ( $field->type != 'select' || strpos( $field->cssClass, 'populate-teachers' ) === false ) {
            continue;
        }

        $choices = array();


        // get teachers
        global $wpdb;
        $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
        $staff_table_results = $wpdb->get_results(
            "SELECT * FROM $bookly_staff_table"
        );
        $wpdb->flush();

        foreach ( $staff_table_results as $staff_table_result ):
            $staff_id = $staff_table_result->id;
            $staff_wp_user_id = (int) $staff_table_result->wp_user_id;
            $staff_full_name = $staff_table_result->full_name;
            $staff_wp_user_obj = get_user_by( 'id', $staff_wp_user_id );
            $choices[] = array( 'text' => $staff_full_name . ' - ' . $staff_wp_user_obj->data->user_email, 'value' => $staff_id );
        endforeach;


        // update 'Select a Post' to whatever you'd like the instructive option to be
        $field->placeholder = 'Select a Teacher';
        $field->choices = $choices;

    }

    return $form;
}

/******
 * Ajax action to submit Gravity forms
 ******/

add_action('wp_ajax_submit_gf', 'submit_gf');
add_action( 'wp_ajax_nopriv_submit_gf', 'submit_gf' );
function submit_gf(){

    //inserts "This is test data" for the meta key "my_test_key" for entry id 14 for form id 1.
    $parent_meta_key = 'workflow_parent_form_id_' . SP_PARENT_FORM_ID() . '_entry_id';
    gform_add_meta(27, $parent_meta_key, '20');
    wp_die();

    // Define the URL that will be accessed.
    $url = rest_url( 'gf/v2/forms/3/submissions' );

    $data = array(
        "input_6" => "6",
        "input_7" => "23",
        "field_values" => "",
        "source_page" => 1,
        "target_page" =>  1
    );
    $response = wp_remote_post( $url, array(
        'body'    => $data,
        'headers' => REST_HEADERS(),
    ) );

    // Check the response code.
    if ( wp_remote_retrieve_response_code( $response ) != 200 || ( empty( wp_remote_retrieve_body( $response ) ) ) ) {
        // If not a 200, HTTP request failed.
        die( 'There was an error attempting to access the API.' );
    }

    pre_dump(json_decode($response['body']));





}


/******
 * Ajax action to get schedule for single program Gravity forms
 ******/

add_action('wp_ajax_view_learner_schedule', 'view_learner_schedule');
add_action( 'wp_ajax_nopriv_view_learner_schedule', 'view_learner_schedule' );
function view_learner_schedule () {

    $program_parent_entry_id = $_POST['program_parent_entry_id'];

    // get form entries after submission
    $schedules = GFAPI::get_entries( SCHEDULE_FORM_ID() );

    $htmlContent = ' <table class="display" style="width:100%">
                        <thead>
                        <tr>
                            <th>Entry ID</th>
                            <th> Start Time </th>
                            <th> Start Effective Dates </th>
                            <th> Days </th>
                            <th> Duration </th>
                            <th> Units </th>
                        </tr>
                        </thead>
                        <tbody>';

    foreach ( $schedules as $schedule ):

        $schedule_days = [];
        $gv_revision_parent_id = $schedule['gv_revision_parent_id'];
        $entry_status = $schedule['status'];
        $entry_approved = $schedule['is_approved'];
        if( $gv_revision_parent_id === false && $entry_status === 'active' && $entry_approved === '1' ): // this is entry not revision one
            $entry_id = $schedule['id'];
            $schedule_start_effective_dates = unserialize($schedule['3']);
            $schedule_start_time = $schedule['5'].':00';
            $schedule_duration = $schedule['6'];
            $units = $schedule_duration/15;
            $schedule_bookly_series_id = $schedule['7'];
            $schedule_program_parent_entry_id = $schedule['workflow_parent_form_id_'. SP_PARENT_FORM_ID() .'_entry_id'];
            // get schedule days
            for ( $i=1; $i<=7; $i++ ):
                $schedule_day = $schedule['1.'.$i.''];
                if( !empty($schedule_day) ):
                    $schedule_days[] = $schedule_day;
                endif;
            endfor;

            $start_effective_dates = '';
            foreach ( $schedule_start_effective_dates as $start_effective_date ):
                $start_effective_dates .= $start_effective_date.'<br>';
            endforeach;

            $days = implode(' <br> ', $schedule_days);

            if( $program_parent_entry_id === $schedule_program_parent_entry_id ):

                $htmlContent .= '
                    <tr> 
                        <td> '. $entry_id .' </td> 
                        <td> '. $schedule_start_time .' </td> 
                        <td> '. $start_effective_dates .' </td> 
                        <td> '. $days .' </td> 
                        <td> '. $schedule_duration .' </td> 
                        <td> '. $units .' </td> 
                    </tr>
                ';

            endif;


        endif;

    endforeach;

    $htmlContent .= '</tbody>
                    </table>';

    echo $htmlContent;
    wp_die();

}


// To enable, add the line of code below to your theme's functions.php file:
add_filter( 'gravityview_is_hierarchical', '__return_true' );



// shortcode to retrieve gravity forms entries

function display_gf_data () {
    // load data tables scripts at shortcode only
    wp_enqueue_style( 'datatables-style' );
    wp_enqueue_script( 'datatables-script' );

    // load jquery modal scripts at shortcode only
    wp_enqueue_style( 'modal-style' );
    wp_enqueue_script( 'modal-script' );


    ?>

    <hr>
    <table class="display data-table" style="width:100%">
        <thead>
        <tr>
            <th>Entry ID</th>
            <th> Service </th>
            <th> Timezone </th>
            <th> BB Group ID </th>
            <th> Teacher </th>
            <th> Learner </th>
            <th> View Schedule </th>
        </tr>
        </thead>
        <tbody>


        <?php
        // get learners entries
        $learners = GFAPI::get_entries( LEARNERS_FORM_ID() );

        foreach ( $learners as $learner ):

            $gv_revision_parent_id = $learner['gv_revision_parent_id'];
            $entry_status = $learner['status'];
            $entry_approved = $learner['is_approved'];
            if( $gv_revision_parent_id === false && $entry_status === 'active' && $entry_approved === '1' ): // this is entry not revision one
                $entry_id = $learner['id'];
                $learners_ids_string = substr( $learner['3'], 1, -1 );
                $learners_ids_array = explode(',', $learners_ids_string);
                foreach ( $learners_ids_array as $learner_id ):
                    $learner_ids[] = (int) preg_replace("/[^0-9]/","",$learner_id);
                endforeach;

                $program_parent_entry_id = $learner['workflow_parent_form_id_'. SP_PARENT_FORM_ID() .'_entry_id'];

                // get parent_entry_info
                $parent_form_entry = GFAPI::get_entry( $program_parent_entry_id );
                $program_timezone = $parent_form_entry['3'];
                $program_bb_group_id = $parent_form_entry['7'];
                $program_category_id = $parent_form_entry['5'];
                $program_service_id = $parent_form_entry['6'];
                $program_teacher_id = $parent_form_entry['8'];

                // get service name
                global $wpdb;
                $table_name = $wpdb->prefix . 'bookly_services';
                $services = $wpdb->get_results(
                    "SELECT * FROM $table_name WHERE id = {$program_service_id}"
                );
                $wpdb->flush();
                $service_title = $services[0]->title;


                // get teacher full name
                global $wpdb;
                $table_name = $wpdb->prefix . 'bookly_staff';
                $staff = $wpdb->get_results(
                    "SELECT * FROM $table_name WHERE id = {$program_teacher_id}"
                );
                $wpdb->flush();
                $teacher_name = $staff[0]->email;


                // for each learner create a table row
                foreach ( $learner_ids as $learner_id ):

                    // get learner (wp_user) data
                    $learner_obj = get_user_by('id', $learner_id);
                    $learner_name = $learner_obj->user_email;

                    ?>

                    <tr>
                        <td> <?php echo $entry_id; ?> </td>
                        <td> <?php echo $service_title; ?> </td>
                        <td> <?php echo $program_timezone; ?> </td>
                        <td> <?php echo $program_bb_group_id; ?> </td>
                        <td> <?php echo $teacher_name; ?> </td>
                        <td> <?php echo $learner_name; ?> </td>
                        <td> <a href="#view-schedule" class="view-schedule" rel="modal:open" data-program-parent-entry-id="<?php echo $program_parent_entry_id; ?>"> <i class="far fa-calendar-alt"></i> </a> </td>
                    </tr>

                <?php

                endforeach;

            endif;

        endforeach;

        ?>

        </tbody>
    </table>


    <div class="view-schedule-modal"></div>
    <div id="view-schedule" class="modal">  </div>


    <?php
    echo '<a href="" class="btn submit_gf">Submit Gravity Form</a>';


}
add_shortcode('display_gf_data', 'display_gf_data');


add_filter('gform_field_value_mslm_usr_meeting_id', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $meeting_id_meta_key = 30;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$meeting_id_meta_key}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_emp_id', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_emp_id = 20;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_emp_id}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_status', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_status = 21;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_status}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_role', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_role = 33;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_role}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_team', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_team = 43;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_team}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_department', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_department = 44;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_department}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_gender', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_gender = 29;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_gender}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_marital_status', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_marital_status = 35;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_marital_status}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_email', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_email = 4;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_email}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_mobile', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_mobile = 6;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_mobile}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_personal_email', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_personal_email = 5;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_personal_email}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_secondary_phone', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_secondary_phone = 7;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_secondary_phone}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_ec_relationship', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_ec_relationship = 18;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_ec_relationship}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_ec_name', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_ec_name = 13;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_ec_name}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_ec_phone', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_ec_phone = 15;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_ec_phone}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_ec_email', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_ec_email = 16;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_ec_email}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_legal_name', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_legal_name = 27;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_legal_name}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_dob', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_dob = 25;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_dob}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_pob', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_pob = 28;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_pob}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_bank_account_name', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_bank_account_name = 37;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_bank_account_name}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_bank_name', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_bank_name = 38;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_bank_name}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_bank_account_number', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_bank_account_number = 39;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_bank_account_number}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_bank_iban', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_bank_iban = 40;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_bank_iban}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_paypal', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_paypal = 41;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_paypal}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_add_street', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_add_street = '11.1';
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_add_street}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_add_street2', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_add_street2 = '11.2';
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_add_street2}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_add_city', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_add_city = '11.3';
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_add_city}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_add_state', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_add_state = '11.4';
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_add_state}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_add_zip', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_add_zip = '11.5';
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_add_zip}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_add_country', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_add_country = '11.6';
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_add_country}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});

add_filter('gform_field_value_mslm_usr_date_joined', function($value) {
    global $wpdb;
    $form_id = 16;
    $email_meta_key = 4;
    $mslm_usr_date_joined = 32;
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;
    $active_forms = [];
    $gforms_table = $wpdb->prefix . 'gf_entry';
    $gforms_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gfEntries = $wpdb->get_results(
        "SELECT * FROM $gforms_table WHERE form_id = {$form_id} AND status = 'active'"
    );
    $wpdb->flush();
    foreach ( $gfEntries as $gfEntry ):
        $gfEntryID = $gfEntry->id;


        $meta_email = $wpdb->get_results(
            "SELECT * FROM $gforms_meta_table WHERE meta_key = {$email_meta_key}  AND entry_id = {$gfEntryID}"
        );
        $wpdb->flush();

        if( $current_user_email === $meta_email[0]->meta_value ):
            $entry_id = $meta_email[0]->entry_id;

            $meta_value = $wpdb->get_results(
                "SELECT * FROM $gforms_meta_table WHERE meta_key = {$mslm_usr_date_joined}  AND entry_id = {$entry_id}"
            );
            $wpdb->flush();

        endif;




    endforeach;


    return $meta_value[0]->meta_value;

});
