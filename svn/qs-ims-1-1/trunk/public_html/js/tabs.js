/** Display tab content that was clicked by index and hide the rest* */
function DisplayTabContent(ifid, deptid, objid, fieldid, vid, ioid, html,fieldorder) {
	var url = sz_BaseUrl + '/user/form/gridOnly';
	var pageno = $('#qss_object_pageno').val();
	var perpage = $('#qss_object_perpage').val();
	var groupby = $('#qss_object_groupby').val();
	var data = {
		ifid : ifid,
		deptid : deptid,
		objid : objid,
		fieldid : fieldid,
		vid : vid,
		ioid : ioid,
		pageno: pageno,
		perpage: perpage,
		groupby: groupby,
		fieldorder: fieldorder
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#sub_content').html(jreturn);
		$("#sub_content").dialog();
		/*var d = 
		d.parent().draggable({
	        containment: '#rightside',
	    });*/
		$("span.ui-dialog-title").text(html); 
	});
	return false;
}