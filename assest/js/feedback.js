jQuery(document).ready(function($) 
{
	
	$(document).on('click','.add-new-question-row',function(e){
		var question_row_html = '<tr><td><input type="text" name="question" class="question"></td><td></td></tr>';
		$('table.question tbody').append(question_row_html);
	});
	

	$(document).on('click','.add-feedback-temp-btn',function(e){
		e.preventDefault();
		var au = $('.admin-url').text();
		var name = $('.fb-name').val();
		var des = $('.fb-des').val();		
		var temp_type = $('.temp-type').val();
		var product_id = $('.product-search').attr('data-id');
		// console.log(product_id);
		var question = [];

		$( "table.question tbody tr" ).each(function( index ){
		  // console.log( index + ": " + $( this ).find('.question').val() );
		  var current_que = $( this ).find('.question').val();
		  question.push(current_que);
		});

		$.ajax({    
                type: "POST", 
                url: au,
                data: {
                    action: 'aspl_fs_save_feedback_temp',
                    name: name,
                    des: des,
                    temp_type: temp_type,
                    question_arr: question,
                    product_id: product_id,                
                },          
                success: function (data) {
                   window.location.href = '?page=asplfs_feedback_page';
                },
        });
	});


	
	$(document).on('click','.add-answer-btn',function(e){

		var que_id = $('.que-id').val();
		var au = $('.admin-url').text();		

		$.ajax({    
                type: "POST", 
                url: au,
                data: {
                    action: 'aspl_fs_add_answer_line',
                    que_id: que_id,                    
                },           
                success: function (data) {
                   	//$('#add_new_facility').css('display','none');
                    //$('.fc-list').html(data);

                    var answer_row_html = '<div class="answer-line"><input type="text" name="answer" class="ans-text"> <input type="hidden" name="ans-id" value="'+data+'" class="ans-id"></div>';
					$('form.update-question .answer-list').append(answer_row_html);
                },
        });

	});

	
	$(document).on('click','.update-que-config',function(e){

		var au = $('.admin-url').text();
		var question = $('.question').val();
		var que_type = $('.que-type').val();
		var ans_mode = $('.ans-mode').val();
		var que_id = $('.que-id').val();
		var temp_id = $('.temp-id').val();

		var answers = new Array();

		$(document).find( "table.answer .answer-list .answer-line" ).each(function( index ){
		  	
		  	var ans_id = $( this ).find('.ans-id').val();
		  	var current_ans = $( this ).find('.ans-text').val();
		  	answers[ans_id] = current_ans;

		});
		$.ajax({    
                type: "POST", 
                url: au,
                data: {
                    action: 'aspl_fs_save_answer',
                    que_id: que_id,
                    question: question,
                    que_type: que_type,
                    ans_mode: ans_mode,
                    answers_arr: answers, 
                    temp_id: temp_id,                  
                },           
                success: function (data) {
                   	//$('#add_new_facility').css('display','none');
                    //$('.fc-list').html(data);
                    window.location.href = '?page=update-question&action=update&question_id='+que_id+'&temp_id='+temp_id;
                },
        });

	});


	

	$(document).on('click','.update-question-form .add-new-question-row',function(e){

		var question_row_html = '<tr><td><input type="text" name="question" class="question-text"></td><td></td></tr>';
		$('table.question-data tbody').append(question_row_html);


	});

	

	$(document).on('click','.update-feedback-template',function(e){

		var au = $('.admin-url').text();
		var name = $('.fb-name').val();
		var des = $('.fb-des').val();
		var temp_type = $('.temp-type').val();
		var product_id = $('.product-search').attr('data-id');
		var temp_id = $('.fb-tempid').val();
		var question = new Array();

		$(document).find( "table.question-data .question-list tr" ).each(function( index ){
		  	
		  	var check_que = $( this ).find('input').html();
		  	
		  	if(typeof check_que !== "undefined"){		

			  	var current_que = $( this ).find('.question-text').val();
			  	// console.log('current---ques--'+current_que);
			  	question.push(current_que);
		  	}		  			  	
		});

		$.ajax({    
                type: "POST", 
                url: au,
                data: {
                    action: 'aspl_fs_update_temp',
                    temp_id: temp_id,                    
                    question: question, 
                    temp_type: temp_type,
                    name: name,
                    des: des,
                    product_id: product_id,
                },           
                success: function (data) {
                   
                    window.location.href = '?page=asplfs_feedback_page';
                },
        });
	});

	
	$(document).on('keyup','.product-search', function () {

		var search_text = $(this).val();
		var au = $('.admin-url').text();

		if(search_text.length > 2){
			$.ajax({    
                type: "POST",
                url: au,
                data: {
                    action: 'aspl_fs_search_product',
                    text:search_text,
                   
                },
                success: function (data) {
                	$('.search-result').html(data);
                    // $('#tagsname').html("<ul class='listul'>"+data+"</ul>");
                    //$(th).parents("tr").find('#tagsname').html("<ul class='listul'>"+data+"</ul>");
                },
            });
		}
		else if(search_text == ''){
			$('.search-result').html('');
		}
		     

	});



	$(document).on('click','.search-result .row', function () {

		var product_id = $(this).attr('data-id');
		var product_name = $(this).text();
		$('.product-search').val(product_name);
		$('.product-search').attr('data-id', product_id);
		
	});

	change_question_type();
	$(document).on('change','.que-type', function () {

		change_question_type();

	});

	change_template_type();

	$(document).on('change','.temp-type', function () {

		change_template_type();
	});



function change_template_type(){

	var temp_type = $('.temp-type').val();
		
	if(temp_type == 'Product'){
		$('.product-selection').css('display','contents');
	}
	else{
		$('.product-selection').css('display','none');
	}
}

function change_question_type(){

	var qye_type = $('.que-type').val();
	if(qye_type == 'Widget'){
		$('.ans-mode').val('Single');
		
		$('.que-widget-hide').css('display', 'none');
	}
	else{
		$('.ans-mode').removeProp('disabled');
		$('.que-widget-hide').css('display', 'table-row');
	}
}


});

