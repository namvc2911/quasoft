var reffieldid = 0;
var refelement = null;
var isrefresh = 0;
function selectCLK(el) {
	if(!el){
		return;
	}
	if (select != null) {
		if(select != el){
			//edit = false;
		}
		select.style.background = col;
		select.style.color = textcol;
	}
	col = el.style.background;
	el.style.background = 'gray';
	textcol = el.style.color;
	el.style.color = '#ffffff';
	select = el;
	$.cookie('object_selected', select.id,{path:'/'});
}
function popupSelect(fieldid, ele, refresh) {
	isrefresh = refresh;
	var url = sz_BaseUrl + '/user/form/select';
	var data = {
		fieldid : fieldid
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_combo').html(jreturn);
		$('#qss_combo').dialog();
	});
}
function popupAttr(fieldid, ele, refresh) {
	refelement = ele;
	isrefresh = refresh;
	var url = sz_BaseUrl + '/extra/element/attr?fieldid='+fieldid;
	var fid = $('#fid').val();
	var objid = $('#objid').val();
	if($('#object_' + objid + '_edit').length){
		var data = $('#object_' + objid + '_edit').serialize();
		$('#object_' + objid + '_edit').find('input[type=checkbox]').each(function(){
			if(!$(this).is(':checked')){
				data += '&' + $(this).attr('name') +'=';
			}
		});
	}
	else{
		var data = $('#form_' + fid + '_edit').serialize();
		$('#form_' + fid + '_edit').find('input[type=checkbox]').each(function(){
			if(!$(this).is(':checked')){
				data += '&' + $(this).attr('name') +'=';
			}
		});
	}
	
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_combo').html(jreturn);
		$('#qss_combo').dialog({ width: 500,height:500 });
	});
}
function selectSearch(fid,objid) {
	var pageno = $('#qss_filter_pageno').val();
	var perpage = $('#qss_filter_perpage').val();
	var groupby = $('#qss_filter_groupby').val();
	var url = sz_BaseUrl + '/user/form/select/?fid=' + fid + '&objid=' + objid;
	var data = $('#form_' + fid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&groupby=' + groupby + '&fieldid=' + reffieldid + '&' + data,
			function(jreturn) {
				$('#qss_combo').html(jreturn);
			});
}
function selectSort(fid,objid, id) {
	var pageno = $('#qss_filter_pageno').val();
	var perpage = $('#qss_filter_perpage').val();
	var groupby = $('#qss_filter_groupby').val();
	var url = sz_BaseUrl + '/user/form/select/?fid=' + fid + '&objid=' + objid;
	var data = $('#form_' + fid + '_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id + '&groupby=' + groupby + '&fieldid='
			+ reffieldid + '&' + data, function(jreturn) {
		$('#qss_combo').html(jreturn);
	});
}
function selectCleanSearch(fid,objid) {
	$('form[name=filter_form] :input').each(function(){
		$(this).val('');
	});
	selectSearch(fid,objid);
}
function selectTrace() {
	if (select == null) {
		return;
	}
	$(refelement).html($(select).attr('vdisplay'));
	$('#' + $(refelement).attr('refid')).val(
			$(select).attr('vid') + ',' + $(select).attr('ioid'));
	$('#qss_combo').html('');
	$('#qss_combo').dialog('close');
	if (isrefresh == 1) {
		rowEditRefresh(null,1);
	}
	select = null;
}
function isDisabled(name)
{
	return $('#'+name).is('.btn_disabled');
}
function formTrace() {
	ifid = $('#ifid').val();
	deptid = $('#deptid').val();
	if(ifid == 0 || ifid ==''){
		return;
	}		
	var url = sz_BaseUrl + '/user/form/trace';
	var data = {
			ifid : ifid,
			deptid : deptid
		};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function rowValidate(fid) {
	if (isDisabled('btnVALIDATE')) {
		return;
	}
	var ifid= 0;
	var deptid = 0;
	ifid = $('#ifid').val();
	deptid = $('#deptid').val();
	if(ifid == 0 || ifid ==''){
		return;
	}		
	var url = sz_BaseUrl + '/user/form/validate';
	var data = {
		ifid : ifid,
		deptid : deptid
	};
	qssAjax.call(url, data, function(jreturn) {
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}
function rowPrint(fid) {
	if (isDisabled('btnPRINT')) {
		return;
	}
	var ifid= 0;
	var deptid = 0;
	ifid = $('#ifid').val();
	deptid = $('#deptid').val();
	if(ifid == 0 || ifid ==''){
		return;
	}		
	var url = sz_BaseUrl + '/user/form/print';
	var data = {
			ifid : ifid,
			deptid : deptid
		};
	var check_url = sz_BaseUrl + '/user/form/print/check?ifid=' + ifid + '&deptid=' + deptid;
	var params = [
	              'height='+screen.height,
	              'width='+screen.width,
	              'fullscreen=yes', // only works in IE, but here for completeness
	              'maximize=yes' 
	          ].join(',');
	qssAjax.getHtml(check_url, {}, function(jreturn) {
		if(/<[a-z][\s\S]*>/i.test(jreturn)){
			$('#qss_trace').html(jreturn);
			$('#qss_trace').dialog({ width: 200,height:300 });	
		}
		else{
			window.open(jreturn + '&ifid=' + ifid + '&deptid=' + deptid, '_blank',params);
		}
	});
}
function rowRecord() {
	var ifid= 0;
	var deptid = 0;
	ifid = $('#ifid').val();
	deptid = $('#deptid').val();
	if(ifid == 0 || ifid ==''){
		return;
	}		
	var url = sz_BaseUrl + '/user/form/document';
	var data = {ifid:ifid,deptid:deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
		$('#upload').file(function(inp) {
			inp.id = inp.name = 'myFileInput';
			
			/* Upload file by ajax */
			disabledLayout();
			$.ajaxFileUpload({
				url : sz_BaseUrl + '/static/m016/upload?folder='+$('#upload_folder').val(),
				secureuri : false,
				fileElementId : inp,
				dataType : 'json',
				success : function(data, status) {
					/* Upload file successfully */
					if (data.error) {
						qssAjax.alert(data.message);
						enabledLayout();
					} else {
						//attachDocument(data.id,row.id);
						$('#document_id').val(data.id);
						$('#document').val(data.name);
						enabledLayout();
					}
				},
				error : function(data, status, e) {
					/* If upload error */
					qssAjax.alert(e);
				}
			});
		});
	});
}
function rowWorkflow(el) {
	if (isDisabled('btnWORKFLOW')) {
		return;
	}
	var ifids = [];
	var deptids = [];
	ifids[0] = $('#ifid').val();
	deptids[0] = $('#deptid').val();
	if($('#ifid').val() == 0 || $('#ifid').val() == ''){
		return;
	}		
	if($(el).parent().find('.dropdown-content').is(':visible')){
		$(el).parent().find('.dropdown-content').hide();
	}
	else{
		var url = sz_BaseUrl + '/user/form/workflow';
		var data = {
			ifid : ifids,
			deptid : deptids
		};
		qssAjax.getHtml(url, data, function(jreturn) {
			//$('#qss_trace').html(jreturn);
			//$('#qss_trace').dialog({ width: 600,height:400 });
			$(el).parent().find('.dropdown-content').html(jreturn);
			$(el).parent().find('.dropdown-content').show();
		});
		
	}
	return;
	var url = sz_BaseUrl + '/user/form/workflow';
	var data = {
		ifid : ifids,
		deptid : deptids
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 600,height:400 });
	});
}

