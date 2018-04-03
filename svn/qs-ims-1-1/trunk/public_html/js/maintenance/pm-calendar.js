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
	$('#btnREEMAIL').addClass('btn_disabled');	
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
	if ($(el).attr('type') == 3) {
		$('#btnREEMAIL').removeClass('btn_disabled');
	} else {
		$('#btnREEMAIL').addClass('btn_disabled');
	}

	$('#btnSEARCH').removeClass('btn_disabled');
	$('#btnREVERT').removeClass('btn_disabled');
	$('#btnRESTORE').removeClass('btn_disabled');
	$('#btnEVENT').removeClass('btn_disabled');
	row = el;
	$.cookie('event_selected', $(row).attr('elid'),{path:'/'});
	//grid_editing = true;
}
function rowEventInsert() {
	//var url = sz_BaseUrl + '/static/m188/edit';
	//window.location.href = url;
	var url = 'http://' + location.host + '/static/m188/edit';
	var data = {};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_event').html(jreturn);
		$('#qss_event').dialog({
		      width: 800,
		      height:450
		    });
	});
}
function rowEventEdit(id) {
	/*if (row == null || isDisabled('btnEDIT')) {
		return;
	}
	var url = sz_BaseUrl + '/static/m188/edit?id=' + row.id;
	window.location.href = url;*/
	var url = 'http://' + location.host + '/static/m188/edit';
	var data = {id:id};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_event').html(jreturn);
		$('#qss_event').dialog({
		      width: 800,
		      height:450
		    });
	});
}
function rowEventRefer() {
	if (row == null) {
		qssAjax.alert(Language.translate('SELECT_ROW'));
		return;
	}
	var url = sz_BaseUrl + '/static/m188/refer?id=' + row.id;
//	window.location.href = url;
        openModule('', url);
}
function rowEventDelete() {
	if (row == null || isDisabled('btnDELETE')) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var id = row.id;
		var data = {
			id : id
		};
		var url = sz_BaseUrl + '/static/m188/delete';
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
function importEvent(id)
{
	if(!$('#fid').val() || $('#object').val() == '' || $('#object').val() == null){
		qssAjax.alert(Language.translate('SELECT_REFER_OBJECT'));
		return;
	}
	gotoImport(id,$('#fid').val(),$('#object').val());;
//	window.location.href = url;
        openModule('', url);
}
function gotoImport(id,fid,objid){
	var url = sz_BaseUrl + '/static/m188/import?eventid='+id+'&fid='+fid+'&objid='+objid;
//	window.location.href = url;
        openModule('', url);
}
function showSearch() {
	if ($('#div-search').is(":visible")) {
		$('#div-search').hide();
	} else {
		$('#div-search').show();
	}
}
function eventCleanSearch(fid, ifid, deptid, objid) {
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
	var eventid = $('#eventid').val();
	var pageno = $('#qss_object_pageno').val();
	var perpage = $('#qss_object_perpage').val();
	var groupby = $('#qss_object_groupby').val();
	var url = sz_BaseUrl + '/static/m188/import/reload?fid=' + fid + '&ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid+'&eventid='+eventid;
	var data = $('#object_' + objid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage  + '&groupby=' + groupby + '&'
			+ data, function(jreturn) {
		$('#qss_object').html(jreturn);
	});
}
function importSort(fid, ifid, deptid, objid) {
	var eventid = $('#eventid').val();
	var pageno = $('#qss_object_pageno').val();
	var perpage = $('#qss_object_perpage').val();
	var groupby = $('#qss_object_groupby').val();
	var url = sz_BaseUrl + '/static/m188/import/reload?fid=' + fid + '&ifid=' + ifid
			+ '&deptid=' + deptid + '&objid=' + objid+'&eventid='+eventid;
	var data = $('#object_' + objid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id + '&groupby=' + groupby +'&eventid='+eventid + '&' + data, function(jreturn) {
		$('#qss_object').html(jreturn);
	});
}
function objectImport(fid, ifid, deptid, objid) {
	var eventid = $('#eventid').val();	
	var url = sz_BaseUrl + '/static/m188/import/import';
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
		eventid : eventid,
		ioidlist : ioidlist,
		fidlist : fidlist
	};
	qssAjax.call(url, data, function(jreturn) {
		importSearch(fid, ifid, deptid, objid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function eventReferBack() {
	var eventid = $('#eventid').val();	
	var url = sz_BaseUrl + '/static/m188/refer?id='+eventid;
//	window.location.href = url;
        openModule('', url);
}
function rowEventBack(id) {
	var url = sz_BaseUrl + '/static/m188?id='+id;
//	window.location.href = url;
        openModule('', url);
}
function eventAction(ele,id, action) {
	if(!$(ele).hasClass('disabled')){
		var url = sz_BaseUrl + '/static/m188/action';
		var data = {
			id : id,
			action : action
		};
		qssAjax.call(url, data, function(jreturn) {
			rowSearch();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	}
}
function eventReferDelete(fid,objid,id,ifid,ioid) {
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/static/m188/refer/delete';
		var data = {
				fid : fid,
				objid : objid,
				id : id,
				ifid : ifid,
				ioid : ioid
		};
		qssAjax.call(url, data, function(jreturn) {
			reloadEventRefer(id,fid, objid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function reloadEventRefer(id,fid, objid) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/static/m188/refer/reload';
	var data = {id:id,
				fid:fid,
				objid:objid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#'+fid+'_'+objid).html(jreturn);
	});
}
function rowSearch() {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/static/m188/grid';
	var data = $('#form_event_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage + '&groupby=' + groupby + '&'
			+ data, function(jreturn) {
		$('#qss_form').html(jreturn);
		// resetBtn();
	});
}
function rowSort(id) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/static/m188/grid';
	var data = $('#form_event_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id + '&groupby=' + groupby +  '&' + data, function(jreturn) {
		$('#qss_form').html(jreturn);
	});
}
function eventLogDelete(ele,elid) {
	if(!$(ele).hasClass('disabled')){
		qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
			var url = sz_BaseUrl + '/static/m188/log/delete';
			var data = {elid:elid};
			qssAjax.call(url, data, function(jreturn) {
				rowSearch();
			}, function(jreturn) {
				qssAjax.alert(jreturn.message);
			});
		});
	}
}
function eventNote(ele,elid) {
	if(!$(ele).hasClass('disabled')){
		var txt = $('<textarea>').attr('cols',85)
								.attr('rows',5)
								.attr('id','note')
								.val($(ele).attr('title'));
		var btn = $('<button>').attr('type','button')
								.text('Ghi lại')
								.addClass('btn-custom');
		$(btn).click(function(){
			 eventNoteSave(elid);
		});
		$('#qss_event').html('');
		$('#qss_event').append(txt);
		$('#qss_event').append(btn);
		$('#qss_event').dialog();
	}
}
function eventNoteSave(elid) {
	var url = sz_BaseUrl + '/static/m188/note/save';
	var data = {elid:elid,note:$('#note').val()};
	qssAjax.call(url, data, function(jreturn) {
		$('#qss_event').dialog('close');
		rowSearch();
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowEmail() {
	var url = sz_BaseUrl + '/user/mail';
//	window.location.href = url;
        openModule('', url);
}
function rowResendEmail() {
	if (row == null || isDisabled('btnREEMAIL')) {
		return;
	}
	var url = sz_BaseUrl + '/user/mail?id='+row.id;
//	window.location.href = url;
        openModule('', url);
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
	var data = {pageno:pageno,perpage:perpage,groupby:groupby};
	qssAjax.getHtml(url, '&fieldorder=' + id +  '&' + data, function(jreturn) {
		$('#qss_attach').html(jreturn);
	});
}
function downloadTemplate(id) {
	var url = sz_BaseUrl + '/static/m188/download?id=' + id;
//	window.location.href = url;
        openModule('', url);
}
function deleteTemplate(id) {
	qssAjax.confirm(Language.translate('CONFIRM_DELETE_TEMPLATE'),function(){
		var url = sz_BaseUrl + '/static/m188/delete/template?id=' + id;
		var data = {id:id};
		qssAjax.call(url, data, function(jreturn) {
			$('#template_exists').hide();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}

function showMonth(m,y) {
	$('#cal_view').attr('style', 'opacity : 0.4');
	var url = sz_BaseUrl + '/static/m188/reload';
	if(m) $('#month').val(m);
	if(y) $('#year').val(y);
	$('#type').val('month');
	var data = $('#cal_form').serialize();
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#cal_view').html(jreturn);
		$('#cal_view').attr('style', 'opacity : 1');
	});
	removeSelected();
	$('#btnMONTH').addClass('extra-selected');
}
function showDay(d,m,y) {
	$('#cal_view').attr('style', 'opacity : 0.4');
	var url = sz_BaseUrl + '/static/m188/reload';
	if(d) $('#day').val(d);
	if(m) $('#month').val(m);
	if(y) $('#year').val(y);
	$('#type').val('day');
	var data = $('#cal_form').serialize();
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#cal_view').html(jreturn);
		$('#cal_view').attr('style', 'opacity : 1');
	});
	removeSelected();
	$('#btnDAY').addClass('extra-selected');
}
function showWeek(w,m,y) {
	$('#cal_view').attr('style', 'opacity : 0.4');
	var url = sz_BaseUrl + '/static/m188/reload';
	if(w) $('#week').val(w);
	if(m) $('#month').val(m);
	if(y) $('#year').val(y);
	$('#type').val('week');
	var data = $('#cal_form').serialize();
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#cal_view').html(jreturn);
		$('#cal_view').attr('style', 'opacity : 1');
	});
	removeSelected();
	$('#btnWEEK').addClass('extra-selected');
}
function showYear(y) {
	$('#cal_view').attr('style', 'opacity : 0.4');
	var url = sz_BaseUrl + '/static/m188/reload';
	if(y) $('#year').val(y);
	$('#type').val('year');
	var data = $('#cal_form').serialize();
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#cal_view').html(jreturn);
		$('#cal_view').attr('style', 'opacity : 1');
	});
	removeSelected();
	$('#btnYEAR').addClass('extra-selected');
}

function showToDay() {
	$('#cal_view').attr('style', 'opacity : 0.4');
	var url = sz_BaseUrl + '/static/m188/reload';
	/*var d = new Date();
	$('#day').val(d.getDate());
	$('#month').val(d.getMonth()+1);
	$('#year').val(d.getFullYear());*/
	var data = $('#cal_form').serialize();
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#cal_view').html(jreturn);
		$('#cal_view').attr('style', 'opacity : 1');
	});
}
function showTrace(ifid,deptid) {
	var url = sz_BaseUrl + '/user/form/trace';
	var data = {ifid:ifid,deptid:deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function reloadCalendar() {
	$('#cal_view').attr('style', 'opacity : 0.4');
	var url = sz_BaseUrl + '/static/m188/reload';
	var data = $('#cal_form').serialize();
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#cal_view').html(jreturn);
		$('#cal_view').attr('style', 'opacity : 1');
	});
}
function removeSelected() {
	$('.extra-button').each(function(){
		$(this).removeClass('extra-selected');
	});
	
}
function createCalendar() {
	var url = sz_BaseUrl + '/static/m188/create';
	var data = {};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').attr('title','Chạy kế hoạch')
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 400,height:200 });
	});
}
function openCalendar(module, ifid, deptid, dat, sFunction)
{
	var url = sz_BaseUrl + '/static/m188/edit';
	var data = {ifid:ifid,deptid:deptid, module:module} ;
	data=  $.extend({}, data, dat);
	$('#qss_combo').data('saved',false);
	qssAjax.getHtml(url, data, function(jreturn) {
	$('#qss_combo').html(jreturn);
		if(sFunction){
			$('#qss_combo').dialog({ width: 900,height:450,closeOnEscape: false, close: function(){
				if($(this).data('saved'))
				{
				sFunction
				}
			}});
		}
		else{
			$('#qss_combo').dialog({ width: 900,height:450,closeOnEscape: false});
		}
	});			
}

