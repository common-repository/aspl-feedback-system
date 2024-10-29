<?php

if ( ! defined( 'ABSPATH' ) ) 
{
    exit;
}

	global $wpdb;
	$temp_id = sanitize_text_field($_GET['temp_id']);
	$feedback_temp_table = $wpdb->prefix . "aspl_feedback_template";
	$sql = "SELECT * FROM $feedback_temp_table where temp_id = '$temp_id'";
	$feedback_data = $wpdb->get_results($sql);
	$feedback_name ="";
	$feedback_des ="";
	$temp_type ="";
	$pname ="";
	$product_id = "";
	foreach ($feedback_data as $key => $value)
	{
		$feedback_name = $value->name;
		$feedback_des = $value->description;
		$website_config = $value->website_config;
		$temp_type = $value->temp_type;
		$product_id = $value->product_id;

		if($product_id != 0){
			$product = wc_get_product( $product_id );
			$pname = $product->get_name();
		}
		
	}

?>
<div class="wrap add_update_template_page">
 	<h1 class="wp-heading-inline">Update <b><?php _e($feedback_name); ?></b></h1>
    <span hidden="" class="admin-url"><?php echo esc_url(admin_url('admin-ajax.php')); ?></span>
	<form action="" method="post" class="update-question-form">
	 	<table class="form-table" >
	 		<tbody>
	            <tr>
	                <th>Name</th>
	                <td> <input type="text" class="fb-name" name="feedback_name" value="<?php echo esc_attr($feedback_name); ?>">
	                	 <input type="hidden" class="fb-tempid" name="" value="<?php echo esc_attr($temp_id); ?>">
	                </td>
	            </tr>
	 			<tr>
	 				<th>Description</th>
	 				<td><input type="text" class="fb-des" name="feedback_des" value="<?php echo esc_attr($feedback_des); ?>"></td>
	 			</tr>
	            <tr>
	                <th>Template Type</th>
	                <td>
	                	<select name="temp-type" class="temp-type">
	                        <option value="0">Select Type</option>
	                       	<option <?php if($temp_type == 'General'){ _e('selected'); } ?> >General</option>
	                       	<option <?php if($temp_type == 'Product'){ _e('selected'); } ?> >Product</option>
                    	</select>
	                </td>
	            </tr>
	            <tr class="product-selection">
	                <th>Product</th>
	                <td>
	                	<input type="text" name="" class="product-search" value="<?php echo esc_attr($pname); ?>" data-id="<?php echo esc_attr($product_id); ?>">
	                	<div class="search-result"></div>
	                </td>
	            </tr>
	        </tbody>
	    </table>
	    <?php
	    $feedback_que_table = $wpdb->prefix . "aspl_feedback_question";
			$sql = "SELECT * FROM $feedback_que_table where temp_id = '$temp_id'";
			$question_data = $wpdb->get_results($sql);
			
		?>
	    <table class="question-data wp-list-table widefat fixed striped feedback">
	    	<thead>
	    		<tr>
	    			<td>Question</td>
	    			<td>Question Type</td>
	    			<td>Answer Mode</td>
	    			<td>Action</td>
	    		</tr>
	    	</thead>
	    	<tbody class="question-list">
	    		<?php

	    		if ( ! class_exists( 'WP_List_Table' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
				}
				class aspl_fs_Feedback_que_List extends WP_List_Table {

					public static function get_question( $per_page = 5, $page_number = 1 ) {

						  global $wpdb;
						  $sql = "SELECT * FROM {$wpdb->prefix}aspl_feedback_question";
						  if ( ! empty( $_REQUEST['orderby'] ) ) {
						    $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
						    $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
						  }
						  $sql .= " LIMIT $per_page";
						  $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
						  $result = $wpdb->get_results( $sql, 'ARRAY_A' );
						  return $result;
					}


					public function __construct() {
						parent::__construct( [
							'singular' => __( 'Question', 'que' ), //singular name of the listed records
							'plural'   => __( 'Question', 'que' ), //plural name of the listed records
							'ajax'     => false //should this table support ajax?
					] );
					}

					public function no_items() {
					  _e( 'No feedback avaliable.', 'que' );
					}

					public function column_default( $item, $column_name ) {
					  	
					   	switch ( $column_name ) {
						    case 'question':
						    case 'que_type':
						    case 'ans_mode':
						      return $item[ $column_name ];
						    default:
					      	return print_r( $item, true ); //Show the whole array for troubleshooting purposes
					  	}
					}

					function get_sortable_columns() {
					  $sortable_columns = array(
					    'question'  => array('question',false),
					    'que_type' => array('que_type',false),
					    'ans_mode'   => array('ans_mode',false)					    
					  );
					  return $sortable_columns;
					}

					function get_columns() {
						  $columns = [
						    'cb'      => '<input type="checkbox" />',
						    'question'    => __( 'Name', 'que' ),
						    'que_type' => __( 'Description', 'que' ),
						  ];
						  return $columns;
					}

					function usort_reorder( $a, $b ) {

						if ( (!empty($_GET['orderby'])) && (!empty($_GET['order'])) ) {
						  $request_orderby = sanitize_text_field($_GET['orderby']);
				          $request_order = sanitize_text_field($_GET['order']);
				          $orderby = (!empty($request_orderby)) ? $request_orderby : 'question_id'; //If no sort, default to title
				          $order = (!empty($request_order)) ? $request_order : 'asc'; //If no order, default to asc
				          $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
				          return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
				      	}
				      	
					}


					function column_name($item){
					  	$actions = array(
					            'edit'      => sprintf('<a href="?page=%s&action=%s&question_id=%s">Edit</a>','template-configuration','edit',$item['question_id']),
					            'delete'    => sprintf('<a href="?page=%s&action=%s&question_id=%s">Delete</a>','template-configuration','delete',$item['question_id']),
					        );
					  	return sprintf('%1$s %2$s', $item['question'], $this->row_actions($actions) );
					}

					public static function record_count() {
					  global $wpdb;

					  $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}aspl_feedback_question";

					  return $wpdb->get_var( $sql );
					}

					function get_bulk_actions() {
					  $actions = array(
					    'delete'    => 'Delete'
					  );
					  return $actions;
					}


					public function process_bulk_action() {

				        // security check!
				        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

				            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
				            $action = 'bulk-' . $this->_args['plural'];

				            if ( ! wp_verify_nonce( $nonce, $action ) )
				                wp_die( 'Nope! Security check failed!' );

				        }

				        $action = $this->current_action();

				        switch ( $action ) {

				            case 'delete':
				                include('bulk-delete-template.php');
				                break;

				            case 'save':
				                wp_die( 'Save something' );
				                break;

				            default:
				                // do nothing or something else
				                return;
				                break;
				        }

				        return;
				    }


				    function column_cb( $item ) {
					  	return sprintf(
					    	'<input type="checkbox" name="temp-delete[]" value="%s" />', $item['temp_id']
					  	);
					}

					public function prepare_items() {

					 	$columns  = $this->get_columns();
				  		$hidden   = array();
						$sortable = $this->get_sortable_columns();
						$this->_column_headers = array( $columns, $hidden, $sortable );
						$data=$this->get_feedback();
					 	 $this->process_bulk_action();
					  	usort($data, array( &$this, 'usort_reorder' ) );
					  
					  	$per_page = 10;
					  	$current_page = $this->get_pagenum();
					  	$total_items = count($data);
					 	// only ncessary because we have sample data
					  	$found_data = array_slice($data,( ( $current_page-1 )* $per_page ), $per_page );
					  	$this->set_pagination_args( array(
						    'total_items' => $total_items,                  //WE have to calculate the total number of items
						    'per_page'    => $per_page                     //WE have to determine how many items to show on a page
						  ) );
					  	$this->items = $found_data;
					}


				}


	    		foreach ($question_data as $key => $question)
				{
					$question_id = $question->question_id;
	    		?>
	    			<tr>
	    				<td><a href="?page=update-question&action=update&question_id=<?php echo esc_attr($question_id); ?>&temp_id=<?php echo esc_attr($temp_id); ?>"><?php _e($question->question); ?></a></td>
	    				<td><?php _e($question->que_type); ?></td>
	    				<td><?php _e($question->ans_mode); ?></td>
	    				<td><a href="?page=update-question&action=update&question_id=<?php echo esc_attr($question_id); ?>&temp_id=<?php echo esc_attr($temp_id); ?>" class="aspl_feed_a_button"><span>&#10011; &nbsp</span>Add Answer</a> &nbsp &nbsp <a href="?page=update-question&action=delete&question_id=<?php echo esc_attr($question_id); ?>&temp_id=<?php echo esc_attr($temp_id); ?>" class="aspl_feed_a_button"><span>&#10005; &nbsp</span>Delete</a></td>
	    			</tr>
	    		<?php
	    		}

	    		?>
	    	</tbody>
	    </table>
	    <br>
	    <button type="button" class="button-primary add-new-question-row">Add Row</button>

		<button class="button-primary woocommerce-save-button update-feedback-template" type="button" name="submit">Save Changes</button> 

	    <a href="<?=get_admin_url();?>/admin.php?page=asplfs_feedback_page" class="button-primary woocommerce-save-button add-installer-btn">Back</a>
	</form>
</div>


