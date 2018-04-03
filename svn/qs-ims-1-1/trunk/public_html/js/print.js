sz_BaseUrl = location.protocol + '//' + location.host;

$(document).ready(function(){


    $( document ).ajaxSuccess(function( event, request, settings ) {
        $( "#print-area table.odd_even_table tr:even").find('td').addClass('bglightblue');
    });
});




function printPage()
{
	var headstr = "<html><head><title>Quasoft CMMS</title><link rel=\"stylesheet\" type=\"text/css\" href=\"/css/print.css\"/></head><body><div id='print-html'>";
	var footstr = "</div></body>";
	var newstr = $('#print-content').html();
	myWindow=window.open('');
	myWindow.document.write(headstr);
	myWindow.document.write(newstr);
	myWindow.document.write(footstr);
	myWindow.document.close();
	myWindow.focus();
	$(myWindow.document).find('.no-print').hide();
	myWindow.onload = function(){
		  // your code...
		if(navigator.userAgent.toLowerCase().indexOf('chrome') > -1){
			myWindow.print();
		}
	};
	
	//print(myWindow);
	//window.print();
	//myWindow.close();
	return;
	
	$('[type=text], textarea').each(function(){ this.defaultValue = this.value; });
	$('[type=checkbox], [type=radio]').each(function(){ this.defaultChecked = this.checked; });
	$('select option').each(function(){ this.defaultSelected = this.selected; });
	var oldstr = $('#leftside').html();
	//document.body.innerHTML = headstr+newstr+footstr;
	//window.print();
	$('#leftside').html(oldstr);
	//$('body').trigger('create');
	//$(window).load();
	//document.body.innerHTML = oldstr;
	return false;
}
function excelRender()
{
	var url = '/report/index/excel?popup=1';
	var data = {};
	data['xmg'] = tagToJSON('xmg');
	data['excel'] = tagToJSON('excel');
	data['xls'] = tagToJSON('xls');
	var content = JSON.stringify(data);
	$('#content').val(content);
	$('#excel').attr('action',url);
	$('#excel').submit();
}
function tagToJSON(tag){
	var data = {};
	$(tag).each(function(index){
		var item = {};
		var nostyle = parseInt($(this).attr('nostyle')) || 0;
		$.each(this.attributes,function(){
			if(nostyle == 0 || this.name == 'nostyle' || this.name == 'column' || this.name == 'row'){
				item[this.name] = this.value;
			}
		});
		item['value'] = $(this).text();
		data[index] = item;
	});
	return data;
}
function pdfRender()
{
	var url = '/report/index/pdf';
	var data = {};
	data['xmg'] = tagToJSON('xmg');
	data['excel'] = tagToJSON('excel');
	data['xls'] = tagToJSON('xls');
	var content = JSON.stringify(data);
	$('#content').val(content);
	$('#excel').attr('action',url);
	$('#excel').submit();
}
$.ctrl = function(key, callback, args) {
	$(document).keydown(function(e) {
		if (!args)
			args = []; // IE barks when args is null
		if (e.keyCode == key.charCodeAt(0) && e.ctrlKey) {
			callback.apply(this, args);
			return false;
		}
	});
};
$.ctrl('E', function() {
	excelRender();
	return false;
});
$.ctrl('D', function() {
	pdfRender();
	return false;
});
$.ctrl('P', function() {
	printPage();
	return false;
});
function saveXLS()
{
	var url = sz_BaseUrl + '/report/index/save/excel';
	var data = {};
	data['xmg'] = tagToJSON('xmg');
	data['excel'] = tagToJSON('excel');
	data['xls'] = tagToJSON('xls');
	var content = JSON.stringify(data);
	qssAjax.call(url, {content:content,name:$('#qss_notice').attr('title')}, function(jreturn) {
		qssAjax.notice(Language.translate('DOCUMENT_SAVE'));
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function savePDF()
{
	var url = sz_BaseUrl + '/report/index/save/pdf';
	var data ={content: $("body").html()};
	qssAjax.call(url, data, function(jreturn) {
		qssAjax.alert(Language.translate('DOCUMENT_SAVE'));
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}

(function ($) {
  $.fn.tooltip = function () {
	  $(this).click(function(){
		  var title = $(this).attr('title');
		  title = title.replace(/(?:\r\n|\r|\n)/g, '<br />');
		  
		  
		  $('#qss_event').html(title);
		  $('#qss_event').dialog();
		  
	  });
  }
})(jQuery);