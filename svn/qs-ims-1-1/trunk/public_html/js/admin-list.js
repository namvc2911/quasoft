col = '';
textcol = '';
function rowCLK(el) {
	if (row != null) {
		row.style.background = col;
		row.style.color = textcol;
	}
	col = el.style.background;
	textcol = el.style.color;
	el.style.background = 'gray';
	el.style.color = '#ffffff';
	$('#horizontal-toolbar').find('button').each(function() {
		$(this).prop('disabled',false);
	});
	row = el;
	//grid_editing = true;
}
function rowDepartmentSearch() {
	var url = sz_BaseUrl + '/admin/department/reload';
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#qss_list').html(sz_Msg);
	});
}
function rowUserSearch() {
	var url = sz_BaseUrl + '/static/m005/reload';
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#qss_list').html(sz_Msg);
	});
}
function rowGroupSearch() {
	var url = sz_BaseUrl + '/static/m006/reload';
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#qss_list').html(sz_Msg);
	});
}
function rowDepartmentInsert() {
	var url = sz_BaseUrl + '/admin/department/edit';
	//window.location.href = url;
        openModule('', url);
}
function rowDepartmentEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/admin/department/edit?deptid=' + row.id;
	//window.location.href = url;
        openModule('', url);
}
function departmentSave(deptid) {
	var url = sz_BaseUrl + '/admin/department/save?deptid=' + deptid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
//		window.location.href = sz_BaseUrl + '/admin/department?deptid='
//				+ deptid;
            openModule('', sz_BaseUrl + '/admin/department?deptid='
				+ deptid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowDepartmentDelete() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/admin/department/delete?deptid=' + row.id;
}
function departmentBack() {
	var url = sz_BaseUrl + '/admin/department';
//	window.location.href = url;
        openModule('', url);
}

function rowUserInsert() {
	var url = sz_BaseUrl + '/static/m005/edit';
	//window.location.href = url;
        openModule('', url);
}
function rowUserEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/static/m005/edit?userid=' + row.id;
	//window.location.href = url;
        openModule('', url);
}
function userSave(userid) {
	var url = sz_BaseUrl + '/static/m005/save?userid=' + userid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		//window.location.href = sz_BaseUrl + '/static/m005?userid=' + userid;
                openModule('', sz_BaseUrl + '/static/m005?userid=' + userid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowUserDelete() {
	if (row == null) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/static/m005/delete';
		var data = {
			userid: row.id
		};
		qssAjax.call(url, data, function(jreturn) {
			 openModule('', sz_BaseUrl + '/static/m005');
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});	
	});
	
}
function userBack() {
	var url = sz_BaseUrl + '/static/m005';
	//window.location.href = url;
        openModule('', url);
}

