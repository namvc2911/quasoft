var col = '';
var textcol = '';
function resetBtn() {
	row = null;
	$('#btnEDIT').addClass('btn_disabled');
	$('#btnDETAIL').addClass('btn_disabled');
	$('#btnDELETE').addClass('btn_disabled');
	$('#btnASSIGN').addClass('btn_disabled');
	$('#btnSHARING').addClass('btn_disabled');
	$('#btnREJECT').addClass('btn_disabled');
	$('#btnPRINT').addClass('btn_disabled');
	$('#btnFINISH').addClass('btn_disabled');
	$('#btnVALIDATE').addClass('btn_disabled');
	$('#btnEVENT').addClass('btn_disabled');
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
		$('#btnINSERT').removeClass('btn_disabled');
	} else {
		$('#btnINSERT').addClass('btn_disabled');
	}
	if (rights & 2) {
		$('#btnEDIT').removeClass('btn_disabled');
	} else {
		$('#btnEDIT').addClass('btn_disabled');
	}
	if (rights & 4) {
		$('#btnDETAIL').removeClass('btn_disabled');
		$('#btnVALIDATE').removeClass('btn_disabled');
	} else {
		$('#btnDETAIL').addClass('btn_disabled');
		$('#btnVALIDATE').addClass('btn_disabled');
	}
	if (rights & 8) {
		$('#btnDELETE').removeClass('btn_disabled');
	} else {
		$('#btnDELETE').addClass('btn_disabled');
	}
	if (rights & 32) {
		$('#btnASSIGN').removeClass('btn_disabled');
	} else {
		$('#btnASSIGN').addClass('btn_disabled');
	}
	if (rights & 64) {
		$('#btnSHARING').removeClass('btn_disabled');
	} else {
		$('#btnSHARING').addClass('btn_disabled');
	}
	if (rights & 128) {
		$('#btnREJECT').removeClass('btn_disabled');
	} else {
		$('#btnREJECT').addClass('btn_disabled');
	}
	if (rights & 256) {
		$('#btnPRINT').removeClass('btn_disabled');
	} else {
		$('#btnPRINT').addClass('btn_disabled');
	}
	if (rights & 512) {
		$('#btnFINISH').removeClass('btn_disabled');
	} else {
		$('#btnFINISH').addClass('btn_disabled');
	}
	$('#btnSEARCH').removeClass('btn_disabled');
	$('#btnREVERT').removeClass('btn_disabled');
	$('#btnRESTORE').removeClass('btn_disabled');
	$('#btnEVENT').removeClass('btn_disabled');
	row = el;
	$.cookie('mail_list_selected', $(row).attr('elid'),{path:'/'});
	//grid_editing = true;
}
function rowMailListInsert() {
	var url = sz_BaseUrl + '/user/mail/list/edit';
//	window.location.href = url;
        openModule('', url);
}
function rowMailListEdit() {
	if (row == null || isDisabled('btnEDIT')) {
		return;
	}
	var url = sz_BaseUrl + '/user/mail/list/edit?id=' + row.id;
//	window.location.href = url;
        openModule('', url);
}
function rowMailListDelete() {
	if (row == null || isDisabled('btnDELETE')) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var id = row.id;
		var data = {
			id : id
		};
		var url = sz_BaseUrl + '/user/mail/list/delete';
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function isDisabled(name)
{
	return $('#'+name).is('.btn_disabled');
}
function showMailSearch() {
	if ($('#div-search').is(":visible")) {
		$('#div-search').hide();
	} else {
		$('#div-search').show();
	}
}
function mailCleanSearch(fid, ifid, deptid, objid) {
	$('form[name=filter_form] :input').each(function(){
		$(this).val('');
	});
	rowSearch();
}
function rowCleanSearch(fid, ifid, deptid, objid) {
	$('form[name=filter_form] :input').each(function(){
		$(this).val('');
	});
	importSearch(fid, ifid, deptid, objid)
}
function importSearch(fid, ifid, deptid, objid) {
	var mlid = $('#id').val();
	var pageno = $('#qss_object_pageno').val();
	var perpage = $('#qss_object_perpage').val();
	var groupby = $('#qss_object_groupby').val();
	var url = sz_BaseUrl + '/user/mail/import/reload?fid=' + fid + '&ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid+'&mlid='+mlid;
	var data = $('#object_' + objid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage  + '&groupby=' + groupby + '&'
			+ data, function(jreturn) {
		$('#qss_object').html(jreturn);
	});
}
function importSort(fid, ifid, deptid, objid,id) {
	var eventid = $('#eventid').val();
	var pageno = $('#qss_object_pageno').val();
	var perpage = $('#qss_object_perpage').val();
	var groupby = $('#qss_object_groupby').val();
	var url = sz_BaseUrl + '/user/mail/import/reload?fid=' + fid + '&ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid+'&eventid='+eventid;
	var data = $('#object_' + objid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id + '&groupby=' + groupby +'&eventid='+eventid + '&' + data, function(jreturn) {
		$('#qss_object').html(jreturn);
	});
}
function objectImport(fid, ifid, deptid, objid) {
	var id = $('#id').val();	
	var url = sz_BaseUrl + '/user/mail/import/import';
	var ioidlist = '';
	var fidlist = '';
	$('input[name="IOID[]"]').each(function() {
		if (this.checked == true) {
			if (ioidlist != '')
				ioidlist += ',';
			if (fidlist != '')
				fidlist += ',';
			ioidlist += this.value;
			fidlist += $(this).attr('ifid');
		}
	});
	var data = {
		mlid : id,
		ioidlist : ioidlist,
		fidlist : fidlist
	};
	qssAjax.call(url, data, function(jreturn) {
		importSearch(fid, ifid, deptid, objid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function importMailList(id)
{
	if($('#form').val() == 0 || $('#object').val() == 0){
		qssAjax.alert(Language.translate('SELECT_REFER_OBJECT'));
		return;
	}
	gotoImport(id,$('#form').val(),$('#object').val());
}
function chooseRefer(ele)
{
	if($(ele).is(':checked')){
		$('#form').val($(ele).attr('fid'));
		$('#object').val($(ele).attr('objid'));
	}
}
function gotoImport(id,fid,objid){
	var url = sz_BaseUrl + '/user/mail/import?mlid='+id+'&fid='+fid+'&objid='+objid;
//	window.location.href = url;
        openModule('', url);
}
function rowMailRefer() {
	if (row == null) {
		qssAjax.alert(Language.translate('SELECT_ROW'));
		return;
	}
	var url = sz_BaseUrl + '/user/mail/refer?id=' + row.id;
//	window.location.href = url;
        openModule('', url);
}
function mailReferBack(id) {
	var url = sz_BaseUrl + '/user/mail/refer?id='+id;
//	window.location.href = url;
        openModule('', url);
}
function rowMailListBack(id) {
	var url = sz_BaseUrl + '/user/mail/list';
//	window.location.href = url;
        openModule('', url);
}
function mailReferDelete(fid,objid,id,ifid,ioid) {
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/user/mail/refer/delete';
		var data = {
				fid : fid,
				objid : objid,
				id : id,
				ifid : ifid,
				ioid : ioid
		};
		qssAjax.call(url, data, function(jreturn) {
			reloadMailRefer(id,fid, objid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function reloadMailRefer(id,fid, objid) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/user/mail/refer/reload';
	var data = {id:id,
				fid:fid,
				objid:objid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#'+fid+'_'+objid).html(jreturn);
	});
}
function rowEventBack(id) {
	var url = sz_BaseUrl + '/user/event?id='+id;
//	window.location.href = url;
        openModule('', url);
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
function attachSearch() {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/user/document/attach';
	var data = {pageno:pageno,perpage:perpage,groupby:groupby};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_attach').html(jreturn);
		// resetBtn();
	});
}
function attachSort(id) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/user/document/attach';
	var data = {fieldorder:id,pageno:pageno,perpage:perpage,groupby:groupby};
	qssAjax.getHtml(url,data, function(jreturn) {
		$('#qss_attach').html(jreturn);
	});
}