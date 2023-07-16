<?php



use Bookly\Lib\Config;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Modules\Calendar\Page;

class muslimetoPage extends Bookly\Backend\Modules\Calendar\Page{

    public static function renderTemplate( $template, $variables = array(), $echo = true )
    {
        extract( $variables );

        // Start output buffering.
        ob_start();
        ob_implicit_flush( 0 );

        include plugin_dir_path( dirname( __FILE__ ) ) . '/includes/calendar/template-' . $template . '.php';

        if ( ! $echo ) {
            return ob_get_clean();
        }

        echo ob_get_clean();
    }



    public static function render()
    {


        Bookly\Backend\Modules\Calendar\Page::enqueueStyles( array(
            'module' => array( 'css/event-calendar.min.css' => array( 'bookly-backend-globals' ) ),
        ) );

//        if ( Config::proActive() ) {
//            if ( Common::isCurrentUserSupervisor() ) {
//                $staff_members = Staff::query()
//                    ->whereNot( 'visibility', 'archive' )
//                    ->sortBy( 'position' )
//                    ->find();
//                $staff_dropdown_data = Bookly\Lib\Proxy\Pro::getStaffDataForDropDown();
//            } else {
//                $staff_members = Staff::query()
//                    ->where( 'wp_user_id', get_current_user_id() )
//                    ->whereNot( 'visibility', 'archive' )
//                    ->find();
//                $staff_dropdown_data = array(
//                    0 => array(
//                        'name' => '',
//                        'items' => empty ( $staff_members ) ? array() : array( $staff_members[0]->getFields() ),
//                    ),
//                );
//            }
//        } else {
//            $staff = Staff::query()->findOne();
//            $staff_members = $staff ? array( $staff ) : array();
//            $staff_dropdown_data = array(
//                0 => array(
//                    'name' => '',
//                    'items' => empty ( $staff_members ) ? array() : array( $staff_members[0]->getFields() ),
//                ),
//            );
//        }

        $staff_members = Staff::query()
            ->whereNot( 'visibility', 'archive' )
            ->find();
        $staff_dropdown_data = array(
            0 => array(
                'name' => '',
                'items' => empty ( $staff_members ) ? array() : array( $staff_members[0]->getFields() ),
            ),
        );



        Bookly\Backend\Modules\Calendar\Page::enqueueScripts(
            $staff_members ?
                array(
                    'module' => array(
                        'js/event-calendar.min.js' => array( 'bookly-backend-globals' ),
                        'js/calendar-common.js' => array( 'bookly-event-calendar.min.js' ),
                        'js/calendar.js' => array( 'bookly-calendar-common.js', 'bookly-dropdown.js' ),
                    ),
                ) :
                array(
                    'alias' => array( 'bookly-backend-globals', ),
                ) );

        Bookly\Backend\Modules\Calendar\Page::enqueueStyles( array(
            'alias' => array( 'bookly-backend-globals', ),
        ) );

        wp_localize_script( 'bookly-calendar.js', 'BooklyL10n', array_merge(
            Bookly\Lib\Utils\Common::getCalendarSettings(),
            array(
                'delete' => __( 'Delete', 'bookly' ),
                'are_you_sure' => __( 'Are you sure?', 'bookly' ),
                'filterResourcesWithEvents' => Config::showOnlyStaffWithAppointmentsInCalendarDayView(),
                'recurring_appointments' => array(
                    'active' => (int) Config::recurringAppointmentsActive(),
                    'title' => __( 'Recurring appointments', 'bookly' ),
                ),
                'waiting_list' => array(
                    'active' => (int) Config::waitingListActive(),
                    'title' => __( 'On waiting list', 'bookly' ),
                ),
                'packages' => array(
                    'active' => (int) Config::packagesActive(),
                    'title' => __( 'Package', 'bookly' ),
                ),
            ) ) );

        $refresh_rate = get_user_meta( get_current_user_id(), 'bookly_calendar_refresh_rate', true );
        $services_dropdown_data = Common::getServiceDataForDropDown( 's.type = "simple"' );

        muslimetoPage::renderTemplate( 'calendar', compact( 'staff_members', 'staff_dropdown_data', 'services_dropdown_data', 'refresh_rate' ) );
    }


}

// include Calendar custom Page class
require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/calendar/class-Ajax.php' );

// register event calendar scripts
function muslimeto_calendar_scripts_register() {

    wp_register_style( 'muslimeto-calendar-style', plugin_dir_url(__DIR__) . 'public/css/event-calendar.min.css', array(), rand(), 'all' );
    wp_register_script( 'muslimeto-calendar-script', plugin_dir_url(__DIR__) . 'public/js/event-calendar.min.js', array('jquery'), rand(), true );
    wp_register_script( 'muslimeto-event-calendar-script', plugin_dir_url(__DIR__) . 'public/js/muslimeto-event-calendar.js', array('jquery'), rand(), true );
    wp_register_script( 'muslimeto-calendar-common.js-js', plugin_dir_url(__DIR__) . 'public/js/muslimeto-calendar-common.js', array('jquery'), rand(), true );
}
add_action( 'wp_enqueue_scripts', 'muslimeto_calendar_scripts_register' );



