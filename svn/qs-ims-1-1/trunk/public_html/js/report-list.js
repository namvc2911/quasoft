col = '';
textcol = '';
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
	$('#btnDETAILEXCEL').addClass('btn_disabled');
	$('#btnDETAILPDF').addClass('btn_disabled');
}
function rowCLK(el) {
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
		$('#btnDETAILEXCEL').removeClass('btn_disabled');
		$('#btnDETAILPDF').removeClass('btn_disabled');
	} else {
		$('#btnDETAIL').addClass('btn_disabled');
		$('#btnDETAILEXCEL').addClass('btn_disabled');
		$('#btnDETAILPDF').addClass('btn_disabled');
	}
	if (rights & 8) {
		$('#btnDELETE').removeClass('btn_disabled');
	} else {
		$('#btnDELETE').addClass('btn_disabled');
	}
	if (rights & 16) {
		$('#btnASSIGN').removeClass('btn_disabled');
	} else {
		$('#btnASSIGN').addClass('btn_disabled');
	}
	if (rights & 32) {
		$('#btnSHARING').removeClass('btn_disabled');
	} else {
		$('#btnSHARING').addClass('btn_disabled');
	}
	if (rights & 64) {
		$('#btnREJECT').removeClass('btn_disabled');
	} else {
		$('#btnREJECT').addClass('btn_disabled');
	}
	if (rights & 128) {
		$('#btnPRINT').removeClass('btn_disabled');
	} else {
		$('#btnPRINT').addClass('btn_disabled');
	}
	if (rights & 256) {
		$('#btnFINISH').removeClass('btn_disabled');
	} else {
		$('#btnFINISH').addClass('btn_disabled');
	}
	if (rights & 512) {
		$('#btnSEARCH').removeClass('btn_disabled');
	} else {
		$('#btnSEARCH').addClass('btn_disabled');
	}

	$('#btnRESTORE').removeClass('btn_disabled');
	row = el;
	//grid_editing = true;
}
function rowInsert(fid) {
//	window.location.href = sz_BaseUrl + '/user/report/edit?fid=' + fid;
        openModule('', sz_BaseUrl + '/user/report/edit?fid=' + fid);
}
function rowEdit() {
	if (row == null) {
		
		return;
	}
	var url = sz_BaseUrl + '/user/report/edit/?ifid=' + row.id + '&deptid='
			+ row.getAttribute('deptid');
	;
//	window.location.href = url;
        openModule('', url);
}
function rowObject(objid) {
	if (row == null) {
		
		return;
	}
//	window.location.href = sz_BaseUrl + '/user/object?ifid=' + row.id
//			+ '&deptid=' + row.getAttribute('deptid') + '&objid=' + objid;
        openModule('', sz_BaseUrl + '/user/object?ifid=' + row.id
			+ '&deptid=' + row.getAttribute('deptid') + '&objid=' + objid);
}
function rowTrace() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/trace';
	var data = {ifid:row.id,deptid:row.getAttribute('deptid')};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog();
	});
}
function rowDetail(type) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/user/report/detail?ifid=' + row.id + '&deptid='
			+ row.getAttribute('deptid')+'&type='+type;
	window
	.open(url, '_blank',
			'statusbar=no,resizable=yes,scrollbars=yes, addressbar=no,maximize=yes');
}
function rowPrint() {
	if (row == null) {
		
		return;
	}
	var url = sz_BaseUrl + '/user/report/print?ifid=' + row.id + '&deptid='
			+ row.getAttribute('deptid');
	window
			.open(url, '_blank',
					'statusbar=no,resizable=yes,scrollbars=yes, addressbar=no,maximize=yes');
}
//function rowImport() {
////	window.location.href = 'form_import.php?params=".ObjectToParams($params)."';
//        openModule('', 'form_import.php?params=".ObjectToParams($params)."');
//}
function rowSharing() {
	if (row == null || isDisabled('btnSHARING')) {
		return;
	}
	var url = sz_BaseUrl + '/user/report/sharing';
	var data = {ifid:row.id,deptid:row.getAttribute('deptid')};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog();
	});
}
function formSharing(ifid, deptid) {
	var url = sz_BaseUrl + '/user/report/dosharing?ifid=' + ifid + '&deptid='
			+ deptid;
	var data = $('#form_sharing').serialize();
	qssAjax.call(url, data, function(jreturn) {
		rowSharing();
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function deleteSharing(ifid, deptid,uid) {
	var url = sz_BaseUrl + '/user/form/deletesharing?ifid=' + ifid + '&deptid='
			+ deptid;
	var data = {uid :uid};
	qssAjax.call(url, data, function(jreturn) {
		rowSharing();
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
function rowAssign() {
	if (row == null || isDisabled('btnASSIGN')) {
		return;
	}
	var url = sz_BaseUrl + '/user/report/assign';
	var data = {ifid:row.id,deptid:row.getAttribute('deptid')};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog();
	});
}
function formAssign(fid, ifid, deptid) {
	var url = sz_BaseUrl + '/user/report/doAssign?ifid=' + ifid + '&deptid='
			+ deptid;
	var data = $('#form_assign').serialize();
	qssAjax.call(url, data, function(jreturn) {
		$('#qss_trace').dialog('close');
		rowSearch(fid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowFinish() {
	if (row == null || isDisabled('btnFINISH')) {
		return;
	}
	var url = sz_BaseUrl + '/user/report/finish';
	var data = {ifid:row.id,deptid:row.getAttribute('deptid')};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog();
	});
}
function formFinish(fid, ifid, deptid) {
	var url = sz_BaseUrl + '/user/report/doFinish?ifid=' + ifid + '&deptid='
			+ deptid;
	var data = $('#form_finish').serialize();
	qssAjax.call(url, data, function(jreturn) {
		$('#qss_trace').dialog('close');
		rowSearch(fid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowReject() {
	if (row == null || isDisabled('btnREJECT')) {
		return;
	}
	var url = sz_BaseUrl + '/user/report/reject';
	var data = {ifid:row.id,deptid:row.getAttribute('deptid')};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog();
	});
}
function rowRestore() {
	if (row == null) {
		
		return;
	}
	var ifid = row.id;
	var deptid = row.getAttribute('deptid');
	var url = sz_BaseUrl + '/user/report/restore';
	var data = {
		ifid : ifid,
		deptid : deptid
	};
	qssAjax.call(url, data, function(jreturn) {
		window.location.reload();
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function formReject(fid, ifid, deptid) {
	var url = sz_BaseUrl + '/user/report/doReject?ifid=' + ifid + '&deptid='
			+ deptid;
	var data = $('#form_finish').serialize();
	qssAjax.call(url, data, function(jreturn) {
		$('#qss_trace').dialog('close');
		rowSearch(fid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowSearch(fid) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var url = sz_BaseUrl + '/user/report/grid/?fid=' + fid;
	var data = $('#form_' + fid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage + '&'
			+ data, function(jreturn) {
		$('#qss_form').html(jreturn);
		resetBtn();
	});
}
function rowDelete(fid) {
	if (row == null) {
		
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var ifid = row.id;
		var deptid = row.getAttribute('deptid');
		var url = sz_BaseUrl + '/user/report/delete';
		var data = {
			ifid : ifid,
			deptid : deptid
		};
		qssAjax.call(url, data, function(jreturn) {
			rowSearch(fid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function rowSort(fid, id) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var url = sz_BaseUrl + '/user/report/grid/?fid=' + fid;
	var data = $('#form_' + fid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id + '&' + data, function(jreturn) {
		$('#qss_form').html(jreturn);
	});
}
function formEdit(fid, deptid) {
	var url = sz_BaseUrl + '/user/report/edit?ifid=' + fid + '&deptid='
			+ deptid;
	//window.location.href = url;
        openModule('', url);
}
function formBack(fid) {
	var url = sz_BaseUrl + '/user/report?fid=' + fid;
	//window.location.href = url;
        openModule('', url);
}
function formSave(fid, ifid, deptid) {
	var url = sz_BaseUrl + '/user/report/save?fid=' + fid + '&ifid=' + ifid
			+ '&deptid=' + deptid;
	var data = $('#form_' + fid + '_edit').serialize();
	qssAjax.call(url, data, function(jreturn) {
		//window.location.href = sz_BaseUrl + '/user/report?fid=' + fid;
                openModule('',sz_BaseUrl + '/user/report?fid=' + fid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
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
        openModule('', url);
}
function showPicture(id, file) {
	var url = sz_BaseUrl + '/user/field/picture?file=' + file;
	$('#' + id).attr('src', url);
}
function showBarcode(id, file) {
	var url = sz_BaseUrl + '/user/field/barcode?' + file;
	$('#' + id).attr('src', url);
}
function reloadUserStep(ifid, deptid, step, field) {
	var url = sz_BaseUrl + '/user/report/getUserStep';
	data = {
		ifid : ifid,
		deptid : deptid,
		step : step
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#' + field).html(jreturn);
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
	});
}
function rowImport(fid, deptid, objid) {

//	window.location.href = sz_BaseUrl + '/user/object/import?fid=' + fid
//			+ '&deptid=' + deptid + '&objid=' + objid;
        openModule('', sz_BaseUrl + '/user/object/import?fid=' + fid
			+ '&deptid=' + deptid + '&objid=' + objid);
}
function showSearch() {
	if ($('#div-search').is(":visible")) {
		$('#div-search').hide();
	} else {
		$('#div-search').show();
	}
}
function rowCleanSearch(fid) {
	$('#form_' + fid + '_filter')[0].reset();
	rowSearch(fid);
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

// Hidden report column
// @todo: Khong the xoa thang con tu thang cha khi thang con con 1 o
function hideReportCol(ahref)
{
	var ele = $(ahref).parent();
	var title_marker = [];
	var i = 0;
	var j = 0;
	var k = 0;
	var l = 0;
	var m;
	var col = parseInt($(ele).attr('col'));
	var total_col_remove = $(ele).attr('colspan')?parseInt($(ele).attr('colspan')):1;
	var remove_arr = [];
	var change_colspan;
	var colspan;
	var divColspanAll = 0;
	var colspanAll = 0;
	var restColspanAll = 0;
	var notDivColspanAll = false;
	var coldeleted = 0;
	// Lay tat ca cell la title ra de lay mang cac phan tu can xoa
	$('.report_title_marker').not(':hidden').each(function(){
		colspan    = $(this).attr('colspan');
		colspan    = colspan?parseInt(colspan):0;
		
		title_marker[i]    = [];
		title_marker[i][0] = $(this).get(0).id;
		title_marker[i][1] = parseInt($(this).attr('col')); /// start
		title_marker[i][2] = $(this).attr('colmerge')?parseInt($(this).attr('colmerge')):parseInt($(this).attr('col'));//seend
		title_marker[i][3] = colspan;
		title_marker[i][4] = $(this).width();
		i++;
	});
	
	for(j in title_marker)
	{
		//$('h2').append(col+'-'+title_marker[j][1]+'-'+title_marker[j][2]+'<br/>')
		if(col >=  title_marker[j][1] && col <=  title_marker[j][2])
		{
			// Tao mang xoa
			coldeleted = j;
			remove_arr[k]    = []; 
			remove_arr[k][0] = title_marker[j][0]; // id
			remove_arr[k][1] = total_col_remove; // col remove
			remove_arr[k][2] = title_marker[j][3]; // colspan thay 
			remove_arr[k][3] = title_marker[j][1]; // start
			remove_arr[k][4] = title_marker[j][2]; // end
			k++;
		}
	}

	for(l in remove_arr)
	{
		if((remove_arr[l][4] - remove_arr[l][3]) > 1)
		{
			divColspanAll += remove_arr[l][1];
			notDivColspanAll = true;
		}
		
		// Tru cho cac the co colspan >= 1
		// Neu colspan tru xong <= 1 thi remove
		if(remove_arr[l][2] > 1)
		{
			change_colspan = remove_arr[l][2] - remove_arr[l][1];
			
			if(change_colspan >= 1)
			{
				$('#'+remove_arr[l][0]).attr('colspan', change_colspan);
			}
			else
			{
				$('#'+remove_arr[l][0]).css('display', 'none');
				$('#'+remove_arr[l][0]).find('xls').val('');
				
				for(m = remove_arr[l][3]; m <= remove_arr[l][4]; m++)
				{
					$('td[col='+m+']').each(function(){
						$(this).css('display', 'none');
						$(this).find('xls').val('');
					});
					
					$('.report_title_marker[col='+m+']').each(function(){
						$(this).css('display', 'none');
						$(this).find('xls').val('');
					});		
				}
				
			}
		}
		else 
		{
			$('#'+remove_arr[l][0]).css('display', 'none');
			$('#'+remove_arr[l][0]).find('xls').val('');			

			$('td[col='+col+']').each(function(){
				$(this).css('display', 'none');
				$(this).find('xls').val('');
			});
			
			if(remove_arr[l][3] < remove_arr[l][4])
			{
				for(m = remove_arr[l][3]; m <= remove_arr[l][4]; m++)
				{
					if($('td[col='+m+']').length)
					{
						$('td[col='+m+']').each(function(){
							$(this).css('display', 'none');
							$(this).find('xls').val('');
						});
					}

					if($('.report_title_marker[col='+m+']').length)
					{
						$('.report_title_marker[col='+m+']').each(function(){
							$(this).css('display', 'none');
							$(this).find('xls').val('');
						});
					}
				}
			}
			
			
			if(!notDivColspanAll)
			{
				divColspanAll++;
			}
		}
	}
	
	
	// Thiet lap cac o group all
	colspanAll = $('.report_title_marker_all').attr('colspan')?parseInt($('.report_title_marker_all').attr('colspan')):0;
	restColspanAll = (colspanAll - divColspanAll);
	
	if(restColspanAll <= 0)
	{
		$('.report_title_marker_all').each(function(){
			$(this).css('display', 'none');
			$(this).find('xls').val('');
		});
	}
	else
	{
		$('.report_title_marker_all').each(function(){
			$(this).attr('colspan', restColspanAll);
		});		
	}
	if(coldeleted != 0)
	{
		var width = 0;
		for(j in title_marker)
		{
			if(coldeleted != j)
			{
				width +=  title_marker[j][4];
			}
		}
		for(j in title_marker)
		{
			if(coldeleted != j)
			{
				title_marker[j][5] = title_marker[j][4] / width;
			}
		}
		for(j in title_marker)
		{
			if(coldeleted != j)
			{
				var newwidth = title_marker[j][4];
				newwidth += title_marker[coldeleted][4] * title_marker[j][5];
				$('#'+title_marker[j][0]).width(newwidth);
			}
		}
	}	
}

// Add "hidden column" element
//function add_hide_report_col_tick(ele)
function addTickHideCol(ele)
{
	var colnum = $(ele).attr('col'); // Lay so thu tu hien tai cua cot can xoa
	var append_ele = ' <a class="option_column_tag"' +
		' style="position: absolute; height: 10px; top: 1px; right: 1px; z-index:1000;" '+
		' onclick="hideReportCol(this)" '+
		' href="#1">'+
		' <img src="/images/event/close.png">'+
		' </a>';

	// Xoa cac nut tick cu di
	$('.option_column_tag').each(function(){ $(this).hide();});
	
	// Chen them mot nut tick de cho nguoi dung xoa cot
	$(ele).css('position','relative');
	$(ele).append(append_ele);
	
	// Chen them su kien mouse out se xoa nut tick
	$(ele).attr('onmouseleave', 'removeTickHideCol(this)');
}

function removeTickHideCol(ele)
{
	$(ele).find('.option_column_tag').hide();
}

$(document).ajaxSuccess(function() {
	$('.remove_col').each(function() {
		$(this).attr('onmouseenter', 'addTickHideCol(this)');
	});
});