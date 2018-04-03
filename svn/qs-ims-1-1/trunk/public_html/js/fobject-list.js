var col = '';
var textcol = '';
var timer;
function resetBtn() {
	row = null;
	$('#btnFObjectEDIT').prop('disabled',true);
	$('#btnFObjectDETAIL').prop('disabled',true);
	$('#btnFObjectWORKFLOW').prop('disabled',true);
	$('#btnFObjectSHARING').prop('disabled',true);
	$('#btnFObjectPRINT').prop('disabled',true);
	$('#btnFObjectVALIDATE').prop('disabled',true);
	$('#btnFObjectEVENT').prop('disabled',true);
	$('#btnFObjectEMAIL').prop('disabled',true);
	$('#btnFObjectDOCUMENT').prop('disabled',true);
	$('#btnFObjectACTIVITY').prop('disabled',true);
	$('#btnFObjectPROCESS').prop('disabled',true);
	$('#btnFObjectOTHER').prop('disabled',true);
	$('#btnFObjectUP').prop('disabled',true);
	$('#btnFObjectDOWN').prop('disabled',true);
	$('.extra-button-record').prop('disabled',true);
	$('#btnFObjectDELETE').prop('disabled',true);
	var rights = 0;
	var wf = false;
	var status = null;
	if($('.grid_selected').length ){
		wf = true;
		rights = 12;
	}
	$('.grid_selected').each(function(){
		rights = rights & $(this).attr('rights');
		if(status == null){
			status = $(this).attr('status');
		}
		else{
			if(status != $(this).attr('status')){
				wf = false;
			}
		}
	});
	if (rights & 8) {
		$('#btnFObjectDELETE').prop('disabled',false);
	} else {
		$('#btnFObjectDELETE').prop('disabled',true);
	}
	if (rights & 4) {
		$('#btnFObjectEDIT').prop('disabled',false);
	} else {
		$('#btnFObjectEDIT').prop('disabled',true);
	}
	if(wf){
		$('#btnFObjectWORKFLOW').prop('disabled',false);
	} else {
		$('#btnFObjectWORKFLOW').prop('disabled',true);
	}	
}