// shortcode for staff calendar
function muslimeto_staff_calendar_callback ($atts) {

    global $wpdb;

    $user_is_support = user_has_role(get_current_user_id(), 'support');
    $user_is_hr = user_has_role(get_current_user_id(), 'hr');
    $user_is_assistant_principle = user_has_role(get_current_user_id(), 'assistant_principle');
    $user_is_administrator = user_has_role(get_current_user_id(), 'administrator');
    $user_is_enrollment = user_has_role(get_current_user_id(), 'enrollment');
    $user_is_team_leader = user_has_role(get_current_user_id(), 'team_leader');

    // check if user is bookly staff or has these roles
    if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 || $user_is_support || $user_is_hr || $user_is_assistant_principle || $user_is_administrator || $user_is_enrollment ):

        // load muslimeto-calendar scripts at shortcode only
        wp_enqueue_style( 'muslimeto-calendar-style' );
        wp_enqueue_script( 'muslimeto-calendar-script' );
        wp_enqueue_script( 'muslimeto-event-calendar-script' );
        wp_enqueue_script( 'muslimeto-calendar-common.js-js' );


        // get current staff id
        $current_user_id = get_current_user_id();
        $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
        $staff_results = $wpdb->get_results(
            "SELECT id FROM $bookly_staff_table WHERE wp_user_id = {$current_user_id}"
        );
        $wpdb->flush();

        if( !empty($staff_results) ):
            $staff_id = $staff_results[0]->id;
        endif;

        // get all services
        $bookly_services_table = $wpdb->prefix . 'bookly_services';
        $services_results = $wpdb->get_results(
            "SELECT * FROM $bookly_services_table"
        );
        $wpdb->flush();
        foreach ( $services_results as $services_result ):
            $services_ids[] = $services_result->id;
        endforeach;


        if( !empty($atts['services_ids']) ):
            $services_ids_atts = $atts['services_ids'];
        endif;
        if( !empty($services_ids_atts)):
            $services_ids = $services_ids_atts;
        else:
            $services_ids = 'custom,' . implode(',', $services_ids);
        endif;

        if( !empty($atts['staff_ids']) ):
            $staff_ids_atts = $atts['staff_ids'];
        endif;

        $access_denied = '';
        if( !empty($staff_ids_atts) && !isset($staff_ids_atts) ):
            $staff_ids = $staff_ids_atts;
            $staff_ids_array = array_unique($staff_ids);
            $staff_ids = implode(',', $staff_ids_array);
        elseif( isset($_GET['team_staff_id']) ):
            // check if this user in list for team leader
            $team_teachers = get_team_teachers( get_current_user_id() );
            if( !empty($team_teachers) ):
                foreach ( $team_teachers as $team_teacher ):
                    $team_staff_ids[] = $team_teacher['bookly_staff_id'];
                endforeach;
            endif;

            $all_staff = getAllBooklyStaff();
            foreach ( $all_staff as $staff):
                $all_staff_ids[] = $staff->id;
            endforeach;


            if( !empty($team_staff_ids) && ( in_array($_GET['team_staff_id'], $team_staff_ids) || in_array($_GET['team_staff_id'], $all_staff_ids) ) ||
                $user_is_support || $user_is_hr || $user_is_enrollment || $user_is_administrator || $user_is_assistant_principle
            ):
                $staff_ids = $_GET['team_staff_id'];
                $access_denied = '';
            else:
                $access_denied = '<div class="alert"> You do not have access to view/edit this teacher schedule. </div>';
            endif;
        else:
            $staff_ids = $staff_id;
        endif;

        // is teacher in support show delete
        if( $user_is_support || $user_is_hr || $user_is_enrollment || $user_is_assistant_principle ):
            $access_delete = 'true';
        else:
            $access_delete = 'false';
        endif;

        if( $user_is_administrator ):
            $delete_without_validation = 'true';
        else:
            $delete_without_validation = 'false';
        endif;


        // is teacher in support or team_leader show edit
        if( $user_is_support || $user_is_hr || $user_is_enrollment || $user_is_administrator || $user_is_assistant_principle ):
            $access_edit = 'true';
        else:
            $access_edit = 'false';
        endif;




        $htmlContent = '<div class="muslimeto_calendar staff">';
        $htmlContent .= $access_denied;
        $htmlContent .= '<input type="hidden" id="calendar_staff_ids" value="'. $staff_ids .'">';
        $htmlContent .= '<input type="hidden" id="calendar_services_ids" value="'. $services_ids .'">';
        $htmlContent .= '<input type="hidden" id="access_delete" value="'. $access_delete .'">';
        $htmlContent .= '<input type="hidden" id="delete_without_validation" value="'. $delete_without_validation .'">';
        $htmlContent .= '<input type="hidden" id="access_edit" value="'. $access_edit .'">';

        ob_start();
        $calendarObject = new muslimetoPage;
        echo  $calendarObject->render();
        $output = ob_get_contents();
        ob_end_clean();

        $htmlContent .= $output;
        $htmlContent .= '</div>';
        echo $htmlContent;

    endif;



}

