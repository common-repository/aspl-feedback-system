<?php
/**
 * Plugin Name: ASPL Feedback System
 * Plugin URI: https://acespritech.com/
 * Description: Feedback system for increase customer communication.
 * Version: 1.1.0
 * Author: Acespritech Solutions Pvt. Ltd.
 * Author URI: https://acespritech.com/
 * Text Domain: aspl
 * Domain Path: /i18n/languages/
 *
 */
//

if ( ! defined( 'ABSPATH' ) ) 
{
    exit;
}


include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) 
{
  	include_once dirname( __FILE__ ) . '/includes/main.php';

  	include_once dirname( __FILE__ ) . '/includes/create_table.php';

  	include_once dirname( __FILE__ ) . '/includes/create-feedback-page.php';

}
else{ 
    deactivate_plugins(plugin_basename(__FILE__));
    add_action( 'admin_notices', 'aspl_fs_woocommerce_not_installed' );
}

function aspl_fs_woocommerce_not_installed()
{
    ?>
    <div class="error notice">
      <p><?php _e( 'You need to install and activate WooCommerce to use WooCommerce Advance Report!', 'WooCommerce-Advance-Report' ); ?></p>
    </div>
    <?php
}


add_action('admin_enqueue_scripts', 'aspl_fs_admin_style');

function aspl_fs_admin_style()
{    
    wp_enqueue_style('aspl_fs_admin_style', plugins_url('assest/css/style.css', __FILE__));

    wp_enqueue_script('aspl_fs_admin_script', plugins_url('assest/js/feedback.js', __FILE__), array('jquery'));


}

add_action('wp_enqueue_scripts', 'aspl_fs_front_script', PHP_INT_MAX);
function aspl_fs_front_script(){

	$style = 'bootstrap';	
	if( ( ! wp_style_is( $style, 'queue' ) ) && ( ! wp_style_is( $style, 'done' ) ) ) {
	    //queue up your bootstrap

	   wp_enqueue_style('aspl_fs_front_style', plugins_url('assest/css/bootstrap.min.css', __FILE__));

    	wp_enqueue_script('aspl_fs_front_script1', plugins_url('assest/js/bootstrap.min.js', __FILE__), array('jquery'));
	}

	wp_enqueue_script('aspl_fs_custom_script', plugins_url('assest/js/feedback-script.js', __FILE__), array('jquery'));

	wp_enqueue_style('aspl_fs_front_style1', plugins_url('assest/css/front-style.css', __FILE__));

}



// SETUP CRON
add_action('wp', 'asplfs_schedule_cron');
function asplfs_schedule_cron() {
  	if ( !wp_next_scheduled( 'asplfs_cron' ) )
    wp_schedule_event(time(), 'daily', 'asplfs_cron');
}

// the CRON hook for firing function
add_action('asplfs_cron', 'asplfs_cron_function', 10, 2);
#add_action('wp_head', 'asplfs_cron_function'); //test on page load

