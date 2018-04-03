var wo_loc_left, wo_loc_right;
var wo_start, wo_end, wo_loc;
var wo_mtype, wo_wc

jQuery(document).ready(function() {
	// set datepicker
	$('.datepicker').each(function() {
		$(this).datepicker({dateFormat: "dd-mm-yy"});
		$(this).attr('placeholder', 'dd-mm-yyyy');
	});/* Datepicker*/


	$('#woinfo-filter #loc').hover(
		function() {
			$('.woinfo-remove-location-filter').show();
		},
		function() {
			$('.woinfo-remove-location-filter').hide();
		});
	// Hien thi khu vuc + phieu bao tri
	woinfo_location();
});

//function woinfo_lines()
//{
//	var display = $('#woinfo-display').val();
//	var page = $('#woinfo-page').val();
//	var url = sz_BaseUrl + '/static/m728/line';
//	var html = '';
//	var left = ($.cookie('wo_loc_left')) ? $.cookie('wo_loc_left') : 0;
//	var right = ($.cookie('wo_loc_right')) ? $.cookie('wo_loc_right') : 0;
//	var locid = ($.cookie('wo_loc_id')) ? $.cookie('wo_loc_id') : 0;
//	var start =($.cookie('wo_start')) ? $.cookie('wo_start') : '';
//	var end = ($.cookie('wo_end')) ? $.cookie('wo_end') : '';
//	var maintType = ($.cookie('wo_mtype')) ? $.cookie('wo_mtype') : '';
//	var workCenter = ($.cookie('wo_wc')) ? $.cookie('wo_wc') : '';
//
//	var data = {
//		page: page,
//		display: display,
//		loc: locid,
//		left: left,
//		right: right,
//		start: start,
//		end: end,
//		mtype: maintType,
//		wc: workCenter
//	};
//	qssAjax.getHtml(url, data, function(jreturn) {
//		$('#woinfo-order-line').html(jreturn);
//	});
//}

function woinfo_comment(ifid, deptid, uid)
{
	var url = sz_BaseUrl + '/user/form/comments';
	var data = {
		ifid: ifid,
		deptid: deptid,
		uid: uid
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({width: 400, height: 400});
	});
}

function woinfo_detail(ifid, deptid)
{
	// xem chi tiet ban ghi
	var url = sz_BaseUrl + '/user/form/detail';
	var data = {ifid: ifid, deptid: deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({width: 900, height: 450});
	});
}

/**
 * Hien thi danh sach khu vuc
 * @param {object} ele
 * @returns {html} 
 */