add_shortcode('muslimeto_staff_calendar', 'muslimeto_staff_calendar_callback');


// shortcode for learner calendar
function muslimeto_learner_calendar_callback ($atts) {

    global $wpdb;


    // check if user is bookly customer (learner)
    if( Bookly\Lib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ):

        // load muslimeto-calendar scripts at shortcode only
        wp_enqueue_style( 'muslimeto-calendar-style' );
        wp_enqueue_script( 'muslimeto-calendar-script' );
        wp_enqueue_script( 'muslimeto-event-calendar-script' );
        wp_enqueue_script( 'muslimeto-calendar-common.js-js' );





        if( !empty($_GET['child_customer_id']) ):
            $customer_id = $_GET['child_customer_id'];
            $wp_user_id = getWPuserIDfromBookly($customer_id);
        else:
            $staff_ids = getLearnerStaff( get_current_user_id() );
            $customer_id = getcustomerID( get_current_user_id() );
        endif;

        // get all services
        $bookly_services_table = $wpdb->prefix . 'bookly_services';
        $services_results = $wpdb->get_results(
            "SELECT * FROM $bookly_services_table"
        );
        $wpdb->flush();
        foreach ( $services_results as $services_result ):
            $services_ids[] = $services_result->id;
        endforeach;


        $services_ids_atts = $atts['services_ids'];
        if( !empty($services_ids_atts)):
            $services_ids = $services_ids_atts;
        else:
            $services_ids = 'custom,' . implode(',', $services_ids);
        endif;

        $staff_ids_atts = $atts['staff_ids'];
        if( !empty($staff_ids_atts)):
            $staff_ids = $staff_ids_atts;
        elseif( !empty($_GET['child_customer_id']) ):
            $staff_ids = getLearnerStaff( $wp_user_id );
            $staff_ids_array = array_unique($staff_ids);
            $staff_ids = implode(',', $staff_ids_array);
        else:
            $staff_ids_array = array_unique($staff_ids);
            $staff_ids = implode(',', $staff_ids_array);
        endif;



        //check if parent has childs show filter
        $parent_childs = getParentChilds( get_current_user_id() );
        if( !empty($parent_childs) ):
            // user is a parent get his childs as bookly cutsomers
            foreach ($parent_childs as $parent_child):
                $customer_child_ids[] = getcustomerID($parent_child);
            endforeach;

            if( !empty($customer_child_ids) ):
                $customers_options = '';
                $selected_id = !empty($_GET['child_customer_id']) ? (int) $_GET['child_customer_id'] : '';
                foreach ($customer_child_ids as $customer_child_id):
                    // get email and full name for child
                    $child_wp_user_id = getWPuserIDfromBookly($customer_child_id);
                    $child_user_obj = get_user_by('id', $child_wp_user_id);
                    $child_email = $child_user_obj->data->user_email;
                    $child_display_name = $child_user_obj->data->display_name;


                    if( $selected_id ===  (int) $customer_child_id ):
                        $selected = 'selected';
                    else:
                        $selected = '';
                    endif;
                    // generate options for select
                    $customers_options .= '<option '.$selected.' value="'. $customer_child_id .'"> ' . $customer_child_id . ' ' . $child_email . ' ' . $child_display_name . ' </option>';
                endforeach;

                global $wp;
                $current_page_url = home_url( $wp->request );

                $select_child = '<div class="parent_childs">
                                    <select class="" id="child_customer_id">
                                       <option selected disabled>-- select learner --</option>
                                       '. $customers_options .'
                                    </select>
                                    <input type="hidden" id="current_page_url" value="'. $current_page_url .'">
                                    <a href="#" class="reset_appointments_view btn"> Reset </a>
                                </div>';

            endif;
        endif;


        if( !empty($_GET['child_customer_id']) && $_GET['child_customer_id'] !== 'null' ):
            if(  in_array($_GET['child_customer_id'], $customer_child_ids) ):
                $customer_child_id = $_GET['child_customer_id'];
                $access_denied = '';
            else:
                $access_denied = '<div class="alert"> You do not have access to view this user schedule. </div>';
            endif;
        endif;

        $htmlContent = '<div class="muslimeto_calendar learner">';
        $htmlContent .= '<input type="hidden" id="calendar_staff_ids" value="'. $staff_ids .'">';
        $htmlContent .= '<input type="hidden" id="calendar_services_ids" value="'. $services_ids .'">';
        $htmlContent .= '<input type="hidden" id="calendar_customer_id" value="'. $customer_id .'">';
        $htmlContent .= $access_denied;
        $htmlContent .= $select_child;


        ob_start();
        $calendarObject = new muslimetoPage;
        echo  $calendarObject->render();
        $output = ob_get_contents();
        ob_end_clean();

        $htmlContent .= $output;
        $htmlContent .= '</div>';
        echo $htmlContent;

    endif;

}

