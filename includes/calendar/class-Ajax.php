<?php


use Bookly\Lib\Config;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Utils\DateTime;
use Bookly\Lib\Utils\Price;


function getStaffAppointments($start, $end, $location_ids, $staff_ids, $customer_id, $display_teacher_timezone)
{


    $result     = array();
    $one_day    = new \DateInterval( 'P1D' );
    $start_date = new \DateTime( $start );
    $end_date   = new \DateTime( $end );
    $location_ids = explode( ',', $location_ids );

    if( $display_teacher_timezone === 'true' ):
        // get current $staff_id time zone
        $display_tz = getTeacherTimeZone($staff_ids);
    else:
        // Determine display time zone
        $display_tz = Common::getCurrentUserTimeZone();
    endif;

    // Due to possibly different time zones of staff members expand start and end dates
    // to provide 100% coverage of the requested date range
    $start_date->sub( $one_day );
    $end_date->add( $one_day );

    // Load staff members
    $query = Staff::query()->whereNot( 'visibility', 'archive' );
    $query->whereIn( 'id', explode( ',', $staff_ids ) );


    /** @var Staff[] $staff_members */
    $staff_members = $query->find();


    if ( ! empty ( $staff_members ) ) {
        // Load special days.
        $special_days = array();
        $staff_ids = array_map( function ( $staff ) { return $staff->getId(); }, $staff_members );
        $schedule  = Bookly\Lib\Proxy\SpecialDays::getSchedule( $staff_ids, $start_date, $end_date ) ?: array();
        foreach ( $schedule as $day ) {
            if ( $location_ids == '' || $location_ids == 'all' || in_array( $day['location_id'], $location_ids ) ) {
                $special_days[ $day['staff_id'] ][ $day['date'] ][] = $day;
            }
        }


        foreach ( $staff_members as $staff ) {

            $query = Bookly\Backend\Modules\Calendar\Ajax::getAppointmentsQueryForCalendar( $staff->getId(), $start_date, $end_date, $location_ids );
            $appointments = buildAppointmentsForCalendar( $query, $staff->getId(), $display_tz, $customer_id );
            $result = array_merge( $result, $appointments );

            // Schedule
            $schedule = array();
            $items = $staff->getScheduleItems();
            $day   = clone $start_date;
            // Find previous day end time.
            $last_end = clone $day;
            $last_end->sub( $one_day );
            $end_time = $items[ (int) $last_end->format( 'w' ) + 1 ]->getEndTime();
            if ( $end_time !== null ) {
                $end_time = explode( ':', $end_time );
                $last_end->setTime( $end_time[0], $end_time[1] );
            } else {
                $last_end->setTime( 24, 0 );
            }
            // Do the loop.
            while ( $day < $end_date ) {
                $start = $last_end->format( 'Y-m-d H:i:s' );
                // Check if $day is Special Day for current staff.
                if ( isset ( $special_days[ $staff->getId() ][ $day->format( 'Y-m-d' ) ] ) ) {
                    $sp_days = $special_days[ $staff->getId() ][ $day->format( 'Y-m-d' ) ];
                    $end     = $sp_days[0]['date'] . ' ' . $sp_days[0]['start_time'];
                    if ( $start < $end ) {
                        $schedule[] = compact( 'start', 'end' );
                    }
                    // Breaks.
                    foreach ( $sp_days as $sp_day ) {
                        if ( $sp_day['break_start'] ) {
                            $break_start = date(
                                'Y-m-d H:i:s',
                                strtotime( $sp_day['date'] ) + DateTime::timeToSeconds( $sp_day['break_start'] )
                            );
                            $break_end = date(
                                'Y-m-d H:i:s',
                                strtotime( $sp_day['date'] ) + DateTime::timeToSeconds( $sp_day['break_end'] )
                            );
                            $schedule[] = array(
                                'start' => $break_start,
                                'end' => $break_end,
                            );
                        }
                    }
                    $end_time = explode( ':', $sp_days[0]['end_time'] );
                    $last_end = clone $day;
                    $last_end->setTime( $end_time[0], $end_time[1] );
                } else {
                    $item = $items[ (int) $day->format( 'w' ) + 1 ];
                    if ( $item->getStartTime() && ! $staff->isOnHoliday( $day ) ) {
                        $end = $day->format( 'Y-m-d ' . $item->getStartTime() );
                        if ( $start < $end ) {
                            $schedule[] = compact( 'start', 'end' );
                        }
                        $last_end = clone $day;
                        $end_time = explode( ':', $item->getEndTime() );
                        $last_end->setTime( $end_time[0], $end_time[1] );

                        // Breaks.
                        foreach ( $item->getBreaksList() as $break ) {
                            $break_start = date(
                                'Y-m-d H:i:s',
                                $day->getTimestamp() + DateTime::timeToSeconds( $break['start_time'] )
                            );
                            $break_end = date(
                                'Y-m-d H:i:s',
                                $day->getTimestamp() + DateTime::timeToSeconds( $break['end_time'] )
                            );
                            $schedule[] = array(
                                'start' => $break_start,
                                'end'   => $break_end,
                            );
                        }
                    }
                }

                $day->add( $one_day );
            }

            if ( $last_end->format( 'Ymd' ) != $day->format( 'Ymd' ) ) {
                $schedule[] = array(
                    'start' => $last_end->format( 'Y-m-d H:i:s' ),
                    'end'   => $day->format( 'Y-m-d 24:00:00' ),
                );
            }

            // Add schedule to result,
            // with appropriate time zone shift if needed
            $staff_tz = $staff->getTimeZone();
            $convert_tz = $staff_tz && $staff_tz !== $display_tz;
            foreach ( $schedule as $item ) {
                if ( $convert_tz ) {
                    $item['start'] = DateTime::convertTimeZone( $item['start'], $staff_tz, $display_tz );
                    $item['end']   = DateTime::convertTimeZone( $item['end'], $staff_tz, $display_tz );
                }
                $result[] = array(
                    'start'      => $item['start'],
                    'end'        => $item['end'],
                    'display'    => 'background',
                    'resourceId' => $staff->getId(),
                );
            }
        }
    }


    wp_send_json( $result );
}

