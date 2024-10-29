<?php

if ( ! defined( 'ABSPATH' ) ) 
{
    exit;
}


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class aspl_fs_Customer_feedback_list extends WP_List_Table {
	
	public static function get_feedback( ) {

		  global $wpdb, $woocommerce;
		  $sql = "SELECT * FROM {$wpdb->prefix}aspl_customer_feedback_main";
		  if ( ! empty( $_REQUEST['orderby'] ) ) {
		    $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
		    $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		  }
		 /* $sql .= " LIMIT $per_page";*/
		 /* $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;*/
		  $result = $wpdb->get_results( $sql, 'ARRAY_A' );

		foreach ($result as $key => $value)
		{
			$cus_id = $result[$key]['customer_id'];
			$user = get_user_by( 'id', $cus_id );
			$cus_name = $user->display_name;
			
			$result[$key]['cus_name'] = $cus_name;

			$temp_id = $result[$key]['template_id'];

			$temp_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}aspl_feedback_template WHERE temp_id ='%s' LIMIT 1", $temp_id ) );

			$result[$key]['temp_name'] = $temp_name;
		}
		  return $result;
	}

	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Feedback', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Feedback', 'sp' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?
		] );
	}

	public function no_items() {
	  _e( 'No feedback avaliable.', 'sp' );
	}

	public function column_default( $item, $column_name ) {
	  	
	   	switch ( $column_name ) {
		    case 'customer_id':
		    	return $item[ 'cus_name' ];
		    case 'temp_name':
		    case 'date_time':
		      	return $item[ $column_name ];
		    default:
	      	return print_r( $item, true ); //Show the whole array for troubleshooting purposes
	  	}
	}

	function get_sortable_columns() {
	  $sortable_columns = array(
	    'customer_id'  => array('customer_id',false),
	    'temp_name' => array('temp_name',false),
	    'date_time'   => array('date_time',false)	    
	  );
	  return $sortable_columns;
	}

	function get_columns() {
		  $columns = [
		    'cb'      => '<input type="checkbox" />',
		    'customer_id'    => __( 'Customer', 'sp' ),
		    'temp_name' => __( 'Template', 'sp' ),
		    'date_time'    => __( 'Feedback Time', 'sp' ),
		  ];
		  return $columns;
	}

	function usort_reorder( $a, $b ) {
			if ( (!empty($_GET['orderby'])) && (!empty($_GET['order'])) ) {
			  		
			  		$request_orderby = sanitize_text_field($_GET['orderby']);
	          		$request_order = sanitize_text_field($_GET['order']);
	         		$orderby = (!empty($request_orderby)) ? $request_orderby : 'cf_id'; //If no sort, default to title
	          		$order = (!empty($request_order)) ? $request_order : 'asc'; //If no order, default to asc
	          		$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
	          		return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
			
			}
	}


	function column_temp_name($item){
	  	$actions = array(
	            'view'      => sprintf('<a href="?page=%s&action=%s&cf_id=%s">View</a>','customer-feedback','view',$item['cf_id']),
	            /*'delete'    => sprintf('<a href="?page=%s&action=%s&temp_id=%s">Delete</a>','template-configuration','delete',$item['temp_id']),*/
	        );
	  	return sprintf('%1$s %2$s', $item['temp_name'], $this->row_actions($actions) );
	}


	/*public static function delete_customer( $id ) {
	  	global $wpdb;

	  	$wpdb->delete(
	    	"{$wpdb->prefix}aspl_feedback_template",
	    	[ 'temp_id' => $temp_id ],
	    	[ '%d' ]
	  	);
	}

*/
	public static function record_count() {
	  global $wpdb;

	  $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}aspl_customer_feedback_main";

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
	    	'<input type="checkbox" name="temp-delete-customer[]" value="%s" />', $item['cf_id']
	  	);
	}

	public function prepare_items() {

		global $cus_fed_hook;
	 	$columns  = $this->get_columns();
  		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		
		$user = get_current_user_id();
		$screen = get_current_screen();
          // retrieve the "per_page" option
        $screen_option = $screen->get_option('per_page', 'option');
          // retrieve the value of the option stored for the current user
        $per_page = get_user_meta($user, $screen_option, true);
          if ( empty ( $per_page) || $per_page < 1 ) {
              // get the default value if none is set
              $per_page = $screen->get_option( 'per_page', 'default' );
          }

		
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$data=$this->get_feedback();
	 	 $this->process_bulk_action();
	  	usort($data, array( &$this, 'usort_reorder' ) );	  
	  	
	  	$current_page = $this->get_pagenum();

	  	$total_items = count($data);
	 	// only ncessary because we have sample data
	  	$found_data = array_slice($data,( ( $current_page-1 )* $per_page ), $per_page );
	  	$this->set_pagination_args( array(
		    'total_items' => $total_items,                  //WE have to calculate the total number of items
		    'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
		    'total_pages' => ceil($total_items/$per_page) 
		  ) );
	  	$this->items = $found_data;
	}
}
?>