add_shortcode('muslimeto_learner_calendar', 'muslimeto_learner_calendar_callback');


// shortcode for academic calendar
function muslimeto_academic_calendar_callback ($atts) {

    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    // check if user is bookly customer (learner) or staff only
    // load muslimeto-calendar scripts at shortcode only
    wp_enqueue_style( 'muslimeto-calendar-style' );
    wp_enqueue_script( 'muslimeto-calendar-script' );
    wp_enqueue_script( 'muslimeto-event-calendar-script' );
    wp_enqueue_script( 'muslimeto-calendar-common.js-js' );
    if( ( Bookly\Lib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ) || ( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ) ):

        // academic class
        if( Bookly\Lib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ): // learner
            $extra_class = 'learner';
        elseif( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ): // staff
            $extra_class = 'staff';
        endif;

        if( !empty($_GET['child_customer_id']) ):
            $customer_id = $_GET['child_customer_id'];
            $wp_user_id = getWPuserIDfromBookly($customer_id);
        else:
            $staff_ids = getLearnerStaff( get_current_user_id() );
            $customer_id = getcustomerID( get_current_user_id() );
        endif;


        // get all services
        $bookly_services_table = $wpdb->prefix . 'bookly_services';
        $services_results = $wpdb->get_results(
            "SELECT * FROM $bookly_services_table"
        );
        $wpdb->flush();
        foreach ( $services_results as $services_result ):
            $services_ids[] = $services_result->id;
        endforeach;


        if( !empty($atts) ):
            $services_ids_atts = $atts['services_ids'];
        endif;

        if( !empty($services_ids_atts)):
            $services_ids = $services_ids_atts;
        else:
            $services_ids = 'custom,' . implode(',', $services_ids);
        endif;

        // get all staff ids
//        $staff_results = $wpdb->get_results(
//            "SELECT * FROM $bookly_staff_table"
//        );
//        $wpdb->flush();
//
//        foreach ( $staff_results as $staff_result ):
//            $all_staff_ids[] = $staff_result->id;
//        endforeach;

        $bb_group_id = bp_get_current_group_id();
        $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
        $custom_fields = json_encode(
            array(
                array(
                    'id' => $bb_custom_field_id,
                    'value' => strval($bb_group_id)
                )
            )
        );

        // get BB group staff id only
        $gf_entry_id = getBBgroupGFentryID($bb_group_id);
        if( !empty($gf_entry_id) ):
            // get staff id assigned
            $gf_staff_id = getSPentryStaffId($gf_entry_id);
        endif;

        if( !empty($atts) ):
            $staff_ids_atts = $atts['staff_ids'];
        endif;
        if( !empty($staff_ids_atts)):
            $staff_ids = $staff_ids_atts;
        else:
            $staff_ids = $gf_staff_id;
//            $staff_ids_array = array_unique($all_staff_ids);
//            $staff_ids = implode(',', $staff_ids_array);
        endif;





        $htmlContent = '<div class="muslimeto_calendar academic '. $extra_class .'">';
        $htmlContent .= '<input type="hidden" id="calendar_staff_ids" value="'. $staff_ids .'">';
        $htmlContent .= '<input type="hidden" id="calendar_services_ids" value="'. $services_ids .'">';
        $htmlContent .= '<input type="hidden" id="bb_group_id" value="'.$bb_group_id.'">';



        ob_start();
        $calendarObject = new muslimetoPage;
        echo  $calendarObject->render();
        $output = ob_get_contents();
        ob_end_clean();

        $htmlContent .= $output;
        $htmlContent .= '</div>';
        echo $htmlContent;

    endif;

}

add_shortcode('muslimeto_academic_calendar', 'muslimeto_academic_calendar_callback');