function buildAppointmentsForCalendar( Bookly\Lib\Query $query, $staff_id, $display_tz , $customer_id )
{



    $one_participant = '<div>' . str_replace( "\n", '</div><div>', get_option( 'bookly_cal_one_participant' ) ) . '</div>';
    $many_participants = '<div>' . str_replace( "\n", '</div><div>', get_option( 'bookly_cal_many_participants' ) ) . '</div>';
    $postfix_any = sprintf( ' (%s)', get_option( 'bookly_l10n_option_employee' ) );
    $participants = null;
    $coloring_mode = get_option( 'bookly_cal_coloring_mode' );
    $default_codes = array(
        'amount_due' => '',
        'amount_paid' => '',
        'appointment_date' => '',
        'appointment_notes' => '',
        'appointment_time' => '',
        'booking_number' => '',
        'category_name' => '',
        'client_address' => '',
        'client_email' => '',
        'client_name' => '',
        'client_first_name' => '',
        'client_last_name' => '',
        'client_phone' => '',
        'client_birthday' => '',
        'client_note' => '',
        'company_address' => get_option( 'bookly_co_address' ),
        'company_name' => get_option( 'bookly_co_name' ),
        'company_phone' => get_option( 'bookly_co_phone' ),
        'company_website' => get_option( 'bookly_co_website' ),
        'custom_fields' => '',
        'extras' => '',
        'extras_total_price' => 0,
        'internal_note' => '',
        'location_name' => '',
        'location_info' => '',
        'number_of_persons' => '',
        'on_waiting_list' => '',
        'payment_status' => '',
        'payment_type' => '',
        'service_capacity' => '',
        'service_duration' => '',
        'service_info' => '',
        'service_name' => '',
        'service_price' => '',
        'signed_up' => '',
        'staff_email' => '',
        'staff_info' => '',
        'staff_name' => '',
        'staff_phone' => '',
        'status' => '',
        'total_price' => '',
    );
    $query
        ->select( 'a.id, ca.id AS ca_id, ca.token, ca.series_id, a.staff_any, a.location_id, a.internal_note, a.start_date, DATE_ADD(a.end_date, INTERVAL IF(ca.extras_consider_duration, a.extras_duration, 0) SECOND) AS end_date,
                COALESCE(s.title,a.custom_service_name) AS service_name, COALESCE(s.color,"silver") AS service_color, s.info AS service_info,
                COALESCE(ss.price,s.price,a.custom_service_price) AS service_price,
                st.full_name AS staff_name, st.email AS staff_email, st.info AS staff_info, st.phone AS staff_phone, st.color AS staff_color,
                (SELECT SUM(ca.number_of_persons) FROM ' . CustomerAppointment::getTableName() . ' ca WHERE ca.appointment_id = a.id) AS total_number_of_persons,
                s.duration,
                s.start_time_info,
                s.end_time_info,
                ca.number_of_persons,
                ca.units,
                ca.custom_fields,
                ca.status AS status,
                ca.extras,
                ca.extras_multiply_nop,
                ca.package_id,
                ca.notes AS appointment_notes,
                ct.name AS category_name,
                c.full_name AS client_name, c.first_name AS client_first_name, c.last_name AS client_last_name, c.phone AS client_phone, c.email AS client_email, c.id AS customer_id, c.birthday AS client_birthday, c.notes AS client_note,
                p.total, p.type AS payment_gateway, p.status AS payment_status, p.paid,
                (SELECT SUM(ca.number_of_persons) FROM ' . CustomerAppointment::getTableName() . ' ca WHERE ca.appointment_id = a.id AND ca.status = "waitlisted") AS on_waiting_list' )
        ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
        ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
        ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
        ->leftJoin( 'Service', 's', 's.id = a.service_id' )
        ->leftJoin( 'Category', 'ct', 'ct.id = s.category_id' )
        ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' );

    if ( Bookly\Lib\Proxy\Locations::servicesPerLocationAllowed() ) {
        $query = Bookly\Proxy\Locations::prepareCalendarQuery( $query );
    } else {
        $query->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id AND ss.location_id IS NULL' );
    }

    if ( Config::groupBookingActive() ) {
        $query->addSelect( 'COALESCE(ss.capacity_max,s.capacity_max,9999) AS service_capacity' );
    } else {
        $query->addSelect( '1 AS service_capacity' );
    }

    if ( Config::proActive() ) {
        $query->addSelect( 'c.country, c.state, c.postcode, c.city, c.street, c.street_number, c.additional_address' );
    }

    // check if customer has parent role
    $wp_user_id = getBooklyWpUserId($customer_id);
    if( $wp_user_id !== false ):
        $user_is_parent = user_has_role($wp_user_id, 'parent');
        if( $user_is_parent == true ):
            // get his children
            $learners = getParentActiveChilds($wp_user_id);
            $learners[] = $wp_user_id;
        endif;
    else:
        $user_is_parent = false;
    endif;

    // Fetch appointments,
    // and shift the dates to appropriate time zone if needed
    $appointments = array();
    $customer_appointments = array();
    $wp_tz = Config::getWPTimeZone();
    $convert_tz = $display_tz !== $wp_tz;


    foreach ( $query->fetchArray() as $appointment ) {

        // convert start and end dates with user timezone
        if ( ! isset ( $appointments[ $appointment['id'] ] ) ) {
            if ( $convert_tz ) {
                $appointment['start_date'] = DateTime::convertTimeZone( $appointment['start_date'], $wp_tz, $display_tz );
                $appointment['end_date'] = DateTime::convertTimeZone( $appointment['end_date'], $wp_tz, $display_tz );
            }
            $appointments[ $appointment['id'] ] = $appointment;
        }

        $appointments[ $appointment['id'] ]['customers'][] = array(
            'appointment_notes' => $appointment['appointment_notes'],
            'client_birthday' => $appointment['client_birthday'],
            'client_email' => $appointment['client_email'],
            'client_first_name' => $appointment['client_first_name'],
            'client_last_name' => $appointment['client_last_name'],
            'client_name' => $appointment['client_name'],
            'client_note' => $appointment['client_note'],
            'client_phone' => $appointment['client_phone'],
            'number_of_persons' => $appointment['number_of_persons'],
            'payment_status' => Bookly\Lib\Entities\Payment::statusToString( $appointment['payment_status'] ),
            'payment_type' => Bookly\Lib\Entities\Payment::typeToString( $appointment['payment_gateway'] ),
            'status' => $appointment['status'],
        );


        if( !empty($customer_id) ):
            if( (int) $appointment['customer_id'] == (int) $customer_id ):
                $customer_appointments[] = $appointment;
            endif;
        endif;


        if( !empty($learners) ):
            foreach ( $learners as $child_id ):
                if( (int) $appointment['customer_id'] == (int) getcustomerID($child_id) ):
                    $customer_appointments[] = $appointment;
                endif;
            endforeach;
        endif;

    }




    $status_codes = array(
        CustomerAppointment::STATUS_APPROVED => 'success',
        CustomerAppointment::STATUS_CANCELLED => 'danger',
        CustomerAppointment::STATUS_REJECTED => 'danger',
    );
    $cancelled_statuses = array(
        CustomerAppointment::STATUS_CANCELLED,
        CustomerAppointment::STATUS_REJECTED,
    );
    $pending_statuses = array(
        CustomerAppointment::STATUS_CANCELLED,
        CustomerAppointment::STATUS_REJECTED,
        CustomerAppointment::STATUS_PENDING,
    );
    $colors = array();
    if ( $coloring_mode == 'status' ) {
        $colors = Bookly\Lib\Proxy\Shared::prepareColorsStatuses( array(
            CustomerAppointment::STATUS_PENDING => get_option( 'bookly_appointment_status_pending_color' ),
            CustomerAppointment::STATUS_APPROVED => get_option( 'bookly_appointment_status_approved_color' ),
            CustomerAppointment::STATUS_CANCELLED => get_option( 'bookly_appointment_status_cancelled_color' ),
            CustomerAppointment::STATUS_REJECTED => get_option( 'bookly_appointment_status_rejected_color' ),
        ) );
        $colors['mixed'] = get_option( 'bookly_appointment_status_mixed_color' );
    }


    if( !empty( $customer_appointments ) ):
        $appointments = $customer_appointments;
    endif;



    foreach ( $appointments as $key => $appointment ) {


        $codes = $default_codes;
        $codes['appointment_date'] = DateTime::formatDate( $appointment['start_date'] );
        $codes['appointment_time'] = $appointment['duration'] >= DAY_IN_SECONDS && $appointment['start_time_info'] ? $appointment['start_time_info'] : Bookly\Lib\Utils\DateTime::formatTime( $appointment['start_date'] );
        $codes['appointment_end_time'] =  Bookly\Lib\Utils\DateTime::formatTime( $appointment['end_date'] );
        $codes['booking_number'] = $appointment['id'];
        $codes['internal_note'] = esc_html( $appointment['internal_note'] );
        $codes['on_waiting_list'] = $appointment['on_waiting_list'];
        $codes['service_name'] = $appointment['service_name'] ? esc_html( $appointment['service_name'] ) : __( 'Untitled', 'bookly' );
        $codes['service_price'] = Price::format( $appointment['service_price'] * $appointment['units'] );
        $codes['service_duration'] = DateTime::secondsToInterval( $appointment['duration'] * $appointment['units'] );
        $codes['signed_up'] = $appointment['total_number_of_persons'];
        foreach ( array( 'staff_name', 'staff_phone', 'staff_info', 'staff_email', 'service_info', 'service_capacity', 'category_name' ) as $field ) {
            $codes[ $field ] = esc_html( $appointment[ $field ] );
        }
        if ( $appointment['staff_any'] ) {
            $codes['staff_name'] .= $postfix_any;
        }


        // get stored bb group
        $stored_bb_group_custom_field = json_decode($appointment['custom_fields']);
        //get bookly custom fields data
        $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
        foreach ( $stored_bb_group_custom_field as $field_data ):
            $custom_field_id = (int) $field_data->id;
            if( $custom_field_id === $bb_custom_field_id ):
                $stored_bb_group_id = $field_data->value;
            endif;

            // get makeup flag value
            if( $custom_field_id === 32859 ):
                $stored_makeup_flag = $field_data->value;
            else:
                $stored_makeup_flag = false;
            endif;

        endforeach;

        if( empty($stored_bb_group_id) ):
            $stored_bb_group_id = null;
        else:
            // get bb group data
            $group_obj = groups_get_group ( $stored_bb_group_id );
            $bb_group_permalink = bp_get_group_permalink( $group_obj );
        endif;

        if( !empty($customer_id) ):

            // get timeznoe from single GF entry
            $sp_entry_id = getBBgroupGFentryID ($stored_bb_group_id);
            $user_timezone = getSPentryTimezone($sp_entry_id);
            // if user is not staff => convert to GF timezone
            if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() <= 0 ):
                $appointment['start_date'] = DateTime::convertTimeZone( $appointment['start_date'], $wp_tz, $user_timezone );
                $appointment['end_date'] = DateTime::convertTimeZone( $appointment['end_date'], $wp_tz, $user_timezone );
            endif;

        endif;

        // start time and end time after tmiezone convertion, in H:i AM/PM format
        $startTimeToDisplay = date('h:i a', strtotime($appointment['start_date']));
        $endTimeToDisplay = date('h:i a', strtotime($appointment['end_date']));


        $first_customer_id = $appointment['customer_id'];
        $wp_user_id = getWPuserIDfromBookly($first_customer_id);
        $customer_full_name = getCustomerFullName($wp_user_id);


        // Customers for popover.
        $popover_customers = '';
        $overall_status = isset( $appointment['customers'][0] ) ? $appointment['customers'][0]['status'] : '';

        $codes['participants'] = array();
        $event_status = null;



        foreach ( $appointment['customers'] as $customer ) {

            $status_color = 'secondary';
            if ( isset( $status_codes[ $customer['status'] ] ) ) {
                $status_color = $status_codes[ $customer['status'] ];
            }
            if ( $coloring_mode == 'status' ) {
                if ( $event_status === null ) {
                    $event_status = $customer['status'];
                } elseif ( $event_status != $customer['status'] ) {
                    $event_status = 'mixed';
                }
            }
            if ( $customer['status'] != $overall_status && ( ! in_array( $customer['status'], $cancelled_statuses ) || ! in_array( $overall_status, $cancelled_statuses ) ) ) {
                if ( in_array( $customer['status'], $pending_statuses ) && in_array( $overall_status, $pending_statuses ) ) {
                    $overall_status = CustomerAppointment::STATUS_PENDING;
                } else {
                    $overall_status = '';
                }
            }
            if ( $customer['number_of_persons'] > 1 ) {
                $number_of_persons = '<span class="badge badge-info mr-1"><i class="far fa-fw fa-user"></i>×' . $customer['number_of_persons'] . '</span>';
            } else {
                $number_of_persons = '';
            }

            // check if user is bookly staff
            if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ):
                $popover_customers .= '<div class="d-flex"><div class="text-muted flex-fill">' . $customer['client_name'] . '</div><div class="text-nowrap">' . $number_of_persons . '<span class="badge badge-' . $status_color . '">' . CustomerAppointment::statusToString( $customer['status'] ) . '</span></div></div>';
            else:
                $popover_customers .= '';
            endif;
            $codes['participants'][] = $customer;
        }


        // Display customer information only if there is 1 customer. Don't confuse with number_of_persons.
        if ( $appointment['number_of_persons'] == $appointment['total_number_of_persons'] ) {
            $participants = 'one';
            $template = $one_participant;
            foreach ( array( 'client_name', 'client_first_name', 'client_last_name', 'client_phone', 'client_email', 'client_birthday' ) as $data_entry ) {
                $codes[ $data_entry ] = esc_html( $appointment['customers'][0][ $data_entry ] );
            }
            $codes['number_of_persons'] = $appointment['number_of_persons'];
            $codes['appointment_notes'] = $appointment['appointment_notes'];
            // Payment.
            if ( $appointment['total'] ) {
                $codes['total_price'] = Price::format( $appointment['total'] );
                $codes['amount_paid'] = Price::format( $appointment['paid'] );
                $codes['amount_due'] = Price::format( $appointment['total'] - $appointment['paid'] );
                $codes['total_price'] = Price::format( $appointment['total'] );
                $codes['payment_type'] = Bookly\Lib\Entities\Payment::typeToString( $appointment['payment_gateway'] );
                $codes['payment_status'] = Bookly\Lib\Entities\Payment::statusToString( $appointment['payment_status'] );
            }
            // Status.
            $codes['status'] = CustomerAppointment::statusToString( $appointment['status'] );
        } else {
            $participants = 'many';
            $template = $many_participants;
        }


        $add_staff_dots = ( strlen($codes['staff_name']) > 20 ) ? ' ... ' : '';
        $add_customer_dots = ( strlen($customer_full_name) > 20 ) ? ' ... ' : '';
        $tooltip = '<i class="fas fa-fw fa-circle mr-1" style="color:%s"></i><span>{service_name}</span>' . $popover_customers . '<span class="d-block text-muted">' . $startTimeToDisplay . ' - ' . $endTimeToDisplay . '</span>';
        $tooltip .= '<span> <i class="fas fa-user-tie"></i> &nbsp;  '. substr($codes['staff_name'], 0, 20) . $add_staff_dots .  ' </span><br>';
        $tooltip .= '<span> <i class="fas fa-users"></i> &nbsp '. substr($customer_full_name, 0, 20) . $add_customer_dots . ' </span><br>';


        $tooltip = sprintf( $tooltip,
            $appointment['service_color'],
            ( $appointment['duration'] * $appointment['units'] >= DAY_IN_SECONDS && $appointment['start_time_info'] ? $appointment['end_time_info'] : DateTime::formatTime( $appointment['end_date'] ) )
        );

        $codes = Bookly\Backend\Modules\Calendar\Proxy\Shared::prepareAppointmentCodesData( $codes, $appointment, $participants );

        switch ( $coloring_mode ) {
            case 'status';
                $color = $colors[ $event_status ];
                break;
            case 'staff':
                $color = $appointment['staff_color'];
                break;
            case 'service':
            default:
                $color = $colors[ $event_status ];
        }

        // get bb group type if ( mvs, family-group or open ) set coloring mode to bookly service color, default event_status
        $bb_group_type = getBBgroupType($stored_bb_group_id);
        if( !empty( $bb_group_type ) && $bb_group_type !== 'one-to-one' ):
            $color = $appointment['service_color'];
        endif;



        //show event title
        if( $appointment['units'] > 1 ):
            $event_title = '<p class="event-time"> '. $startTimeToDisplay . ' - ' . $endTimeToDisplay .' </p>';
            $event_title .= '<p class="event-teacher">  '. substr($codes['staff_name'], 0, 15) .' .. </p>';
            $event_title .= '<p class="event-learner">  '. substr($customer_full_name, 0, 15) .' .. </p>';
        else:
            $event_title = '<p class="event-time"> '. $startTimeToDisplay . ' - ' . $endTimeToDisplay .' </p>';
            $event_title .= '<p class="event-teacher">  '. substr($codes['staff_name'], 0, 15) .' ..  </p>';
        endif;


        // in parent calendar => show cancel button if nowUTC < start_date
        // parent cannot cancel makeup session ( add this cond in ajax calendar show_cancel_btn if makeup custom_field != true)
        $show_cancel_btn = false;
        if( $user_is_parent == true ):
            // get now in user timezone
            $now_date_timeUTC = gmdate("Y-m-d H:i:s");
            $now_date_time = convertTimezone1ToTimezone2 ( $now_date_timeUTC, 'UTC', $user_timezone );
            if( strtotime($now_date_time) < strtotime($appointment['start_date']) && $appointment['status'] !== 'cancelled' && $stored_makeup_flag !== 'True' ):
                $show_cancel_btn = true;
            endif;
        endif;

        // if makeup flag == 'True' => add
        $showMakeupFlagBtn = false;
        if( $stored_makeup_flag == 'True' ):
            $showMakeupFlagBtn = true;
            $event_title .= '<span class="makeup-session"> M </span>';
        endif;

        if( $appointment['status'] == 'pending' && Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ):
            $event_title .= '<span class="makeup-session pending"> <i class="fas fa-exclamation-triangle"></i> </span>';
        endif;


        $parent_wp_user_id = get_current_user_id();


        /// remove in prod
//        global $wpdb;
//        $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
//        $customer_appointments_results = $wpdb->get_results(
//            " SELECT * FROM `zrsap_bookly_customer_appointments` WHERE `appointment_id` = {$appointment['id']} "
//        );
//        $wpdb->flush();
//
//        $customers_array = array_column($customer_appointments_results, 'customer_id');

        $appointments[ $key ] = array(
            'id' => $appointment['id'],
            'start' => $appointment['start_date'],
            'end' => $appointment['end_date'],
            'title' => '',
            'color' => $color,
            'resourceId' => $staff_id,
            'extendedProps' => array(
                'tooltip' => Bookly\Lib\Utils\Codes::replace( $tooltip, $codes, false ),
                'desc' => Bookly\Lib\Utils\Codes::replace( $template, $codes, false ),
                'staffId' => $staff_id,
                'series_id' => (int) $appointment['series_id'],
                'package_id' => (int) $appointment['package_id'],
                'waitlisted' => (int) $appointment['on_waiting_list'],
                'staff_any' => (int) $appointment['staff_any'],
                'overall_status' => $overall_status,
                'bb_group_permalink' => $bb_group_permalink,
                'stored_bb_group_id' => $stored_bb_group_id,
                'event_title' => $event_title,
                'status' => $appointment['status'],
                'ca_id' => $appointment['ca_id'],
                'show_cancel_btn' => $show_cancel_btn,
                'showMakeupFlagBtn' => $showMakeupFlagBtn,
                'tk' => $appointment['token'],
                'pid' => $parent_wp_user_id
            ),
        );
        if ( $appointment['duration'] * $appointment['units'] >= DAY_IN_SECONDS && $appointment['start_time_info'] ) {
            $appointments[ $key ]['extendedProps']['header_text'] = sprintf( '%s - %s', $appointment['start_time_info'], $appointment['end_time_info'] );
        }
    }

    return array_values( $appointments );
}