function woinfo_location(ele)
{
	// bo loc khu vuc neu khong co khu vuc duoc chon
	if (ele == undefined)
	{
		var left = ($.cookie('wo_loc_left')) ? parseInt($.cookie('wo_loc_left')) : 0;
		var right = ($.cookie('wo_loc_right')) ? parseInt($.cookie('wo_loc_right')) : 0;
		var locID = ($.cookie('wo_loc_id')) ? parseInt($.cookie('wo_loc_id')) : 0;
		var loc = ($.cookie('wo_loc')) ? $.cookie('wo_loc') : '';
	}
	else
	{
		var left = parseInt($(ele).attr('left'));
		var right = parseInt($(ele).attr('right'));
		var locID = parseInt($(ele).attr('ioid'));
		var loc = $(ele).children().eq(0).attr('code') + ' - ' + $(ele).children().eq(0).attr('name');

	}
	var start = $('#woinfo-fitler-start-date').val();
	var end = $('#woinfo-fitler-end-date').val();	
	var maintType = $('#woinfo_fitler_maint_type').val() ;
	var workCenter = $('#woinfo_fitler_work_center').val();

	woinfo_set_cookies(
		maintType
		, workCenter
		, loc
		, locID
		, left
		, right
		, start
		, end);


	// neu ngay ket thuc nho hon ngay bat dau, hoi xem co loc tu ngay bat dau ko?
	if (common_compareDate(start, end) == -1)
	{
		qssAjax.confirm('Ngày kết thúc nhỏ hơn ngày bắt đầu! Bạn có muốn lọc từ ngày bắt đầu không?', function() {
			end = '';
			$('#woinfo-fitler-end-date').val('');
			// sap xep data va lay url
			var data = {left: left, right: right, start: start, end: end, locid: locID, mtype: maintType, wc: workCenter, loc: locID};
			var url = "/static/m728/location";

			// bien html va cac bien chi so
			var html = '';
			var i = 0, j = 0, k = 0;


			// ghi lai khu vuc va thoi gian len dong ghi loc
			woinfo_add_loc_to_filter_alert($.cookie('wo_loc'));

			// ghi lai thoi gian len dong loc
			//woinfo_add_time_to_filter_alert($.cookie('wo_start'), $.cookie('wo_end'))


			// hien thi ra bang khu vuc da duoc loc vao the html
			// Hien thi dong phieu bao tri sau khi hien thi khu vuc
			qssAjax.getHtml(url, data, function(jreturn) {

				// Loc du lieu phieu bao tri
				// woinfo_lines();

				// In khu vuc
				$('#woinfo-general-analytics-table').html(jreturn);
			});
		}, function() {
			return;
			start = '';
			end = '';

			$('#woinfo-fitler-start-date').val('');
			$('#woinfo-fitler-end-date').val('');
		});
	}
	else {
		// sap xep data va lay url
		var data = {left: left, right: right, start: start, end: end, locid: locID, mtype: maintType, wc: workCenter, loc: locID};
		var url = "/static/m728/location";

		// bien html va cac bien chi so
		var html = '';
		var i = 0, j = 0, k = 0;


		// ghi lai khu vuc va thoi gian len dong ghi loc
		woinfo_add_loc_to_filter_alert($.cookie('wo_loc'));

		// ghi lai thoi gian len dong loc
		//woinfo_add_time_to_filter_alert($.cookie('wo_start'), $.cookie('wo_end'))


		// hien thi ra bang khu vuc da duoc loc vao the html
		// Hien thi dong phieu bao tri sau khi hien thi khu vuc
		qssAjax.getHtml(url, data, function(jreturn) {

			// Loc du lieu phieu bao tri
			// woinfo_lines();

			// In khu vuc
			$('#woinfo-general-analytics-table').html(jreturn);
		});
	}
}

function woinfo_add_loc_to_filter_alert(loc_code, loc_name)
{
	loc_name = '';
	var html = '';

	if (loc_code)
	{
		$('#woinfo-filter #loc').css({'position': 'relative', 'padding': '5px 15px 1px 8px', 'position': 'relative'});
		html += '<span>' + loc_code + '</span>';
		html += '<a href="#1" onclick="woinfo_remove_location_filter()" class="woinfo-remove-location-filter" style="height:16px; display:none; '
			+ 'position:absolute; height:10px; top:5px; right:0px; "><img src ="/images/event/close.png" /></a>'
		$('#woinfo-filter #loc').html(html);
	}

}

function woinfo_remove_location_filter()
{
	$('#woinfo-filter #loc').html('');// xoa filter khu vuc
	$('#woinfo-filter #loc').removeAttr('style');
	$.cookie('wo_loc', ''); // xoa khu vuc
	$.cookie('wo_loc_id', 0); // xoa khu vuc
	$.cookie('wo_loc_left', 0); // loc khu vuc - left
	$.cookie('wo_loc_right', 0); // loc khu vuc - right
	woinfo_location();
}

function woinfo_set_cookies(
	maintType
	, workCenter
	, loc
	, locID
	, left
	, right
	, start
	, end)
{
	var date = new Date();
	var hoursleft = 23 - date.getHours();
	var minutesleft = 59 - date.getMinutes();
	var secondsleft = 59 - date.getSeconds();
	var seconds = (hoursleft * 3600) + (minutesleft * 60) + secondsleft;
	date.setTime(date.getTime() + (seconds * 1000));

	if (isNaN(workCenter))
	{
		workCenter = 0;
	}

	if (isNaN(maintType))
	{
		maintType = 0;
	}

	// ghi lai gia tri loc vao cookie
	$.cookie('wo_mtype', maintType); // lay khu vuc
	$.cookie('wo_wc', workCenter); // lay khu vuc
	$.cookie('wo_loc', loc); // lay khu vuc
	$.cookie('wo_loc_id', locID); // lay khu vuc
	$.cookie('wo_loc_left', left); // loc khu vuc - left
	$.cookie('wo_loc_right', right); // loc khu vuc - right
	$.cookie('wo_start', start, {expires: date}); // loc thoi gian - start
	$.cookie('wo_end', end, {expires: date}); // loc thoi gian - end
}