function runCalendar()
{
	var start = $('#startdate').val();
	var end   = $('#enddate').val();
	var diff  = common_diffDate(start, end);
	
	// Limit 7 days
	if(diff > 7)
	{
		qssAjax.confirm('Chạy kế hoạch được giới hạn trong vòng 7 ngày! Bạn muốn tiếp tục không?'
		, function(){
			ajaxRunCalendar();
		}
		, function(){
			$('#qss_combo').dialog('close');
		});		
	}
	else
	{
		ajaxRunCalendar();
	}
}

function ajaxRunCalendar()
{
	var url  = sz_BaseUrl + '/static/m188/saveplanworkorder';
	var data = $('#filter_plan').serialize();
	qssAjax.call(url, data, function(jreturn) {
		if(jreturn.message)
		{
			qssAjax.alert(jreturn.message);
		}
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});

}

function saveWorkOrder()
{
	var url  = sz_BaseUrl + '/static/m188/saveworkorder';
	var data = $('#work_order_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		$('#qss_combo').data('saved', true);
		if(jreturn.message)
		{
			qssAjax.alert(jreturn.message);
		}
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});	
}

function reloadTasks(module, ifid, deptid, dat, sFunction)
{
	var url = sz_BaseUrl + '/static/m188/tasks';
	var data = {ifid:ifid,deptid:deptid, module:module} ;
	data=  $.extend({}, data, dat);
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#tasks_box').html(jreturn);
	});		
}
function showResource(startdate,enddate,$wc){
	var url = sz_BaseUrl + '/static/m785/show';
	var data = {start:startdate,end:enddate,workcenter:$wc};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_dialog').html(jreturn);
		$('#qss_dialog').dialog({
		      width: $(window).innerWidth(),
		      height:450
		    });
	});	
}