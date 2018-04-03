jQuery.fn.file = function(fn) {
	return this.each(function() {
		var el = $(this);
		var holder = $('<div></div>').appendTo(el).css({
			position:'absolute',
			overflow:'hidden',
			'-moz-opacity':'0',
			filter:'alpha(opacity: 0)',
			opacity:'0',
			zoom:'1',
			width:el.outerWidth()+'px',
			height:el.outerHeight()+'px',
			'z-index':999999
		});	

		var wid = 0;
		var inp;

		var addInput = function() {
			var current = inp = holder.html('<input '+(window.FormData ? 'multiple ' : '')+'type="file" style="border:none; position:absolute">').find('input');

			wid = wid || current.width();

			current.change(function() {
				current.unbind('change');

				addInput();
				fn(current[0]);
			});
		};
		var position = function(e) {
			holder.offset(el.offset());					

			if (e) {
				inp.offset({left:e.pageX-wid+25, top:e.pageY-10});						
			}
		};

		addInput();

		el.mouseover(position);
		el.mousemove(position);
		position();		
	});
};

var reffieldid = 0;
var refelement = null;
var isrefresh = false;
var select_col = '';
var select_textcol = '';
function selectCLK(el) {
	if(!el){
		return;
	}
	if (select != null) {
		if(select != el){
			//edit = false;
		}
		select.style.background = select_col;
		select.style.color = select_textcol;
	}
	select_col = el.style.background;
	el.style.background = 'gray';
	select_textcol = el.style.color;
	el.style.color = '#ffffff';
	select = el;
	//ddtreemenu.setCookie('object_selected', select.id, 1);
}
function popupSelect(fieldid, ele, refresh) {
	refelement = ele;
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
/*function popupAttr(fieldid, ele, refresh) {
	refelement = ele;
	isrefresh = refresh;
	var url = sz_BaseUrl + '/extra/element/attr?fieldid='+fieldid;
	var fid = $('#fid').val();
	var data = $('#form_' + fid + '_edit').serialize();
	$('#form_' + fid + '_edit').find('input[type=checkbox]').each(function(){
		if(!$(this).is(':checked')){
			data += '&' + $(this).attr('name') +'=';
		}
	});
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_combo').html(jreturn);
		$('#qss_combo').dialog({ width: 400,height:400 });
	});
}*/
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
	$(refelement).attr('value',$(select).attr('vid') + ',' + $(select).attr('ioid'));
	$(refelement).text($(select).attr('vdisplay'));
	$('#qss_combo').html('');
	$('#qss_combo').dialog('close');
	if (isrefresh == 1) {
		//rowEditRefresh();
	}
	select = null;
}