function formSave(fid, ifid, deptid,back) {
	ifid = ifid ? ifid : $('#ifid').val();
	var url = sz_BaseUrl + '/user/form/save?fid=' + fid + '&ifid=' + ifid
			+ '&deptid=' + deptid;
	var data = $('#form_' + fid + '_edit').serialize();
	$('#form_' + fid + '_edit').find('input[type=checkbox]').each(function(){
		if(!$(this).is(':checked')){
			data += '&' + $(this).attr('name') +'=';
		}
	});
	qssAjax.call(url, data, function(jreturn) {
		if(jreturn.message != ''){
			qssAjax.notice(jreturn.message,function(){
				var lastActiveModule = $.cookie('lastActiveModule');
				if(!back && (!ifid || ifid == 0)){
					var url = sz_BaseUrl + '/user/form/edit?ifid=' + jreturn.status + '&deptid='+deptid; 
					$.cookie(lastActiveModule,url,{path:'/'});
					history.pushState(null, null,url);
					$('#ifid').val(jreturn.status);
					$('#btnDETAIL').prop('disabled',false);
					$('#btnFDELETE').prop('disabled',false);
					$('#btnWORKFLOW').prop('disabled',false);
					$('#btnSHARING').prop('disabled',false);
					$('#btnVALIDATE').prop('disabled',false);
					$('#btnSHARING').prop('disabled',false);
					$('#btnDOCUMENT').prop('disabled',false);
					$('#btnACTIVITY').prop('disabled',false);
					$('#btnEVENT').prop('disabled',false);
					$('#btnEMAIL').prop('disabled',false);
					$('#btnPRINT').prop('disabled',false);
					$('#tabs_sub').css('opacity',1);
					$('.extra-button-record').prop('disabled',false);
					$('.custom-button-field').prop('disabled',false);
					var objid = $.cookie('form_' + fid  + '_object_selected');
					if(objid === undefined || ($('#'+objid).length && $('#'+objid).is(":visible") === false)){
						objid = '';
					}
					if(objid != '' && objid != -1 && objid != -2 && objid != -3 ){
						rowObjectSearch(jreturn.status,deptid,0);
					}
					else if(objid == -1){
						rowDocument();
					}
					else if(objid == -2){
						rowSecure();
					}
					else if(objid == -3){
						rowComment();
					}
					else if($('#einfo_maintain_plan_tab_document').length != 0){
						rowDocument();
					}
					else if( $('#einfo_maintain_plan_tab_comment').length != 0){
						rowComment();
					}
					else if( $('#einfo_maintain_plan_tab_secure').length != 0){
						rowSecure();
					}
					else if( $('#einfo_maintain_plan_tab li').length != 0){
						rowObjectSearch(jreturn.status,deptid,0);
					}
				}
				fedit = false;
				$('#btnSAVE').prop('disabled',true);
				$('#btnSAVEBACK').prop('disabled',true);
				if(bLS){
					localStorage.removeItem(lastActiveModule);
				}
				//chuyển từ dưới lên trên
				if(esc && typeof save !== 'undefined' && $.isFunction(save)){
					$.when(save(back)).done(function(){
						if(back){
							openModule('',sz_BaseUrl + '/user/form?fid=' + fid);
						}
					});
				}
				else if(back){
					openModule('',sz_BaseUrl + '/user/form?fid=' + fid);
				}
			});
		}
		else{
			var lastActiveModule = $.cookie('lastActiveModule');
			if(!back && (!ifid || ifid == 0)){
				var url = sz_BaseUrl + '/user/form/edit?ifid=' + jreturn.status + '&deptid='+deptid; 
				$.cookie(lastActiveModule,url,{path:'/'});
				history.pushState(null, null,url);
				$('#ifid').val(jreturn.status);
				$('#btnDETAIL').prop('disabled',false);
				$('#btnFDELETE').prop('disabled',false);
				$('#btnWORKFLOW').prop('disabled',false);
				$('#btnSHARING').prop('disabled',false);
				$('#btnVALIDATE').prop('disabled',false);
				$('#btnSHARING').prop('disabled',false);
				$('#btnDOCUMENT').prop('disabled',false);
				$('#btnACTIVITY').prop('disabled',false);
				$('#btnEVENT').prop('disabled',false);
				$('#btnEMAIL').prop('disabled',false);
				$('#btnPRINT').prop('disabled',false);
				$('#tabs_sub').css('opacity',1);
				$('.extra-button-record').prop('disabled',false);
				$('.custom-button-field').prop('disabled',false);
				var objid = $.cookie('form_' + fid  + '_object_selected');
				if(objid === undefined || ($('#'+objid).length && $('#'+objid).is(":visible") === false)){
					objid = '';
				}
				if(objid != '' && objid != -1 && objid != -2 && objid != -3 ){
					rowObjectSearch(jreturn.status,deptid,0);
				}
				else if(objid == -1){
					rowDocument();
				}
				else if(objid == -2){
					rowSecure();
				}
				else if(objid == -3){
					rowComment();
				}
				else if($('#einfo_maintain_plan_tab_document').length != 0){
					rowDocument();
				}
				else if( $('#einfo_maintain_plan_tab_comment').length != 0){
					rowComment();
				}
				else if( $('#einfo_maintain_plan_tab_secure').length != 0){
					rowSecure();
				}
				else if( $('#einfo_maintain_plan_tab li').length != 0){
					rowObjectSearch(jreturn.status,deptid,0);
				}
			}
			fedit = false;
			$('#btnSAVE').prop('disabled',true);
			$('#btnSAVEBACK').prop('disabled',true);
			if(bLS){
				localStorage.removeItem(lastActiveModule);
			}
			//chuyển từ dưới lên trên
			if(esc && typeof save !== 'undefined' && $.isFunction(save)){
				$.when(save(back)).done(function(){
					if(back){
						openModule('',sz_BaseUrl + '/user/form?fid=' + fid);
					}
				});
			}
			else if(back){
				openModule('',sz_BaseUrl + '/user/form?fid=' + fid);
			}
		}
	}, function(jreturn) {
		back = false;
		qssAjax.alert(jreturn.message);
	},false);
	
}
function formDelete(fid) {
	if (isDisabled('btnFDELETE')) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		id = $('#ifid').val();
		did = $('#deptid').val();
		if(id == 0 || did ==''){
			return;
		}		
		var ifid = [];
		var deptid = [];
		ifid[0] = id;
		deptid[0] = did;
		var url = sz_BaseUrl + '/user/form/delete';
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
function formpopupOther(fid,path,el) {
	var ifid = 0;
	ifid = $('#ifid').val();
	//(ifid == 0 && $(el).is('.extra-button-record')) || 
	if ($(el).is('.extra-disabled')) {
		return;
	}
	var url = sz_BaseUrl + path;
	//var data = {fid:fid,ifid:ifid};
	var data = $('#form_' + fid + '_edit').serialize();
	$('#form_' + fid + '_edit').find('input[type=checkbox]').each(function(){
		if(!$(this).is(':checked')){
			data += '&' + $(this).attr('name') +'=';
		}
	});
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').dialog('close');
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 800,height:400 });
	});
}
function formbashRun(id,name,el,popup) {
	var ifid = 0;
	ifid = $('#ifid').val();
	if ((ifid == 0 && $(el).is('.extra-button-record'))|| $(el).is('.extra-disabled')) {
		return;
	}
	qssAjax.confirm(Language.translate('CONFIRM_PROCESS'),function(){
		var url = sz_BaseUrl + '/user/form/bash';
		var data = {id:id,ifid:ifid,popup:popup};
		qssAjax.call(url, data, function(jreturn) {
			qssAjax.notice(Language.translate('ACTION_DONE') + name + "\n" + jreturn.message);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}


function formopenOther(fid,path,el) {
	var ifid = 0;
	ifid = $('#ifid').val();
	if ((ifid == 0 && $(el).is('.extra-button-record')) || $(el).is('.extra-disabled')) {
		return;
	}
	var url = sz_BaseUrl + path+'?fid='+fid+'&ifid='+ifid+'&popup=1';


    var windowObjectReference;
    var strWindowFeatures = "modal=yes,menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes";
    windowObjectReference = window.open(url , "_blank", strWindowFeatures);

}
function rowDetail() {
	if (row == null || objDisabled('btnDETAIL')) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/detail';
	var data = {ifid:row.id,deptid:row.getAttribute('deptid')};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 900,height:450 });
	});
}
function sendRequest(fid,ifid, deptid,stepno,log) {
	if(fedit){
		$('#btnSAVE').click();
	}
	if(fedit){
		return;
	}
	if(edit){
		save();
	}
	if(edit){
		return;
	}
	$('button').attr('disabled','true');
	if(stepno === undefined){
		stepno = $('input[name="stepno"]:checked').val();
	}
	if(stepno === undefined){
		stepno = $('#stepno').val();
	}	
	var userid = $('#user_id').val();
	var comment = $('#comment').val();
	if(stepno == ''){
		qssAjax.alert(Language.translate('SELECT_ACTION'));
		return;
	}	
	var url = sz_BaseUrl + '/user/form/request';
	if(ifid == 0){
		ifid = $('#ifid').val();
	}
	if(log == 0){
		var data ={ifids:ifid,deptids:deptid,stepno:stepno,userid:userid,comment:comment};
		qssAjax.call(url, data, function(jreturn) {
			if(jreturn.message != ''){
				qssAjax.notice(jreturn.message,function(){
					window.location.reload();
				});	
			}
			else{
				window.location.reload();
			}
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
			$('button').removeAttr('disabled');
		});
		$('.dropdown-content').hide();
	}
	else{
		qssAjax.logs(Language.translate('CONFIRM_LOGS'),function(comment){
			if(comment == ''){
				qssAjax.alert(Language.translate('CONFIRM_LOGS'));
				$('button').removeAttr('disabled');
				return;
			}
			var data ={ifids:ifid,deptids:deptid,stepno:stepno,userid:userid,comment:comment};
			qssAjax.call(url, data, function(jreturn) {
				if(jreturn.message != ''){
					qssAjax.notice(jreturn.message,function(){
						window.location.reload();
					});	
				}
				else{
					window.location.reload();
				}
			}, function(jreturn) {
				qssAjax.alert(jreturn.message);
				$('button').removeAttr('disabled');
			});
			$('.dropdown-content').hide();
		},function(){
			$('button').removeAttr('disabled');
		});
	}
}
function onKeyupRefresh() {
	clearInterval(timer);
	timer = setTimeout(function() { //then give it a second to see if the user is finished
		var fid = $('#fid').val();
		var ifid = $('#ifid').val();
		var deptid = $('#deptid').val();
		var url = sz_BaseUrl + '/user/form/refresh?fid=' + fid + '&ifid=' + ifid
				+ '&deptid=' + deptid;
		var data = $('#form_' + fid + '_edit').serialize();
		$('#form_' + fid + '_edit').find('input[type=checkbox]').each(function(){
			if(!$(this).is(':checked')){
				data += '&' + $(this).attr('name') +'=';
			}
		});
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#qss_form').html(jreturn);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
    }, 2000);
	
}
function formPrintPreview(){
	var ifid = 0;
	var deptid = 0;
	ifid = $('#ifid').val();
	deptid = $('#deptid').val();
	if (ifid == 0) {
		return;
	}
	var designid = $("input[name='designid']:checked").val();
	var params = [
	              'height='+screen.height,
	              'width='+screen.width,
	              'fullscreen=yes', // only works in IE, but here for completeness
	              'maximize=yes' 
	          ].join(',');
	if(typeof designid != 'undefined'){
		if($("input[name='designid']:checked").attr('url') != ''){
			var url = sz_BaseUrl + $("input[name='designid']:checked").attr('url') + '&popup=1&ifid=' + ifid + '&deptid=' + deptid + '&design='+designid;
			window.open(url, '_blank',params);
		}
		else{
			var url = sz_BaseUrl + '/user/form/print?popup=1&ifid=' + ifid + '&deptid=' + deptid + '&design='+designid;
			window.open(url, '_blank',params);
		}	
	}
	else{
		qssAjax.alert('Bạn phải chọn một mẫu in.');
	}
}
function deletePicture(ele,name){
	qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
		$('#'+name).val('');
		$(ele).parent().find('img').remove();
	});
}
function approve(said,ifid, deptid) {
	qssAjax.confirm('Bạn có muốn duyệt bản ghi này không',function(){
		var url = sz_BaseUrl + '/user/form/approve';
		var data = {said:said,ifid:ifid,deptid:deptid};
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}

function reject(said,ifid, deptid) {
	qssAjax.confirm('Bạn có muốn hủy duyệt bản ghi này không',function(){
		var url = sz_BaseUrl + '/user/form/reject';
		var data = {said:said,ifid:ifid,deptid:deptid};
		qssAjax.call(url, data, function(jreturn) {
			window.location.reload();
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}
function setDefaultTimmer(el){
	var d = new Date();
    var n = d.getHours() + ':' + d.getMinutes();
    if(!$('input[name="'+$(el).attr('for')+'"]').prop('readonly')){
    	$('input[name="'+$(el).attr('for')+'"]').val(n);
    	$('input[name="'+$(el).attr('for')+'"]').keyup();
    }   
}
function deleteFile(fid,objid,fieldid,ifid,ioid,file) {
	var url = sz_BaseUrl + '/user/field/deletefile';
	var data = {fid:fid,objid:objid,fieldid:fieldid,ifid:ifid,ioid:ioid,file:file};
	qssAjax.call(url, data, function(jreturn) {
		if($('#'+objid+'_'+fieldid).is('input')){
			$('#'+objid+'_'+fieldid).parent().find('a').remove();
		}
		//if($('#'+objid).is('li')){
		else{
			rowObject(objid);
		}
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}