// the actual function
function asplfs_cron_function( $arg1, $arg2 ) {
    

	$send_mail_after = esc_attr(get_option('send_mail_after', ''));
	if($send_mail_after == ''){
		$send_mail_after = '10';
	}

	$date_before = '-5 days';
	
	 $order_query = array(
            'pos_type' => 'shop_order',
            'numberposts'   => -1,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'date_query' => array(
	            //'after' => date('Y-m-d', strtotime('-10 days')),
	            'before' => date('Y-m-d', strtotime($date_before)) 
	        )
        );

     $loop = new WP_Query($order_query);
       
     if($loop->have_posts()){

        while ($loop->have_posts())
        {
            
            $loop->the_post();
            $order_id = $loop->post->ID;
            _e('Order ID '.$order_id.'------------');                        
            $order = wc_get_order($order_id);
          	
            $order_data = $order->get_data(); 
            $order_created = $order->get_date_created();
            $order_date = date("d-m-Y", strtotime($order_created));
            $order_items = $order->get_items();
            $customer = new WC_Customer( $order_id );
            $customer_mail = $order->get_billing_email();

            foreach ($order_items as $item_id => $item_data)
            {
            	if($item_data['variation_id'] != ''){
                    $order_prd_id = $item_data['variation_id'];
                }
                else{
                    $order_prd_id = $item_data['product_id'];
                }
                $message = get_site_url().'/aspl_feedback?product_id='.$order_prd_id.'&order_id='.$order_id;
               	 

				$fb_temp_main = $wpdb->prefix . "aspl_feedback_template";

				$temp_id = $wpdb->get_var( $wpdb->prepare( "SELECT temp_id FROM $fb_temp_main WHERE product_id ='%s' LIMIT 1", $order_prd_id ) );
				
				if($temp_id){
					

					$fb_mail_notification = $wpdb->prefix . "aspl_mail_notification"; 
					$check_record = "SELECT * FROM $fb_mail_notification WHERE product_id = '$order_prd_id' and order_id = '$order_id'";

					$check_record_result = $wpdb->get_results($check_record);
					
					$record_ount = count($check_record_result);

					$timestamp = time()+date("Z");
					$current_date = gmdate("d-m-Y",$timestamp);

					$first_mail_time = '+'.$send_mail_after.' days';						
					$first_mail_date = date('d-m-Y', strtotime($order_date. $first_mail_time));
					

					_e('Current date -----'.$current_date);
					_e(' Order date-----------'.$order_date);
					_e('first_mail ------'.$first_mail_date);

					if($record_ount == 0)
					{							
					    $insert = $wpdb->insert($fb_mail_notification, array(
				                        'product_id' => $order_prd_id,
				                        'order_id' => $order_id,
				                        'count' => '0',
				                        'response' => '0',
				                        'mail_date' => $first_mail_date,
				                    ));
					}
					else{

						foreach ($check_record_result as $key => $value) {
							//var_dump($value);
							$response = $value->response;
							$count = $value->count;
							$send_mail_count = esc_attr(get_option('send_mail_count', ''));
							$send_mail_interval = esc_attr(get_option('send_mail_interval', ''));
							

							if($response == '' || $response == '0'){

								if($count < $send_mail_count)
								{

									if($count == 0)
									{
										_e('first mail');
										if($first_mail_date == $current_date){
											_e('first mail send today');

											wp_mail($customer_mail,'Feedback', $message);

											$update_answer = $wpdb->get_results("UPDATE $fb_mail_notification set count = '1', mail_date = '$current_date' WHERE product_id = '$order_prd_id' and order_id = '$order_id' ");	
										}										

									}
									else{										
										
										$diff = strtotime($current_date) - strtotime($order_date);
										$day_of_order = abs(round($diff / 86400));  
										
										$test = intval($count) * intval($send_mail_interval);
										
										$testa = intval($send_mail_after) + intval($test);

										$other_mail_time = '+'.$testa.' days';
										$next_mail_date = date('d-m-Y', strtotime($order_date. $other_mail_time));
										_e('Next mail date -------'.$next_mail_date);
										if($next_mail_date == $current_date){

											_e('mail send today');
											$count = $count + 1;

											wp_mail($customer_mail,'Feedback', $message);

											$update_answer = $wpdb->get_results("UPDATE $fb_mail_notification set count = '1', mail_date = $current_date WHERE product_id = '$order_prd_id' and order_id = '$order_id' ");	
										}

									}
									
								}
							}
						}

					}	
					

				}

            }
        }
    }


}



