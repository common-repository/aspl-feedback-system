<?php 
if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}
?>
<div class="wrap add_new_template_page">
 	<h1 class="wp-heading-inline">Add New Template</h1>
 	<div class="admin-url" hidden=""><?php echo esc_url(admin_url('admin-ajax.php')); ?></div>
	<form action="" method="post">
	 	<table class="form-table" >
	 		<tbody>
	            <tr>
	                <th>Name</th>
	                <td> <input type="text" name="feedback_name" class="fb-name"></td>
	            </tr>
	 			<tr>
	 				<th>Description</th>
	 				<td><input type="text" name="feedback_des" class="fb-des"></td>
	 			</tr>
	            <tr>
	                <th>Template Type</th>
	                <td>
	                	<select name="temp-type" class="temp-type">
	                        <option value="0">Select Type</option>
	                       	<option>General</option>
	                       	<option>Product</option>
                    	</select>
	                </td>
	            </tr>
	            <tr class="product-selection">
	                <th>Product</th>
	                <td>
	                	<input type="text" name="" class="product-search">
	                	<div class="search-result"></div>
	                </td>
	            </tr>
	        </tbody>
	    </table>

	    <table class="question wp-list-table widefat fixed striped feedback">
	    	<thead>
	    		<tr>
	    			<th>Question</th>
	    			
	    		</tr>
	    	</thead>
	    	<tbody>
	    		<tr>	    			
	    			<td>
	    				<input type="text" name="question" class="question">
	    			</td>	    			
	    		</tr>	    		
	    	</tbody>
	    </table>
	    <br>
	    <button type="button" class="button-primary add-new-question-row">Add Row</button>
		<button class="button-primary woocommerce-save-button add-feedback-temp-btn" type="button" name="submit">Save Changes</button> 

	    <a href="<?php echo esc_url(get_admin_url());?>/admin.php?page=asplfs_feedback_page" class="button-primary woocommerce-save-button add-installer-btn">Back</a>
	</form>
</div>