// get academic appointments

function getAcademyAppointments($start, $end, $location_ids, $staff_ids, $bb_group_id)
{

    $result     = array();
    $one_day    = new \DateInterval( 'P1D' );
    $start_date = new \DateTime( $start );
    $end_date   = new \DateTime( $end );
    $location_ids = explode( ',', $location_ids );

    // Determine display time zone
    $display_tz = Common::getCurrentUserTimeZone();

    // Due to possibly different time zones of staff members expand start and end dates
    // to provide 100% coverage of the requested date range
    $start_date->sub( $one_day );
    $end_date->add( $one_day );

    // Load staff members
    $query = Staff::query()->whereNot( 'visibility', 'archive' );
    $query->whereIn( 'id', explode( ',', $staff_ids ) );
//    if ( Config::proActive() ) {
//        if ( Common::isCurrentUserSupervisor() ) {
//            $query->whereIn( 'id', explode( ',', $staff_ids ) );
//        } else {
//            $query->whereIn( 'id', explode( ',', $staff_ids ) );
//            //$query->where( 'wp_user_id', get_current_user_id() );
//        }
//    } else {
//        $query->limit( 1 );
//    }
    /** @var Staff[] $staff_members */
    $staff_members = $query->find();

    if ( ! empty ( $staff_members ) ) {
        // Load special days.
        $special_days = array();
        $staff_ids = array_map( function ( $staff ) { return $staff->getId(); }, $staff_members );
        $schedule  = Bookly\Lib\Proxy\SpecialDays::getSchedule( $staff_ids, $start_date, $end_date ) ?: array();
        foreach ( $schedule as $day ) {
            if ( $location_ids == '' || $location_ids == 'all' || in_array( $day['location_id'], $location_ids ) ) {
                $special_days[ $day['staff_id'] ][ $day['date'] ][] = $day;
            }
        }

        foreach ( $staff_members as $staff ) {

            $query = Bookly\Backend\Modules\Calendar\Ajax::getAppointmentsQueryForCalendar( $staff->getId(), $start_date, $end_date, $location_ids );
            $appointments = buildAcademyForCalendar( $query, $staff->getId(), $display_tz, $bb_group_id );
            $result = array_merge( $result, $appointments );

            // Schedule
            $schedule = array();
            $items = $staff->getScheduleItems();
            $day   = clone $start_date;
            // Find previous day end time.
            $last_end = clone $day;
            $last_end->sub( $one_day );
            $end_time = $items[ (int) $last_end->format( 'w' ) + 1 ]->getEndTime();
            if ( $end_time !== null ) {
                $end_time = explode( ':', $end_time );
                $last_end->setTime( $end_time[0], $end_time[1] );
            } else {
                $last_end->setTime( 24, 0 );
            }
            // Do the loop.
            while ( $day < $end_date ) {
                $start = $last_end->format( 'Y-m-d H:i:s' );
                // Check if $day is Special Day for current staff.
                if ( isset ( $special_days[ $staff->getId() ][ $day->format( 'Y-m-d' ) ] ) ) {
                    $sp_days = $special_days[ $staff->getId() ][ $day->format( 'Y-m-d' ) ];
                    $end     = $sp_days[0]['date'] . ' ' . $sp_days[0]['start_time'];
                    if ( $start < $end ) {
                        $schedule[] = compact( 'start', 'end' );
                    }
                    // Breaks.
                    foreach ( $sp_days as $sp_day ) {
                        if ( $sp_day['break_start'] ) {
                            $break_start = date(
                                'Y-m-d H:i:s',
                                strtotime( $sp_day['date'] ) + DateTime::timeToSeconds( $sp_day['break_start'] )
                            );
                            $break_end = date(
                                'Y-m-d H:i:s',
                                strtotime( $sp_day['date'] ) + DateTime::timeToSeconds( $sp_day['break_end'] )
                            );
                            $schedule[] = array(
                                'start' => $break_start,
                                'end' => $break_end,
                            );
                        }
                    }
                    $end_time = explode( ':', $sp_days[0]['end_time'] );
                    $last_end = clone $day;
                    $last_end->setTime( $end_time[0], $end_time[1] );
                } else {
                    $item = $items[ (int) $day->format( 'w' ) + 1 ];
                    if ( $item->getStartTime() && ! $staff->isOnHoliday( $day ) ) {
                        $end = $day->format( 'Y-m-d ' . $item->getStartTime() );
                        if ( $start < $end ) {
                            $schedule[] = compact( 'start', 'end' );
                        }
                        $last_end = clone $day;
                        $end_time = explode( ':', $item->getEndTime() );
                        $last_end->setTime( $end_time[0], $end_time[1] );

                        // Breaks.
                        foreach ( $item->getBreaksList() as $break ) {
                            $break_start = date(
                                'Y-m-d H:i:s',
                                $day->getTimestamp() + DateTime::timeToSeconds( $break['start_time'] )
                            );
                            $break_end = date(
                                'Y-m-d H:i:s',
                                $day->getTimestamp() + DateTime::timeToSeconds( $break['end_time'] )
                            );
                            $schedule[] = array(
                                'start' => $break_start,
                                'end'   => $break_end,
                            );
                        }
                    }
                }

                $day->add( $one_day );
            }

            if ( $last_end->format( 'Ymd' ) != $day->format( 'Ymd' ) ) {
                $schedule[] = array(
                    'start' => $last_end->format( 'Y-m-d H:i:s' ),
                    'end'   => $day->format( 'Y-m-d 24:00:00' ),
                );
            }

            // Add schedule to result,
            // with appropriate time zone shift if needed
            $staff_tz = $staff->getTimeZone();
            $convert_tz = $staff_tz && $staff_tz !== $display_tz;
            foreach ( $schedule as $item ) {
                if ( $convert_tz ) {
                    $item['start'] = DateTime::convertTimeZone( $item['start'], $staff_tz, $display_tz );
                    $item['end']   = DateTime::convertTimeZone( $item['end'], $staff_tz, $display_tz );
                }
                $result[] = array(
                    'start'      => $item['start'],
                    'end'        => $item['end'],
                    'display'    => 'background',
                    'resourceId' => $staff->getId(),
                );
            }
        }
    }

    wp_send_json( $result );
}

