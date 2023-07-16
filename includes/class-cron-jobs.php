<?php
/*
// cron action to update user billing indicators and update parent_stats table

add_filter( 'cron_schedules', 'muslimeto_updateUserBillingIndicator_30_sec' );
function muslimeto_updateUserBillingIndicator_30_sec( $schedules ) {
    $schedules['every_30_sec'] = array(
        'interval'  => 1800,
        'display'   => __( 'Every 30 Sec', 'muslimeto' )
    );
    return $schedules;
}

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'muslimeto_updateUserBillingIndicator_30_sec' ) ) {
    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $next_run = $current_date_object->format('Y-m-d 23:00:00');
    $next_runtime = strtotime($next_run);
    wp_schedule_event( time(), 'every_30_sec', 'muslimeto_updateUserBillingIndicator_30_sec' );
}

add_action( 'muslimeto_updateUserBillingIndicator_30_sec', 'update_user_billing_indicator_callback' );
function update_user_billing_indicator_callbackOLD() {
    set_time_limit(30);
    global $wpdb;

    $parents = getAllParents();
    $parent_ids = array_column($parents, 'id');

    // check if option next parent id has value
    $cron_next_user_id = get_option('cron_update_billing_last_userid');

    if( !empty($cron_next_user_id) ):
        $last_parent_record = $cron_next_user_id;
        $last_parent_record_index = array_search($last_parent_record, $parent_ids);
        $next_parent_id = $parent_ids[$last_parent_record_index+1];

        // check if last index of parents ids
        if( count($parent_ids)-1 === $last_parent_record_index ):
            $next_parent_id = $parent_ids[0];
        endif;

    else:
        $next_parent_id = $parent_ids[0];
    endif;

    // update option with next parent id
    update_option( 'cron_update_billing_last_userid', $next_parent_id );

    //update option with next parent id
    $updateUser = updateUserBillingIndicator($next_parent_id, '');
    if( $updateUser  !== true ):
        // insert into cron log
        $cron_log_table = $wpdb->prefix . 'muslimeto_activity_log';
        $cron_log_data = array(
            array(
                'event_title' => 'cron_muslimeto_updateUserBillingIndicator_daily',
                'event_desc' => 'user_id: ' . $next_parent_id . ' - error: ' .$updateUser
            ),
        );

        wpdb_bulk_insert($cron_log_table, $cron_log_data);

    endif;

}

function update_user_billing_indicator_callback() {

    $url = site_url();
    $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(),
            'body'        => array(
                'start_sync' => 'start_sync',
            ),
            'cookies'     => array()
        )
    );


}*/