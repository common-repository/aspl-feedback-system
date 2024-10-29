jQuery(document).ready(function($) 
{

	$(document).on('click','.customer-feedback-submit',function(e){

		var au = $('.admin-url').text();
		var temp_id = $('.temp-id').attr('data-temp-id');
		var prd_id = $('.prd-id').attr('data-prd-id');
		var order_id = $('.order-id').attr('data-order-id');
		var success_gif = $('.success-gif').text();
		
		var validate = 0;
		var validate_arr = [];
		$(".customer-feedback-form .single-question" ).each(function( index ){		  	
			var single_que = $(this);
			if($(single_que).hasClass('Widget')){

				var star = $(this).find('p.stars');
				if($(star).hasClass('selected')){
					validate = 1;
					$(single_que).find('.error-msg').text('');
				}
				else{
					validate = 0;  
		 			$(single_que).find('.error-msg').text('Please Select Answer');
				}
				
			}
			else{

				var que_id = $(this).attr('data-qid');			
			 	var ans_name = 'input[name="'+que_id+'-ans"]:checked';
			 	if($(ans_name).is(':checked')) { 
			 		validate = 1; 
			 		$(this).find('.error-msg').text(''); 
			 	}
			 	else{ 
			 		validate = 0;  
			 		$(this).find('.error-msg').text('Please Select Answer'); 
			 	}	
			}			

		 	validate_arr.push(validate);		 	
		
		});
		
		
		if( jQuery.inArray( 0, validate_arr ) == '-1' ){
			
			$.ajax({    
	                type: "POST", 
	                url: au,
	                data: {
	                    action: 'aspl_fs_save_cus_fb_main',
	                    temp_id: temp_id,
	                    prd_id: prd_id,
	                    order_id: order_id,
	                                      
	                },          
	                success: function (data) {
	                  	
	                  	$(".customer-feedback-form .single-question" ).each(function( index ){		  	
							
							var single_que = $(this);
							var que_id = $(this).attr('data-qid');	
							if($(single_que).hasClass('Widget'))
							{
								var star_count = $(single_que).find('p.stars a.active').attr('data-count');
								
								$.ajax({    
					                type: "POST", 
					                url: au,
					                data: {
					                    action: 'aspl_fs_save_cus_que_ans',
					                    temp_id: temp_id,		
					                    cf_id: data,
					                    que_id: que_id,
					                    ans_id: star_count,		                                      
					                },          
					                success: function (data) {
					                    
					                },
					        	});								
							}
							else{

								var allInputs = $(this).find(':input');
								
								var input_type = allInputs.attr('type');												
								
								var ans_name = 'input[name="'+que_id+'-ans"]:checked';						
								console.log("ans_name>>>>>>>"+ans_name);

								var ans_id = $(ans_name).attr('data-aid');
								console.log("ans_id>>>>>>>"+ans_id);

								if(input_type == 'checkbox')
								{
									var ans_arr = [];
									
									$(this).find(':input').each(function( index ){	

										if($(this).attr("checked")){
											var ans_id = $(this).attr('data-aid');
											ans_arr.push(ans_id);
										}
									console.log(ans_arr);
									});	  	

									$.ajax({    
						                type: "POST", 
						                url: au,
						                data: {
						                    action: 'aspl_fs_save_cus_que_ans',
						                    temp_id: temp_id,		
						                    cf_id: data,
						                    que_id: que_id,
						                    ans_id: ans_arr,		                                      
						                },          
						                success: function (data) {
						                    
						                },
						        	});	

								}
								else{

									$.ajax({    
						                type: "POST", 
						                url: au,
						                data: {
						                    action: 'aspl_fs_save_cus_que_ans',
						                    temp_id: temp_id,		
						                    cf_id: data,
						                    que_id: que_id,
						                    ans_id: ans_id,		                                      
						                },          
						                success: function (data) {
						                    
						                },
						        	});	
								}	
							}								

						});

						$('.customer-feedback-form').css('display','none');
				        $('.col-md-8').append('<img src="'+success_gif+'">');

	                },
	        });
		}
		
	});

	
	$(document).on('click','.widget-ans .stars a',function(e){

		$('.widget-ans .stars a').removeClass('active');
		$(this).addClass('active');
		$(this).parents('.stars').addClass('selected');

	});

});