add_action('admin_menu', 'asplfs_menu');
function asplfs_menu()
{	
    $hook = add_menu_page('Feedback', 'Feedback', 'manage_options', 'asplfs_feedback_page', 'asplfs_menu_page_fun' , 'dashicons-thumbs-up',  61);

    add_submenu_page('asplfs_feedback_page','Feedback Add Page', 'Add New', 'manage_options', 'template-configuration', 'asplfs_feedback_from',62 );

    add_submenu_page('asplfs_feedback_page','Feedback Add Page', 'Update Question', 'manage_options', 'update-question', 'asplfs_que_update_page',62 );

    add_submenu_page('asplfs_feedback_page','Feedback Setting', 'Seeting', 'manage_options', 'feedback_setting', 'asplfs_setting_page',62 );

    global $cus_fed_hook;
    $cus_fed_hook = add_submenu_page('asplfs_feedback_page','Customer Feedback', 'Customer Feedback Report', 'manage_options', 'customer-feedback', 'asplfs_customer_feedback',62 );

    add_action( "load-$hook", 'aspl_fs_options');

    add_action( "load-$cus_fed_hook", 'aspl_fs_cus_fed_hook');
}

function aspl_fs_options() {
	global $feedbacklisttable;
	$option = 'per_page';
	$args = array(
	         'label' => 'Feedback',
	         'default' => 10,
	         'option' => 'feedback_per_page'
	         );
	add_screen_option( $option, $args );  
}

function aspl_fs_cus_fed_hook() {
	global $CusFBList;
	 global $cus_fed_hook;
	$option = 'per_page';
	$args = array(
	         'label' => 'Customer Feedback',
	         'default' => 10,
	         'option' => 'cus_fb_per_page'
	         );
	add_screen_option( $option, $args );  
}

function asplfs_set_screen_option($status, $option, $value) {


	if ( 'cus_fb_per_page' == $option ) return $value;
}
add_filter('set-screen-option', 'asplfs_set_screen_option', 10, 3);

function asplfs_menu_page_fun(){

	global $wpdb, $woocommerce;
	include_once dirname( __FILE__ ) . '/includes/admin-feedback-page.php';

	$feedbacklisttable = new aspl_fs_Feedback_List();
	?>

	<h1 class="wp-heading-inline">Feedback <span class="dashicons-before dashicons-thumbs-up"></span>
		<a href="?page=template-configuration" class="page-title-action">Add New</a>
	</h1>
	
	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<form method="post">
						<?php
						$feedbacklisttable->prepare_items();
						$feedbacklisttable->display(); ?>
					</form>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
	<?php
   
}

