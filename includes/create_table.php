<?php

	if ( ! defined( 'ABSPATH' ) ) 
	{
	    exit;
	}

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$table_name = $wpdb->prefix . "aspl_feedback";
	
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	    $sql = "CREATE TABLE $table_name (
	            feedback_id mediumint(9) NOT NULL AUTO_INCREMENT,
	            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	            order_id varchar(5) NOT NULL,
	            product_id varchar(5) NOT NULL,
	            PRIMARY KEY  (feedback_id)
	            ) $charset_collate;";

	    dbDelta($sql);
	} 

	
	$feedback_temp_table = $wpdb->prefix . "aspl_feedback_template";
	if ($wpdb->get_var("SHOW TABLES LIKE '$feedback_temp_table'") != $feedback_temp_table) {
	    $sql = "CREATE TABLE $feedback_temp_table (
	            temp_id mediumint(9) NOT NULL AUTO_INCREMENT,
	            name varchar(50) NOT NULL,
	            description text NOT NULL,
	            website_config varchar(50) NOT NULL,
	            temp_type varchar(100) NOT NULL,
	            product_id varchar(10) NOT NULL,
	            PRIMARY KEY  (temp_id)
	            ) $charset_collate;";

	    dbDelta($sql);
	} 

	$feedback_que_table = $wpdb->prefix . "aspl_feedback_question";
	if ($wpdb->get_var("SHOW TABLES LIKE '$feedback_que_table'") != $feedback_que_table) {
	    $sql = "CREATE TABLE $feedback_que_table (
	            question_id mediumint(9) NOT NULL AUTO_INCREMENT,
	            temp_id varchar(50) NOT NULL,
	            question text NOT NULL,
	            que_type varchar(100) NOT NULL,
	            ans_mode varchar(100) NOT NULL,
	            PRIMARY KEY  (question_id)
	            ) $charset_collate;";

	    dbDelta($sql);
	} 


	$feedback_ans_table = $wpdb->prefix . "aspl_feedback_answer";
	if ($wpdb->get_var("SHOW TABLES LIKE '$feedback_ans_table'") != $feedback_ans_table){
	    $sql = "CREATE TABLE $feedback_ans_table (
	            answer_id mediumint(9) NOT NULL AUTO_INCREMENT,
	            que_id varchar(50) NOT NULL,
	            answer text NOT NULL,
	            PRIMARY KEY  (answer_id)
	            ) $charset_collate;";

	    dbDelta($sql);
	}

	$feedback_customer_main = $wpdb->prefix . "aspl_customer_feedback_main";
	if ($wpdb->get_var("SHOW TABLES LIKE '$feedback_customer_main'") != $feedback_customer_main){
	    $sql = "CREATE TABLE $feedback_customer_main (
	            cf_id mediumint(9) NOT NULL AUTO_INCREMENT,
	           customer_id varchar(50) NOT NULL,
	            date_time text NOT NULL,
	            template_id varchar(10) NOT NULL,
	            PRIMARY KEY  (cf_id)
	            ) $charset_collate;";

	    dbDelta($sql);
	} 


	$fb_cus_que_ans = $wpdb->prefix . "aspl_cust_fb_que_ans";
	if ($wpdb->get_var("SHOW TABLES LIKE '$fb_cus_que_ans'") != $fb_cus_que_ans){
	    $sql = "CREATE TABLE $fb_cus_que_ans (
	            cf_qa_id mediumint(9) NOT NULL AUTO_INCREMENT,
	           cf_id mediumint(9) NOT NULL,
	           	que_id mediumint(9) NOT NULL,
	            ans_id varchar(100) NOT NULL,
	            PRIMARY KEY  (cf_qa_id)
	            ) $charset_collate;";

	    dbDelta($sql);
	}

	$fb_mail_notification = $wpdb->prefix . "aspl_mail_notification";
	if ($wpdb->get_var("SHOW TABLES LIKE '$fb_mail_notification'") != $fb_cus_que_ans){
	    $sql = "CREATE TABLE $fb_mail_notification (
	            notification_id mediumint(9) NOT NULL AUTO_INCREMENT,
	           order_id mediumint(9) NOT NULL,
	           	product_id mediumint(9) NOT NULL,
	            count mediumint(9) NOT NULL,
	            response varchar(10) NOT NULL,
	            mail_date varchar(10) NOT NULL,
	            PRIMARY KEY  (notification_id)
	            ) $charset_collate;";

	    dbDelta($sql);
	}