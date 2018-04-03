var col = '';
var textcol = '';
function resetBtn() {
	row = null;
	$('#btnEDIT').prop('disabled',true);
	$('#btnDETAIL').prop('disabled',true);
	$('#btnDELETE').prop('disabled',true);
	$('#btnASSIGN').prop('disabled',true);
	$('#btnSHARING').prop('disabled',true);
	$('#btnREJECT').prop('disabled',true);
	$('#btnPRINT').prop('disabled',true);
	$('#btnFINISH').prop('disabled',true);
	$('#btnVALIDATE').prop('disabled',true);
	$('#btnEVENT').prop('disabled',true);
}

function rowCLK(el) {
	if(!el){
		resetBtn();
		return;
	}
	if (row != null) {
		row.style.background = col;
		row.style.color = textcol;
	}
	col = el.style.background;
	textcol = el.style.color;
	el.style.background = 'gray';
	el.style.color = '#ffffff';
	rights = el.getAttribute('rights');
	if (rights & 1) {
		$('#btnINSERT').prop('disabled',false);
	} else {
		$('#btnINSERT').prop('disabled',false);
	}
	if (rights & 2) {
		$('#btnEDIT').prop('disabled',false);
	} else {
		$('#btnEDIT').prop('disabled',true);
	}
	if (rights & 4) {
		$('#btnDETAIL').prop('disabled',false);
		$('#btnVALIDATE').prop('disabled',false);
	} else {
		$('#btnDETAIL').prop('disabled',true);
		$('#btnVALIDATE').prop('disabled',true);
	}
	if (rights & 8) {
		$('#btnDELETE').prop('disabled',false);
	} else {
		$('#btnDELETE').prop('disabled',true);
	}
	if (rights & 32) {
		$('#btnASSIGN').prop('disabled',false);
	} else {
		$('#btnASSIGN').prop('disabled',true);
	}
	if (rights & 64) {
		$('#btnSHARING').prop('disabled',false);
	} else {
		$('#btnSHARING').prop('disabled',true);
	}
	if (rights & 128) {
		$('#btnREJECT').prop('disabled',false);
	} else {
		$('#btnREJECT').prop('disabled',true);
	}
	if (rights & 256) {
		$('#btnPRINT').prop('disabled',false);
	} else {
		$('#btnPRINT').prop('disabled',true);
	}
	if (rights & 512) {
		$('#btnFINISH').prop('disabled',false);
	} else {
		$('#btnFINISH').prop('disabled',true);
	}
	$('#btnSEARCH').prop('disabled',false);
	$('#btnREVERT').prop('disabled',false);
	$('#btnRESTORE').prop('disabled',false);
	$('#btnEVENT').prop('disabled',false);
	row = el;
	$.cookie('doc_selected', row.id,{path:'/'});
	//grid_editing = true;
}
function showSearch() {
	if ($('#div-search').is(":visible")) {
		$('#div-search').hide();
	} else {
		$('#div-search').show();
	}
}
function rowBack() {
	var url = sz_BaseUrl + '/static/m016';
//	window.location.href = url;
        openModule('', url);
}
function rowInsert() {
	var url = sz_BaseUrl + '/static/m016/edit';
//	window.location.href = url;
        openModule('', url);
}
function rowEdit() {
	if (row == null || isDisabled('btnEDIT')) {
		return;
	}
	var url = sz_BaseUrl + '/static/m016/edit?id=' + row.id;
//	window.location.href = url;
        openModule('', url);
}
function rowDelete() {
	if (row == null || isDisabled('btnDELETE')) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var id = row.id;
		var data = {
			id : id
		};
		var url = sz_BaseUrl + '/static/m016/delete';
		qssAjax.call(url, data, function(jreturn) {
			rowSearch();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function isDisabled(name)
{
	return $('#'+name).is('.btn_disabled');
}
function rowSearch() {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/static/m016/grid';
	var data = $('#form_event_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
				+ '&groupby=' + groupby +  '&' + data, function(jreturn) {
		$('#qss_form').html(jreturn);
		// resetBtn();
	});
}
function rowSort(id) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/static/m016/grid';
	var data = $('#form_event_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id + '&groupby=' + groupby + '&fieldorder=' + id + '&' + data, function(jreturn) {
		$('#qss_form').html(jreturn);
		// resetBtn();
	});
}
function uploadFile() {
	/* Upload file by ajax */
	disabledLayout();
	$.ajaxFileUpload({
		url : sz_BaseUrl + '/static/m016/uploadfile',
		secureuri : false,
		fileElementId : $('#file'),
		dataType : 'json',
		success : function(data, status) {
			/* Upload file successfully */
			if (data.error) {
				qssAjax.alert(data.message);
				enabledLayout();
			} else {
				$('#doc').val(data.value);
				if($('#name').val() == ''){
					$('#name').val(data.name);
				}
				enabledLayout();
			}
		},
		error : function(data, status, e) {
			/* If upload error */
			qssAjax.alert(e);
		}
	});
}
function docSave() {
	var url = sz_BaseUrl + '/static/m016/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
//		window.location.href = sz_BaseUrl + '/user/document';
        openModule('', sz_BaseUrl + '/static/m016');
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowCleanSearch() {
	$('form[name=filter_form] :input').each(function(){
		$(this).val('');
	});
	rowSearch();
}
function downloadDoc(id){
	var url = sz_BaseUrl + '/static/m016/download?popup=1&id='+id;
    window.open(url);
    enabledLayout();
}