<?php

if ( ! defined( 'ABSPATH' ) ) 
{
    exit;
}


 $que_id = sanitize_text_field($_GET['question_id']);
 $temp_id = sanitize_text_field($_GET['temp_id']);

global $wpdb, $woocommerce;
$feedback_que_table = $wpdb->prefix . "aspl_feedback_question";

$feedback_ans_table = $wpdb->prefix . "aspl_feedback_answer";

$delete_que = $wpdb->get_results("DELETE from $feedback_que_table where question_id = '$que_id'");

$delete_ans = $wpdb->get_results("DELETE from $feedback_ans_table where que_id = '$que_id'");

wp_redirect('?page=template-configuration&action=edit&temp_id='.$temp_id);