// shortcode for staff calendar with new css styles
function muslimeto_modern_staff_calendar_callback ($atts) {

    global $wpdb;

    $user_is_support = user_has_role(get_current_user_id(), 'support');
    $user_is_hr = user_has_role(get_current_user_id(), 'hr');
    $user_is_assistant_principle = user_has_role(get_current_user_id(), 'assistant_principle');
    $user_is_administrator = user_has_role(get_current_user_id(), 'administrator');
    $user_is_enrollment = user_has_role(get_current_user_id(), 'enrollment');
    $user_is_team_leader = user_has_role(get_current_user_id(), 'team_leader');

    // check if user is bookly staff or has these roles
    if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 || $user_is_support || $user_is_hr || $user_is_assistant_principle || $user_is_administrator || $user_is_enrollment ):

        // load muslimeto-calendar scripts at shortcode only
        wp_enqueue_style( 'muslimeto-calendar-style' );
        wp_enqueue_script( 'muslimeto-calendar-script' );
        wp_enqueue_script( 'muslimeto-event-calendar-script' );
        wp_enqueue_script( 'muslimeto-calendar-common.js-js' );


        // get current staff id
        $current_user_id = get_current_user_id();
        $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
        $staff_results = $wpdb->get_results(
            "SELECT id FROM $bookly_staff_table WHERE wp_user_id = {$current_user_id}"
        );
        $wpdb->flush();

        if( !empty($staff_results) ):
            $staff_id = $staff_results[0]->id;
        endif;

        // get all services
        $bookly_services_table = $wpdb->prefix . 'bookly_services';
        $services_results = $wpdb->get_results(
            "SELECT * FROM $bookly_services_table"
        );
        $wpdb->flush();
        foreach ( $services_results as $services_result ):
            $services_ids[] = $services_result->id;
        endforeach;


        if( !empty($atts['services_ids']) ):
            $services_ids_atts = $atts['services_ids'];
        endif;
        if( !empty($services_ids_atts)):
            $services_ids = $services_ids_atts;
        else:
            $services_ids = 'custom,' . implode(',', $services_ids);
        endif;

        if( !empty($atts['staff_ids']) ):
            $staff_ids_atts = $atts['staff_ids'];
        endif;

        $access_denied = '';
        if( !empty($staff_ids_atts) && !isset($staff_ids_atts) ):
            $staff_ids = $staff_ids_atts;
            $staff_ids_array = array_unique($staff_ids);
            $staff_ids = implode(',', $staff_ids_array);
        elseif( isset($_GET['team_staff_id']) ):
            // check if this user in list for team leader
            $team_teachers = get_team_teachers( get_current_user_id() );
            if( !empty($team_teachers) ):
                foreach ( $team_teachers as $team_teacher ):
                    $team_staff_ids[] = $team_teacher['bookly_staff_id'];
                endforeach;
            endif;

            $all_staff = getAllBooklyStaff();
            foreach ( $all_staff as $staff):
                $all_staff_ids[] = $staff->id;
            endforeach;


            if( !empty($team_staff_ids) && ( in_array($_GET['team_staff_id'], $team_staff_ids) || in_array($_GET['team_staff_id'], $all_staff_ids) ) ||
                $user_is_support || $user_is_hr || $user_is_enrollment || $user_is_administrator || $user_is_assistant_principle
            ):
                $staff_ids = $_GET['team_staff_id'];
                $access_denied = '';
            else:
                $access_denied = '<div class="alert"> You do not have access to view/edit this teacher schedule. </div>';
            endif;
        else:
            $staff_ids = $staff_id;
        endif;

        // is teacher in support show delete
        if( $user_is_support || $user_is_hr || $user_is_enrollment || $user_is_assistant_principle ):
            $access_delete = 'true';
        else:
            $access_delete = 'false';
        endif;

        if( $user_is_administrator ):
            $delete_without_validation = 'true';
        else:
            $delete_without_validation = 'false';
        endif;


        // is teacher in support or team_leader show edit
        if( $user_is_support || $user_is_hr || $user_is_enrollment || $user_is_administrator || $user_is_assistant_principle ):
            $access_edit = 'true';
        else:
            $access_edit = 'false';
        endif;




        $htmlContent = '<div class="muslimeto_calendar full_calendar staff">';
        $htmlContent .= $access_denied;
        $htmlContent .= '<input type="hidden" id="calendar_staff_ids" value="'. $staff_ids .'">';
        $htmlContent .= '<input type="hidden" id="calendar_services_ids" value="'. $services_ids .'">';
        $htmlContent .= '<input type="hidden" id="access_delete" value="'. $access_delete .'">';
        $htmlContent .= '<input type="hidden" id="delete_without_validation" value="'. $delete_without_validation .'">';
        $htmlContent .= '<input type="hidden" id="access_edit" value="'. $access_edit .'">';

        ob_start();
        $calendarObject = new muslimetoPage;
        echo  $calendarObject->render();
        $output = ob_get_contents();
        ob_end_clean();

        $htmlContent .= $output;
        $htmlContent .= '</div>';
        echo $htmlContent;

    endif;



}

add_shortcode('muslimeto_modern_staff_calendar', 'muslimeto_modern_staff_calendar_callback');