function rowGroupInsert() {
	var url = sz_BaseUrl + '/static/m006/edit';
	//window.location.href = url;
        openModule('', url);
}
function rowGroupEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/static/m006/edit?groupid=' + row.id;
	//window.location.href = url;
        openModule('', url);
}
function groupSave(groupid) {
	var url = sz_BaseUrl + '/static/m006/save?groupid=' + groupid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		//window.location.href = sz_BaseUrl + '/static/m006?groupid=' + groupid;
                openModule('', sz_BaseUrl + '/static/m006?groupid=' + groupid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowGroupDelete() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/static/m006/delete?groupid=' + row.id;
}
function groupBack() {
	var url = sz_BaseUrl + '/static/m006';
	//window.location.href = url;
        openModule('', url);
}
function groupCheckAll(id) {
	$('#C_' + id).attr('checked', $('#C_' + id).is(':checked') ? false : true);
	$('#R_' + id).attr('checked', $('#R_' + id).is(':checked') ? false : true);
	$('#U_' + id).attr('checked', $('#U_' + id).is(':checked') ? false : true);
	$('#D_' + id).attr('checked', $('#D_' + id).is(':checked') ? false : true);
	$('#S_' + id).attr('checked', $('#S_' + id).is(':checked') ? false : true);
}
function stepCheckAll(id) {
	$('#C_Step_' + id).attr('checked', $('#C_Step_' + id).is(':checked') ? false : true);
	$('#R_Step_' + id).attr('checked', $('#R_Step_' + id).is(':checked') ? false : true);
	$('#U_Step_' + id).attr('checked', $('#U_Step_' + id).is(':checked') ? false : true);
	$('#D_Step_' + id).attr('checked', $('#D_Step_' + id).is(':checked') ? false : true);
	$('#S_Step_' + id).attr('checked', $('#S_Step_' + id).is(':checked') ? false : true);
}
function changeProfile(groupid) {
	var url = sz_BaseUrl + '/static/m012/change';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		qssAjax.notice(Language.translate('PROFILE_CHANGED'),function(){
			openModule('M000');
		});
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function templateFormWebSearch(val) {
	var url = sz_BaseUrl + '/admin/template/form/web/reload?fid=' + val;
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#qss_list').html(sz_Msg);
	});
	$('#btnINSERT').prop('disabled',false);
}
function templateFormWebSave(fid) {
	var url = sz_BaseUrl + '/admin/template/form/web/save?fid=' + fid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
//		window.location.href = sz_BaseUrl + '/admin/template/form/web?fid='
//				+ fid;
                openModule('', sz_BaseUrl + '/admin/template/form/web?fid='
				+ fid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function templateFormWebInsert() {
	var url = sz_BaseUrl + '/admin/template/form/web/edit?fid='
			+ $('#combobox').val();
	//window.location.href = url;
        openModule('', url);
}
function templateFormWebEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/admin/template/form/web/edit?fid='
			+ $('#combobox').val() + '&designid=' + row.id;
	//window.location.href = url;
        openModule('', url);
}
function templateFormWebBack(fid) {
	var url = sz_BaseUrl + '/admin/template/form/web?fid=' + fid;
	//window.location.href = url;
        openModule('', url);
}
function templateFormWebDelete() {
	fid = $('#combobox').val();
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var designid = row.id;
		var url = sz_BaseUrl + '/admin/template/form/web/delete';
		var data = {
			designid : designid
		};
		qssAjax.call(url, data, function(jreturn) {
			templateFormWebSearch(fid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});	
	});
}
// BEGIN OBJECR WEB
function templateObjectWebSearch(val) {
	var url = sz_BaseUrl + '/admin/template/object/web/reload?fid=' + val;
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#qss_list').html(sz_Msg);
	});
	$('#btnINSERT').prop('disabled',false);
}
function templateObjectWebSave(fid) {
	var url = sz_BaseUrl + '/admin/template/object/web/save?fid=' + fid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
//		window.location.href = sz_BaseUrl + '/admin/template/object/web?fid='
//				+ fid;
                openModule('', sz_BaseUrl + '/admin/template/object/web?fid='
				+ fid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function templateObjectWebInsert() {
	var url = sz_BaseUrl + '/admin/template/object/web/edit?fid='
			+ $('#combobox').val();
	//window.location.href = url;
        openModule('', url);
}
function templateObjectWebEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/admin/template/object/web/edit?fid='
			+ $('#combobox').val() + '&designid=' + row.id;
	window.location.href = url;
}
function templateObjectWebBack(fid) {
	var url = sz_BaseUrl + '/admin/template/object/web?fid=' + fid;
	//window.location.href = url;
        openModule('', url);
}
function templateObjectWebDelete() {
	fid = $('#combobox').val();
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var designid = row.id;
		var url = sz_BaseUrl + '/admin/template/object/web/delete';
		var data = {
			designid : designid
		};
		qssAjax.call(url, data, function(jreturn) {
			templateObjectWebSearch(fid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
// END OBJECR WEB

function printSearch(val) {
	var url = sz_BaseUrl + '/admin/print/reload?fid=' + val;
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#qss_list').html(sz_Msg);
	});
	$('#btnINSERT').prop('disabled',false);
}
function printSave(fid) {
	var url = sz_BaseUrl + '/admin/print/save?FID=' + fid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
//		window.location.href = sz_BaseUrl + '/admin/print?fid='
//				+ fid;
                openModule('', sz_BaseUrl + '/admin/print?fid='
				+ fid);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function printInsert() {
	var url = sz_BaseUrl + '/admin/print/edit?fid='
			+ $('#combobox').val();
	window.location.href = url;
}
function printEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/admin/print/edit?fid='
			+ $('#combobox').val() + '&designid=' + row.id;
//	window.location.href = url;
        openModule('', url);
}
function templateFormPrintBack(fid) {
	var url = sz_BaseUrl + '/admin/print?fid=' + fid;
//	window.location.href = url;
        openModule('', url);
}
function printDelete() {
	fid = $('#combobox').val();
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var designid = row.id;
		var url = sz_BaseUrl + '/admin/print/delete';
		var data = {
			designid : designid
		};
		qssAjax.call(url, data, function(jreturn) {
			templateFormPrintSearch(fid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}


function rowTemplateWebSearch() {
	var url = sz_BaseUrl + '/admin/template/web/load';
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#qss_list').html(sz_Msg);
	});
}
function rowTemplateWebSave(fid) {
	var url = sz_BaseUrl + '/admin/template/web/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/admin/template/web';
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowTemplateWebInsert() {
	var url = sz_BaseUrl + '/admin/template/web/edit';
//	window.location.href = url;
        openModule('', url);
}
function rowTemplateWebEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/admin/template/web/edit?cms_id=' + row.id;
//	window.location.href = url;
        openModule('', url);
        
}
function rowTemplateWebBack(fid) {
	var url = sz_BaseUrl + '/admin/template/web';
//	window.location.href = url;
        openModule('', url);
}
function rowTemplateWebDelete() {
	qssAjax.alert(Language.translate('FUNCTION_NOT_SUPPORT'));
	return;
	fid = $('#combobox').val();
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var designid = row.id;
		var url = sz_BaseUrl + '/admin/template/web/delete';
		var data = {
			designid : designid
		};
		qssAjax.call(url, data, function(jreturn) {
			templateObjectPrintSearch(fid);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function templateWebJSCSSEdit(file) {
	var url = sz_BaseUrl + '/admin/template/web/jscss/edit?file=' + file;
//	window.location.href = url;
        openModule('', url);
}
function templateWebJSCSSBack() {
	var url = sz_BaseUrl + '/admin/template/web/jscss';
//	window.location.href = url;
        openModule('', url);
}
function templateWebJSCSSDelete(file) {
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/admin/template/web/jscss/delete';
		var data = {
			file : file
		};
		qssAjax.call(url, data, function(jreturn) {
//			window.location.href = sz_BaseUrl + '/admin/template/web/jscss';
                        openModule('', sz_BaseUrl + '/admin/template/web/jscss');
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function templateWebJSCSSSave() {
	var url = sz_BaseUrl + '/admin/template/web/jscss/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
//		window.location.href = sz_BaseUrl + '/admin/template/web/jscss';
                openModule('', sz_BaseUrl + '/admin/template/web/jscss');
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowParamEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/admin/param/edit?id=' + row.id;
//	window.location.href = url;
        openModule('', url);
}
function paramSave() {
	var url = sz_BaseUrl + '/admin/param/save';
	var data = $('#qss_param').serialize();
	qssAjax.call(url, data, function(jreturn) {
//		window.location.href = sz_BaseUrl + '/admin/param';
                openModule('', sz_BaseUrl + '/admin/param');
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function paramBack() {
	var url = sz_BaseUrl + '/admin/param';
//	window.location.href = url;
        openModule('', url);
}
function rowDocumentInsert() {
	var url = sz_BaseUrl + '/admin/document/edit';
//	window.location.href = url;
        openModule('', url);
}
function rowDocumentEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/admin/document/edit?dtid=' + row.id;
//	window.location.href = url;
        openModule('', url);
}
function documentSave(dtid) {
	var url = sz_BaseUrl + '/admin/document/save?dtid=' + dtid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
//		window.location.href = sz_BaseUrl + '/admin/document?dtid='
//				+ dtid;
                openModule('',  sz_BaseUrl + '/admin/document?dtid='
				+ dtid);
                        
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowDocumentDelete() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/admin/document/delete?dtid=' + row.id;
        //openModule('', url);
}
function documentBack() {
	var url = sz_BaseUrl + '/admin/document';
//	window.location.href = url;
        openModule('', url);
}
function downloadTemplate(id) {
	var url = sz_BaseUrl + '/admin/document/download?id=' + id;
//	window.location.href = url;
        openModule('', url);
}
function deleteTemplate(id) {
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/admin/document/delete/template?id=' + id;
		var data = {id:id};
		qssAjax.call(url, data, function(jreturn) {
			$('#template_exists').hide();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function reportSearch(val) {
	var url = sz_BaseUrl + '/static/m021/reload?fid=' + val;
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#qss_list').html(sz_Msg);
	});
	$('#btnINSERT').prop('disabled',false);
}
function reportInsert() {
	var url = sz_BaseUrl + '/user/report';
	var data = {fid: $('#combobox').val(),dashboard:1};
	qssAjax.getHtml(url, data, function(jreturn) {
		if(jreturn!=''){
			$('#qss_trace').html(jreturn);
			$('#qss_trace').dialog({  width: 400 ,height: 200});	
		}
	});
}
function reportEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/user/report';
	var data = {fid: $('#combobox').val(),urid:row.id,dashboard:1};
	qssAjax.getHtml(url, data, function(jreturn) {
		if(jreturn!=''){
			$('#qss_trace').html(jreturn);
			$('#qss_trace').dialog({  width: 400 , height: 200 });	
		}
	});
}