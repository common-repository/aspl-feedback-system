<?php

if ( ! defined( 'ABSPATH' ) ) 
{
    exit;
}

add_action( 'init', 'asplfs_create_feedback_page' );


function asplfs_create_feedback_page()
{
	$pages = get_pages(); 
	$contact_page= array(	'slug' => 'aspl_feedback',	'title' =>'Feedback');

	$check_page_exist = get_page_by_title('Feedback', 'OBJECT', 'page');
	   
	if(empty($check_page_exist))
	{

		$page_id = wp_insert_post(array(
            'post_title' => $contact_page['title'],
            'post_type' =>'page',   
            'post_name' => $contact_page['slug'],
            'post_status' => 'publish',
            'post_excerpt' => ' ',  
          ));
        
	}
}


// Filter page template
add_filter('page_template', 'asplfs_feedback_template');


function asplfs_feedback_template($template) {
    
    global $post;
    $post_slug = $post->post_name;
    if( is_page('aspl_feedback') ){
    	$template = dirname( __FILE__ )  . '/feedback-page-template.php';
    }

    return $template;
}