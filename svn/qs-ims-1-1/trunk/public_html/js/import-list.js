var col = '';
var textcol = '';
function rowCLK(el) {
	if (row != null) {
		row.style.background = col;
		row.style.color = textcol;
	}
	col = el.style.background;
	el.style.background = 'gray';
	textcol = el.style.color;
	el.style.color = '#ffffff';
	if (document.getElementById('btnDETAIL') != undefined)
		document.getElementById('btnDETAIL').disabled = false;
	if (document.getElementById('btnDELETE') != undefined)
		document.getElementById('btnDELETE').disabled = false;
	if (document.getElementById('btnASSIGN') != undefined)
		document.getElementById('btnASSIGN').disabled = false;
	if (document.getElementById('btnSENDA') != undefined)
		document.getElementById('btnSENDA').disabled = false;
	if (document.getElementById('btnLOCK') != undefined)
		document.getElementById('btnLOCK').disabled = false;
	if (document.getElementById('btnSTOP') != undefined)
		document.getElementById('btnSTOP').disabled = false;
	if (document.getElementById('btnRESTORE') != undefined)
		document.getElementById('btnRESTORE').disabled = false;
	if (document.getElementById('btnEDIT') != undefined)
		document.getElementById('btnEDIT').disabled = false;
	if (document.getElementById('btnACCEPT') != undefined)
		document.getElementById('btnACCEPT').disabled = false;
	if (document.getElementById('btnREJECT') != undefined)
		document.getElementById('btnREJECT').disabled = false;
	if (document.getElementById('btnPRINT') != undefined)
		document.getElementById('btnPRINT').disabled = false;
	row = el;
}
function importSearch(fid, ifid, deptid, objid) {
	var pageno = $('#qss_object_pageno').val();
	var perpage = $('#qss_object_perpage').val();
	var groupby = $('#qss_object_groupby').val();
	var url = sz_BaseUrl + '/user/import/grid?fid=' + fid + '&ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid;
	var data = $('#object_' + objid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage  + '&groupby=' + groupby + '&'
			+ data, function(jreturn) {
		$('#qss_object').html(jreturn);
	});
}
function importSort(fid, ifid, deptid, objid, id) {
	var pageno = $('#qss_object_pageno').val();
	var perpage = $('#qss_object_perpage').val();
	var groupby = $('#qss_object_groupby').val();
	var url = sz_BaseUrl + '/user/import/grid?fid=' + fid + '&ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid;
	var data = $('#object_' + objid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id + '&groupby=' + groupby + '&' + data, function(jreturn) {
		$('#qss_object').html(jreturn);
	});
}
function objectImport(fid, ifid, deptid, objid, id) {
	var url = sz_BaseUrl + '/user/import/import?fid=' + fid + '&ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid;
	var ioidlist = '';
	var fidlist = '';
	$('input[name="IOID[]"]').each(function() {
		if (this.checked == true) {
			if (ioidlist != '')
				ioidlist += ',';
			if (fidlist != '')
				fidlist += ',';
			ioidlist += this.value;
			fidlist += $(this).attr('fid');
		}
	});
	var data = {
		ioidlist : ioidlist,
		fidlist : fidlist
	};
	qssAjax.call(url, data, function(jreturn) {
		importSearch(fid, ifid, deptid, objid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function formBack(fid) {
	//window.location.href = sz_BaseUrl + '/user/form?fid=' + fid;
        openModule('',sz_BaseUrl + '/user/form?fid=' + fid);
}
function objectBack(ifid, deptid, objid) {
	//window.location.href = sz_BaseUrl + '/user/object?ifid=' + ifid
	//		+ '&deptid=' + deptid + '&objid=' + objid;
        var url = sz_BaseUrl + '/user/object?ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid;
        openModule('', url);
        
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
				$('#btnUPDATE').prop('disabled',false);
			}
		},
		error : function(data, status, e) {
			/* If upload error */
			qssAjax.alert(e);
		}
	});
}
function excelImport() {
	if (isDisabled('btnUPDATE')) {
		return;
	}
	if($('#fid').val() == 0 && $('#fid').val() == 0){
		qssAjax.alert(Language.translate('SELECT_IMPORT_MODULE'));
		return;
	}
	//$('#btnUPDATE').prop('disabled',true);
	disabledLayout();
	var url = sz_BaseUrl + '/user/import/excelImport';
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_import').html(jreturn);
		enabledLayout();
	});
}
function downloadIMPO(type) {
	if($('#fid').val() == 0 && $('#fid').val() == 0){
		qssAjax.alert(Language.translate('SELECT_TEMPLATE_MODULE'));
		return;
	}
	var url = sz_BaseUrl + '/user/import/download?type=' + type;
	var data = $('#qss_form').serialize();
	window.open(url + '&' + data);
	//window.location.href = url + '&' + data;
        //openModule('', url + '&' + data);
}
function showSearch() {
	if ($('#div-search').is(":visible")) {
		$('#div-search').hide();
	} else {
		$('#div-search').show();
	}
}
function rowCleanSearch(fid, ifid, deptid, objid) {
	$('form[name=filter_form] :input').each(function(){
		$(this).val('');
	});
	importSearch(fid, ifid, deptid, objid)
}
function isDisabled(name) {
	return $('#' + name).is('.btn_disabled');
}
function toggleCheckBox()
{
	$('.IOID').each(function(){
		if($(this).is(':checked')){
			$(this).removeAttr('checked');
		}
		else{
			$(this).attr('checked',true);
		}
	});
}