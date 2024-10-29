<?php

if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}

if (isset($_POST['temp-delete'])) {

	$temp_ids = array_map( 'sanitize_text_field',$_POST['temp-delete']);

	global $wpdb, $woocommerce;
	$feedback_temp_table = $wpdb->prefix . "aspl_feedback_template";
	$feedback_que_table = $wpdb->prefix . "aspl_feedback_question";

	foreach ($temp_ids as $key => $value)
	{
		$delete_template = $wpdb->get_results("DELETE from $feedback_temp_table where temp_id = '$value'");

		$delete_question = $wpdb->get_results("DELETE from $feedback_que_table where temp_id = '$value'");
	}

	wp_redirect('?page=asplfs_feedback_page');

}else{

	$temp_ids = array_map( 'sanitize_text_field',$_POST['temp-delete-customer']);
	global $wpdb, $woocommerce;
	$feedback_temp_table = $wpdb->prefix . "aspl_customer_feedback_main";
	$wp_aspl_cust_fb_que_ans = $wpdb->prefix .'aspl_cust_fb_que_ans';

	foreach ($temp_ids as $key => $value){
		$delete_template = $wpdb->get_results("DELETE from $feedback_temp_table where cf_id = '$value'");
		$wpdb->get_results("DELETE from $wp_aspl_cust_fb_que_ans where cf_id = '$value'");		
	}	
	
	wp_redirect('?page=customer-feedback');

}

