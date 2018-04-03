try {
	document.execCommand('BackgroundImageCache', false, true);
} 
catch (e) {
	
}
sz_BaseUrl = location.protocol + '//' + location.host;
element = null;
var bLS = window.localStorage;// undefined;//;
function loadModule(url) {
	var data = {
		1 : 1
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#rightside').html(jreturn);
	});
}

var mouse_is_inside = false;
var mouse_is_click = false;
function resize(){
	//$('#leftside').height($(window).height() - 67);
	var width = $(window).width();
	if($('#header-box').length){
		if($('#horizontal-toolbar').length){
			$('#view').height($(window).height() - 107);
		}
		else{
			$('#view').height($(window).height() - 85);
		}
	}
	else{
		$('#view').height($(window).height() - 49);
	}
}
$(window).resize(function() {
	resize();
	});

$(document).ready(function() {
	resize();
	$('form[name=filter_form] :input').each(function(){
		$(this).keypress(function(e){
			 if(e.keyCode == 13) {
				 $('form[name=filter_form]')[0].onsubmit();
			    }
		});
	});
	$('#searchModule').keypress(function(e){
		if(e.keyCode == 13) {
			searchModule();
		 }
	});
	$('#mask').click(function(event){
	    event.stopPropagation();
	});
	$('.datepicker').each(function(){
		$(this).datepicker({ 
			dateFormat: "dd-mm-yy" ,
			onClose: function(dateText, inst) { 
		        $(this).attr("readonly", false);
		    },
		    beforeShow: function(input, inst) {
		        $(this).attr("readonly", true);
		    }
	    });
	});
});
var row = null;
var select = null;
var edit = false;//object grid edit
var fedit = false;//form edit change
var oedit = false;//popup object
var esc = true;
var ctrlPress = false;
var shiftPress = false;
$(window).bind('beforeunload', function(e){ 
	if(fedit || edit || oedit){
		return Language.translate('CONFIRM_SAVE');
	}
});
function saveLocalData(){
	if(fedit){
		if(bLS){
			var lastActiveModule = $.cookie('lastActiveModule');
			var form = document.forms[0];
			var data = $(form).serializeArray();
			
			$(form).find('input[type=checkbox]').each(function(){
				if(!$(this).is(':checked')){
					data.push({name:$(this).attr('name'),value:false});
				}
			});
			/*var print = {name:'print-area',value:$('#print-area').html()};
			data.push(print);*/
			data = JSON.stringify(data);
			localStorage.setItem(lastActiveModule, data);
		}
		fedit = false;
	}
}
// Err
$(document).click(function(event) {
	if (!$(event.target).closest('ul').hasClass('dropdown-content')) {
		$('.dropdown-content').hide();
	}
	if (!$(event.target).closest('ul').hasClass('dropdown-action')) {
		$('.dropdown-action').hide();
	}
	if (!$(event.target).hasClass('notify')) {
		$('.notify1').removeClass('notify_active');
		$('.notify2').removeClass('notify_active');
		$('.notify3').removeClass('notify_active');
		$('#qss_notify').hide();
	}
	if(ctrlPress || shiftPress){
		document.getSelection().removeAllRanges();
	}
});
$(document).keyup(function(e) {
	ctrlPress = false;
	shiftPress = false;
});
$(document).keydown(function(e) {
	
	if(e.keyCode == 113 && $(".ui-dialog").is(":visible") == false){
		if(!edit && $('.grid_edit').length){
			edit = true;
			editSelectedRow();
			toggleFormSave();
		}
		return false;
	}
	if(e.keyCode == 114 && $(".ui-dialog").is(":visible") == false){
		if($('.grid_edit').length){
			edit = true;
			insertRow();
			toggleFormSave();
		}
		return false;
	}
	if(e.keyCode == 115 && $(".ui-dialog").is(":visible") == false){
		if(!edit && $('.grid_edit').length){
			edit = true;
			editAllRow();
			toggleFormSave();
		}
		return false;
	}
	if (e.keyCode == 17) {
		ctrlPress = true;
	}
	if (e.keyCode == 16) {
		shiftPress = true;
	}
	if(e.keyCode == 112)
    {
        showHelper();
        return false;
    }
	if($("#qss_trace").dialog('isOpen')===true 
			|| $("#qss_event").dialog('isOpen')===true 
			|| $(".tag-container").is(':visible')===true) {
			return;
	}
    if($(".tag-container").is(':visible')===true || (!esc)){
		if (e.keyCode == 13) {
			$(select).dblclick();
		}
		return;
	}
	if(e.keyCode == 27 && edit){
		if(esc){
			edit = false;
			cancelRow();
			toggleFormSave();
		}
		else{
			esc = true;
		}
	}
    if(select){
                
		switch (e.keyCode) {
		case 38:
			newrow = $(select).prev();
			break;
		case 40:
			newrow = $(select).next();
			break;
		}
		if (newrow && newrow.attr('id')) {
			selectCLK(newrow[0]);
		}
		if (e.keyCode == 13) {
			$(select).dblclick();
		}
		return;
	}
	if (row) {
		var newrow = null;
		switch (e.keyCode) {
		case 38:
			newrow = $(row).prev();
			break;
		case 40:
			newrow = $(row).next();
			break;
		}
		if (newrow && newrow.attr('id')) {
			rowCLK(newrow[0]);
			if($(newrow).offset().top >= $(newrow).parent().parent().parent().offset().top && $(newrow).offset().top <= ($(newrow).parent().parent().parent().offset().top +$(newrow).parent().parent().parent().height() - $(newrow).height())){
				e.preventDefault();
			}
			
		}
		if (e.keyCode == 13 && !e.shiftKey) {
			if(e.target.nodeName == 'BODY'){
				$(row).dblclick();
			}
		}
	}
});

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
$.ctrl('S', function() {
	$('#searchModule').focus();
	if($('#btnCUSTOMSAVE').length && $('#btnCUSTOMSAVE').is(':visible')){
		$('#btnCUSTOMSAVE').click();
	}
	else if($('#btnSAVE').length){
		$('#btnSAVE').click();
	}
	else if(edit && esc){
		save(row);
	}
	return false;
});
/*$.ctrl('F', function() {
	   $('#searchModule').focus();
	   //grid_editing = false;
	   return false;
	});
*/
function showNotify(event) {
	if ($(event.target).attr('id') == 'detail_notify' || $(event.target).attr('id') == 'content_notify') {
		event.stopPropagation();
		return;
	}
	if($('.notify1').hasClass('notify_active')){
		$('.notify1').removeClass('notify_active');
		$("#qss_notify").hide();
	}
	else{
		$('.notify1').removeClass('notify_active');
		$('.notify2').removeClass('notify_active');
		$('.notify3').removeClass('notify_active');
		var url = sz_BaseUrl + '/user/statistic/notify';
		var data = {};
		qssAjax.getHtml(url, data, function(jreturn) {
			$('.notify1').addClass('notify_active');
			$('#qss_notify').removeClass('qss_notify2');
			$('#qss_notify').removeClass('qss_notify3');
			$('#qss_notify').html(jreturn);
			$("#qss_notify").show('blind', {
				to : {
					width : 280,
					height : 185
				}
			}, 100);
		});
	}
}
function showMessage(event) {
	if ($(event.target).attr('id') == 'qss_notify') {
		event.stopPropagation();
		return;
	}
	if($('.notify2').hasClass('notify_active')){
		$('.notify2').removeClass('notify_active');
		$("#qss_notify").hide();
	}
	else{
		$('.notify1').removeClass('notify_active');
		$('.notify2').removeClass('notify_active');
		$('.notify3').removeClass('notify_active');
		var url = sz_BaseUrl + '/user/statistic/warning';
		var data = {};
		qssAjax.getHtml(url, data, function(jreturn) {
			$('.notify2').addClass('notify_active');
			$('#qss_notify').addClass('qss_notify2');
			$('#qss_notify').removeClass('qss_notify3');
			$('#qss_notify').html(jreturn);
			$("#qss_notify").show('blind', {
				to : {
					width : 280,
					height : 185
				}
			}, 100);
		});
	}
}
var errorpageno = 1;
function showMoreNotifyError() {
	var url = sz_BaseUrl + '/user/statistic/warning1';
	var data = {pageno:errorpageno};
	qssAjax.getHtml(url, data, function(jreturn) {
		errorpageno++;
		$("#content_notify").append(jreturn);
	});
}
function showEvent(event) {
	if ($(event.target).attr('id') == 'detail_notify') {
		event.stopPropagation();
		return;
	}
	if($('.notify3').hasClass('notify_active')){
		$('.notify3').removeClass('notify_active');
		$("#qss_notify").hide();
	}
	else{
		$('.notify1').removeClass('notify_active');
		$('.notify2').removeClass('notify_active');
		$('.notify3').removeClass('notify_active');
		var url = sz_BaseUrl + '/user/statistic/event';
		var data = {};
		qssAjax.getHtml(url, data, function(jreturn) {
			$('.notify3').addClass('notify_active');
			$('#qss_notify').addClass('qss_notify3');
			$('#qss_notify').removeClass('qss_notify2');
			$('#qss_notify').html(jreturn);
			$("#qss_notify").show('blind', {
				to : {
					width : 280,
					height : 185
				}
			}, 100);
		});
	}
}
function quickEvent(elid,eventid,ifid,eventtype,stepno,etid) {
	var url = sz_BaseUrl + '/user/event/create';
	var data = {elid:elid,eventid:eventid,ifid:ifid,eventtype:eventtype,stepno:stepno,etid:etid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$("#qss_notify").hide();
		$('.notify3').removeClass('notify_active');
		$('#qss_event').html(jreturn);
		$('#qss_event').dialog();
	});
}
function disabledLayout(){
	//set display to block and opacity to 0 so we can use fadeTo  
    $('#mask').css({ 'display' : 'block'});  
  
    //fade in the mask to opacity 0.8  
    $('#mask').fadeTo(100,1);  

}
function enabledLayout(){
	 //hide the mask  
   $('#mask').fadeOut(100);  
}
function getCaret(el) { 
  if (el.selectionStart) { 
    return el.selectionStart; 
  } else if (document.selection) { 
    el.focus(); 

    var r = document.selection.createRange(); 
    if (r == null) { 
      return 0; 
    } 

    var re = el.createTextRange(), 
        rc = re.duplicate(); 
    re.moveToBookmark(r.getBookmark()); 
    rc.setEndPoint('EndToStart', re); 

    return rc.text.length; 
  }  
  return 0; 
}
function openModule(code,url){
	//check localstorage
	var ready = false;
	disabledLayout();
	//saveLocalData();
	var detail = false;
	if(url == '' || url === undefined){
		if(code === 'M000'){
			ready = true;
			url = '/';
		}
		else{
			url = $('#'+code).val();
			if(url === undefined){
				qssAjax.alert('Bạn chưa được phân quyền mô đun ' + code,function(){
					ready = false;
				});
			}
			else{
				ready = true;
			}
		}
	}
	else{
		ready = true;
		detail = true;
	}
	if(ready){
		var lastActiveModule = $.cookie('lastActiveModule');
		//var curl = window.location.pathname + window.location.search;
		if(code != '' && !detail){
			var lurl = $.cookie(code);//get last url of last opened module
			if(lurl != '' && lurl !== undefined){
				url = lurl;
			}
		}
		$.cookie(code,url,{path:'/'});
		$.cookie('lastActiveModule',code,{path:'/'});
		
		if(code != 'M000'){
			//save order
			var order = $.cookie('moduleOrders');
			if(order != '' && order !== undefined){
				var parsed = JSON.parse(order);
				var arrOrder = new Array();
				for(var x in parsed){
					arrOrder.push(parsed[x]);
				}
			}
			else{
				var arrOrder = new Array();
			}
			var i = arrOrder.indexOf(code);
			if(i != -1){
				arrOrder.splice(i,1);	
			}
			arrOrder.push(code);
			var orderString  = JSON.stringify(arrOrder);
			$.cookie('moduleOrders', orderString,{path:'/'});
		}
		window.location.href = url;
	}
	enabledLayout();
}
function searchModule(){
	var code = $('#searchModule').val();
	if(code != '' && $('#'+code.toUpperCase()).length){
		openModule(code.toUpperCase());
	}
	else{
		qssAjax.alert('Bạn phải nhập mã mô đun');
	}
}
function closeModule(code){
	var last = $.cookie('lastActiveModule');
	$.removeCookie(code,{path:'/'});
	//delete order
	var order = $.cookie('moduleOrders');
	var idx = 0;
	if(order != '' && order !== undefined){
		var arrOrder = JSON.parse(order);
		idx = $.inArray(code,arrOrder);
		arrOrder.splice(idx,1);
		var orderString  = JSON.stringify(arrOrder);
		$.cookie('moduleOrders',orderString,{path:'/'});
	}
	
	if(code == last){
		$.removeCookie('lastActiveModule',{path:'/'});
		if(idx != 0 && idx != undefined){
			idx = idx - 1;
			var newmodule = arrOrder[idx];
			if(newmodule != '' && newmodule != undefined){
				openModule(newmodule);
			}
			else{
				openModule('M000');	
			}	
		}
		else{
			openModule('M000');	
		}
	}
	if(0 && bLS!==undefined ){
		localStorage.removeItem('html_'+code);
		localStorage.removeItem('head_'+code);
	}
	$('#page_'+code).parent().remove();
}

