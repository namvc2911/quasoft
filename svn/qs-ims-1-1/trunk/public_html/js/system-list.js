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
function rowSearch() {
	var url = sz_BaseUrl + '/system/form/reload';
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#qss_form_list').html(sz_Msg);
		row = null;
	});
}
function rowFormInsert(type) {
	var url = sz_BaseUrl + '/system/form/edit?type=' + type;
	window.location.href = url;
}
function rowFormDelete() {

}
function rowFormEdit(type) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/form/edit?type=' + type + '&fid=' + row.id;
	window.location.href = url;
}
function rowFormDuplicate(type) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/form/duplicate?type=' + type + '&fid='
			+ row.id;
	window.location.href = url;
}
function rowFormObject(type) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/form/object?type=' + type + '&fid='
			+ row.id;
	window.location.href = url;
}
function rowFormWorkflow() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/workflow?fid=' + row.id;
	window.location.href = url;
}
function rowFormObjectInsert(type, fid) {
	var url = sz_BaseUrl + '/system/form/object/edit?type=' + type + '&fid='
			+ fid;
	window.location.href = url;
}
function rowFormObjectEdit(type, fid) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/form/object/edit?type=' + type + '&objid='
			+ row.id + '&fid=' + fid;
	window.location.href = url;
}
function rowFormObjectDelete(fid) {
	if (row == null) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/system/form/object/delete';
		var data = {
			fid   :fid,
			objid : row.id
		};
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function rowFormObjectBack(type, fid) {
	var url = sz_BaseUrl + '/system/form?type=' + type + '&fid=' + fid;
	window.location.href = url;
}
function formObjectSave(fid) {
	var url = sz_BaseUrl + '/system/form/object/save?fid=' + fid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/form/object?fid=' + fid;
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function formObjectBack(fid) {
	var url = sz_BaseUrl + '/system/form/object?fid=' + fid;
	window.location.href = url;
}
function formSave(type, notback) {
	var url = sz_BaseUrl + '/system/form/save?type=' + type;
	$('#document_list').find("option").attr("selected", true);
	$('#activity_list').find("option").attr("selected", true);
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		if(notback == undefined || !notback)
		{
			window.location.href = sz_BaseUrl + '/system/form?type=' + type;
		}
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function formDuplicate(type) {
	var url = sz_BaseUrl + '/system/form/duplicate/save?type=' + type;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/form?type=' + type;
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function formBack(type) {
	var url = sz_BaseUrl + '/system/form?type=' + type;
	window.location.href = url;
}

function rowObjectSearch() {
	var url = sz_BaseUrl + '/system/object/reload';
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#qss_object_list').html(sz_Msg);
		row = null;
	});
}
function rowObjectInsert() {
	var url = sz_BaseUrl + '/system/object/edit';
	window.location.href = url;
}
function rowObjectEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/object/edit?objid=' + row.id;
	window.location.href = url;
}
function objectSave(notback) {
	var url = sz_BaseUrl + '/system/object/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
        if(notback == undefined || !notback)
        {
            window.location.href = sz_BaseUrl + '/system/object';
        }
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowObjectDelete() {

}
function rowObjectField() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/field?objid=' + row.id;
	window.location.href = url;
}
function rowFieldInsert(objid) {
	var url = sz_BaseUrl + '/system/field/edit?objid=' + objid;
	window.location.href = url;
}
function rowFieldEdit(objid) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/field/edit?objid=' + objid + '&fieldid='
			+ row.id;
	window.location.href = url;
}
function rowFieldBack() {
	var url = sz_BaseUrl + '/system/object';
	window.location.href = url;
}
function rowFieldDelete(objid) {
	if (row == null) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/system/field/delete';
		var data = {objid:objid,fieldid:row.id};
		qssAjax.call(url, data, function(jreturn) {
			window.location.href = sz_BaseUrl + '/system/field?objid='+objid;
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function fieldBack(objid) {
	var url = sz_BaseUrl + '/system/field?objid=' + objid;
	window.location.href = url;
}
function fieldLoadObject(fid, selected) {
	var url = sz_BaseUrl + '/system/field/load/object';
	var data = {
		fid : fid,
		selected : selected
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#intRefObjID').html(jreturn);
	});
}
function fieldLoadField(objid, selected) {
	var url = sz_BaseUrl + '/system/field/load/field';
	var data = {
		objid : objid,
		selected : selected
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#intRefFieldID').html(jreturn);
	});
}
function fieldLoadDisplayField(objid, selected) {
	var url = sz_BaseUrl + '/system/field/load/field';
	var data = {
		objid : objid,
		selected : selected
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#intRefDisplayID').html(jreturn);
	});
}
function fieldSave(objid, notback) {
	var url = sz_BaseUrl + '/system/field/save?objid=' + objid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
        if(notback == undefined || !notback)
        {
            window.location.href = sz_BaseUrl + '/system/field?objid=' + objid;
        }
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowWorkflowBack(type) {
	var url = sz_BaseUrl + '/system/form?type=' + type;
	window.location.href = url;
}
function rowWorkflowInsert(fid) {
	var url = sz_BaseUrl + '/system/workflow/edit?fid=' + fid;
	window.location.href = url;
}
function rowWorkflowEdit(fid) {
	var url = sz_BaseUrl + '/system/workflow/edit?fid=' + fid + '&wfid='
			+ row.id;
	window.location.href = url;
}
function workflowBack(fid) {
	var url = sz_BaseUrl + '/system/workflow?fid=' + fid;
	window.location.href = url;
}
function workflowBack(fid) {
	var url = sz_BaseUrl + '/system/workflow?fid=' + fid;
	window.location.href = url;
}
function workflowSave(fid) {
	var url = sz_BaseUrl + '/system/workflow/save?fid=' + fid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/workflow?fid=' + fid;
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowWorkflowDelete(fid) {
	if (row == null) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/system/workflow/delete?fid=' + fid;
		var data = {
			wfid : row.id
		};
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function rowWorkflowStep() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/workflow/step?wfid=' + row.id;
	window.location.href = url;
}
function rowWorkflowStepBack(fid) {
	var url = sz_BaseUrl + '/system/workflow?fid=' + fid;
	window.location.href = url;
}
function rowWorkflowStepInsert(wfid) {
	var url = sz_BaseUrl + '/system/workflow/step/edit?wfid=' + wfid;
	window.location.href = url;
}
function rowWorkflowStepEdit(wfid) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/workflow/step/edit?wfid=' + wfid + '&sid='
			+ row.id;
	window.location.href = url;
}
function rowWorkflowStepDelete(wfid) {
	if (row == null) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/system/workflow/step/delete?wfid=' + wfid;
		var data = {
			sid : row.id
		};
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function workflowStepBack(wfid) {
	var url = sz_BaseUrl + '/system/workflow/step?wfid=' + wfid;
	window.location.href = url;
}
function workflowStepSave(wfid, notback) {
	var url = sz_BaseUrl + '/system/workflow/step/save?wfid=' + wfid;
	$('#document_list').find("option").attr("selected", true);
	$('#activity_list').find("option").attr("selected", true);
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
        if(notback == undefined || !notback)
        {
            window.location.href = sz_BaseUrl + '/system/workflow/step?wfid='
                + wfid;
        }
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowFormMenuInsert(id,mid) {
	var url = sz_BaseUrl + '/system/menu/edit/?parentid=' + id + '&mid=' + mid;
	window.location.href = url;
}
function rowFormMenuEdit(mid) {
	var menuid = $('#menuid').val();
	if(menuid != '')
	{
		var url = sz_BaseUrl + '/system/menu/edit?id=' + menuid + '&mid=' + mid;
		window.location.href = url;
	}
}
function formMenuBack(mid) {
	var url = sz_BaseUrl + '/system/menu?mid=' + mid;
	window.location.href = url;
}
function reloadMenu(el) {
	var url = sz_BaseUrl + '/system/menu?mid=' + el.value;
	window.location.href = url;
}
function formMenuSave(mid) {
	var url = sz_BaseUrl + '/system/menu/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/menu?mid='+mid;
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowFormMenuDelete() {
	var menuid = $('#menuid').val();
	if(menuid != '')
	{
		qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
			var url = sz_BaseUrl + '/system/menu/delete';
			var data = {
				id : menuid
			};
			qssAjax.call(url, data, function(jreturn) {
				window.location.reload();
			}, function(jreturn) {
				qssAjax.alert(jreturn.message);
			});
		});
	}
}
function rowFormTransferInsert() {
	var url = sz_BaseUrl + '/system/form/transfer/edit';
	window.location.href = url;
}
function rowFormTransferEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/form/transfer/edit/?trid=' + row.id;
	window.location.href = url;
}
function rowFormTransferDelete() {
	if (row == null) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/system/form/transfer/delete';
		var data = {
			ftid : row.id
		};
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function formTransferSave() {
	var url = sz_BaseUrl + '/system/form/transfer/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/form/transfer';
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function formTransferBack() {
	var url = sz_BaseUrl + '/system/form/transfer';
	window.location.href = url;
}
function formTransferLoadObject(fid, ele, selected) {
	var url = sz_BaseUrl + '/system/field/load/object';
	var data = {
		fid : fid,
		selected : selected
	};
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#' + ele).html(sz_Msg);
	});
}
function formTransferLoadField(objid, ele, selected) {
	var url = sz_BaseUrl + '/system/field/load/field';
	var data = {
		objid : objid,
		selected : selected
	};
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#' + ele).html(sz_Msg);
	});
}
function rowFormInheritInsert() {
	var url = sz_BaseUrl + '/system/form/inherit/edit';
	window.location.href = url;
}
function rowFormInheritEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/form/inherit/edit?fiid='+row.id;
	window.location.href = url;
}
function rowFormInheritDelete() {
	if (row == null) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/system/form/inherit/delete';
		var data = {
			fiid : row.id
		};
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function formInheritSave() {
	var url = sz_BaseUrl + '/system/form/inherit/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/form/inherit';
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function formInheritBack() {
	var url = sz_BaseUrl + '/system/form/inherit';
	window.location.href = url;
}
function add_form(fid,selected) {
	var url = sz_BaseUrl + '/system/form/inherit/edit/load/form';
	var data = {
		toid : fid,
		selected: selected
	};
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#FFID').html(sz_Msg);
	});
}
function add_object(fromid,selected) {
	var url = sz_BaseUrl + '/system/form/inherit/edit/load/object';
	var data = {
		toid : $('#TFID').val(),
		fromid : fromid,
		selected: selected
	};
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#ObjID').html(sz_Msg);
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
				$('#btnUPDATE').prop('disabled',false);
			}
		},
		error : function(data, status, e) {
			/* If upload error */
			qssAjax.alert(e);
		}
	});
}
function deleteFormTemplate(fid) {
	var url = sz_BaseUrl + '/system/index/delete/form/template';
	data = {
		fid : fid
	};
	qssAjax.call(url, data, function(jreturn) {
		qssAjax.notice('Template đã được xóa!');
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function deleteAllTemplate(fid) {
	var url = sz_BaseUrl + '/system/index/delete/template';
	data = {
		fid : fid
	};
	qssAjax.call(url, data, function(jreturn) {
		qssAjax.notice('Template đã được xóa!');
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function deleteObjectTemplate() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/index/delete/object/template';
	data = {
		objid : row.id
	};
	qssAjax.call(url, data, function(jreturn) {
		qssAjax.notice('Template đã được xóa!');
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowParamInsert() {
	var url = sz_BaseUrl + '/system/param/edit';
	window.location.href = url;
}
function rowParamDelete() {
	if (row == null) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/system/param/delete';
		data = {
			PID : row.id
		};
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function rowParamEdit() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/param/edit?id=' + row.id;
	window.location.href = url;
}
function paramSave() {
	var url = sz_BaseUrl + '/system/param/save';
	var data = $('#qss_param').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/param';
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function paramBack() {
	var url = sz_BaseUrl + '/system/param';
	window.location.href = url;
}
function rowLanguageInsert() {
	var url = sz_BaseUrl + '/system/language/edit';
	window.location.href = url;
}
function rowLanguageEdit(type) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/language/edit?code=' + row.id;
	window.location.href = url;
}
function LanguageSave() {
	var url = sz_BaseUrl + '/system/language/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/language';
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function LanguageBack(type) {
	var url = sz_BaseUrl + '/system/language';
	window.location.href = url;
}
function translationSave(id) {
	var url = sz_BaseUrl + '/system/language/translate/save?id='+id;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/language/translate';
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function translationDelete(id) {
	var url = sz_BaseUrl + '/system/language/translate/delete';
	var data = {id:id};
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/language/translate';
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowFormExport() {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/form/export?fid=' + row.id;
	window.location.href = url;
}
function rowFormImport() {
	var url = sz_BaseUrl + '/system/form/import';
	window.location.href = url;
}
function formImport(){
	if($('#btnUPDATE').hasClass('btn_disabled')){
		return;
	}
	var url = sz_BaseUrl + '/system/form/do/import';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		$('#qss_import').html(jreturn.message);
	}, function(jreturn) {
		$('#qss_import').html(jreturn.message);
	});
}
function menuCLK() {
	$('#horizontal-toolbar').find('button').each(function() {
		$(this).prop('disabled',false);
	});
}
function showFilter(){
	var fid = $('#intRefFID').val();
	if(fid == 0){
		qssAjax.alert('Bạn phải chọn mô đun tham chiếu');
		return;
	}
	var url = sz_BaseUrl + '/system/field/filter';
	qssAjax.getHtml(url, {fid:fid,params:$('#szFilter').val()}, function(jreturn) {
		if(jreturn!=''){
			$('#qss_dialog').html(jreturn);
			$('#qss_dialog').dialog({ maxHeight: 400 });	
		}
	});
}
function rowCalendarInsert() {
	var url = sz_BaseUrl + '/system/calendar/edit';
	window.location.href = url;
}
function rowCalendarDelete() {
	if (row == null) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/system/calendar/delete';
		var data = {
			foid : row.id
		};
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function rowCalendarEdit(type) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/calendar/edit?fid=' + row.id;
	window.location.href = url;
}
function calendarSave() {
	var url = sz_BaseUrl + '/system/calendar/save';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/calendar';
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function calendarBack() {
	var url = sz_BaseUrl + '/system/calendar';
	window.location.href = url;
}
function rowUIGroup() {
	var objid = $('#objid').val();
	if(objid === undefined || objid == '' || objid == 0){
		return;
	}
	var url = sz_BaseUrl + '/system/ui/group?objid=' + objid;
	window.location.href = url;
}
function rowUIGroupInsert(objid) {
	var url = sz_BaseUrl + '/system/ui/group/edit?objid='+objid;
	window.location.href = url;
}
function rowUIGroupEdit(objid) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/ui/group/edit?objid=' + objid +'&uigid=' + row.id;
	window.location.href = url;
}
function UIGroupSave(objid) {
	var url = sz_BaseUrl + '/system/ui/group/save?objid='+objid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/ui/group?objid='+objid;
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowUIGroupDelete() {

}
function UIGroupBack(objid) {
	var url = sz_BaseUrl + '/system/ui/group?objid='+objid;
	window.location.href = url;
}
function rowUIBox(objid) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/ui/box?objid=' + objid + '&gid=' +row.id;
	window.location.href = url;
}
function rowUIBoxInsert(objid,gid) {
	var url = sz_BaseUrl + '/system/ui/box/edit?objid='+objid+'&gid='+gid;
	window.location.href = url;
}
function rowUIBoxEdit(objid,gid) {
	if (row == null) {
		return;
	}
	var url = sz_BaseUrl + '/system/ui/box/edit?objid=' + objid +'&gid='+gid+'&bid=' + row.id;
	window.location.href = url;
}
function UIBoxSave(objid,gid) {
	var url = sz_BaseUrl + '/system/ui/box/save?objid='+objid;
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		window.location.href = sz_BaseUrl + '/system/ui/box?objid='+objid+'&gid='+gid;
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowUIBoxDelete() {

}
function rowUIBoxBack(objid) {
	var url = sz_BaseUrl + '/system/ui/group?objid='+objid;
	window.location.href = url;
}
function UIBoxBack(objid,gid) {
	var url = sz_BaseUrl + '/system/ui/box?objid='+objid+'&gid='+gid;
	window.location.href = url;
}
function editApprover(said,sid,wfid) {
	var url = sz_BaseUrl + '/system/workflow/step/approver';
	var data = {said:said,sid:sid,wfid:wfid};
	qssAjax.getHtml(url, data, function(jreturn) {
		if(jreturn!=''){
			$('#qss_dialog').html(jreturn);
			$('#qss_dialog').dialog({ });	
		}
	});
}