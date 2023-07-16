<?php

/**
 * Fired during plugin activation
 *
 * @link       https://muslimeto.com/
 * @since      1.0.0
 *
 * @package    Muslimeto_Core
 * @subpackage Muslimeto_Core/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Muslimeto_Core
 * @subpackage Muslimeto_Core/includes
 * @author     Muslimeto <info@muslimeto.com>
 */
class Muslimeto_Core_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


        // generate custom tables for makeup balance
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();


        $table_name = $wpdb->prefix . 'muslimeto_makeup_log';

        $makeup_table = "CREATE TABLE $table_name (
                  id int(11) NOT NULL AUTO_INCREMENT,
                  trans_amount INT NOT NULL,
                  makeup_balance INT NOT NULL,
                  ca_id INT NOT NULL,
                  aid INT NULL,
                  cid INT NULL,
                  parent_id INT NULL,
                  trans_type varchar(100) DEFAULT '' NULL,
                  trans_notes TEXT NULL,
                  user_role varchar(100) DEFAULT '' NULL,
                  user_id int(11) NULL,
                  updated_at varchar(100) DEFAULT '' NULL,
                  created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                  PRIMARY KEY  (id),
                  UNIQUE KEY (`ca_id`)
                ) $charset_collate;";

        dbDelta( $makeup_table );

        // generate custom tables for parent stats
        $table_name = $wpdb->prefix . 'muslimeto_parent_stats';

        $parent_stats_table = "CREATE TABLE $table_name (
                  id int(11) NOT NULL AUTO_INCREMENT,
                  parent_wp_user_id INT NOT NULL,
                  keap_user_id INT NOT NULL,
                  cids TEXT DEFAULT '' NULL,
                  active_childs TEXT DEFAULT '' NULL,
                  due_balance varchar(255) DEFAULT '' NULL,
                  renew_on varchar(255) DEFAULT '' NULL,
                  last_payment varchar(255) DEFAULT '' NULL,
                  total_hours varchar(255) DEFAULT '' NULL,
                  paid_amount varchar(255) DEFAULT '' NULL,
                  paid_hours varchar(255) DEFAULT '' NULL,
                  cancelled_amount varchar(255) DEFAULT '' NULL,
                  support_tickets varchar(255) DEFAULT '' NULL,
                  happiness_rate varchar(255) DEFAULT '' NULL, 
                  assigned_to varchar(255) DEFAULT '' NULL,
                  has_opening_balance BOOLEAN NOT NULL, 
                  update_status varchar(100) DEFAULT '' NULL, 
                  updated_at varchar(100) DEFAULT '' NULL,
                  created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                  PRIMARY KEY  (id),
                  UNIQUE KEY (`parent_wp_user_id`)
                ) $charset_collate;";


        dbDelta( $parent_stats_table );

        // generate custom tables for error log
        $table_name = $wpdb->prefix . 'muslimeto_error_log';

        $error_log_table = "CREATE TABLE $table_name (
                  id int(11) NOT NULL AUTO_INCREMENT,
                  event_title varchar(255) DEFAULT '' NULL,
                  event_desc TEXT DEFAULT '' NULL,
                  user_id INT NOT NULL,
                  created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                  PRIMARY KEY  (id)
                ) $charset_collate;";
        
        dbDelta( $error_log_table );

	}

}
