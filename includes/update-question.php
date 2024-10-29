<?php 
	if ( ! defined( 'ABSPATH' ) ) 
	{
	    exit;
	}

 ?>
	<div class="wrap add_new_template_page">
 	<h1 class="wp-heading-inline">Question Configuration</h1>
    <span hidden="" class="admin-url"><?php echo esc_url(admin_url('admin-ajax.php')); ?></span>
    <span class="update-redirect"></span>
    <?php
    	$question_id = sanitize_text_field($_GET['question_id']);
    	$temp_id = sanitize_text_field($_GET['temp_id']);
    ?>
    <div class="back-to-template"><a href="?page=template-configuration&action=edit&temp_id=<?php echo esc_attr($temp_id); ?>">Back</a></div>
	<form action="" method="post" class="update-question">
		<?php
			global $wpdb;

			$feedback_que_table = $wpdb->prefix . "aspl_feedback_question";
			$sql = "SELECT * FROM $feedback_que_table where question_id = '$question_id'";
			$question_data = $wpdb->get_results($sql);

			foreach ($question_data as $key => $question)
			{
				$que_id = $question->question_id;
				$curr_que = $question->question;
				$que_type = $question->que_type;
				$ans_mode = $question->ans_mode;
				
			}

			$feedback_ans_table = $wpdb->prefix . "aspl_feedback_answer";
			$sql = "SELECT * FROM $feedback_ans_table where que_id = '$que_id'";
			$answer_data = $wpdb->get_results($sql);

		?>
		<h3>Question: <?php _e($curr_que); ?></h3>
		<table class="form-table answer">
	 		<tbody>
	            <tr>
	                <th>Question</th>
	                <td>

	                	<input type="hidden" name="" class="que-id" value="<?php echo esc_attr($que_id); ?>">
	                	<input type="hidden" name="" class="temp-id" value="<?php echo esc_attr($temp_id); ?>">
	                 	<input type="text" class="question" name="question" value="<?php echo esc_attr($curr_que); ?>"></td>
	            </tr>
	 			<tr>
	 				<th>Question Type</th>
	 				<td>
	 					<select name="que-type" class="que-type">
	                        <option value="0">Select Type</option>
	                       	<option <?php if($que_type == 'Normal'){ echo esc_attr('selected'); } ?> >Normal</option>
	                       	<option <?php if($que_type == 'Widget'){ echo esc_attr('selected'); } ?> >Widget</option>
                    	</select>
                    </td>
	 			</tr>
	 			<tr class="que-widget-hide">
	 				<th>Answer Mode</th>
	 				<td>
	 					<select name="ans-mode" class="ans-mode">
	                        <option value="0">Select Type</option>
	                       	<option <?php if($ans_mode == 'Single'){ echo esc_attr('selected'); } ?> >Single</option>
	                       	<option <?php if($ans_mode == 'Multiple'){ echo esc_attr('selected'); } ?> >Multiple</option>
                    	</select>
	 				</td>
	 			</tr>
	            <tr class="que-widget-hide">
	                <th>Answer List</th>
	                <td>
	                	<div class="answer-list">
	                		<?php
				    		foreach ($answer_data as $key => $answer)
							{
								$answer_text = $answer->answer;
								$ans_id = $answer->answer_id;
					    		?>
					    		<div class="answer-line">

					    			<input type="text" name="answer" value="<?php echo esc_attr($answer_text); ?>" class="ans-text">
					    			<input type="hidden" name="ans-id" value="<?php echo esc_attr($ans_id); ?>" class="ans-id">
					    		</div>
					    		<?php
				    		}

				    		?>
	                	</div>
	                	<div class="add-answer-btn"> Add Answer</div>
	                </td>
	            </tr>
	        </tbody>
	    </table>

	    <button type="button" class="update-que-config button button-primary" >Update Question</button>
	</form>
</div>