function asplfs_customer_feedback(){

	if(isset($_GET['action']) && $_GET['action'] == 'view'){

		$cf_id = sanitize_text_field($_GET['cf_id']);
		global $wpdb, $woocommerce;
		$fb_cus_fb_main = $wpdb->prefix . "aspl_customer_feedback_main";
		$cus_sql = "SELECT * FROM $fb_cus_fb_main where cf_id = '$cf_id'";
		$customer_fb_data = $wpdb->get_results($cus_sql);
		foreach ($customer_fb_data as $key => $customer)
		{
			$temp_id = $customer->template_id;
			$temp_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}aspl_feedback_template WHERE temp_id ='%s' LIMIT 1", $temp_id ) );
			$customer_id = $customer->customer_id;
			$user_meta = get_userdata($customer_id);
			$user_name = $user_meta->display_name;
        	$date_time = $customer->date_time;
		}
		?>
	<div class="wrap ">
		<h2></h2>
	</div>
	<div class="wrap add_new_template_page">
		<h1>Customer Feedback <span class="dashicons-before dashicons-thumbs-up"></span></h1>
		<form action="" method="post">
		 	<table class="form-table" >
		 		<tbody>
		            <tr>
		                <th>Customer Name</th>
		                <td><?php _e($user_name); ?></td>
		            </tr>
		            <tr>
		                <th>Date</th>
		                <td><?php _e($date_time); ?></td>
		            </tr>
		             <tr>
		                <th>Template Name</th>
		                <td><?php _e($temp_name); ?></td>
		            </tr>
		        </tbody>
		    </table>
		</form>
		<table class="question wp-list-table widefat fixed customer-feedback-data">
			<thead>
				<th>Question</th>
				<th>Answer</th>
			</thead>
			<tbody>

		<?php

		$fb_cus_que_ans = $wpdb->prefix . "aspl_cust_fb_que_ans";
		$qa_sql = "SELECT * FROM $fb_cus_que_ans where cf_id = '$cf_id'";
		$feedback_data = $wpdb->get_results($qa_sql);
		//die();
		foreach ($feedback_data as $key => $value)
		{
			$que_id = $value->que_id;

			$question = $wpdb->get_var( $wpdb->prepare( "SELECT question FROM {$wpdb->prefix}aspl_feedback_question WHERE question_id ='%s' LIMIT 1", $que_id ) );
			$que_type = $wpdb->get_var( $wpdb->prepare( "SELECT que_type FROM {$wpdb->prefix}aspl_feedback_question WHERE question_id ='%s' LIMIT 1", $que_id ) );			
			$ans_id = $value->ans_id;
			if (is_serialized($ans_id)) {
				$ans_id = unserialize($ans_id);
			}

			$answer = '';
			//die();
			if($que_type == 'Widget'){
				
				for($i=1; $i <= $ans_id; $i++){

					$answer = $answer.'<span class="dashicons dashicons-star-filled"></span>';
					?>
					
					<?php
				}
			}
			else{
				
				if(is_array($ans_id)){
					foreach ($ans_id as $ans_key => $aid) {
						$current_answer = $wpdb->get_var( $wpdb->prepare( "SELECT answer FROM {$wpdb->prefix}aspl_feedback_answer WHERE answer_id ='%s' LIMIT 1", $aid ) );
						if($answer == ''){
							$answer = $current_answer;
						}else{
							$answer = $answer.'</br>'.$current_answer;
						}

					}
				}
				else{
					
					$answer = $wpdb->get_var( $wpdb->prepare( "SELECT answer FROM {$wpdb->prefix}aspl_feedback_answer WHERE answer_id ='%s' LIMIT 1", $ans_id ) );
				}
			}
			
			
			
			?>
			<tr>
				<td><?php _e($question); ?></td>
				<td><?php _e($answer); ?></td>
			</tr>
			<?php
		}
		?>
			</tbody>
		</table>
	</div>
		<?php
	}
	else{

		include_once dirname( __FILE__ ) . '/includes/customer-feedback-report-table.php';
		$CusFBList = new aspl_fs_Customer_feedback_list();
		?>

		<h1>Customer Feedback <span class="dashicons-before dashicons-thumbs-up"></span></h1>	
		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<form method="post">
							<?php
							$CusFBList->prepare_items();
							$CusFBList->display(); ?>
						</form>
					</div>
				</div>
			</div>
			<br class="clear">
		</div>
	<?php 
	}  
}

function asplfs_feedback_from(){

	if(isset($_GET['action'])){

		$action = sanitize_text_field($_GET['action']);

		if($action=='edit'){
	  	include('includes/update-feedback-data.php');	  	
		}
		elseif($action=='delete'){
			include('includes/delete-feedback-data.php');
		}
	}
	else{
		include('includes/add-new-feedback.php');
	}
	
}


