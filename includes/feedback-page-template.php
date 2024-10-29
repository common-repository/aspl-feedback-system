<?php

if ( ! defined( 'ABSPATH' ) ) 
{
    exit;
}


if(!is_user_logged_in()){
	$login_url = wp_login_url();
	wp_redirect($login_url);
	exit();
}
 get_header();

$feedback_temp_table = $wpdb->prefix . "aspl_feedback_template";
	// $product_id = '';
	$response = '';
	$customer_id = get_current_user_id();
	if(isset($_GET['product_id'])){

		$product_id = sanitize_text_field($_GET['product_id']);
		$order_id = sanitize_text_field($_GET['order_id']);
		$prd_sql = "SELECT * FROM $feedback_temp_table where product_id = '$product_id'";

		$template_id = $wpdb->get_var( $wpdb->prepare( "SELECT temp_id FROM $feedback_temp_table WHERE product_id ='%s' LIMIT 1", $product_id ) );

		$fb_mail_notification = $wpdb->prefix . "aspl_mail_notification"; 
		$check_record = "SELECT * FROM $fb_mail_notification WHERE product_id = '$product_id' and order_id = '$order_id'";

		$check_record_result = $wpdb->get_results($check_record);
		foreach ($check_record_result as $key => $value) {
			$response = $value->response;
		}

					
	}
	else{
		$template_id = esc_attr(get_option('default_temp', ''));

		$fb_main = $wpdb->prefix . "aspl_customer_feedback_main";
		$fb_main_result = "SELECT * FROM $fb_main WHERE template_id = '$template_id' and customer_id = '$customer_id'";
		
		$fb_main_result_data = $wpdb->get_results($fb_main_result);
		
		if(count($fb_main_result_data) != 0){
			$response = '1';
		}
	}

	if($response == 0 || $response == ''){
	
?>
<div class="form-feedback container">
	<span hidden="" class="admin-url"><?php echo esc_url(admin_url('admin-ajax.php')); ?></span>
	<span hidden="" class="temp-id" data-temp-id="<?php echo esc_attr($template_id)	; ?>"></span>
	<span hidden="" class="prd-id" data-prd-id="<?php echo esc_attr($product_id); ?>"></span>
	<span hidden="" class="order-id" data-order-id="<?php echo esc_attr($order_id); ?>"></span>
	<span hidden="" class="success-gif"><?php echo esc_url(plugin_dir_url( __DIR__ ).'assest/images/tenor.gif'); ?></span>
	<?php	
	
	$sql = "SELECT * FROM $feedback_temp_table where temp_id = '$template_id'";
	$feedback_data = $wpdb->get_results($sql);
	foreach ($feedback_data as $key => $value)
	{
		$feedback_name = $value->name;
		$feedback_des = $value->description;
		$website_config = $value->website_config;
		$temp_type = $value->temp_type;
	}
	$feedback_que_table = $wpdb->prefix . "aspl_feedback_question";
	$sql = "SELECT * FROM $feedback_que_table where temp_id = '$template_id'";
	$question_data = $wpdb->get_results($sql);
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="col-md-2">
				
			</div>
			
			<div class="col-md-8">
				
				<form class="customer-feedback-form">
					<?php
					$que_row = 1;
					foreach ($question_data as $key => $question)
					{
						$que_id = $question->question_id;
						$curr_que = $question->question;
						$que_type = $question->que_type;
						$ans_mode = $question->ans_mode;
						$question = $question->question;

						$feedback_ans_table = $wpdb->prefix . "aspl_feedback_answer";
						$sql = "SELECT * FROM $feedback_ans_table where que_id = '$que_id'";
						$answer_data = $wpdb->get_results($sql);

						
					?>
				  	<div class="form-group single-question <?php echo esc_attr($que_type); ?>" data-qid="<?php echo esc_attr($que_id); ?>">
					    <label for="exampleFormControlInput1"><?php _e($que_row.' '. $question); ?></label>
					   							
					    <?php
					    if ($que_type == 'Widget') {
					    	?>
					    	<div class="widget-ans">
					   			<p class="stars">						
					   				<span>	
							    	<?php
							    	for ($i=1; $i < 6; $i++) {					    		
							    	
							    	?>
							    		<a class="star-<?php echo esc_attr($i); ?>" href="#" data-count="<?php echo esc_attr($i);?>"><?php echo esc_html($i); ?></a>
							    	<?php
							    	}
							    	?>
							    	</span>
					    		</p>
							</div>
					    	<?php
					    }

					    else{
					    if($ans_mode == 'Single'){

					    	foreach ($answer_data as $key => $answer)
							{
								$answer_text = $answer->answer;
								$ans_id = $answer->answer_id;

								// var_dump($ans_id);
								?>
								<div class="single-answer" data-aid="<?php echo esc_attr($ans_id); ?>">
								  	<input class="form-check-input" type="radio" name="<?php echo esc_attr($que_id); ?>-ans" value="option1" data-aid="<?php echo esc_attr($ans_id); ?>" >
								  	<label class="form-check-label" for="exampleRadios1">
								    <?php echo esc_html($answer_text); ?>
								  	</label>
								</div>
								<?php
							}
					    }
					    elseif ($ans_mode == 'Multiple') {					    	

					    	foreach ($answer_data as $key => $answer)
							{
								$answer_text = $answer->answer;
								$ans_id = $answer->answer_id;

								?>
								<div class="single-answer" data-aid="<?php echo esc_attr($ans_id); ?>">
								  	<input class="form-check-input" type="checkbox" name="<?php echo esc_attr($que_id); ?>-ans" value="option1" data-aid="<?php echo esc_attr($ans_id); ?>" >
								  	<label class="form-check-label" for="exampleRadios1">
								    <?php echo esc_html($answer_text); ?>
								  	</label>
								</div>
								<?php
							}
					    }
					    
					    }
						?>
						<div class="error-msg"></div>
				  	</div>
				  <?php 
				  	$que_row = $que_row + 1;
					} ?>
				<input type="button" class="customer-feedback-submit" value="Submit Feedback">


			</form>

			</div>
		</div>		
	</div>
</div>

<?php
}
else if($response == '1'){
	?>
	<div class="form-feedback container">
		<div class="row">
			<div class="col-md-12 text-center">
				<h3>Thank you for your Response</h3>
			</div>
		</div>
	</div>
	
	<?php
}


get_footer();
?>