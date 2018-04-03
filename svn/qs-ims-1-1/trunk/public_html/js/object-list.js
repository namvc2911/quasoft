var col = '';
var textcol = '';
var timer;
function resetBtn() {
	row = null;
	$('#btnOEDIT').prop('disabled',true);
	$('#btnDELETE').prop('disabled',true);
	$('#btnOTHER').prop('disabled',true);
	$('#btnODETAIL').prop('disabled',true);
	$('#btnUP').prop('disabled',true);
	$('#btnDOWN').prop('disabled',true);
	$('.extra-button-orecord').prop('disabled',true);
	var rights = 12;
	$('.grid_selected').each(function(){
		rights = rights & $(this).attr('rights');
	});
	if ((rights & 8)) {
		$('#btnDELETE').prop('disabled',false);
	}
	else {
		$('#btnDELETE').prop('disabled',true);
	}
	if ((rights & 4)) {
		$('#btnOEDIT').prop('disabled',false);
	}
	else {
		$('#btnOEDIT').prop('disabled',true);
	}
}

function rowCLK(el,event) {
	if(event !== undefined 
			&& ($(event.target).hasClass('grid_checkbox')||$(event.target).hasClass('nocount'))){
		ctrlPress = true;
		row = null;
	}
	if(el == row){
		return;
	}	
	if(!el){
		resetBtn();
		return;
	}
	if(shiftPress){
		//loop
		var start = $(row).index();
		var end = $(el).index();
		if(start > 0){
			//loop
			var diff = Math.abs(end - start);
			var newrow = row;
			while(diff){
				if(start > end){
					newrow = $(newrow).prev();
				}
				else{
					newrow = $(newrow).next();
				}
				$(newrow).addClass('grid_selected');
				$(newrow).find('.grid_checkbox').prop('checked',true);
				diff--;
			}
			resetBtn();
			if($('.grid_selected').length == 1){
				row = el;
			}
			return;
		}
	}
	else if(ctrlPress){
		if($(el).hasClass('grid_selected')){
			$(el).removeClass('grid_selected');
			$(el).find('.grid_checkbox').prop('checked',false);
		}
		else{
			$(el).addClass('grid_selected');	
			$(el).find('.grid_checkbox').prop('checked',true);
		}
		resetBtn();
		if($('.grid_selected').length == 1){
			row = el;
		}
		ctrlPress = false;
		return;
	}
	$('.grid_selected').each(function(){
		$(this).removeClass('grid_selected');
		$(this).find('.grid_checkbox').prop('checked',false);
	});
	$(el).addClass('grid_selected');
	$(el).find('.grid_checkbox').prop('checked',true);
	rights = $(el).attr('rights');
	if($(el).attr('id') == 0){
		rights = rights & 9;//cho cả truowngf hợp được xóa
	}
	if (rights & 1) {
		$('#btnOINSERT').prop('disabled',false);
		$('#btnOINSERT').prop('disabled',false);
	} else {
		$('#btnOINSERT').prop('disabled',true);
		$('#btnOINSERT').prop('disabled',true);
	}
	if (rights & 2) {
		$('#btnOTHER').prop('disabled',false);
		$('.extra-button-object-record').prop('disabled',false);
	} else {
		$('#btnOTHER').prop('disabled',true);
		$('.extra-button-object-record').prop('disabled',true);
	}
	if ((rights & 20) && $(el).attr('id') != 0) {
		$('#btnODETAIL').prop('disabled',false);
		$('#btnUP').prop('disabled',false);
		$('#btnODETAIL').prop('disabled',false);
		$('#btnDOWN').prop('disabled',false);
		$('#btnUP').prop('disabled',false);
		$('#btnDOWN').prop('disabled',false);
	} else {
		$('#btnODETAIL').prop('disabled',true);
		$('#btnUP').prop('disabled',true);
		$('#btnDOWN').prop('disabled',true);
		$('#btnODETAIL').prop('disabled',true);
		$('#btnUP').prop('disabled',true);
		$('#btnDOWN').prop('disabled',true);
	}
	if (rights & 8) {
		$('#btnDELETE').prop('disabled',false);
	} else {
		$('#btnDELETE').prop('disabled',true);
	}
	if (rights & 4) {
		$('#btnOEDIT').prop('disabled',false);
	} else {
		$('#btnOEDIT').prop('disabled',true);
	}
	if(row != el && $(row).attr('id') == 0 && !edit){
		$(row).remove();
	}
	row = el;
	row.id = $(el).attr('id'); 
	//grid_editing = true;
	$.cookie('object_selected', row.id,{path:'/'});
}
function rowInsert(ifid, deptid, objid) {
	if (objDisabled('btnOINSERT')) {
		return;
	}
	popupObjectInsert(ifid, deptid, objid, {},function(){
		rowObject(objid);
	});
}
function rowEdit(ifid, deptid, objid) {
	if (edit || objDisabled('btnOEDIT')) {
		return;
	}
	var ioid = [];
	if(row == null){
		var i = 0;
		$('.grid_selected').each(function(){
			ioid[i] = $(this).attr('id');
			i++;
		});
		var data = {ifid:ifid, deptid:deptid, objid:objid,ioid:ioid};
		var url = sz_BaseUrl + '/user/object/medit';
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#qss_dialog').html(jreturn);
			$('#qss_dialog').dialog({ width: 800,height:400 });
		});
	}
	else{
		ioid[0] = row.id;
		popupObjectEdit(ifid, deptid, objid,ioid[0], {}, function(){
			rowObject(objid);
		});
	}