add_action('wp_ajax_aspl_fs_save_feedback_temp', 'aspl_fs_save_feedback_temp');
add_action('wp_ajax_nopriv_aspl_fs_save_feedback_temp', 'aspl_fs_save_feedback_temp');
function aspl_fs_save_feedback_temp()
{
	$name = sanitize_text_field($_POST['name']);
	$question_arr = array_map( 'esc_attr',$_POST['question_arr']);
	$des = sanitize_text_field($_POST['des']);
	$temp_type = sanitize_text_field($_POST['temp_type']);
	$product_id = sanitize_text_field($_POST['product_id']);
	if($temp_type == 'General'){
		$product_id = '0';
	}

	global $woocommerce, $wpdb;
 	$feedback_temp_table = $wpdb->prefix . "aspl_feedback_template";

 	
	$insert = $wpdb->insert($feedback_temp_table, array(
                        'name' => $name,
                        'description' => $des,
                        'website_config' => 'single',
                        'temp_type' => $temp_type,
                        'product_id' => $product_id,
                    ));

 	$template_id = $wpdb->insert_id;
 	$wpdb->print_error();
 	if($wpdb->last_error !== '') :
	    $wpdb->print_error();
	endif;
 	//var_dump($template_id);

 	$feedback_que_table = $wpdb->prefix . "aspl_feedback_question";

	foreach($question_arr as $key => $question)
	{
		$insert = $wpdb->insert($feedback_que_table, array(
	                                'temp_id' => $template_id,
	                                'question' => $question,
	                                'que_type' => 'Normal',
	                                'ans_mode' => 'Single',
	                            ));
	}
	die();
}

function asplfs_que_update_page(){

	$action = sanitize_text_field($_GET['action']);
	if($action == 'update'){
		include('includes/update-question.php');
	}
	else{
		include('includes/delete-question.php');
	}
	
}

add_action('wp_ajax_aspl_fs_save_answer', 'aspl_fs_save_answer');
add_action('wp_ajax_nopriv_aspl_fs_save_answer', 'aspl_fs_save_answer');
function aspl_fs_save_answer()
{
	global $woocommerce, $wpdb;
	$feedback_ans_table = $wpdb->prefix . "aspl_feedback_answer";
	$feedback_que_table = $wpdb->prefix . "aspl_feedback_question";
	$answers_arr = array_map( 'esc_attr',$_POST['answers_arr']);	
	$fil_answers_arr = array_filter($answers_arr);
	$que_id = sanitize_text_field($_POST['que_id']);
	$question = sanitize_text_field($_POST['question']);
	$que_type = sanitize_text_field($_POST['que_type']);
	$ans_mode = sanitize_text_field($_POST['ans_mode']);

	$update_que_config = $wpdb->get_results("UPDATE $feedback_que_table set question = '$question', que_type = '$que_type', ans_mode = '$ans_mode' WHERE question_id = '$que_id' ");

	foreach($fil_answers_arr as $key => $answer)
	{
		$update_answer = $wpdb->get_results("UPDATE $feedback_ans_table set answer = '$answer' WHERE answer_id = '$key' ");	
	}

}


add_action('wp_ajax_aspl_fs_add_answer_line', 'aspl_fs_add_answer_line');
add_action('wp_ajax_nopriv_aspl_fs_add_answer_line', 'aspl_fs_add_answer_line');
function aspl_fs_add_answer_line()
{
	$que_id = sanitize_text_field($_POST['que_id']);
	global $woocommerce, $wpdb;
	$answer = ' ';
	$feedback_ans_table = $wpdb->prefix . "aspl_feedback_answer";
	$insert = $wpdb->insert($feedback_ans_table, array(
	                                'answer' => $answer,
	                                'que_id' => $que_id,
	                               
	                            ));
	$ans_id = $wpdb->insert_id;
	_e($ans_id);
	die();
}

