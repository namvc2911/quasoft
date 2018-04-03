$(document).ready(function() {
	$('.grid_navigator td:first').hide();
	$(':text').on('focus', function() {
		$('#mobile-fix-form').height(200);
	});
	$(':text').on('blur', function() {
		$('#mobile-fix-form').height(0);
	});
	$('#menu').parent().find('a').click(function(){
		$(this).parent().parent().children('li').not($(this).parent()).each(function(){
			$(this).hide();
		});
		if($(this).parent().children('ul').first().is(':visible')){
			if($(this).attr('id')=='menu'){
				$('.overlay').hide();
				$('#header-menu *').removeAttr('style');
			}
			$(this).parent().children('ul').first().animate({width:'hide'},200);
		}
		else{
			if($(this).attr('id')=='menu'){
				$('.overlay').show();
			}
			$(this).parent().children('ul').first().animate({width:'show'},200);
		}
		 
	});
});
$(document).ajaxStop(function() {
	$('.grid_navigator td:first').hide();
	$('.ui-dialog').css("width", $(window).width()*9.9/10);
	$('.ui-dialog').css("left", 0);
	
	$(':text').on('focus', function() {
	    $('#mobile-fix-object').height(200);
	});
	$(':text').on('blur', function() {
		$('#mobile-fix-object').height(0);
	});
});