// shortcode for learner calendar with new css styles
function muslimeto_modern_learner_calendar_callback ($atts) {

    global $wpdb;


    // check if user is bookly customer (learner)
    if( Bookly\Lib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ):

        // load muslimeto-calendar scripts at shortcode only
        wp_enqueue_style( 'muslimeto-calendar-style' );
        wp_enqueue_script( 'muslimeto-calendar-script' );
        wp_enqueue_script( 'muslimeto-event-calendar-script' );
        wp_enqueue_script( 'muslimeto-calendar-common.js-js' );




        if( !empty($_GET['child_customer_id']) ):
            $customer_id = $_GET['child_customer_id'];
            $wp_user_id = getWPuserIDfromBookly($customer_id);
        else:
            $staff_ids = getLearnerStaff( get_current_user_id() );
            $customer_id = getcustomerID( get_current_user_id() );
        endif;

        // get all services
        $bookly_services_table = $wpdb->prefix . 'bookly_services';
        $services_results = $wpdb->get_results(
            "SELECT * FROM $bookly_services_table"
        );
        $wpdb->flush();
        foreach ( $services_results as $services_result ):
            $services_ids[] = $services_result->id;
        endforeach;

        if( !empty($atts) ):
            $services_ids_atts = $atts['services_ids'];
        endif;
        if( !empty($services_ids_atts)):
            $services_ids = $services_ids_atts;
        else:
            $services_ids = 'custom,' . implode(',', $services_ids);
        endif;

        if( !empty($atts) ):
            $staff_ids_atts = $atts['staff_ids'];
        endif;
        if( !empty($staff_ids_atts)):
            $staff_ids = $staff_ids_atts;
        elseif( !empty($_GET['child_customer_id']) ):
            $staff_ids = getLearnerStaff( $wp_user_id );
            $staff_ids_array = array_unique($staff_ids);
            $staff_ids = implode(',', $staff_ids_array);
        else:
            $staff_ids_array = array_unique($staff_ids);
            $staff_ids = implode(',', $staff_ids_array);
        endif;



        //check if parent has childs show filter
        $parent_childs = getParentChilds( get_current_user_id() );
        if( !empty($parent_childs) ):
            // user is a parent get his childs as bookly cutsomers
            foreach ($parent_childs as $parent_child):
                $customer_child_ids[] = getcustomerID($parent_child);
            endforeach;

            if( !empty($customer_child_ids) ):
                $customers_options = '';
                $selected_id = !empty($_GET['child_customer_id']) ? (int) $_GET['child_customer_id'] : '';
                foreach ($customer_child_ids as $customer_child_id):
                    // get email and full name for child
                    $child_wp_user_id = getWPuserIDfromBookly($customer_child_id);
                    $child_user_obj = get_user_by('id', $child_wp_user_id);
                    $child_email = $child_user_obj->data->user_email;
                    $child_display_name = $child_user_obj->data->display_name;


                    if( $selected_id ===  (int) $customer_child_id ):
                        $selected = 'selected';
                    else:
                        $selected = '';
                    endif;
                    // generate options for select
                    $customers_options .= '<option '.$selected.' value="'. $customer_child_id .'"> ' . $customer_child_id . ' ' . $child_email . ' ' . $child_display_name . ' </option>';
                endforeach;

                global $wp;
                $current_page_url = home_url( $wp->request );

                $select_child = '<div class="parent_childs">
                                    <select class="" id="child_customer_id">
                                       <option selected disabled>-- select learner --</option>
                                       '. $customers_options .'
                                    </select>
                                    <input type="hidden" id="current_page_url" value="'. $current_page_url .'"> 
                                    <a href="#" class="reset_appointments_view btn"> Reset </a>
                                </div>';

            endif;
        endif;


        if( !empty($_GET['child_customer_id']) && $_GET['child_customer_id'] !== 'null' ):
            if(  in_array($_GET['child_customer_id'], $customer_child_ids) ):
                $customer_child_id = $_GET['child_customer_id'];
                $access_denied = '';
            else:
                $access_denied = '<div class="alert"> You do not have access to view this user schedule. </div>';
            endif;
        endif;

        $htmlContent = '<div class="muslimeto_calendar full_calendar learner">';
        $htmlContent .= '<input type="hidden" id="calendar_staff_ids" value="'. $staff_ids .'">';
        $htmlContent .= '<input type="hidden" id="calendar_services_ids" value="'. $services_ids .'">';
        $htmlContent .= '<input type="hidden" id="calendar_customer_id" value="'. $customer_id .'">';
        $htmlContent .= $access_denied;
        $htmlContent .= $select_child;


        ob_start();
        $calendarObject = new muslimetoPage;
        echo  $calendarObject->render();
        $output = ob_get_contents();
        ob_end_clean();

        $htmlContent .= $output;
        $htmlContent .= '</div>';
        echo $htmlContent;

    endif;

}

add_shortcode('muslimeto_modern_learner_calendar', 'muslimeto_modern_learner_calendar_callback');