add_action('wp_ajax_aspl_fs_update_temp', 'aspl_fs_update_temp');
add_action('wp_ajax_nopriv_aspl_fs_update_temp', 'aspl_fs_update_temp');
function aspl_fs_update_temp()
{
	$name = sanitize_text_field($_POST['name']);
	$question_arr = array_map( 'sanitize_text_field',$_POST['question']);
	$des = sanitize_text_field($_POST['des']);
	$temp_type = sanitize_text_field($_POST['temp_type']);
	 $temp_id = sanitize_text_field($_POST['temp_id']);
	$product_id = sanitize_text_field($_POST['product_id']);
	if($temp_type == 'General'){
		$product_id = '0';
	}

	global $woocommerce, $wpdb;
 	$feedback_temp_table = $wpdb->prefix . "aspl_feedback_template";
 	
	$update_template = $wpdb->get_results("UPDATE $feedback_temp_table set name = '$name', temp_type = '$temp_type', description = '$des', product_id = '$product_id' WHERE temp_id = '$temp_id' ");

 	$feedback_que_table = $wpdb->prefix . "aspl_feedback_question";

 	
	foreach($question_arr as $key => $question)
	{
		$insert = $wpdb->insert($feedback_que_table, array(
	                                'temp_id' => $temp_id,
	                                'question' => $question,
	                                'que_type' => '0',
	                                'ans_mode' => '0',
	                            ));
	}
	die();
}


add_action('wp_ajax_aspl_fs_save_cus_fb_main', 'aspl_fs_save_cus_fb_main');
add_action('wp_ajax_nopriv_aspl_fs_save_cus_fb_main', 'aspl_fs_save_cus_fb_main');
function aspl_fs_save_cus_fb_main()
{
	global $woocommerce, $wpdb;
 	$fb_cus_main = $wpdb->prefix . "aspl_customer_feedback_main";
 	$fb_mail_notification = $wpdb->prefix . "aspl_mail_notification";

 	$timestamp = time()+date("Z");
	$current_date = gmdate("Y/m/d H:i:s",$timestamp); 	
 	$current_user = get_current_user_id();
 	$temp_id = sanitize_text_field($_POST['temp_id']);
 	$order_id = sanitize_text_field($_POST['order_id']);
 	$product_id = sanitize_text_field($_POST['prd_id']);
 	
	$insert = $wpdb->insert($fb_cus_main, array(
	                                'customer_id' => $current_user,
	                                'date_time' => $current_date,
	                                'template_id' => $temp_id,
	                            ));
	echo $ans_id = $wpdb->insert_id;

	$update_mail = $wpdb->get_results("UPDATE $fb_mail_notification set response = '1' WHERE product_id = '$product_id' and order_id = '$order_id' ");

	die();
}



add_action('wp_ajax_aspl_fs_save_cus_que_ans', 'aspl_fs_save_cus_que_ans');
add_action('wp_ajax_nopriv_aspl_fs_save_cus_que_ans', 'aspl_fs_save_cus_que_ans');
function aspl_fs_save_cus_que_ans(){

	global $woocommerce, $wpdb;
	$cf_id = sanitize_text_field($_POST['cf_id']);
	$que_id = sanitize_text_field($_POST['que_id']);
	
	$fb_cus_que_ans = $wpdb->prefix . "aspl_cust_fb_que_ans";

	if (is_array($_POST['ans_id'])) {

		$ans_id_temp = array_map( 'sanitize_text_field',$_POST['ans_id']);	
		$ans_id = serialize($ans_id_temp);	

	}else{

		$ans_id =  sanitize_text_field($_POST['ans_id']);/*array_map( 'sanitize_text_field',);*/	

	}

	$insert = $wpdb->insert($fb_cus_que_ans, array(
	                                'cf_id' => $cf_id,
	                                'que_id' => $que_id,
	                                'ans_id' => $ans_id,
	                            ));

	echo $ans_id = $wpdb->insert_id;
}

add_action('wp_ajax_aspl_fs_search_product', 'aspl_fs_search_product');
add_action('wp_ajax_nopriv_aspl_fs_search_product', 'aspl_fs_search_product');
function aspl_fs_search_product(){

	global $woocommerce, $wpdb;
	$result = '';
	$search_text = sanitize_text_field($_POST['text']);

	$post_table = $wpdb->prefix . "posts";
	$sql = "SELECT ID FROM $post_table where post_title LIKE '$search_text%' and post_type = 'product'";	
	$product_data = $wpdb->get_results($sql);	
	foreach ($product_data as $key => $product_id)
	{
		$product = wc_get_product( $product_id );
		$pname = $product->get_name();
		$pid = $product->get_id();
		$result = $result . '<div class="row" data-id="'.$pid.'">'.$pname.'</div>';
	}
	_e($result);
	die();
}

