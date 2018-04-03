col = '';
textcol = '';
function connectDatabase() {
	var url = sz_BaseUrl + '/system/database/connect';
	var data = $('#qss_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		alert('OK');
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}

function loadForms() {
	var data = {viewall:$('#viewall').is(':checked')?1:0};
	var url = sz_BaseUrl + '/system/database/form';
	qssAjax.getHtml(url, data, function(sz_Msg) {
		$('#form_div').html(sz_Msg);
	});
}

function showForm(fid){
	var url = sz_BaseUrl + '/system/database/form/edit';
	qssAjax.getHtml(url, {fid:fid}, function(jreturn) {
		if(jreturn!=''){
			$('#qss_dialog').html(jreturn);
			$('#qss_dialog').dialog({ width: 500,height:500 });	
		}
	});
}
function copyForm() {
	var url = sz_BaseUrl + '/system/database/form/copy';
	var data = $('#qss_copy_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		$('#qss_dialog').dialog('close');
		loadForms();
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}

function copyMenus() {
	var url  = sz_BaseUrl + '/system/database/menu';
	var data = $('#qss_form').serialize();;

	qssAjax.confirm('Menu cũ sẽ bị xóa đi thay bằng menu mới, bạn muốn tiếp tục chứ? ', function(){
		qssAjax.call(url, data, function(jreturn) {
			if(jreturn.message != '')
			{
				qssAjax.alert(jreturn.message);
			}
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}

function loadObjects() {
	var url = sz_BaseUrl + '/system/database/object';
	qssAjax.getHtml(url, {}, function(sz_Msg) {
		$('#form_div').html(sz_Msg);
	});
}

function showObject(objid){
	var url = sz_BaseUrl + '/system/database/object/edit';
	qssAjax.getHtml(url, {objid:objid}, function(jreturn) {
		if(jreturn!=''){
			$('#qss_dialog').html(jreturn);
			$('#qss_dialog').dialog({ width: 500,height:500 });	
		}
	});
}
function copyField() {
	var url = sz_BaseUrl + '/system/database/field/copy';
	var data = $('#qss_copy_form').serialize();
	qssAjax.call(url, data, function(jreturn) {
		$('#qss_dialog').dialog('close');
		loadObjects();
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function detachObjectInForm(formcode,objectcode) {
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/system/database/object/detach';
		var data = {formcode:formcode,objectcode:objectcode};
		qssAjax.call(url, data, function(jreturn) {
			loadForms();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function deleteField(objectcode,fieldcode) {
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		var url = sz_BaseUrl + '/system/database/field/delete';
		var data = {objectcode:objectcode,fieldcode:fieldcode};
		qssAjax.call(url, data, function(jreturn) {
			loadObjects();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}