// shortcode for parent calendar
function muslimeto_parent_calendar_callback ($atts) {

    global $wpdb;
    $wp_user_id = get_current_user_id();
    // check if user is parent
    if( checkIfParent($wp_user_id) == true ):

        // load muslimeto-calendar scripts at shortcode only
        wp_enqueue_style( 'muslimeto-calendar-style' );
        wp_enqueue_script( 'muslimeto-calendar-script' );
        wp_enqueue_script( 'muslimeto-event-calendar-script' );
        wp_enqueue_script( 'muslimeto-calendar-common.js-js' );




        if( !empty($_GET['child_customer_id']) ):
            $customer_id = $_GET['child_customer_id'];
            $wp_user_id = getWPuserIDfromBookly($customer_id);
        else:
            $staff_ids = getLearnerStaff( get_current_user_id() );
            $customer_id = getcustomerID( get_current_user_id() );
        endif;

        if( !empty($atts['customer_id']) ):
            $customer_id = $atts['customer_id'];
        endif;

        // get all services
        $bookly_services_table = $wpdb->prefix . 'bookly_services';
        $services_results = $wpdb->get_results(
            "SELECT * FROM $bookly_services_table"
        );
        $wpdb->flush();
        foreach ( $services_results as $services_result ):
            $services_ids[] = $services_result->id;
        endforeach;


        if( !empty($atts) ):
            $services_ids_atts = $atts['services_ids'];
        endif;
        if( !empty($services_ids_atts)):
            $services_ids = $services_ids_atts;
        else:
            $services_ids = 'custom,' . implode(',', $services_ids);
        endif;

        if( !empty($atts) ):
            $staff_ids_atts = $atts['staff_ids'];
        endif;

        if( !empty($staff_ids_atts)):
            $staff_ids = $staff_ids_atts;
        elseif( !empty($_GET['child_customer_id']) ):
            $staff_ids = getLearnerStaff( $wp_user_id );
            $staff_ids_array = array_unique($staff_ids);
            $staff_ids = implode(',', $staff_ids_array);
        else:
            $staff_ids_array = array_unique($staff_ids);
            $staff_ids = implode(',', $staff_ids_array);
        endif;



        //check if parent has childs show filter
        $parent_childs = getParentChilds( get_current_user_id() );
        if( !empty($parent_childs) ):
            // user is a parent get his childs as bookly cutsomers
            foreach ($parent_childs as $parent_child):
                $customer_child_ids[] = getcustomerID($parent_child);
            endforeach;

            if( !empty($customer_child_ids) ):
                $customers_options = '';
                $selected_id = !empty($_GET['child_customer_id']) ? (int) $_GET['child_customer_id'] : '';
                foreach ($customer_child_ids as $customer_child_id):
                    // get email and full name for child
                    $child_wp_user_id = getWPuserIDfromBookly($customer_child_id);
                    $child_user_obj = get_user_by('id', $child_wp_user_id);
                    $child_email = $child_user_obj->data->user_email;
                    $child_display_name = $child_user_obj->data->display_name;

                    if( $selected_id ===  (int) $customer_child_id ):
                        $selected = 'selected';
                    else:
                        $selected = '';
                    endif;
                    // generate options for select
                    $customers_options .= '<option '.$selected.' value="'. $customer_child_id .'"> ' . $customer_child_id . ' ' . $child_email . ' ' . $child_display_name . ' </option>';

                    // if parent get childs groups => and get groups assigned to them
                    $user_groups = groups_get_user_groups($child_wp_user_id);
                    if( !empty($user_groups['groups']) ):
                        foreach($user_groups['groups'] as $bb_group_id):
                            $groups_list[] = $bb_group_id;
                        endforeach;
                    endif;

                endforeach;


                $groups_list = array_unique($groups_list);
                // get staff ids assigned to each group
                if( !empty($groups_list) ):
                    foreach ( $groups_list as $bb_group_id ):
                        $bb_group_id = (int) $bb_group_id;
                        $sp_entry_id = getBBgroupGFentryID ($bb_group_id);
                        $assigned_staff_id[] = getSPentryStaffId($sp_entry_id);
                    endforeach;
                    $staff_ids_array = array_unique($assigned_staff_id);
                    $staff_ids = implode(',', $staff_ids_array);
                endif;






                global $wp;
                $current_page_url = home_url( $wp->request );

                $select_child = '<div class="calendar-actions">
                                    <div class="parent_childs">
                                        <select class="" id="child_customer_id">
                                           <option selected disabled>-- select learner --</option>
                                           '. $customers_options .'
                                        </select>
                                        <input type="hidden" id="current_page_url" value="'. $current_page_url .'">
                                        <a href="#" class="reset_appointments_view btn"> Reset </a>
                                    </div>
                                    
                                    <div class="mak_actions hidden">
                                        <button class="btn schedule-makeup-class" data-balloon-pos="down" data-balloon="Schedule Makeup Class" 
                                        data-bs-toggle="modal" data-bs-target="#schedule-makeup-session">
                                            <span class="material-icons">edit_calendar</span> 
                                         </button>
                                    </div>
                                </div>';

            endif;
        endif;


        if( !empty($_GET['child_customer_id']) && $_GET['child_customer_id'] !== 'null' ):
            if(  in_array($_GET['child_customer_id'], $customer_child_ids) ):
                $customer_child_id = $_GET['child_customer_id'];
                $access_denied = '';
            else:
                $access_denied = '<div class="alert w-50 mx-auto"> You do not have access to view this user schedule. </div>';
            endif;
        endif;

        get_template_part('template-parts/common/template-session-schedule');

        $htmlContent = '<div class="muslimeto_calendar learner full_calendar">';
        $htmlContent .= '<input type="hidden" id="calendar_staff_ids" value="'. $staff_ids .'">';
        $htmlContent .= '<input type="hidden" id="calendar_services_ids" value="'. $services_ids .'">';
        $htmlContent .= '<input type="hidden" id="calendar_customer_id" value="'. $customer_id .'">';
        $htmlContent .= '<input type="hidden" id="is_parent" value="true">';
        $htmlContent .= $access_denied;
        $htmlContent .= $select_child;


        ob_start();

        get_template_part('template-parts/common/template-makeup-widget', null, array(
            'wp_user_id' => get_current_user_id()
        ));

        get_template_part('template-parts/common/template-modal', null, array(
                'id' => 'cancel-session',
                'title' => 'Cancel Class Session'
            )
        );

        $calendarObject = new muslimetoPage;
        echo  $calendarObject->render();
        $output = ob_get_contents();
        ob_end_clean();

        $htmlContent .= $output;
        $htmlContent .= '</div>';
        echo $htmlContent;

    else:
        echo " <div class='alert w-50 mx-auto'> You do not have access to view this user schedule. </div> ";
    endif;

}
add_shortcode('muslimeto_parent_calendar', 'muslimeto_parent_calendar_callback');