function asplfs_setting_page(){ ?>
	<div class="wrap ">
		<h2></h2>
	</div>
	<div class="wrap add_new_template_page" >
	 	<h1 class="wp-heading-inline">Feedback Setting</h1>
	 	<div class="admin-url" hidden=""><?php echo esc_url(admin_url('admin-ajax.php')); ?></div>
	 	<?php
	 		global $woocommerce, $wpdb;
 			$feedback_temp_table = $wpdb->prefix . "aspl_feedback_template";

	 		$default_temp = esc_attr(get_option('default_temp', ''));
	 		
	 		if(!get_option('send_mail_after')){
			    add_option('send_mail_after', '10');
			}
			$send_mail_after = esc_attr(get_option('send_mail_after', ''));

			if(!get_option('send_mail_count')){
			    add_option('send_mail_count', '2');
			}
	 		$send_mail_count = esc_attr(get_option('send_mail_count', ''));

	 		if(!get_option('send_mail_interval')){
			    add_option('send_mail_interval', '5');
			}
	 		$send_mail_interval = esc_attr(get_option('send_mail_interval', ''));

	 		$default_temp_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}aspl_feedback_template WHERE temp_id ='%s' LIMIT 1", $default_temp ) );


	 		
 			$fb_temp = "SELECT * FROM $feedback_temp_table where temp_type = 'General'";
			$fb_temp_data = $wpdb->get_results($fb_temp);
			
	 	?>
	 	<form action="" method="post" >

	 		<table class="form-table">
	 			
	 			<tr>
	 				<th>Default Template</th>
	 				<td>
	 					<select name="default_temp" class="default_temp">
	 						<option>Select Template</option>
	 						<?php
	 						foreach ($fb_temp_data as $key => $template_data)
							{
								$template_id = $template_data->temp_id;
								$temp_name = $template_data->name;
								?>
								<option value="<?php echo esc_attr($template_id); ?>" <?php if($default_temp_name == $temp_name){ echo esc_attr('selected'); } ?>><?php _e($temp_name); ?></option>
								<?php
							}
	 						?>
	 					</select>	 					
	 				</td>
	 			</tr>
	 			<tr>
	 				<td>Send Mail After</td>
	 				<td><input type="number" name="send-mail-after" value="<?php echo esc_attr($send_mail_after); ?>"></td>
	 			</tr>
	 			<tr>
	 				<td>Email Interval</td>
	 				<td><input type="number" name="send-mail-interval" value="<?php echo esc_attr($send_mail_interval); ?>"></td>
	 			</tr>
	 			<tr>
	 				<td>Number Of Email</td>
	 				<td><input type="number" name="send-mail-count" value="<?php echo esc_attr($send_mail_count); ?>"></td>
	 			</tr>
	 			<tr>
	 				<td>	 					
	 					<input type="submit" name="save_settings" value="Save" class="button button-primary">
	 				</td>
	 			</tr>
	 		</table>

	 	</form>
	</div>
	<?php
	if(isset($_POST['save_settings'])){
		$default_temp = sanitize_text_field($_POST['default_temp']);
		update_option('default_temp', $default_temp);
		$send_mail_after = sanitize_text_field($_POST['send-mail-after']);
		$send_mail_interval = sanitize_text_field($_POST['send-mail-interval']);
		$send_mail_count = sanitize_text_field($_POST['send-mail-count']);
		update_option('send_mail_after', $send_mail_after);
		update_option('send_mail_interval', $send_mail_interval);
		update_option('send_mail_count', $send_mail_count);
		wp_redirect('?page=feedback_setting');
	}

}