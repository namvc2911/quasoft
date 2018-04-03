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
		$('#btnINSERT').prop('disabled',true);
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
	$.cookie('bash_selected', row.id,{path:'/'});
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
	var url = sz_BaseUrl + '/system/bash';
//	window.location.href = url;
        openModule('', url);
}
function rowInsert() {
	var url = sz_BaseUrl + '/system/bash/edit';
//	window.location.href = url;
        openModule('', url);
}
function rowEdit() {
	if (row == null || isDisabled('btnEDIT')) {
		return;
	}
	var url = sz_BaseUrl + '/system/bash/edit?id=' + row.id;
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
		var url = sz_BaseUrl + '/system/bash/delete';
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
	var pageno = $('#bash_pageno').val();
	var perpage = $('#bash_perpage').val();
	var groupby = $('#bash_groupby').val();
	var url = sz_BaseUrl + '/system/bash/grid';
	var data = $('#form_bash_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
				+ '&groupby=' + groupby +  '&' + data, function(jreturn) {
		$('#qss_form').html(jreturn);
		// resetBtn();
	});
}
function rowSort(id) {
	var pageno = $('#bash_pageno').val();
	var perpage = $('#bash_perpage').val();
	var groupby = $('#bash_groupby').val();
	var url = sz_BaseUrl + '/system/bash/grid';
	var data = $('#form_bash_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id + '&groupby=' + groupby + '&fieldorder=' + id + '&' + data, function(jreturn) {
		$('#qss_form').html(jreturn);
		// resetBtn();
	});
}
function bashSave() {
	var url = sz_BaseUrl + '/system/bash/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
//		window.location.href = sz_BaseUrl + '/system/bash';
                openModule('', sz_BaseUrl + '/system/bash');
                
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
function rowBashFields(id){
	if (!id && row == null) {
		qssAjax.alert(Language.translate('SELECT_ROW'));
		return;
	}
	var bid = id?id:row.id;
	var url = sz_BaseUrl + '/system/bash/fields?id='+bid;
	window.location.href = url;
}
function bashFieldInsert(bashid){
	var url = sz_BaseUrl + '/system/bash/field/edit?bashid='+bashid;
//	window.location.href = url;
        openModule('', url);
}
function bashFieldEdit(bashid,id){
	var url = sz_BaseUrl + '/system/bash/field/edit?bashid='+bashid+'&id='+id;
//	window.location.href = url;
        openModule('', url);
}
function bashFieldBack(id){
	var url = sz_BaseUrl + '/system/bash/fields?id='+id;
//	window.location.href = url;
        openModule('', url);
}
function bashFieldSave() {
	var url = sz_BaseUrl + '/system/bash/field/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
//		window.location.href = sz_BaseUrl + '/system/bash/fields?id='+$('#bashid').val();
                openModule('', sz_BaseUrl + '/system/bash/fields?id='+$('#bashid').val());
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function bashFieldDelete(id) {
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var data = {
			id : id
		};
		var url = sz_BaseUrl + '/system/bash/field/delete';
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function rowBashHistory(id){
	if (row == null && !id) {
		qssAjax.alert(Language.translate('SELECT_ROW'));
		return;
	}
	var bid = id?id:row.id;
	var url = sz_BaseUrl + '/system/bash/history?id='+ bid;
//	window.location.href = url;
        openModule('', url);
}
function rowHistorySort(id) {
	var pageno = $('#bash_history_pageno').val();
	var perpage = $('#bash_history_perpage').val();
	var url = sz_BaseUrl + '/system/bash/history/grid';
	var data = {pageno:pageno ,perpage:perpage,fieldorder:1,id:id};
	qssAjax.getHtml(url,data, function(jreturn) {
		$('#qss_form').html(jreturn);
	});
}
function rowHistorySearch(id) {
	var pageno = $('#bash_history_pageno').val();
	var perpage = $('#bash_history_perpage').val();
	var url = sz_BaseUrl + '/system/bash/history/grid';
	var data = {pageno:pageno ,perpage:perpage,id:id};
	qssAjax.getHtml(url,data, function(jreturn) {
		$('#qss_form').html(jreturn);
	});
}
function bashRun(ifid,id) {
	var url = sz_BaseUrl + '/system/bash/run';
	var data = {id:id,ifid:ifid};
	qssAjax.call(url, data, function(jreturn) {
		rowHistorySearch(id)
	}, function(jreturn) {
		rowHistorySearch(id)
	});
}
function bashLoadObject(fid, ele, selected) {
	var url = sz_BaseUrl + '/system/field/load/object';
	var data = {
		fid : fid,
		selected : selected
	};
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#' + ele).html(sz_Msg);
	});
}