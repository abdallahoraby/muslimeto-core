<?php

// register test scripts
use Bookly\Lib\Entities\Appointment;
use Bookly\Lib\Entities\Customer;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Entities\Service;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Utils\DateTime;


/**
 * Check whether interval is available for given appointment.
 *
 * @param $start_date
 * @param $end_date
 * @param $staff_id
 * @param $appointment_id
 * @return bool
 */
function dateIntervalIsAvailableForAppointment( $start_date, $end_date, $staff_id, $appointment_id )
{
    return Appointment::query( 'a' )
            ->whereNot( 'a.id', $appointment_id )
            ->where( 'a.staff_id', $staff_id )
            ->whereLt( 'a.start_date', $end_date )
            ->whereGt( 'a.end_date', $start_date )
            ->count() == 0;
}


/**
 * Check whether appointment settings produce errors.
 */
function checkAppointmentErrors($start_date, $end_date, $staff_id, $service_id, $location_id, $appointment_id, $customers)
{
    //$start_date     = self::parameter( 'start_date' );
    //$end_date       = self::parameter( 'end_date' );
    //$staff_id       = (int) self::parameter( 'staff_id' );
    //$service_id     = (int) self::parameter( 'service_id' );
    $location_id    = Bookly\Lib\Proxy\Locations::prepareStaffScheduleLocationId( $location_id, $staff_id ) ?: null;
    //$appointment_id = (int) self::parameter( 'appointment_id' );
    $appointment_duration = strtotime( $end_date ) - strtotime( $start_date );
    if( !empty($customers) ):
        $customers      = json_decode( $customers, true );
    endif;
    $service        = Service::find( $service_id );
    $service_duration = $service ? $service->getDuration() : 0;

    $result = array(
        'staff_appointment_overlap_status'      => false,
        'date_interval_warning'            => false,
        'interval_not_in_staff_schedule'   => false,
        'interval_not_in_service_schedule' => false,
        'staff_reaches_working_time_limit' => false,
        'customers_appointments_limit'     => array(),
    );

    $max_extras_duration = 0;
    foreach ( $customers as $customer ) {
        if ( in_array( $customer['status'], Lib\Proxy\CustomStatuses::prepareBusyStatuses( array(
            CustomerAppointment::STATUS_PENDING,
            CustomerAppointment::STATUS_APPROVED
        ) ) ) ) {
            if ( $customer['extras_consider_duration'] ) {
                $extras_duration = Lib\Proxy\ServiceExtras::getTotalDuration( $customer['extras'] );
                if ( $extras_duration > $max_extras_duration ) {
                    $max_extras_duration = $extras_duration;
                }
            }
        }
    }

    if ( $start_date && $end_date ) {
        // Determine display time zone,
        // and shift the dates to WP time zone if needed
        $display_tz = Common::getCurrentUserTimeZone();
        $wp_tz = Bookly\Lib\Config::getWPTimeZone();
        if ( $display_tz !== $wp_tz ) {
            $start_date = DateTime::convertTimeZone( $start_date, $display_tz, $wp_tz );
            $end_date   = DateTime::convertTimeZone( $end_date, $display_tz, $wp_tz );
        }



        // Dates in staff time zone
        $staff_start_date = $start_date;
        $staff_end_date = $end_date;

        $total_end_date = $end_date;
        if ( $max_extras_duration > 0 ) {
            $total_end_date = date_create( $end_date )->modify( '+' . $max_extras_duration . ' sec' )->format( 'Y-m-d H:i:s' );
        }
        if ( ! dateIntervalIsAvailableForAppointment( $start_date, $total_end_date, $staff_id, $appointment_id ) ) {
            $result['staff_appointment_overlap_status'] = true;
        }

        // Check if selected interval fits into staff schedule
        if ( $staff_id ) {
            $interval_valid = true;
            $staff = Staff::find( $staff_id );

            // Shift dates to staff time zone if needed
            $staff_tz = $staff->getTimeZone();
//            if ( $staff_tz ) {
//                // convert start and end from UTC to teacher timezone
//                $staff_start_date = convertTimezone1ToTimezone2( $start_date, 'UTC', $staff_tz );
//                $staff_end_date = convertTimezone1ToTimezone2( $end_date, 'UTC', $staff_tz );
//            }



            $staff_start_date = convertTimezone1ToTimezone2( $start_date, 'UTC', $staff_tz );
            $staff_end_date = convertTimezone1ToTimezone2( $end_date, 'UTC', $staff_tz );

            //echo '<br>UTC staff start: ' . $staff_start_date . ' - staff end date: ' . $staff_end_date . '<br>';
            //echo '<br>CAI user start: ' . $staff_start_date . ' -  user end date: ' . $staff_end_date . '<br>';


            // Check if interval is suitable for staff's hours limit
            $result['staff_reaches_working_time_limit'] = (bool) Bookly\Lib\Proxy\Pro::getWorkingTimeLimitError(
                $staff,
                $staff_start_date,
                $staff_end_date,
                $appointment_duration + $max_extras_duration,
                $appointment_id
            );

            $start = date_create( $staff_start_date );
            $end = date_create( $staff_end_date );
            $schedule_items = $staff->getScheduleItems( $location_id );
            $special_days = array();
            $schedule = Bookly\Lib\Proxy\SpecialDays::getSchedule( array( $staff_id ), $start, $end ) ?: array();
            foreach ( $schedule  as $day ) {
                if ( $location_id == $day['location_id'] ) {
                    $special_days[ $day['date'] ][] = $day;
                }
            }



            // Check staff schedule for holidays and days off
            $date = clone $start;
            while ( $date < $end ) {
                if (
                    ! isset ( $special_days[ $date->format( 'Y-m-d' ) ] ) && (
                        $staff->isOnHoliday( $date ) ||
                        ! $schedule_items[ $date->format( 'w' ) + 1 ]->getStartTime()
                    )
                ) {
                    $interval_valid = false;
                    break;
                }
                $date->modify( '+1 day' );
            }

            if ( $interval_valid && $service_duration < DAY_IN_SECONDS ) {
                // For services with duration not in days check staff working hours
                $interval_valid = false;
                // Check start and previous day to get night schedule
                $date = clone $start;
                $date->modify( '-1 day' );
                while ( $date <= $start ) {
                    $Ymd = $date->format( 'Y-m-d' );
                    $Ymd_secs = strtotime( $Ymd );
                    if ( isset ( $special_days[ $Ymd ] ) ) {
                        // Special day
                        $day_start = $Ymd . ' ' . $special_days[ $Ymd ][0]['start_time'];
                        $day_end = date( 'Y-m-d H:i:s', $Ymd_secs + DateTime::timeToSeconds( $special_days[ $Ymd ][0]['end_time'] ) );
                        if ( $day_start <= $staff_start_date && $day_end >= $staff_end_date ) {
                            // Check if interval does not intersect with breaks
                            $intersects = false;
                            foreach ( $special_days[ $Ymd ] as $break ) {
                                if ( $break['break_start'] ) {
                                    $break_start = date(
                                        'Y-m-d H:i:s',
                                        $Ymd_secs + DateTime::timeToSeconds( $break['break_start'] )
                                    );
                                    $break_end = date(
                                        'Y-m-d H:i:s',
                                        $Ymd_secs + DateTime::timeToSeconds( $break['break_end'] )
                                    );

                                    if ( $break_start < $staff_end_date && $break_end > $staff_start_date ) {
                                        $intersects = true;
                                        break;
                                    }
                                }
                            }
                            if ( ! $intersects ) {
                                $interval_valid = true;
                                break;
                            }
                        }
                    } else {
                        // Regular schedule
                        $item = $schedule_items[ $date->format( 'w' ) + 1 ];
                        if ( $item->getStartTime() ) {
                            $day_start = $Ymd . ' ' . $item->getStartTime();
                            $day_end = date( 'Y-m-d H:i:s', $Ymd_secs + DateTime::timeToSeconds( $item->getEndTime() ) );
                            if ( $day_start <= $staff_start_date && $day_end >= $staff_end_date ) {
                                // Check if interval does not intersect with breaks
                                $intersects = false;
                                foreach ( $item->getBreaksList() as $break ) {
                                    $break_start = date(
                                        'Y-m-d H:i:s',
                                        $Ymd_secs + DateTime::timeToSeconds( $break['start_time'] )
                                    );
                                    $break_end = date(
                                        'Y-m-d H:i:s',
                                        $Ymd_secs + DateTime::timeToSeconds( $break['end_time'] )
                                    );

                                    if ( $break_start < $staff_end_date && $break_end > $staff_start_date ) {
                                        $intersects = true;
                                        break;
                                    }
                                }
                                if ( ! $intersects ) {
                                    $interval_valid = true;
                                    break;
                                }
                            }
                        }
                    }
                    $date->modify( '+1 day' );
                }
            }

            if ( ! $interval_valid ) {
                $result['interval_not_in_staff_schedule'] = true;
            }
        }

        if ( $service ) {
            $result = Bookly\Backend\Components\Dialogs\Appointment\Edit\Proxy\ServiceSchedule::checkAppointmentErrors( $result, $staff_start_date, $staff_end_date, $service_id, $service_duration );

            // Service duration interval is not equal to.
            $result['date_interval_warning'] = ! ( $appointment_duration >= $service->getMinDuration() && $appointment_duration <= $service->getMaxDuration() && ( $service_duration == 0 || $appointment_duration % $service_duration == 0 ) );

            // Check customers for appointments limit
            foreach ( $customers as $index => $customer ) {
                if ( $service->appointmentsLimitReached( $customer['id'], array( $start_date ) ) ) {
                    $customer_error = Customer::find( $customer['id'] );
                    $result['customers_appointments_limit'][] = sprintf( __( '%s has reached the limit of bookings for this service', 'bookly' ), $customer_error->getFullName() );
                }
            }

            $result['customers_appointments_limit'] = array_unique( $result['customers_appointments_limit'] );
        }
    }

    return $result ;
}