// shortcode for attendance calendar
function muslimeto_attendance_calendar_callback ($atts) {

    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    // check if user is bookly customer (learner) or staff only
    // load muslimeto-calendar scripts at shortcode only
    wp_enqueue_style( 'muslimeto-calendar-style' );
    wp_enqueue_script( 'muslimeto-calendar-script' );
    wp_enqueue_script( 'muslimeto-event-calendar-script' );
    wp_enqueue_script( 'muslimeto-calendar-common.js-js' );
    if( ( Bookly\Lib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ) || ( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ) ):

        // academic class
        if( Bookly\Lib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ): // learner
            $extra_class = 'learner';
        elseif( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ): // staff
            $extra_class = 'staff';
        endif;

        if( !empty($_GET['child_customer_id']) ):
            $customer_id = $_GET['child_customer_id'];
            $wp_user_id = getWPuserIDfromBookly($customer_id);
        else:
            $staff_ids = getLearnerStaff( get_current_user_id() );
            $customer_id = getcustomerID( get_current_user_id() );
        endif;


        // get all services
        $bookly_services_table = $wpdb->prefix . 'bookly_services';
        $services_results = $wpdb->get_results(
            "SELECT * FROM $bookly_services_table"
        );
        $wpdb->flush();
        foreach ( $services_results as $services_result ):
            $services_ids[] = $services_result->id;
        endforeach;

        if( !empty($atts) ):
            $services_ids_atts = $atts['services_ids'];
        endif;
        if( !empty($services_ids_atts)):
            $services_ids = $services_ids_atts;
        else:
            $services_ids = 'custom,' . implode(',', $services_ids);
        endif;

        // get all staff ids
//        $staff_results = $wpdb->get_results(
//            "SELECT * FROM $bookly_staff_table"
//        );
//        $wpdb->flush();
//
//        foreach ( $staff_results as $staff_result ):
//            $all_staff_ids[] = $staff_result->id;
//        endforeach;

        $bb_group_id = bp_get_current_group_id();
        $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
        $custom_fields = json_encode(
            array(
                array(
                    'id' => $bb_custom_field_id,
                    'value' => strval($bb_group_id)
                )
            )
        );

        // get BB group staff id only
        $gf_entry_id = getBBgroupGFentryID($bb_group_id);
        if( !empty($gf_entry_id) ):
            // get staff id assigned
            $gf_staff_id = getSPentryStaffId($gf_entry_id);
        endif;

        if( !empty($atts) ):
            $staff_ids_atts = $atts['staff_ids'];
        endif;
        if( !empty($staff_ids_atts)):
            $staff_ids = $staff_ids_atts;
        else:
            $staff_ids = $gf_staff_id;
//            $staff_ids_array = array_unique($all_staff_ids);
//            $staff_ids = implode(',', $staff_ids_array);
        endif;





        $htmlContent = '<div class="muslimeto_calendar academic '. $extra_class .'">';
        $htmlContent .= '<input type="hidden" id="calendar_staff_ids" value="'. $staff_ids .'">';
        $htmlContent .= '<input type="hidden" id="calendar_services_ids" value="'. $services_ids .'">';
        $htmlContent .= '<input type="hidden" id="bb_group_id" value="'.$bb_group_id.'">';



        ob_start();
        $calendarObject = new muslimetoPage;
        echo  $calendarObject->render();
        $output = ob_get_contents();
        ob_end_clean();

        $htmlContent .= $output;
        $htmlContent .= '</div>';
        echo $htmlContent;

    endif;

}

add_shortcode('muslimeto_attendance_calendar', 'muslimeto_attendance_calendar_callback');

