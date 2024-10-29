<?php

if ( ! defined( 'ABSPATH' ) ) 
{
    exit;
}


 $temp_id = sanitize_text_field($_GET['temp_id']);

global $wpdb, $woocommerce;
$feedback_temp_table = $wpdb->prefix . "aspl_feedback_template";

$delete_template = $wpdb->get_results("DELETE from $feedback_temp_table where temp_id = '$temp_id'");

wp_redirect('?page=asplfs_feedback_page');