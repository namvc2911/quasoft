var col = '';
var textcol = '';
function formBack(fid) {
	//window.location.href = sz_BaseUrl + '/user/form?fid=' + fid;
        openModule('',sz_BaseUrl + '/user/form?fid=' + fid);
}
function rowSearch(ifid) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/user/process/grid';
	var data = $('#form_process_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
				+ '&groupby=' + groupby + '&ifid=' + ifid + '&' + data, function(jreturn) {
		$('#qss_process').html(jreturn);
		// resetBtn();
	});
}
function rowSort(id,ifid) {
	var pageno = $('#qss_form_pageno').val();
	var perpage = $('#qss_form_perpage').val();
	var groupby = $('#qss_form_groupby').val();
	var url = sz_BaseUrl + '/user/process/grid';
	var data = $('#form_process_filter').serialize();
	qssAjax.getHtml(url, 'pageno=' + pageno + '&perpage=' + perpage
			+ '&fieldorder=' + id + '&groupby=' + groupby + '&ifid=' + ifid +  '&fieldorder=' + id + '&' + data, function(jreturn) {
		$('#qss_process').html(jreturn);
		// resetBtn();
	});
}
function showSearch() {
	if ($('#div-search').is(":visible")) {
		$('#div-search').hide();
	} else {
		$('#div-search').show();
	}
}
function rowCleanSearch(ifid) {
	$('form[name=filter_form] :input').each(function(){
		$(this).val('');
	});
	rowSearch(ifid);
}