function rowFObjectCLK(el,event) {
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
		rights = rights & 9;
	}
	if (rights & 52) {
		$('#btnFObjectOTHER').prop('disabled',false);
		$('.extra-button-record').prop('disabled',true);
		$('.extra-button-record').each(function(){
			var ind = this;
			$.map($(this).attr('steps').split(','),function(val){
				if(parseInt(val) == parseInt(el.getAttribute('status'))){
					$(ind).removeClass('extra-disabled');
				}
			});
		});
	} else {
		$('#btnFObjectOTHER').prop('disabled',true);
		$('.extra-button-record').prop('disabled',true);
	}
	if ((rights | 15) && $(el).attr('id') != 0) {
		$('#btnFObjectDETAIL').prop('disabled',false);
		$('#btnFObjectVALIDATE').prop('disabled',false);
		$('#btnFObjectEMAIL').prop('disabled',false);
		$('#btnFObjectDOCUMENT').prop('disabled',false);
		$('#btnFObjectACTIVITY').prop('disabled',false);
		$('#btnFObjectEVENT').prop('disabled',false);
		$('#btnFObjectWORKFLOW').prop('disabled',false);
		$('#btnFObjectUP').prop('disabled',false);
		$('#btnFObjectDOWN').prop('disabled',false);
	} else {
		$('#btnFObjectDETAIL').prop('disabled',true);
		$('#btnFObjectVALIDATE').prop('disabled',true);
		$('#btnFObjectEMAIL').prop('disabled',true);
		$('#btnFObjectDOCUMENT').prop('disabled',true);
		$('#btnFObjectACTIVITY').prop('disabled',true);
		$('#btnFObjectEVENT').prop('disabled',true);
		$('#btnFObjectWORKFLOW').prop('disabled',true);
		$('#btnFObjectUP').prop('disabled',true);
		$('#btnFObjectDOWN').prop('disabled',true);
	}
	if (rights & 8) {
		$('#btnFObjectDELETE').prop('disabled',false);
	} else {
		$('#btnFObjectDELETE').prop('disabled',true);
	}
	if (rights & 4) {
		$('#btnFObjectEDIT').prop('disabled',false);
	} else {
		$('#btnFObjectEDIT').prop('disabled',true);
	}
	if (rights & 63) {
		$('#btnFObjectPROCESS').prop('disabled',false);
	} else {
		$('#btnFObjectPROCESS').prop('disabled',true);
	}
	if (rights & 48) {//owner or control
		$('#btnFObjectSHARING').prop('disabled',false);
	} else {
		$('#btnFObjectSHARING').prop('disabled',true);
	}
	/*if (rights & 128) {
		$('#btnFObjectREJECT').removeClass('btn_disabled');
	} else {
		$('#btnFObjectREJECT').addClass('btn_disabled');
	}*/
	$('#btnFObjectPRINT').prop('disabled',false);
	$('#btnFObjectSEARCH').prop('disabled',false);
	$('#btnFObjectREVERT').prop('disabled',false);
	$('#btnFObjectRESTORE').prop('disabled',false);
	//$('#btnFObjectEVENT').removeClass('btn_disabled');
	if(row != el && $(row).attr('id') == 0 && !edit){
		$(row).remove();
	}
	row = el;
	row.id = $(el).attr('id');
	$.cookie('fobject_selected', row.id,{path:'/'});
	if(row.id == 0){
		$('#form_detail_infor').html('');
	}
	else{
		var url = sz_BaseUrl + '/user/form/count';
		var data = {ifid:row.id,deptid:row.getAttribute('deptid')};
		qssAjax.silient(url, data, function(jreturn) {
			$.each(jreturn.message,function(k,v){
				$('#'+k).html(v); 
			});
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	}
	if($('.grid_selected').length > 1){
         $('.grid').css('user-select', 'none');
         $('.grid').on('selectstart', false);
	}
	//grid_editing = true;
}

function rowFObjectInsert(fid,deptid,data,refifid) {
	if (isDisabled('btnFObjectINSERT')) {
		return;
	}
	popupWindowFormInsert(fid,deptid,data,function(){
		rowFormSearch(fid,refifid);
	});
}
function rowFObjectEdit(fid,refifid) {
	if (edit || isDisabled('btnEDIT')) {
		return;
	}
	var ifid = [];
	var deptid = [];
	if(row == null){
		var i = 0;
		$('.grid_selected').each(function(){
			ifid[i] = $(this).attr('id');
			deptid[i] = $(this).attr('deptid');
			i++;
		});
		var data = {ifid:ifid,deptid:deptid};
		var url = sz_BaseUrl + '/user/form/medit';
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#qss_trace').html(jreturn);
			$('#qss_trace').dialog({ width: 800,height:400 });
		});
	}
	else{
		ifid[0] = row.id;
		deptid[0] = row.getAttribute('deptid');
		popupWindowFormEdit(ifid[0],deptid[0], {},function(){
			rowFormSearch(fid,refifid);
		});
	}
}
function rowFObjectTrace() {
	if (edit) {//|| isDisabled('btnDETAIL')
		return;
	}
	var ifid= 0;
	var deptid = 0;
	if (row == null) {
		ifid = $('#ifid').val();
		deptid = $('#deptid').val();
	}
	else
	{
		ifid = row.id;
		deptid = row.getAttribute('deptid');
	}
	if(ifid == 0 || ifid ==''){
		return;
	}		
	var url = sz_BaseUrl + '/user/form/trace';
	var data = {
			ifid : ifid,
			deptid : deptid
		};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function rowEvent(fid) {
	var url = sz_BaseUrl + '/user/calendar?fid='+fid;
//	window.location.href = url;
        openModule('', url);
}
function rowEvent1() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/user/event/create';
	var data = {ifid:row.id,ioid:0};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_event').html(jreturn);
		$('#qss_event').dialog({ width: 800,height:400 });
	});
}
function rowDetail() {
	if (row == null || isDisabled('btnDETAIL')) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/detail';
	var data = {ifid:row.id,deptid:row.getAttribute('deptid')};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 900,height:450 });
	});
}
function rowFObjectPrint() {
	if (row == null || isDisabled('btnPRINT')) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/print?ifid=' + row.id + '&deptid='
			+ row.getAttribute('deptid');
	var check_url = sz_BaseUrl + '/user/form/print/check?ifid=' + row.id + '&deptid=' + row.getAttribute('deptid');
	var params = [
	              'height='+screen.height,
	              'width='+screen.width,
	              'fullscreen=yes', // only works in IE, but here for completeness
	              'maximize=yes' 
	          ].join(',');
	qssAjax.getHtml(check_url, {}, function(jreturn) {
		if(/<[a-z][\s\S]*>/i.test(jreturn)){
			$('#qss_trace').html(jreturn);
			$('#qss_trace').dialog({ width: 200,height:300 });	
		}
		else{
			window.open(jreturn + '&ifid=' + ifid + '&deptid=' + deptid, '_blank',
			params);
		}
	});
}
function rowExcel(fid) {
	if (!fid) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/excel?fid=' + fid;
//	window.location.href = url;	
        openModule('', url);
}
function rowSharing() {
	if (isDisabled('btnSHARING')) {
		return;
	}
	var ifid= 0;
	var deptid = 0;
	if (row == null) {
		ifid = $('#ifid').val();
		deptid = $('#deptid').val();
	}
	else
	{
		ifid = row.id;
		deptid = row.getAttribute('deptid');
	}
	if(ifid == 0 || ifid ==''){
		return;
	}		
	var url = sz_BaseUrl + '/user/form/sharing';
	var data = {
		ifid : ifid,
		deptid : deptid
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function rowForceSharing() {
        if (isDisabled('btnSHARING')) {
		return;
	}
	var ifid= 0;
	var deptid = 0;
	if (row == null) {
		ifid = $('#ifid').val();
		deptid = $('#deptid').val();
	}
	else
	{
		ifid = row.id;
		deptid = row.getAttribute('deptid');
	}
	if(ifid == 0 || ifid ==''){
		return;
	}	

	var url = sz_BaseUrl + '/user/form/sharing';
	var data = {ifid:ifid,deptid:deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function formSharing(ifid, deptid) {
    
	var url = sz_BaseUrl + '/user/form/dosharing?ifid=' + ifid + '&deptid='
			+ deptid;
	var data = $('#form_sharing').serialize();
	qssAjax.call(url, data, function(jreturn) {
		rowForceSharing();
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function deleteSharing(ifid, deptid,uid) {
	var url = sz_BaseUrl + '/user/form/deletesharing?ifid=' + ifid + '&deptid='
			+ deptid;
	var data = {uid :uid};
	qssAjax.call(url, data, function(jreturn) {
		rowForceSharing();
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function updateSharing(ifid, deptid,uid,status) {
	var url = sz_BaseUrl + '/user/form/updatesharing?ifid=' + ifid + '&deptid='
	+ deptid;
	var data = {uid :uid,status:status};
	qssAjax.call(url, data, function(jreturn) {
	rowTrace();
	}, function(jreturn) {
	qssAjax.alert(jreturn.message);
});
}
function rowFObjectWorkflow(el,refifid) {
	if (isDisabled('btnWORKFLOW')) {
		return;
	}
	var ifid = [];
	var deptid = [];
	if(row == null){
		var i = 0;
		$('.grid_selected').each(function(){
			ifid[i] = $(this).attr('id');
			deptid[i] = $(this).attr('deptid');
			i++;
		});
	}
	else{
		ifid[0] = row.id;
		deptid[0] = row.getAttribute('deptid');
	}
	
	if($(el).parent().find('.dropdown-content').is(':visible')){
		$(el).parent().find('.dropdown-content').hide();
	}
	else{
		var url = sz_BaseUrl + '/user/fobject/workflow';
		var data = {
			ifid : ifid,
			deptid : deptid,
			refifid:refifid
		};
		qssAjax.getHtml(url, data, function(jreturn) {
			//$('#qss_trace').html(jreturn);
			//$('#qss_trace').dialog({ width: 600,height:400 });
			$(el).parent().find('.dropdown-content').html(jreturn);
			$(el).parent().find('.dropdown-content').show();
		});
		
	}
	return;
	var url = sz_BaseUrl + '/user/form/workflow';
	var data = {
		ifid : ifid,
		deptid : deptid
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		//$('#qss_trace').html(jreturn);
		//$('#qss_trace').dialog({ width: 600,height:400 });
		
	});
}

function rowFObjectSearch(fid,gotoifid,refifid) {
	if ($('#qss_form_pageno').length) {
		var tree = $('#qss_form_tree').val();
		var pageno = $('#qss_form_pageno').val();
		var perpage = $('#qss_form_perpage').val();
		var groupby = $('#qss_form_groupby').val();
		var status = $('#qss_form_status').val();
		var uid = $('#qss_form_uid').val();
		var url = sz_BaseUrl + '/user/fobject/grid/?fid=' + fid;
		var data = $('#form_' + fid + '_filter, #form_' + fid + '_extra').serialize();
		resetBtn();
		if(gotoifid === undefined){
			gotoifid = 0;
		}
		qssAjax.getHtml(url, 'gotoifid=' + gotoifid + '&pageno=' + pageno + '&perpage=' + perpage + '&groupby=' + groupby + '&status=' + status + '&uid=' + uid + '&tree=' + tree + '&refifid='+ refifid +'&' 
				+ data, function(jreturn) {
			$('#qss_fobject').html(jreturn);
			$('#qss_fobject_tree').val(0);
		});
	}
}
function rowFObjectDelete(fid,refifid) {
	if (isDisabled('btnDELETE')) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var ifid = [];
		var deptid = [];
		if(row == null){
			var i = 0;
			$('.grid_selected').each(function(){
				if($(this).attr('id') != 0){
					ifid[i] = $(this).attr('id');
					deptid[i] = $(this).attr('deptid');
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
				ifid[0] = row.id;
				deptid[0] = row.getAttribute('deptid');
			}
		}
		if(ifid.length == 0){
			return;
		}
		var url = sz_BaseUrl + '/user/form/delete';
		var data = {
			ifid : ifid,
			deptid : deptid
		};
		qssAjax.call(url, data, function(jreturn) {
			var newrow = null;
			newrow = $(row).next();
			if(!newrow || !newrow.attr('id')){
				newrow = $(row).prev();
			}
			if(newrow && newrow.attr('id')){
				$.cookie('fobject_selected', newrow.attr('id'),{path:'/'});
			}
			rowFObjectSearch(fid,0,refifid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}

function rowFObjectSort(fid, id,refifid) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var status = $('#qss_form_status').val();
	var uid = $('#qss_form_uid').val();
	var url = sz_BaseUrl + '/user/fobject/grid/?fid=' + fid;
	var data = $('#form_' + fid + '_filter, #form_' + fid + '_extra').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id + '&groupby=' + groupby + '&status=' + status + '&uid=' + uid + '&refifid='+refifid + '&' + data, function(jreturn) {
		$('#qss_fobject').html(jreturn);
	});
}
function formEdit(fid, deptid) {
	var url = sz_BaseUrl + '/user/form/edit?ifid=' + fid + '&deptid=' + deptid;
	//window.location.href = url;
        openModule('',url);
}
function formBack(fid) {
	var url = sz_BaseUrl + '/user/form?fid=' + fid;
	//window.location.href = url;
        openModule('',url);
}
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
				enabledLayout();
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
				enabledLayout();
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
function downloadFile(file) {
	var url = sz_BaseUrl + '/user/field/downloadfile?file=' + file;
//	window.location.href = url;
	//openModule('', url);
	window.open(url);
}
function deleteFile(fid,objid,fieldid,ifid,ioid,file) {
	var url = sz_BaseUrl + '/user/field/deletefile';
	var data = {fid:fid,objid:objid,fieldid:fieldid,ifid:ifid,ioid:ioid,file:file};
	qssAjax.call(url, data, function(jreturn) {
		rowFObjectSearch(fid);
	}, function(fid) {
		qssAjax.alert(jreturn.message);
	});
}
function showPicture(id, file, deptid) {
	var url = sz_BaseUrl + '/user/field/picture?file=' + file + '&deptid='
			+ deptid;
	$('#' + id).attr('src', url);
}
function showBarcode(id, file) {
	var url = sz_BaseUrl + '/user/field/barcode?' + file;
	$('#' + id).attr('src', url);
}
function reloadUserStep(ifid, deptid, step, field,el) {
	if($(el).attr('canselect') == 0){
		$('#' + field).val(0);
		$('#' + field).attr('disabled',true);
		$('#comment').removeAttr('disabled');
		return;
	}
	var url = sz_BaseUrl + '/user/form/get/user/step';
	data = {
		ifid : ifid,
		deptid : deptid,
		step : step
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#' + field).html(jreturn);
		$('#' + field).removeAttr('disabled');
		$('#comment').removeAttr('disabled');
	});
}
function reloadUserGroup(groupid,field) {
	if(groupid == 0){
		return;
		}
	var url = sz_BaseUrl + '/user/form/getUserGroup';
	data = {
		groupid : groupid
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#' + field).html(jreturn);
		$('#user_id').removeAttr('disabled');
	});
}
function rowImport(fid, deptid, objid) {

//	window.location.href = sz_BaseUrl + '/user/import?fid=' + fid + '&deptid='
//			+ deptid + '&objid=' + objid;
        openModule('', sz_BaseUrl + '/user/import?fid=' + fid + '&deptid='
			+ deptid + '&objid=' + objid);
}
function rowEditReload(fid) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var url = sz_BaseUrl + '/user/form/grid/?fid=' + fid;
	var data = $('#form_' + fid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage + '&'
			+ data, function(jreturn) {
		$('#qss_form').html(jreturn);
	});
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
function showFObjectSearch() {
	if ($('#div-fobject-search').is(":visible")) {
		$('#div-fobject-search').hide();
		$('#btnFObjectSEARCH').removeClass('extra-selected');
	} else {
		//var offset = $('#btnFObjectSEARCH').offset();
		//var left = offset.left + $('#btnFObjectSEARCH').width() -  $('#div-search').width();
		if($('#horizontal-sub-toolbar').length != 0){//mobile không có
			var offset = $('#horizontal-sub-toolbar').offset();
			$('#div-fobject-search').css({ right: '12px'});
			$('#div-fobject-search').css({ top: offset.bottom + 'px'});
		}
		else{
			var offset = $('.mobile-box-wrap').offset();
			$('#div-fobject-search').css({ top: 36-offset.top + 'px'});
		}
		$('#div-fobject-search').show();
		$('#btnFObjectSEARCH').addClass('extra-selected');
	}
}
function rowCleanSearch(fid,refifid) {
	$('form[name=filter_form] :input').each(function(){
		if($(this).is(':checkbox') || $(this).is(':radio')){
			$(this).prop('checked', false);
		}
		else{
			$(this).val('');
		}
	});
	rowFObjectSearch(fid,0,refifid);
}
function isDisabled(name)
{
	return $('#'+name).is('.btn_disabled');
}
function saveComment(event,ifid, deptid,uid) {
	var comment = $('#comment').val();
	var keycode = event.which?event.which:event.keyCode;
	if (keycode == '13' && !event.shiftKey) {
			$('#comment').attr('disabled',true);
			var url = sz_BaseUrl + '/user/form/comment';
			var data = {
				ifid : ifid,
				deptid: deptid,
				uid: uid,
				comment : comment
			};
			qssAjax.call(url, data, function(jreturn) {
				rowTrace();
			}, function(jreturn) {
				qssAjax.alert(jreturn.message);
			});
	}
}
function rowEmail() {
	if (row == null || isDisabled('btnEMAIL')) {
		return;
	}
//	window.location.href = sz_BaseUrl + '/user/mail?ifid=' + row.id;
        openModule('', sz_BaseUrl + '/user/mail?ifid=' + row.id);
}
function rowReport(fid) {
	var url = sz_BaseUrl + '/user/report?fid=' + fid;
	qssAjax.getHtml(url, {}, function(jreturn) {
		if(jreturn!=''){
			$('#qss_trace').html(jreturn);
			$('#qss_trace').dialog({ width: 800,height:400 });	
		}
	});
}

function rowProcess() {
	var ifid = 0;
	if (row == null) {
		ifid = $('#ifid').val();
	}
	else{
		ifid = row.id;
	}
	if (ifid == 0) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_PROCESS'),function(){
		var url = sz_BaseUrl + '/user/process/run';
		var data = {ifid:ifid};
		qssAjax.call(url, data, function(jreturn) {
			
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function rowProcessLog() {
	if (row == null || row.id == 0) {
		qssAjax.alert(Language.translate('SELECT_ROW'));
		return;
	}
//	window.location.href = sz_BaseUrl + '/user/process?ifid=' + row.id
//			+ '&deptid=' + row.getAttribute('deptid');
        openModule('', sz_BaseUrl + '/user/process?ifid=' + row.id
			+ '&deptid=' + row.getAttribute('deptid'));
}
function rowOthers() {
	var ifid = 0;
	if (row == null) {
		ifid = $('#ifid').val();
	}
	else if($(el).is('.extra-button-record')){
		ifid = row.id;
	}
	if (ifid == 0 || isDisabled('btnOTHER')) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/other';
	var data = {ifid:ifid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 200,height:300 });
	});
}
function sendFObjectRequest(fid,ifids, deptids,stepno,log,refifid) {
	if(fedit){
		
	}
		
	$('button').attr('disabled','true');
	if(stepno === undefined){
		var stepno = $('input[name="stepno"]:checked').val();
	}
	if(stepno === undefined){
		stepno = $('#stepno').val();
	}	
	var userid = $('#user_id').val();
	var comment = $('#comment').val();
	if(stepno == ''){
		qssAjax.alert(Language.translate('SELECT_ACTION'));
		return;
	}	
	if(log == 0){
		var url = sz_BaseUrl + '/user/form/request';
		var data ={ifids:ifids,deptids:deptids,stepno:stepno,userid:userid,comment:comment};
		qssAjax.call(url, data, function(jreturn) {
			$('#qss_trace').dialog('close');
			rowFObjectSearch(fid,0,refifid);
			$('button').removeAttr('disabled');
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
			$('button').removeAttr('disabled');
		});
		$('.dropdown-content').hide();
	}
	else{
		qssAjax.logs(Language.translate('CONFIRM_LOGS'),function(comment){
			if(comment == ''){
				qssAjax.alert(Language.translate('CONFIRM_LOGS'));
				$('button').removeAttr('disabled');
				return;
			}
			var url = sz_BaseUrl + '/user/form/request';
			var data ={ifids:ifids,deptids:deptids,stepno:stepno,userid:userid,comment:comment};
			qssAjax.call(url, data, function(jreturn) {
				$('#qss_trace').dialog('close');
				rowFObjectSearch(fid,0,refifid);
				$('button').removeAttr('disabled');
			}, function(jreturn) {
				qssAjax.alert(jreturn.message);
				$('button').removeAttr('disabled');
			});
			$('.dropdown-content').hide();
		},function(){
			$('button').removeAttr('disabled');
		});
	}
}
function formVerify(fid,ifid, deptid,traceid,accepted) {
	var url = sz_BaseUrl + '/user/form/verify';
	var data = {ifid:ifid,deptid:deptid,traceid:traceid,accepted:accepted,comment:$('#comment').val()};
	qssAjax.call(url, data, function(jreturn) {
		$('#qss_trace').dialog('close');
		rowFObjectSearch(fid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function stopRequest(fid,ifid, deptid,traceid) {
	var url = sz_BaseUrl + '/user/form/stop';
	var data = {ifid:ifid,deptid:deptid,traceid:traceid};
	qssAjax.call(url, data, function(jreturn) {
		$('#qss_trace').dialog('close');
		rowFObjectSearch(fid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function formpopupFOOther(fid,path,el) {
	var ifid = 0;
	if($(el).parent().parent().is('tr')){
		row = $(el).parent().parent()[0]; 
	}
	ifid = $('#ifid').val();
	ioid = $('#ioid').val();
	var url = sz_BaseUrl + path;
	var data = {fid:fid,ifid:ifid,ioid:ioid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').dialog('close');
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function formopenFOOther(fid,path,el) {
	var ifid = 0;
	if (row == null) {
		ifid = $('#ifid').val();
	}
	else if($(el).is('.extra-button-record')){
		ifid = row.id;
	}
	if ((ifid == 0 && $(el).is('.extra-button-record')) || $(el).is('.extra-disabled')) {
		return;
	}
	var url = sz_BaseUrl + path+'?fid='+fid+'&ifid='+ifid+'&popup=1';

    var windowObjectReference;
    var strWindowFeatures = "modal=yes,menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes";
    windowObjectReference = window.open(url , "_blank", strWindowFeatures);
}
function formbashFORun(id,name,el,popup) {
	var ifid = 0;
	if (row == null) {
		ifid = $('#ifid').val();
	}
	else if($(el).is('.extra-button-record')){
		ifid = row.id;
	}
	if ((ifid == 0 && $(el).is('.extra-button-record'))|| $(el).is('.extra-disabled')) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_PROCESS'),function(){
		var url = sz_BaseUrl + '/user/form/bash';
		var data = {id:id,ifid:ifid,popup:popup};
		qssAjax.call(url, data, function(jreturn) {
			qssAjax.alert(Language.translate('ACTION_DONE') + name + "\n" + jreturn.message);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function formWorkflow(ifid,deptid) {
	if (ifid == '' || isDisabled('btnWORKFLOW')) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/workflow';
	var data = {ifid:ifid,deptid:deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function oneSharing(ifid,deptid) {
	if (ifid == '' || isDisabled('btnSHARING')) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/sharing';
	var data = {ifid:ifid,deptid:deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function formDocument(ifid,deptid) {
	if (ifid == '') {
		return;
	}
	var url = sz_BaseUrl + '/static/m016/select';
	var data = {ifid:ifid,deptid:deptid};
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
						attachDocument(data.id,ifid);
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
function formCalendar(ifid,deptid) {
	if (ifid == '') {
		return;
	}
	var url = sz_BaseUrl + '/user/process/calendar';
	var data = {ifid:ifid,deptid:deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_event').html(jreturn);
		$('#qss_event').dialog({ width: 800,height:400 });
	});
}
function formEvent(ifid,deptid) {
	if (ifid == '') {
		return;
	}
	var url = sz_BaseUrl + '/user/event/create';
	var data = {ifid:ifid,ioid:0};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_event').html(jreturn);
		$('#qss_event').dialog({ width: 800,height:400 });
	});
}
function formEmail(ifid,deptid) {
	if (ifid == '' || isDisabled('btnEMAIL')) {
		return;
	}
//	window.location.href = sz_BaseUrl + '/user/mail?ifid=' + ifid;
        openModule('', sz_BaseUrl + '/user/mail?ifid=' + ifid);
}
function rowRecord() {
	var ifid= 0;
	var deptid = 0;
	if (row == null) {
		ifid = $('#ifid').val();
		deptid = $('#deptid').val();
	}
	else
	{
		ifid = row.id;
		deptid = row.getAttribute('deptid');
	}
	if(ifid == 0 || ifid ==''){
		return;
	}		
	var url = sz_BaseUrl + '/user/form/document';
	var data = {ifid:ifid,deptid:deptid};
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
function rowActivity() {
	var ifid= 0;
	var deptid = 0;
	if (row == null) {
		ifid = $('#ifid').val();
		deptid = $('#deptid').val();
	}
	else
	{
		ifid = row.id;
		deptid = row.getAttribute('deptid');
	}
	if(ifid == 0 || ifid ==''){
		return;
	}		
	var url = sz_BaseUrl + '/user/form/activity';
	var data = {ifid:ifid,deptid:deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function onKeyupRefresh() {
	clearInterval(timer);
	timer = setTimeout(function() { //then give it a second to see if the user is finished
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
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
    }, 2000);
	
}
function rowUpDown(fid,type) {
	if (row == null || (isDisabled('btnUP') && isDisabled('btnDOWN'))) {
		return;
	}
	var ifid = row.id;
	var deptid = row.getAttribute('deptid');
	var url = sz_BaseUrl + '/user/form/updown';
	var data = {
			ifid : ifid,
			deptid : deptid,
			type:type
	};
	qssAjax.call(url, data, function(jreturn) {
			rowFObjectSearch(fid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
	});
}
function formFObjectPrintPreview(){
	var ifid = 0;
	var deptid = 0;
	ifid = row.id;
	deptid = row.getAttribute('deptid');
	if (ifid == 0) {
		return;
	}
	var designid = $("input[name='designid']:checked").val();
	var params = [
	              'height='+screen.height,
	              'width='+screen.width,
	              'fullscreen=yes', // only works in IE, but here for completeness
	              'maximize=yes' 
	          ].join(',');
	if(typeof designid != 'undefined'){
		if($("input[name='designid']:checked").attr('url') != ''){
			var url = sz_BaseUrl + $("input[name='designid']:checked").attr('url') + '&popup=1&ifid=' + ifid + '&deptid=' + deptid + '&design='+designid;
			window.open(url, '_blank',params);
		}
		else{
			var url = sz_BaseUrl + '/user/form/print?popup=1&ifid=' + ifid + '&deptid=' + deptid + '&design='+designid;
			window.open(url, '_blank',params);
		}	
	}
	else{
		qssAjax.alert('Bạn phải chọn một mẫu in.');
	}
	
}
function exportExcel(fid,deptid,objid){
	var url = sz_BaseUrl + '/user/import/download?type=3&fid='+fid+'&deptid='+deptid+'&ifid=0'+'&objid='+'&excel_import=';
	window.open(url);
}
function approve(said,ifid, deptid,fid) {
	qssAjax.confirm('Bạn có muốn duyệt bản ghi này không',function(){
		var url = sz_BaseUrl + '/user/form/approve';
		var data = {said:said,ifid:ifid,deptid:deptid};
		qssAjax.call(url, data, function(jreturn) {
			rowFObjectSearch(fid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}

function reject(said,ifid, deptid,fid) {
	qssAjax.confirm('Bạn có muốn hủy duyệt bản ghi này không',function(){
		var url = sz_BaseUrl + '/user/form/reject';
		var data = {said:said,ifid:ifid,deptid:deptid};
		qssAjax.call(url, data, function(jreturn) {
			rowFObjectSearch(fid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}