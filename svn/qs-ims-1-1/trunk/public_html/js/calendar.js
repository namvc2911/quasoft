$(document).ready(function(){
	$('#left').hide();
	$('#midle').hide();
	$('#left').width(0);
	$('#midle').width(0);
});
sz_BaseUrl = location.protocol + '//' + location.host;
col = '';
textcol = '';
function showMonth(m,y) {
	$('#cal_view').attr('style', 'opacity : 0.4');
	var url = sz_BaseUrl + '/user/calendar/reload';
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
	var url = sz_BaseUrl + '/user/calendar/reload';
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
	var url = sz_BaseUrl + '/user/calendar/reload';
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
	var url = sz_BaseUrl + '/user/calendar/reload';
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
function calendarBack(fid){
	var url = sz_BaseUrl + '/user/form?fid='+fid;
	//window.location.href = url;
        openModule('', sz_BaseUrl + '/user/form?fid='+fid);
}
function showToDay() {
	$('#cal_view').attr('style', 'opacity : 0.4');
	var url = sz_BaseUrl + '/user/calendar/reload';
	var d = new Date();
	$('#day').val(d.getDate());
	$('#month').val(d.getMonth()+1);
	$('#year').val(d.getFullYear());
	var data = $('#cal_form').serialize();
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#cal_view').html(jreturn);
		$('#cal_view').attr('style', 'opacity : 1');
	});
}
function showTrace(ifid,deptid) {
	var url = sz_BaseUrl + '/user/form/detail';
	var data = {ifid:ifid,deptid:deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({ width: 900,height:450 });
	});
}
function reloadCalendar() {
	$('#cal_view').attr('style', 'opacity : 0.4');
	var url = sz_BaseUrl + '/user/calendar/reload';
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