//	window.location.href = sz_BaseUrl + '/user/object/edit?ifid=' + ifid
//			+ '&deptid=' + deptid + '&ioid=' + ioid + '&objid=' + objid;
      //  openModule('', sz_BaseUrl + '/user/object/edit?ifid=' + ifid
		//	+ '&deptid=' + deptid + '&ioid=' + ioid + '&objid=' + objid);
}
/*function rowObject(objid, ifid, deptid) {
//	window.location.href = sz_BaseUrl + '/user/object?ifid=' + ifid
//			+ '&deptid=' + deptid + '&objid=' + objid;
        openModule('', sz_BaseUrl + '/user/object?ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid);
                
}*/
function rowObject(objid,ele) {
	if (timer) clearTimeout(timer);
    timer = setTimeout(function() { 
    	if(!$(ele).hasClass('active')){
    		var ifid= 0;
        	var deptid = 0;
        	ifid = $('#ifid').val();
        	deptid = $('#deptid').val();
        	if(ifid == 0) {
        		qssAjax.alert(Language.translate('SAVE_FIRST'));
        		return;
        	}
        	var ref = $('#'+objid+'_ref_form_code').val();
        	if(ref != '' && ref !== undefined){
        		rowFormSearch(ref,ifid,objid);
        	}
        	else{
        		rowObjectSearch(ifid,deptid,objid);
        	}
    	}
    }, 200);    
	
}
function rowObjectToggle() {
	$('#qss_form').toggle();
	
}
function rowEvent(fid) {
	var url = 'http://' + location.host + '/user/calendar?fid='+fid;
//	window.location.href = url;
        openModule('', url);
}
function rowEvent1(ifid) {
	if (row == null) {
		return;
	}
	var url = 'http://' + location.host + '/user/event/create';
	var data = {ifid:ifid,ioid:row.id};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_event').html(jreturn);
		$('#qss_event').dialog({ width: 800,height:400 });
	});
}
function rowAction(act) {
	if (row == null) {
		return;
	}
//	window.location.href = 'form_action.php?params='
//			+ row.getAttribute('params') + '&epStaus=' + act;
        openModule('', 'form_action.php?params='
			+ row.getAttribute('params') + '&epStaus=' + act);
}
function rowImport(ifid, deptid, objid) {
	var url = sz_BaseUrl + '/user/import';
	var data = {ifid:ifid,deptid:deptid,objid:objid};
	qssAjax.getHtml(url, data, function(jreturn) {
	$('#qss_trace').html(jreturn);
    	$('#qss_trace').dialog({ width: 900,height:450});
	});	
}
function rowDelete(ifid, deptid, objid) {
	if (objDisabled('btnDELETE')) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var ioid = [];
		if(row == null){
			var i = 0;
			$('.grid_selected').each(function(){
				if($(this).attr('id') != 0){
					ioid[i] = $(this).attr('id');
					i++;
				}
				else{
					$(this).remove();
				}
			});
		}
		else{
			if(row.id == 0){
				$(row).remove();
			}
			else{
				ioid[0] = row.id;
			}
		}
		if(ioid.length == 0){
			return;
		}
		var url = sz_BaseUrl + '/user/object/delete';
		var data = {
			ifid : ifid,
			deptid : deptid,
			ioid : ioid,
			objid : objid
		};
		qssAjax.call(url, data, function(jreturn) {
			var newrow = null;
			newrow = $(row).next();
			if(!newrow || !newrow.attr('id')){
				newrow = $(row).prev();
			}
			if(newrow && newrow.attr('id')){
				$.cookie('object_selected', newrow.attr('id'),{path:'/'});
			}
			rowObjectSearch(ifid, deptid, objid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function rowObjectSearch(ifid, deptid, objid,gotoioid) {
	var tree = $('#qss_'+objid+'_tree').val();
	var pageno = $('#qss_'+objid+'_pageno').val();
	var perpage = $('#qss_'+objid+'_perpage').val();
	var groupby = $('#qss_'+objid+'_groupby').val();
	var url = sz_BaseUrl + '/user/object/grid/?ifid=' + ifid + '&deptid='
			+ deptid + '&objid=' + objid;
	var data = $('#object_' + objid + '_filter').serialize();
	if(gotoioid === undefined){
		gotoioid = 0;
	}
	qssAjax.getHtml(url, 'gotoioid=' + gotoioid + '&pageno=' + pageno + '&perpage=' + perpage  + '&groupby=' + groupby + '&tree=' + tree + '&'
			+ data, function(jreturn) {
		$('#qss_object').html(jreturn);
		$('#qss_'+objid+'_tree').val(0);
		if(objid != 0){
			$('.einfo_maintain_plan_tab').removeClass('active');
			$('#einfo_maintain_plan_tab_'+objid).addClass('active');
			$('.einfo_maintain_plan_tab').parent().removeClass('active');
			$('#einfo_maintain_plan_tab_'+objid).parent().addClass('active');
		}
		//resetBtn();
	});
}
function rowSearch(fid) {
	var url = sz_BaseUrl + '/user/form?fid=' + fid;
	var data = $('#form_' + fid + '_filter, #form_' + fid + '_extra').serialize();
	$.cookie('form_' + fid + '_filter','',{path:'/'});
	//alert(JSON.stringify(data));
	window.location.href = url+'&'+data;
}
function rowSort(ifid, deptid, objid, id) {
	var pageno = $('#qss_'+objid+'_pageno').val();
	var perpage = $('#qss_'+objid+'_perpage').val();
	var groupby = $('#qss_'+objid+'_groupby').val();
	var url = sz_BaseUrl + '/user/object/grid/?ifid=' + ifid + '&deptid='
			+ deptid + '&objid=' + objid;
	var data = $('#object_' + objid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id  + '&groupby=' + groupby + '&' + data, function(jreturn) {
		$('#qss_object').html(jreturn);
	});
}
function formBack(fid) {
	// window.location.href = sz_BaseUrl + '/user/form?fid=' + fid;
        openModule('',sz_BaseUrl + '/user/form?fid=' + fid);
}
function formEdit(ifid,deptid) {
	// window.location.href = sz_BaseUrl + '/user/form?fid=' + fid;
        openModule('',sz_BaseUrl + '/user/form/edit?ifid=' + ifid+'&deptid='+deptid);
}
function objectBack(ifid, deptid, objid) {
//	window.location.href = sz_BaseUrl + '/user/object?ifid=' + ifid
//			+ '&deptid=' + deptid + '&objid=' + objid;
        var url = sz_BaseUrl + '/user/object?ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid;
        openModule('',url);
}
function objectSave(ifid, deptid, ioid, objid, back) {
	ioid = ioid ? ioid : $('#ioid').val();
	var url = sz_BaseUrl + '/user/object/save?ifid=' + ifid + '&deptid='
			+ deptid + '&ioid=' + ioid + '&objid=' + objid;
	var data = $('#object_' + objid + '_edit').serialize();
	$('#object_' + objid + '_edit').find('input[type=checkbox]').each(function(){
		if(!$(this).is(':checked')){
			data += '&' + $(this).attr('name') +'=';
		}
	});
	qssAjax.call(url, data, function(jreturn) {
		if(jreturn.message != ''){
			qssAjax.notice(jreturn.message,function(){
				if(back){
					openModule('', sz_BaseUrl + '/user/object?ifid=' + ifid
					+ '&deptid=' + deptid + '&objid=' + objid);
				}
				if(!ioid || ioid == 0){
					$('#ioid').val(jreturn.status);
					$('.extra-button-orecord').removeClass('extra-disabled');
					$('.custom-button-field').prop('disabled',false);
				}
				fedit = false;
				if(bLS){
					var lastActiveModule = $.cookie('lastActiveModule');
					localStorage.removeItem(lastActiveModule);
				}		
			});
		}
		else{
			if(back){
				openModule('', sz_BaseUrl + '/user/object?ifid=' + ifid
				+ '&deptid=' + deptid + '&objid=' + objid);
			}
			if(!ioid || ioid == 0){
				$('#ioid').val(jreturn.status);
				$('.extra-button-orecord').removeClass('extra-disabled');
				$('.custom-button-field').prop('disabled',false);
			}
			fedit = false;
			if(bLS){
				var lastActiveModule = $.cookie('lastActiveModule');
				localStorage.removeItem(lastActiveModule);
			}	
		}
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function objectDuplicate(ifid, deptid, ioid, objid, back) {
	ioid = 0;
	var url = sz_BaseUrl + '/user/object/save?ifid=' + ifid + '&deptid='
			+ deptid + '&ioid=0' + '&objid=' + objid;
	var data = $('#object_' + objid + '_edit').serialize();
	$('#object_' + objid + '_edit').find('input[type=checkbox]').each(function(){
		if(!$(this).is(':checked')){
			data += '&' + $(this).attr('name') +'=';
		}
	});
	qssAjax.call(url, data, function(jreturn) {
		if(back){
//			window.location.href = sz_BaseUrl + '/user/object?ifid=' + ifid
//			+ '&deptid=' + deptid + '&objid=' + objid;
                        openModule('', sz_BaseUrl + '/user/object?ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid);
		}
		if(!ioid || ioid == 0){
			$('#ioid').val(jreturn.status);
		}
		qssAjax.notice('Bạn đã nhân đôi thành công');
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowIOExcel(ifid, deptid, objid) {
	if (objDisabled('btnEIMPORT')) {
		return;
	}
	var url = sz_BaseUrl + '/user/import/object';
	var data = {ifid:ifid,objid:objid,deptid:deptid};
	$('#qss_combo').data('saved',false);
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_combo').dialog('close');
		$('#qss_combo').html(jreturn);
		$('#qss_combo').dialog({ width: 800,height:400,close: function(){
			if($(this).data('saved')){
        		rowObject(objid);
        	}
		}});
	});
}
/*function rowEditRefresh(input) {
	var ifid = $('#ifid').val();
	var deptid = $('#deptid').val();
	var ioid = $('#ioid').val();
	var objid = $('#objid').val();
	var url = sz_BaseUrl + '/user/object/refresh?ifid=' + ifid + '&deptid='
			+ deptid + '&ioid=' + ioid + '&objid=' + objid;
	var data = $('#object_' + objid + '_edit').serialize();
	$('#object_' + objid + '_edit').find('input[type=checkbox]').each(function(){
		if(!$(this).is(':checked')){
			data += '&' + $(this).attr('name') +'=';
		}
	});
	qssAjax.getHtml(url, data, function(jreturn) {
		if(jreturn != ''){
			$('#qss_object').html(jreturn);
			$(input).focus();
			var val = $(input).val();
			$(input).val('');
			$(input).val(val);
		}
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}*/
function uploadFile(field) {
	/* Upload file by ajax */
	disabledLayout();
	$.ajaxFileUpload({
		url : sz_BaseUrl + '/user/field/uploadfile',
		secureuri : false,
		fileElementId : $('#' + field + '_file'),
		dataType : 'json',
		success : function(data, status) {
			/* Upload file successfully */
			if (data.error) {
				qssAjax.alert(data.message);
				disabledLayout();
			} else {
				$('#' + field).val(data.image);
				enabledLayout();
			}
		},
		error : function(data, status, e) {
			/* If upload error */
			qssAjax.alert(e);
		}
	});
}
function uploadPicture(field) {
	/* Upload file by ajax */
	disabledLayout();
	$.ajaxFileUpload({
		url : sz_BaseUrl + '/user/field/uploadpicture',
		secureuri : false,
		fileElementId : $('#' + field + '_picture'),
		dataType : 'json',
		success : function(data, status) {
			/* Upload file successfully */
			if (data.error) {
				qssAjax.alert(data.message);
				disabledLayout();
			} else {
				$('#' + field).val(data.image);
				$('#' + field).parent().find('img').attr('src','/user/field/picture?file='+data.image);
				enabledLayout();
			}
		},
		error : function(data, status, e) {
			/* If upload error */
			qssAjax.alert(e);
		}
	});
}
function showSearch() {
	if ($('#div-search').is(":visible")) {
		$('#div-search').hide();
		$('#btnSEARCH').removeClass('extra-selected');
	} else {
		var offset = $('#btnSEARCH').offset();
		var left = offset.left + $('#btnSEARCH').width() -  $('#div-search').width();
		$('#div-search').css({ left: left+'px'});
		$('#div-search').show();
		$('#btnSEARCH').addClass('extra-selected');
	}
}
function rowCleanSearch(fid) {
	$('form[name=filter_form] :input').each(function(){
		if($(this).is(':checkbox') || $(this).is(':radio')){
			$(this).prop('checked', false);
		}
		else{
			$(this).val('');
		}
	});
	rowSearch(fid);
}
function objDisabled(name)
{
	return $('#'+name).is('.btn-disabled');
}
function rowTrace(ifid,deptid,objid) {
	if (row == null || edit) {
		return;
	}
	var url = sz_BaseUrl + '/user/object/trace';
	var data = {ifid:ifid,deptid:deptid,objid:objid,ioid:row.id};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function downloadFile(file) {
	var url = sz_BaseUrl + '/user/field/downloadfile?file=' + file;
//	window.location.href = url;
    //openModule('', url);
	window.open(url);
}
function bashRun(ifid,id,name,el,objid) {
	var ioid = 0;
	if (row == null) {
		ioid = $('#ioid').val();
	}
	else if($(el).is('.extra-button-object-record')){
		ioid = row.id;
	}
	if ((ifid == 0 && $(el).is('.extra-button-object-record'))|| $(el).is('.extra-disabled')) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_PROCESS'),function(){
		var url = sz_BaseUrl + '/user/object/bash';
		var data = {ifid:ifid,id:id,ioid:ioid,objid:objid};
		qssAjax.call(url, data, function(jreturn) {
			qssAjax.notice('Bạn đã thực hiện xong: ' + name,function(){
				rowObjectSearch(ifid,1,objid);				
			});
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function popupOther(ifid,objid,path,el) {
	var ioid = 0;
	if($(el).parent().parent().is('tr')){
		row = $(el).parent().parent()[0]; 
	}
	if (row == null) {
		ioid = $('#ioid').val();
	}
	else if($(el).is('.extra-button-orecord') || $(el).is('.custom-button-field')){
		ioid = row.id;
	}//ioid == 0 && ($(el).is('.extra-button-record')||$(el).is('.custom-button-field'))
	if ($(el).is('.extra-disabled')) {
		return;
	}
	var url = sz_BaseUrl + path;
	//var data = {ifid:ifid,objid:objid,ioid:ioid};
	if($('#object_' + objid + '_edit').is(':visible')){
		var data = $('#object_' + objid + '_edit').serialize();
		$('#object_' + objid + '_edit').find('input[type=checkbox]').each(function(){
			if(!$(this).is(':checked')){
				data += '&' + $(this).attr('name') +'=';
			}
		});
	}
	else{
		if(row == null){
			var data = {ifid:ifid,objid:objid,ioid:ioid};
		}
		else{
			var data = {fid:$(row).attr('fid'),ifid:$(row).attr('ifid'),deptid:$(row).attr('deptid'),objid:$(row).attr('objid'),ioid:$(row).attr('id')};
			$(row).find('td').each(function(){
				var val = $(this).attr('value');
				if(val == '' || val === undefined){
					if($(this).attr('inputtype') == 3 || $(this).attr('inputtype') == 4 || $(this).attr('inputtype') == 11){
						val = $(this).attr('vid')+',0';
					}
					else{
						val = $(this).text();
					}	
				}
				data[$(this).attr('name')] = val;
		});
		}
	}
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_combo').dialog('close');
		$('#qss_combo').html(jreturn);
		$('#qss_combo').dialog({ width: 800,height:400 });
	});
}
function openOther(ifid,objid,path,el) {
	var ioid = 0;
	if (row == null) {
		ioid = $('#ioid').val();
	}
	else if($(el).is('.extra-button-orecord')){
		ioid = row.id;
	}
	if ((ioid == 0 && $(el).is('.extra-button-orecord')) || $(el).is('.extra-disabled')) {
		return;
	}
	var url = sz_BaseUrl + path+'?ifid='+ifid+'&objid='+objid+'&ioid='+ioid+'&popup=1';
	window.open(url,'_blank');
}
function rowOthers(ifid,objid) {
	var ioid = 0;
	if (row == null) {
		ioid = $('#ioid').val();
	}
	else{
		ioid = row.id;
	}
	if (ioid == 0 || objDisabled('btnOTHER')) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/other';
	var data = {ifid:ifid,objid:objid,ioid:ioid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 200,height:300 });
	});
}
/*function onKeyupRefresh() {
	clearInterval(timer);
	timer = setTimeout(function() { //then give it a second to see if the user is finished
		var ifid = $('#ifid').val();
		var deptid = $('#deptid').val();
		var ioid = $('#ioid').val();
		var objid = $('#objid').val();
		var url = sz_BaseUrl + '/user/object/refresh?ifid=' + ifid + '&deptid='
				+ deptid + '&ioid=' + ioid + '&objid=' + objid;
		var data = $('#object_' + objid + '_edit').serialize();
		$('#object_' + objid + '_edit').find('input[type=checkbox]').each(function(){
			if(!$(this).is(':checked')){
				data += '&' + $(this).attr('name') +'=';
			}
		});
		qssAjax.getHtml(url, data, function(jreturn) {
			if(jreturn != ''){
				$('#qss_object').html(jreturn);
			}
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
    }, 2000);
	
}*/
function rowProcessLog(ifid,deptid) {
//	window.location.href = sz_BaseUrl + '/user/process?ifid=' + ifid
//			+ '&deptid=' + deptid;
        openModule('', sz_BaseUrl + '/user/process?ifid=' + ifid
			+ '&deptid=' + deptid);
}
function rowEditRefresh(input,refresh) {
	if($(input).val()=='null'){
		var selected = $(input).find(':selected');
		org_fieldid = $(selected).attr('org_fieldid');
		org_objid = $(selected).attr('org_objid');
		fid = $(selected).attr('fid');
		objid = $(selected).attr('objid');
		fielid = $(selected).attr('fielid');
		ifid = $(selected).attr('ifid');
		json = $(selected).attr('json');
		createNew(org_objid,org_fieldid,fid,objid,fielid,ifid,json);
		$(input).val('');
		return false;
	}
	if(refresh == 1){
		var fid = $('#fid').val();
		var ifid = $('#ifid').val();
		var deptid = $('#deptid').val();
		var url = sz_BaseUrl + '/user/form/refresh?fid=' + fid + '&ifid=' + ifid
				+ '&deptid=' + deptid;
		var data = $('#form_' + fid + '_edit').serialize();
		$('#form_' + fid + '_edit').find('input[type=checkbox]').each(function(){
			if(!$(this).is(':checked')){
				data += '&' + $(this).attr('name') +'=';
			}
		});
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#qss_form').html(jreturn);
			$(input).focus();
			var val = $(input).val();
			$(input).val('');
			$(input).val(val);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	}
}
function rowUpDown(ifid,deptid,objid,type) {
	if (row == null || (isDisabled('btnUP') && isDisabled('btnDOWN'))) {
		return;
	}
	var ioid = row.id;
	var url = sz_BaseUrl + '/user/object/updown';
	var data = {
			ifid : ifid,
			deptid : deptid,
			objid:objid,
			ioid:ioid,
			type:type
	};
	qssAjax.call(url, data, function(jreturn) {
			rowObjectSearch(ifid,deptid,objid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
	});
}
function rowDocument(ele) {
	var ifid= 0;
	var deptid = 0;
	ifid = $('#ifid').val();
	deptid = $('#deptid').val();
	if(ifid == 0 || ifid =='') {
		qssAjax.alert(Language.translate('SAVE_FIRST'));
		return;
	}
	if (timer) clearTimeout(timer);
    timer = setTimeout(function() { 
    	if(!$(ele).hasClass('active')){
    		var url = sz_BaseUrl + '/user/form/document';
    		var data = {ifid:ifid,deptid:deptid};
    		qssAjax.getHtml(url, data, function(jreturn) {
    			$('#qss_object').html(jreturn);
    			//$('#qss_'+objid+'_tree').val(0);
    			$('.einfo_maintain_plan_tab').removeClass('active');
    			$('#einfo_maintain_plan_tab_document').addClass('active');
    			$('.einfo_maintain_plan_tab').parent().removeClass('active');
    			$('#einfo_maintain_plan_tab_document').parent().addClass('active');
    			$('#upload').file(function(inp) {
    				inp.id = inp.name = 'myFileInput';
    				
    				/* Upload file by ajax */
    				disabledLayout();
    				$.ajaxFileUpload({
    					url : sz_BaseUrl + '/static/m016/upload?folder='+$('#upload_folder').val(),
    					secureuri : false,
    					fileElementId : inp,
    					dataType : 'json',
    					success : function(data, status) {
    						/* Upload file successfully */
    						if (data.error) {
    							qssAjax.alert(data.message);
    							enabledLayout();
    						} else {
    							//attachDocument(data.id,row.id);
    							$('#document_id').val(data.id);
    							$('#document').val(data.name);
    							enabledLayout();
    						}
    					},
    					error : function(data, status, e) {
    						/* If upload error */
    						qssAjax.alert(e);
    					}
    				});
    			});
    		});   		
    	}
    }, 200);    
}
function rowSecure(ele) {
	var ifid= 0;
	var deptid = 0;
	ifid = $('#ifid').val();
	deptid = $('#deptid').val();
	if(ifid == 0 || ifid =='') {
		qssAjax.alert(Language.translate('SAVE_FIRST'));
		return;
	}
	if (timer) clearTimeout(timer);
    timer = setTimeout(function() { 
    	if(!$(ele).hasClass('active')){
    		var url = sz_BaseUrl + '/user/form/secure';
    		var data = {ifid:ifid,deptid:deptid};
    		qssAjax.getHtml(url, data, function(jreturn) {
    			$('#qss_object').html(jreturn);
    			//$('#qss_'+objid+'_tree').val(0);
    			$('.einfo_maintain_plan_tab').removeClass('active');
    			$('#einfo_maintain_plan_tab_secure').addClass('active');
    			$('.einfo_maintain_plan_tab').parent().removeClass('active');
    			$('#einfo_maintain_plan_tab_secure').parent().addClass('active');
    		});   		
    	}
    }, 200);    
}
function rowComment(ele) {
	var ifid= 0;
	var deptid = 0;
	ifid = $('#ifid').val();
	deptid = $('#deptid').val();
	if(ifid == 0 || ifid =='') {
		qssAjax.alert(Language.translate('SAVE_FIRST'));
		return;
	}
	if (timer) clearTimeout(timer);
    timer = setTimeout(function() { 
    	if(!$(ele).hasClass('active')){
    		var url = sz_BaseUrl + '/user/form/comments';
    		var data = {ifid:ifid,deptid:deptid};
    		qssAjax.getHtml(url, data, function(jreturn) {
    			$('#qss_object').html(jreturn);
    			//$('#qss_'+objid+'_tree').val(0);
    			$('.einfo_maintain_plan_tab').removeClass('active');
    			$('#einfo_maintain_plan_tab_comment').addClass('active');
    			$('.einfo_maintain_plan_tab').parent().removeClass('active');
    			$('#einfo_maintain_plan_tab_comment').parent().addClass('active');
    		});   		
    	}
    }, 200);    
}
function objectDocument(ifid,deptid,ioid) {
	var url = sz_BaseUrl + '/user/object/document';
	var data = {ifid:ifid,deptid:deptid,ioid:ioid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
		$('#upload').file(function(inp) {
			inp.id = inp.name = 'myFileInput';
			
			/* Upload file by ajax */
			disabledLayout();
			$.ajaxFileUpload({
				url : sz_BaseUrl + '/static/m016/upload?folder='+$('#upload_folder').val(),
				secureuri : false,
				fileElementId : inp,
				dataType : 'json',
				success : function(data, status) {
					/* Upload file successfully */
					if (data.error) {
						qssAjax.alert(data.message);
						enabledLayout();
					} else {
						//attachObjectDocument(data.id,row.id);
						$('#document_id').val(data.id);
						$('#document').val(data.name);
						enabledLayout();
					}
				},
				error : function(data, status, e) {
					/* If upload error */
					qssAjax.alert(e);
				}
			});
		});
	});
}
function rowFormSearch(fid,refifid,objid) {
		var url = sz_BaseUrl + '/user/fobject?fid=' + fid+'&refifid='+refifid;
		qssAjax.getHtml(url, {}, function(jreturn) {
			$('#qss_object').html(jreturn);
			$('#qss_form_tree').val(0);
			if(objid != 0){
				$('.einfo_maintain_plan_tab').removeClass('active');
				$('#einfo_maintain_plan_tab_'+objid).addClass('active');
				$('.einfo_maintain_plan_tab').parent().removeClass('active');
				$('#einfo_maintain_plan_tab_'+objid).parent().addClass('active');
			}
		});
}