function buildAcademyForCalendar( Bookly\Lib\Query $query, $staff_id, $display_tz , $bb_group_id )
{
    if( !empty($bb_group_id) ):
        //get bookly custom fields data
        $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
        $custom_fields = json_encode(
            array(
                array(
                    'id' => $bb_custom_field_id,
                    'value' => strval($bb_group_id)
                )
            )
        );
    endif;

    $one_participant = '<div>' . str_replace( "\n", '</div><div>', get_option( 'bookly_cal_one_participant' ) ) . '</div>';
    $many_participants = '<div>' . str_replace( "\n", '</div><div>', get_option( 'bookly_cal_many_participants' ) ) . '</div>';
    $postfix_any = sprintf( ' (%s)', get_option( 'bookly_l10n_option_employee' ) );
    $participants = null;
    $coloring_mode = get_option( 'bookly_cal_coloring_mode' );
    $default_codes = array(
        'amount_due' => '',
        'amount_paid' => '',
        'appointment_date' => '',
        'appointment_notes' => '',
        'appointment_time' => '',
        'booking_number' => '',
        'category_name' => '',
        'client_address' => '',
        'client_email' => '',
        'client_name' => '',
        'client_first_name' => '',
        'client_last_name' => '',
        'client_phone' => '',
        'client_birthday' => '',
        'client_note' => '',
        'company_address' => get_option( 'bookly_co_address' ),
        'company_name' => get_option( 'bookly_co_name' ),
        'company_phone' => get_option( 'bookly_co_phone' ),
        'company_website' => get_option( 'bookly_co_website' ),
        'custom_fields' => '',
        'extras' => '',
        'extras_total_price' => 0,
        'internal_note' => '',
        'location_name' => '',
        'location_info' => '',
        'number_of_persons' => '',
        'on_waiting_list' => '',
        'payment_status' => '',
        'payment_type' => '',
        'service_capacity' => '',
        'service_duration' => '',
        'service_info' => '',
        'service_name' => '',
        'service_price' => '',
        'signed_up' => '',
        'staff_email' => '',
        'staff_info' => '',
        'staff_name' => '',
        'staff_phone' => '',
        'status' => '',
        'total_price' => '',
    );
    $query
        ->select( 'a.id, ca.series_id, a.staff_any, a.location_id, a.internal_note, a.start_date, DATE_ADD(a.end_date, INTERVAL IF(ca.extras_consider_duration, a.extras_duration, 0) SECOND) AS end_date,
                COALESCE(s.title,a.custom_service_name) AS service_name, COALESCE(s.color,"silver") AS service_color, s.info AS service_info,
                COALESCE(ss.price,s.price,a.custom_service_price) AS service_price,
                st.full_name AS staff_name, st.email AS staff_email, st.info AS staff_info, st.phone AS staff_phone, st.color AS staff_color,
                (SELECT SUM(ca.number_of_persons) FROM ' . CustomerAppointment::getTableName() . ' ca WHERE ca.appointment_id = a.id) AS total_number_of_persons,
                s.duration,
                s.start_time_info,
                s.end_time_info,
                ca.number_of_persons,
                ca.units,
                ca.custom_fields,
                ca.status AS status,
                ca.extras,
                ca.extras_multiply_nop,
                ca.package_id,
                ca.notes AS appointment_notes,
                ct.name AS category_name,
                c.full_name AS client_name, c.first_name AS client_first_name, c.last_name AS client_last_name, c.phone AS client_phone, c.email AS client_email, c.id AS customer_id, c.birthday AS client_birthday, c.notes AS client_note,
                p.total, p.type AS payment_gateway, p.status AS payment_status, p.paid,
                (SELECT SUM(ca.number_of_persons) FROM ' . CustomerAppointment::getTableName() . ' ca WHERE ca.appointment_id = a.id AND ca.status = "waitlisted") AS on_waiting_list' )
        ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
        ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
        ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
        ->leftJoin( 'Service', 's', 's.id = a.service_id' )
        ->leftJoin( 'Category', 'ct', 'ct.id = s.category_id' )
        ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' );

    if ( Bookly\Lib\Proxy\Locations::servicesPerLocationAllowed() ) {
        $query = Bookly\Proxy\Locations::prepareCalendarQuery( $query );
    } else {
        $query->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id AND ss.location_id IS NULL' );
    }

    if ( Config::groupBookingActive() ) {
        $query->addSelect( 'COALESCE(ss.capacity_max,s.capacity_max,9999) AS service_capacity' );
    } else {
        //$query->addSelect( '1 AS service_capacity' );
    }

    if ( Config::proActive() ) {
        $query->addSelect( 'c.country, c.state, c.postcode, c.city, c.street, c.street_number, c.additional_address' );
    }

    // Fetch appointments,
    // and shift the dates to appropriate time zone if needed
    $appointments = array();
    $customer_appointments = array();
    $wp_tz = Config::getWPTimeZone();
    $convert_tz = $display_tz !== $wp_tz;

    foreach ( $query->fetchArray() as $appointment ) {

        if ( ! isset ( $appointments[ $appointment['id'] ] ) ) {
            if ( $convert_tz ) {
                $appointment['start_date'] = DateTime::convertTimeZone( $appointment['start_date'], $wp_tz, $display_tz );
                $appointment['end_date'] = DateTime::convertTimeZone( $appointment['end_date'], $wp_tz, $display_tz );
            }
            $appointments[ $appointment['id'] ] = $appointment;
        }

        $appointments[ $appointment['id'] ]['customers'][] = array(
            'appointment_notes' => $appointment['appointment_notes'],
            'client_birthday' => $appointment['client_birthday'],
            'client_email' => $appointment['client_email'],
            'client_first_name' => $appointment['client_first_name'],
            'client_last_name' => $appointment['client_last_name'],
            'client_name' => $appointment['client_name'],
            'client_note' => $appointment['client_note'],
            'client_phone' => $appointment['client_phone'],
            'number_of_persons' => $appointment['number_of_persons'],
            'payment_status' => Bookly\Lib\Entities\Payment::statusToString( $appointment['payment_status'] ),
            'payment_type' => Bookly\Lib\Entities\Payment::typeToString( $appointment['payment_gateway'] ),
            'status' => $appointment['status'],
            'custom_fields' => $appointment['custom_fields']
        );


    }




    $status_codes = array(
        CustomerAppointment::STATUS_APPROVED => 'success',
        CustomerAppointment::STATUS_CANCELLED => 'danger',
        CustomerAppointment::STATUS_REJECTED => 'danger',
    );
    $cancelled_statuses = array(
        CustomerAppointment::STATUS_CANCELLED,
        CustomerAppointment::STATUS_REJECTED,
    );
    $pending_statuses = array(
        CustomerAppointment::STATUS_CANCELLED,
        CustomerAppointment::STATUS_REJECTED,
        CustomerAppointment::STATUS_PENDING,
    );
    $colors = array();
    if ( $coloring_mode == 'status' ) {
        $colors = Bookly\Lib\Proxy\Shared::prepareColorsStatuses( array(
            CustomerAppointment::STATUS_PENDING => get_option( 'bookly_appointment_status_pending_color' ),
            CustomerAppointment::STATUS_APPROVED => get_option( 'bookly_appointment_status_approved_color' ),
            CustomerAppointment::STATUS_CANCELLED => get_option( 'bookly_appointment_status_cancelled_color' ),
            CustomerAppointment::STATUS_REJECTED => get_option( 'bookly_appointment_status_rejected_color' ),
        ) );
        $colors['mixed'] = get_option( 'bookly_appointment_status_mixed_color' );
    }





    foreach ( $appointments as $key => $appointment ) {


        $codes = $default_codes;
        $codes['appointment_date'] = DateTime::formatDate( $appointment['start_date'] );
        $codes['appointment_time'] = $appointment['duration'] >= DAY_IN_SECONDS && $appointment['start_time_info'] ? $appointment['start_time_info'] : Bookly\Lib\Utils\DateTime::formatTime( $appointment['start_date'] );
        $codes['appointment_end_time'] =  Bookly\Lib\Utils\DateTime::formatTime( $appointment['end_date'] );
        $codes['booking_number'] = $appointment['id'];
        $codes['internal_note'] = esc_html( $appointment['internal_note'] );
        $codes['on_waiting_list'] = $appointment['on_waiting_list'];
        $codes['service_name'] = $appointment['service_name'] ? esc_html( $appointment['service_name'] ) : __( 'Untitled', 'bookly' );
        $codes['service_price'] = Price::format( $appointment['service_price'] * $appointment['units'] );
        $codes['service_duration'] = DateTime::secondsToInterval( $appointment['duration'] * $appointment['units'] );
        $codes['signed_up'] = $appointment['total_number_of_persons'];
        foreach ( array( 'staff_name', 'staff_phone', 'staff_info', 'staff_email', 'service_info', 'service_capacity', 'category_name' ) as $field ) {
            $codes[ $field ] = esc_html( $appointment[ $field ] );
        }
        if ( $appointment['staff_any'] ) {
            $codes['staff_name'] .= $postfix_any;
        }

        // get stored bb group
        $stored_bb_group_custom_field = json_decode($appointment['custom_fields']);
        //get bookly custom fields data
        $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
        foreach ( $stored_bb_group_custom_field as $field_data ):
            $custom_field_id = (int) $field_data->id;
            if( $custom_field_id === $bb_custom_field_id ):
                $stored_bb_group_id = $field_data->value;
            endif;
        endforeach;

        if( empty($stored_bb_group_id) ):
            $stored_bb_group_id = null;
        else:
            // get bb group data
            $group_obj = groups_get_group ( $stored_bb_group_id );
            $bb_group_permalink = bp_get_group_permalink( $group_obj );
        endif;

        $first_customer_id = $appointment['customer_id'];
        $wp_user_id = getWPuserIDfromBookly($first_customer_id);
        $customer_full_name = getCustomerFullName($wp_user_id);

        // Customers for popover.
        $popover_customers = '';
        $overall_status = isset( $appointment['customers'][0] ) ? $appointment['customers'][0]['status'] : '';

        $codes['participants'] = array();
        $event_status = null;
        foreach ( $appointment['customers'] as $customer ) {
            $status_color = 'secondary';
            if ( isset( $status_codes[ $customer['status'] ] ) ) {
                $status_color = $status_codes[ $customer['status'] ];
            }
            if ( $coloring_mode == 'status' ) {
                if ( $event_status === null ) {
                    $event_status = $customer['status'];
                } elseif ( $event_status != $customer['status'] ) {
                    $event_status = 'mixed';
                }
            }
            if ( $customer['status'] != $overall_status && ( ! in_array( $customer['status'], $cancelled_statuses ) || ! in_array( $overall_status, $cancelled_statuses ) ) ) {
                if ( in_array( $customer['status'], $pending_statuses ) && in_array( $overall_status, $pending_statuses ) ) {
                    $overall_status = CustomerAppointment::STATUS_PENDING;
                } else {
                    $overall_status = '';
                }
            }
            if ( $customer['number_of_persons'] > 1 ) {
                $number_of_persons = '<span class="badge badge-info mr-1"><i class="far fa-fw fa-user"></i>×' . $customer['number_of_persons'] . '</span>';
            } else {
                $number_of_persons = '';
            }

            // check if user is bookly staff
            if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ):
                $popover_customers .= '<div class="d-flex"><div class="text-muted flex-fill">' . $customer['client_name'] . '</div><div class="text-nowrap">' . $number_of_persons . '<span class="badge badge-' . $status_color . '">' . CustomerAppointment::statusToString( $customer['status'] ) . '</span></div></div>';
            else:
                $popover_customers .= '';
            endif;
            $codes['participants'][] = $customer;
        }

        // Display customer information only if there is 1 customer. Don't confuse with number_of_persons.
        if ( $appointment['number_of_persons'] == $appointment['total_number_of_persons'] ) {
            $participants = 'one';
            $template = $one_participant;
            foreach ( array( 'client_name', 'client_first_name', 'client_last_name', 'client_phone', 'client_email', 'client_birthday' ) as $data_entry ) {
                $codes[ $data_entry ] = esc_html( $appointment['customers'][0][ $data_entry ] );
            }
            $codes['number_of_persons'] = $appointment['number_of_persons'];
            $codes['appointment_notes'] = $appointment['appointment_notes'];
            // Payment.
            if ( $appointment['total'] ) {
                $codes['total_price'] = Price::format( $appointment['total'] );
                $codes['amount_paid'] = Price::format( $appointment['paid'] );
                $codes['amount_due'] = Price::format( $appointment['total'] - $appointment['paid'] );
                $codes['total_price'] = Price::format( $appointment['total'] );
                $codes['payment_type'] = Bookly\Lib\Entities\Payment::typeToString( $appointment['payment_gateway'] );
                $codes['payment_status'] = Bookly\Lib\Entities\Payment::statusToString( $appointment['payment_status'] );
            }
            // Status.
            $codes['status'] = CustomerAppointment::statusToString( $appointment['status'] );
        } else {
            $participants = 'many';
            $template = $many_participants;
        }

        $add_staff_dots = ( strlen($codes['staff_name']) > 20 ) ? ' ... ' : '';
        $add_customer_dots = ( strlen($customer_full_name) > 20 ) ? ' ... ' : '';
        $tooltip = '<i class="fas fa-fw fa-circle mr-1" style="color:%s"></i><span>{service_name}</span>' . $popover_customers . '<span class="d-block text-muted">{appointment_time} - %s</span>';
        $tooltip .= '<span> <i class="fas fa-user-tie"></i> &nbsp;  '. substr($codes['staff_name'], 0, 20) . $add_staff_dots . ' </span><br>';
        $tooltip .= '<span> <i class="fas fa-users"></i> &nbsp '. substr($customer_full_name, 0, 20) . $add_customer_dots . ' </span><br>';


        $tooltip = sprintf( $tooltip,
            $appointment['service_color'],
            ( $appointment['duration'] * $appointment['units'] >= DAY_IN_SECONDS && $appointment['start_time_info'] ? $appointment['end_time_info'] : DateTime::formatTime( $appointment['end_date'] ) )
        );

        $codes = Bookly\Backend\Modules\Calendar\Proxy\Shared::prepareAppointmentCodesData( $codes, $appointment, $participants );

        switch ( $coloring_mode ) {
            case 'status';
                $color = $colors[ $event_status ];
                break;
            case 'staff':
                $color = $appointment['staff_color'];
                break;
            case 'service':
            default:
                $color = $colors[ $event_status ];
        }

        // get bb group type if ( mvs, family-group or open ) set coloring mode to bookly service color, default event_status
        $bb_group_type = getBBgroupType($stored_bb_group_id);
        if( !empty( $bb_group_type ) && $bb_group_type !== 'one-to-one' ):
            $color = $appointment['service_color'];
        endif;

        // filter calendar events based on BB group id
        if( !empty($bb_group_id) ):
            $stored_bb_group_custom_field = json_decode($appointment['custom_fields']);
            foreach ( $stored_bb_group_custom_field as $field_data ):
                $custom_field_id = (int) $field_data->id;
                if( $custom_field_id === $bb_custom_field_id ):
                    $stored_bb_group_id = $field_data->value;
                endif;
            endforeach;
        endif;

        if( (int) $stored_bb_group_id === (int) $bb_group_id ):
            $show_status = 'show';
            $current_bb_group_appointment[$key] = 1;
            //show event title
            if( $appointment['units'] > 1 ):
                $event_title = '<p class="event-time"> '. $codes['appointment_time'] . ' - ' . $codes['appointment_end_time'] .' </p>';
                $event_title .= '<p class="event-teacher">  '. substr($codes['staff_name'], 0, 15) .' .. </p>';
                $event_title .= '<p class="event-learner">  '. substr($customer_full_name, 0, 15) .' .. </p>';
            else:
                $event_title = '<p class="event-time"> '. $codes['appointment_time'] . ' - ' . $codes['appointment_end_time'] .' </p>';
                $event_title .= '<p class="event-teacher">  '. substr($codes['staff_name'], 0, 13) .'  </p>';
            endif;
        else:
            // hide event
            $show_status = 'hide';
            $current_bb_group_appointment[$key] = 0;
            $event_title = '';
        endif;







        $appointments[ $key ] = array(
            'id' => $appointment['id'],
            'start' => $appointment['start_date'],
            'end' => $appointment['end_date'],
            'title' => ' ',
            'color' => $color,
            'resourceId' => $staff_id,
            'extendedProps' => array(
                'tooltip' => Bookly\Lib\Utils\Codes::replace( $tooltip, $codes, false ),
                'desc' => Bookly\Lib\Utils\Codes::replace( $template, $codes, false ),
                'staffId' => $staff_id,
                'series_id' => (int) $appointment['series_id'],
                'package_id' => (int) $appointment['package_id'],
                'waitlisted' => (int) $appointment['on_waiting_list'],
                'staff_any' => (int) $appointment['staff_any'],
                'overall_status' => $overall_status,
                'hide' => $show_status,
                'bb_group_permalink' => $bb_group_permalink,
                'event_title' =>   $event_title,

            )
        );



        if ( $appointment['duration'] * $appointment['units'] >= DAY_IN_SECONDS && $appointment['start_time_info'] ) {
            $appointments[ $key ]['extendedProps']['header_text'] = sprintf( '%s - %s', $appointment['start_time_info'], $appointment['end_time_info'] );
        }


    }

    return array_values( $appointments );
}

add_action('wp_ajax_muslimeto_get_staff_appointments', 'muslimeto_get_staff_appointments');
add_action( 'wp_ajax_nopriv_muslimeto_get_staff_appointments', 'muslimeto_get_staff_appointments' );
function muslimeto_get_staff_appointments(){


    $staff_ids = $_POST['staff_ids'];
    $service_ids = $_POST['service_ids'];
    $service_ids = $_POST['service_ids'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $location_ids = $_POST['location_ids'];
    $customer_id = !empty($_POST['customer_id']) ? (int) $_POST['customer_id'] : 0;
    $bb_group_id = $_POST['bb_group_id'];
    $display_teacher_timezone = $_POST['display_teacher_timezone'];


    if( !empty($bb_group_id) && $bb_group_id !== 'undefined' ):
        return getAcademyAppointments($start, $end, $location_ids, $staff_ids, $bb_group_id);
    else:
        // for each staff id get appointment data in this period
        return getStaffAppointments($start, $end, $location_ids, $staff_ids, $customer_id, $display_teacher_timezone);
    endif;


    wp_die();

}