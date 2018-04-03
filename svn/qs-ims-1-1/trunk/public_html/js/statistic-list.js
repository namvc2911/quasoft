function statisticPerformance() {
	var url = sz_BaseUrl + '/user/statistic/loadperformance';
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_statistic').html(jreturn);
	});
}
function statisticQuantity() {
	var url = sz_BaseUrl + '/user/statistic/loadquantity';
	var data = $('#qss_form').serialize();
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_statistic').html(jreturn);
	});
}
function loadUser(element) {
	var data = {
		detpid : element.value
	};
	var url = sz_BaseUrl + '/user/statistic/user';
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#sysUser').html(jreturn);
	});
}
function loadStep(element) {
	var data = {
		fid : element.value
	};
	var url = sz_BaseUrl + '/user/statistic/step';
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#sysStep').html(jreturn);
	});
}