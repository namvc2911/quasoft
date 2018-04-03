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
	$('#btnEMAIL').prop('disabled',true);
	$('#btnDOCUMENT').prop('disabled',true);
}

function rowCLK(el) {
	if(el == row){
		return;
	}		
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
		$('#btnEMAIL').prop('disabled',false);
		$('#btnDOCUMENT').prop('disabled',false);
		$('#btnEVENT').prop('disabled',false);
	} else {
		$('#btnDETAIL').prop('disabled',true);
		$('#btnVALIDATE').prop('disabled',true);
		$('#btnEMAIL').prop('disabled',true);
		$('#btnDOCUMENT').prop('disabled',true);
		$('#btnEVENT').prop('disabled',true);
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
	if (rights & 1024) {
		$('#btnSEARCH').prop('disabled',false);
	} else {
		$('#btnSEARCH').prop('disabled',true);
	}
	$('#btnREVERT').prop('disabled',false);
	$('#btnRESTORE').prop('disabled',false);
	$('#btnEVENT').prop('disabled',false);
	if(row != el && $(row).attr('id') == 0 && !edit){
		$(row).remove();
	}
	row = el;
	$.cookie('grid_selected', row.id,{path:'/'});
	//grid_editing = true;
}
function rowInsert() {
	if (isDisabled('btnINSERT')) {
		return;
	}
//	window.location.href = sz_BaseUrl + '/user/currencies/edit';
        openModule('', sz_BaseUrl + '/user/currencies/edit');
}
function rowEdit() {
	if (row == null || isDisabled('btnEDIT')) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/edit?id=' + row.id;
//	window.location.href = url;
        openModule('', url);
}
function rowSearch() {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/user/currencies/grid';
	var data = $('#currencies_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage + '&'
			+ data, function(jreturn) {
		$('#qss_form').html(jreturn);
		// resetBtn();
	});
}
function rowSort(id) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var url = sz_BaseUrl + '/user/currencies/grid';
	var data = $('#qss_form_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id +  '&' + data, function(jreturn) {
		$('#qss_form').html(jreturn);
	});
}
function rowDelete(fid) {
	if (row == null || isDisabled('btnDELETE')) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var id = row.id;
		var url = sz_BaseUrl + '/user/currencies/delete';
		var data = {
			id : id
		};
		qssAjax.call(url, data, function(jreturn) {
			var newrow = null;
			newrow = $(row).next();
			if(!newrow || !newrow.attr('id')){
				newrow = $(row).prev();
			}
			if(newrow && newrow.attr('id')){
				$.cookie('grid_selected', newrow.attr('id'),{path:'/'});
			}
			rowSearch(fid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function showSearch() {
	if ($('#div-search').is(":visible")) {
		$('#div-search').hide();
	} else {
		$('#div-search').show();
	}
}
function rowCleanSearch(fid) {
	$('form[name=filter_form] :input').each(function(){
		$(this).val('');
	});
	rowSearch(fid);
}
function isDisabled(name)
{
	return $('#'+name).is('.btn_disabled');
}