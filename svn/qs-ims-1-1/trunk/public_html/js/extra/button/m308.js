var m308_calendar = {
	change: false,
	showMonth : function(m,y)
    {
    	$('#cal_view').attr('style', 'opacity : 0.4');
    	var url = sz_BaseUrl + '/button/m308/calendar/reload';
    	if(m) $('#month').val(m);
    	if(y) $('#year').val(y);
    	$('#type').val('month');
    	var data = $('#cal_form').serialize();
    	qssAjax.getHtml(url, data, function(jreturn) {
    		$('#cal_view').html(jreturn);
    		$('#cal_view').attr('style', 'opacity : 1');
    	});
    	//removeSelected();
    	$('#btnMONTH').addClass('extra-selected');
    },
	reloadCalendar : function()
	{
		$('#cal_view').attr('style', 'opacity : 0.4');
		var url = sz_BaseUrl + '/button/m308/calendar/reload';
		var data = $('#cal_form').serialize();
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#cal_view').html(jreturn);
			$('#cal_view').attr('style', 'opacity : 1');
		});
	}
  ,
	showTaskDate : function(eid,date)
	{
		var data = {eid:eid,date:date};
		var url = sz_BaseUrl + '/button/m308/calendar/date';
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#qss_dialog').html(jreturn);
			$('#qss_dialog').dialog({ width: 400,height:400,close: function(){
				if(m308_calendar.change == true){
					m308_calendar.change = false;
					m308_calendar.reloadCalendar();
	        	}
			} });
		});
	},
	saveTaskDate : function(eid,date)
	{
		var url = sz_BaseUrl + '/button/m308/calendar/save';
		var data = $('#m308_shift_form').serialize();
		data = data + '&eid='+eid+'&date='+date;
		qssAjax.call(url, data, function(jreturn) {
			m308_calendar.change = true;
			$('#qss_dialog').dialog('close');
        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
	},
	cancelTaskDate : function(eid,date)
	{
		var url = sz_BaseUrl + '/button/m308/calendar/cancel';
		//var data = $('#m308_shift_form').serialize();
		var id = $('#ifid').val();
		var did = $('#deptid').val();
		var ifid = [];
		var deptid = [];
		ifid[0] = id;
		deptid[0] = did;
		var data ={ifid:ifid,deptid:deptid};
		qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
			qssAjax.call(url, data, function(jreturn) {
				m308_calendar.change = true;
				$('#qss_dialog').dialog('close');
	        }, function(jreturn) {
	            qssAjax.alert(jreturn.message);
	        });
		});
	},
	toggleCheckbox: function(el)
	{
		if($(el).is(':checked')){
			$('.workorder').prop('checked',true);
		}
		else{
			$('.workorder').prop('checked',false);			
		}
	}
};