function logout()
{
    var url     = sz_BaseUrl + '/user/index/logout';
    var cookies = $.cookie();
    
    // remove all cookie
    for(var cookie in cookies) {
    	if(cookie != 'lang' && cookie != 'dept_id' && cookie != 'moduleOrders'){
    		$.removeCookie(cookie,{path:'/'});
    	}
    }
    
    // remove localStorage
    if(bLS!==undefined ){
    	localStorage.clear();
    }

    
    // redirect
    window.location.href = url;
}
function showHelper(){
	helpwin = window.open(sz_BaseUrl + '/extra/help/index#'+ $.cookie('lastActiveModule'), 'helper', 'toolbar=0,location=0,menubar=0');
	helpwin.focus();
	//window.open(sz_BaseUrl + '/extra/help/index#'+ file_in_handle[1], 'helper', 'toolbar=0,location=0,menubar=0');
}
function popupFormInsert(fid, dat, sFunction,sFunction2){
	var id = $.now();
	$('BODY').append('<div id='+id+' class="popup-edit">');
	var url = sz_BaseUrl + '/user/form/popup/edit';
	var data = {fid:fid,ifid:0,deptid:1} ;
	data=  $.extend({}, data, dat);
	$('#'+id).data('saved',false);
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#'+id).html(jreturn);
		$('#'+id).data('sFunction2',sFunction2);//trigger khi save
		if(sFunction){
			$('#'+id).dialog({ width: 900,height:450, close: function(){
        		if($(this).data('saved')){
        			sFunction(this);	
        		}
    			$(this).remove();
         	},
            beforeClose: function(event, ui) { 
            	var obj = window;
            	if(obj['dialog_'+fid]['fedit']){
            		var cfm = confirm(Language.translate('CONFIRM_SAVE'));
                	if(!cfm){
                		return false;
                	}
            	}
             }});
		}
		else{
			$('#'+id).dialog({ width: 900,height:450,close:function(){$(this).remove();}});
		}
	});	
}
function popupFormEdit(fid,ifid,deptid, dat,sFunction){
	var id = $.now();
	$('BODY').append('<div id='+id+' class="popup-edit">');
	var url = sz_BaseUrl + '/user/form/popup/edit';
	var data = {ifid:ifid,deptid:deptid} ;
	data=  $.extend({}, data, dat);
	$('#'+id).data('saved',false);
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#'+id).html(jreturn);
    	if(sFunction ){
    		$('#'+id).dialog({ width: 900,height:450,closeOnEscape: false, close: function(){
            	if($(this).data('saved')){
            		sFunction();	
            	}
            	$(this).remove();
            },
            beforeClose: function(event, ui) { 
            	var obj = window;
            	if(obj['dialog_'+fid]['fedit']){
            		var cfm = confirm(Language.translate('CONFIRM_SAVE'));
                	if(!cfm){
                		return false;
                	}
            	}
             }});
    	}
    	else{
    		$('#'+id).dialog({ width: 900,height:450,closeOnEscape: false,close:function(){$(this).remove();}});
        }
	});	
}
function popupFormDetail(ifid,deptid) {
	var id = $.now();
	$('BODY').append('<div id='+id+' class="popup-edit">');
	var url = sz_BaseUrl + '/user/form/detail';
	var data = {ifid:ifid,deptid:deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#'+id).html(jreturn);
		$('#'+id).dialog({ width: 900,height:450,close:function(){$(this).remove();} });
	});
}
function popupObjectInsert(ifid, deptid, objid, dat,sFunction){
	var id = $.now();
	$('BODY').append('<div id='+id+' class="popup-edit">');
	var url = sz_BaseUrl + '/user/object/popup/edit';
	var data = {ifid:ifid,deptid:deptid,objid:objid, ioid:0};
	if(dat){
		data=  $.extend({}, data, dat);
	}
	$('#'+id).data('saved',false);
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#'+id).html(jreturn);
	    if(sFunction){
	    	$('#'+id).dialog({ width: 900,height:450,closeOnEscape: false, close: function(){
            	if($(this).data('saved')){
            		sFunction(this);
            	}
            	$(this).remove();
            },
            beforeClose: function(event, ui) { 
            	if(oedit){
            		var cfm = confirm(Language.translate('CONFIRM_SAVE'));
                	if(!cfm){
                		return false;
                	}
                	oedit = false;
            	}
             }});
	    }
	    else{
	    	$('#'+id).dialog({ width: 900,height:450,closeOnEscape: false, close: function(){$(this).remove();}});
	    }
	});	
}
function popupObjectEdit(ifid, deptid, objid,ioid, dat, sFunction){
	var id = $.now();
	$('BODY').append('<div id='+id+' class="popup-edit">');
	var url = sz_BaseUrl + '/user/object/popup/edit';
	var data = {ifid:ifid,deptid:deptid,objid:objid,ioid:ioid};
	data=  $.extend({}, data, dat);
	$('#'+id).data('saved',false);
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#'+id).html(jreturn);
        if(sFunction){
        	$('#'+id).dialog({ width: 900,height:450,closeOnEscape: false, close: function(){
                	if($(this).data('saved')){
                		sFunction();
                	}
                	$(this).remove();
                },
                beforeClose: function(event, ui) { 
                	if(oedit){
                		var cfm = confirm(Language.translate('CONFIRM_SAVE'));
                    	if(!cfm){
                    		return false;
                    	}
                    	oedit = false;
                	}
                 }});
        }
        else{
        	$('#'+id).dialog({ width: 900,height:450,closeOnEscape: false,close:function(){$(this).remove();}});
        }
	});	
}
function deleteForm(ifid,deptid,sFunction) {
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/user/form/delete';
		var data = {
			ifid : ifid,
			deptid : deptid
		};
		qssAjax.call(url, data, function(jreturn) {
			if(sFunction){
				sFunction();
			}
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function deleteObject(ifid, deptid, objid,ioid,sFunction) {
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/user/object/delete';
		var data = {
			ifid : ifid,
			deptid : deptid,
			ioid : ioid,
			objid : objid
		};
		qssAjax.call(url, data, function(jreturn) {
                                                       if(sFunction)
                                                       {
			sFunction();
                                                        }
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
$( document ).ajaxStop(function() {
	enabledLayout();
});
function saveQuickAccess(fid,checked){
	var url = sz_BaseUrl + '/user/statistic/savequickaccess';
	var data = {fid:fid,checked:checked};
	qssAjax.call(url, data, function(jreturn) {
		url = '/user/statistic/detailuserquickaccess';
		qssAjax.getHtml(url, {}, function(jreturn) {
			$('#leftside').html(jreturn);
		});
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});	
}
function popupWindowFormEdit(ifid,deptid, dat,sFunction){
	var windowObjectReference;
	var strWindowFeatures = "modal=yes,menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes";
	windowObjectReference = window.open('/user/form/popup?ifid='+ifid+'&deptid='+deptid+'&popup=1&'+$.param(dat), "_blank", strWindowFeatures);
	window.callback = sFunction;
}

function popupWindowFormInsert(fid,deptid, dat,sFunction){
	var windowObjectReference;
	var strWindowFeatures = "modal=yes,menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes";
	windowObjectReference = window.open('/user/form/popup?fid='+fid+'&deptid='+deptid+'&popup=1&'+$.param(dat), "_blank", strWindowFeatures);
	window.callback = sFunction;
}
function createNew(org_objid,org_fieldid,fid,objid,fielid,ifid,json){
	if(ifid != 0){
		popupObjectInsert(ifid, 1, objid, $.parseJSON(json),function(el){
			var ifid = $(el).data('ifid');
			var ioid = $(el).data('ioid');
			if($(el).data('saved')){
				var url = sz_BaseUrl + '/user/field/lookup';
				var data = {org_objid:org_objid,org_fieldid:org_fieldid,fid:fid,objid:objid,fielid:fielid,ifid:ifid,ioid:ioid};
				qssAjax.call(url, data, function(jreturn) {
					//$('#' + jreturn.id).val(jreturn.value);
					$('#' + jreturn.id+'_textbox').val(jreturn.name);
					$('#' + jreturn.id).append('<option value="'+jreturn.value+'" selected>'+jreturn.name+'</option>');
					$('#' + jreturn.id).val(jreturn.value);
					$('#' + jreturn.id).change();
					$('#' + jreturn.id+'_textbox').blur();
					$('#' + jreturn.id+'_textbox').removeClass('bgpink');
					$('#' + jreturn.id+'_textbox').focus();
				});
			}
		});
	}
	else{
		popupFormInsert(fid, $.parseJSON(json), function(el) {
			var ifid = $(el).data('ifid');
			var ioid = $(el).data('ioid');
			if($(el).data('saved')){
				var url = sz_BaseUrl + '/user/field/lookup';
				var data = {org_objid:org_objid,org_fieldid:org_fieldid,fid:fid,objid:objid,fielid:fielid,ifid:ifid,ioid:ioid};
				qssAjax.call(url, data, function(jreturn) {
					//$('#' + jreturn.id).val(jreturn.value);
					$('#' + jreturn.id+'_textbox').val(jreturn.name);
					$('#' + jreturn.id).append('<option value="'+jreturn.value+'" selected>'+jreturn.name+'</option>');
					$('#' + jreturn.id).val(jreturn.value);
					$('#' + jreturn.id).change();
					$('#' + jreturn.id+'_textbox').blur();
					$('#' + jreturn.id+'_textbox').removeClass('bgpink');
					$('#' + jreturn.id+'_textbox').focus();
				});
			}
		});
	}
	return false;
}
function toggleFormSave(){
	if(fedit || edit){
		$('#btnSAVE').prop('disabled',false);
		$('#btnSAVEBACK').prop('disabled',false);		
	}
	else{
		$('#btnSAVE').prop('disabled',true);
		$('#btnSAVEBACK').prop('disabled',true);		
	}
}
function toggleGridCheckbox(el){
	if($(el).is(':checked')){
		row = null;
		$('.grid_checkbox').prop('checked',true);
		$('.grid_checkbox').parent().parent().addClass('grid_selected');
	}
	else{
		row = null;
		$('.grid_checkbox').parent().parent().removeClass('grid_selected');
		$('.grid_checkbox').prop('checked',false);
	}
	resetBtn();
}

// var time = new Date().getTime();
// $(document).bind("mousemove keypress", function(e) {
//     time = new Date().getTime();
// });
//
// function refreshHtml() {
// 	if(new Date().getTime() - time >= 60000) {
//     	window.location.reload(true);
//     }
//     else{
//     	setTimeout(refreshHtml, 1000);
//     }
// }
//
// setTimeout(refreshHtml, 1000);