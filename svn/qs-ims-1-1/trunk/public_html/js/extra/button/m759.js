var strOCongViecBTPBT = 'OCongViecBTPBT';
var strOPhieuBaoTri   = 'OPhieuBaoTri';

var imported = document.createElement('script');
imported.src = '/js/common.js';
document.head.appendChild(imported);

var m759_plans_button = {
    show : function()
    {
        var url = sz_BaseUrl + '/button/m759/plans/show';
        var data = $('#M729_Plans_Form').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#m729_create_from_plan_show_table').html(jreturn);
        });
    },

    save : function()
    {
        $('#M838_Save_Button').attr('disabled', true);
        var data = $('#M729_Plans_Form').serialize();
        var url  = sz_BaseUrl + '/button/m759/plans/save';

        qssAjax.call(url, data, function(jreturn) {
            if(jreturn.message != '')
            {
                qssAjax.alert(jreturn.message);
            }
            $('#M838_Save_Button').removeAttr('disabled');
            window.close();
            opener.rowSearch('M759');
        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
    },

    plansLoadEquip : function()
    {
        var url = sz_BaseUrl + '/button/m759/plans/equip/';
        var data = $('#M729_Plans_Form').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#plansLoadEquip').html(jreturn);
        });
    },
};

var m759_generalplans_button = {
    show : function()
    {
        var url = sz_BaseUrl + '/button/m759/generalplans/show';
        var data = $('#M729_General_Plan_Form').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#M729_General_Plan_Form .popup_content').html(jreturn);
        });
    },

    save : function()
    {
        var data = $('#M729_General_Plan_Form').serialize();
        var url  = sz_BaseUrl + '/button/m759/generalplans/save';

        qssAjax.call(url, data, function(jreturn) {
            if(jreturn.message != '')
            {
                qssAjax.alert(jreturn.message);
            }
            $('#qss_trace').dialog('close');
            $('#qss_combo').dialog('close');
            window.close();           
            opener.rowSearch('M759');

        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
    }
};

var m759_checklist_button = {
    show : function()
    {
        var url = sz_BaseUrl + '/button/m759/checklist/show';
        var data = $('#m759_checklist_button').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#m759_checklist_button .popup_content').html(jreturn);
        });
    },

    save : function()
    {
        var data   = $('#m759_checklist_button').serialize();
        var url    = sz_BaseUrl + '/button/m759/checklist/save';
        var ifid   = parseInt($('#ifid').val());
        var deptid = parseInt($('#deptid').val());

        qssAjax.call(url, data, function(jreturn) {
            if(jreturn.message != '')
            {
                qssAjax.alert(jreturn.message);
            }
            $('#qss_trace').dialog('close');
            $('#qss_combo').dialog('close');
            rowObjectSearch(ifid, deptid, strOPhieuBaoTri);

        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
    }
};

var m759_assign_button = {
    save : function()
    {
        // common_selectDialBox('PhieuBaoTri');
        // var data   = $('#m759_assign_button').serialize();
        var url    = sz_BaseUrl + '/button/m759/assign/save';

        var NguoiGiao              = $('#NguoiGiao').val();
        var NguoiChiuTrachNhiem    = $('#NguoiChiuTrachNhiem').val();
        var XoaNguoiGiaoViec       = 0;
        var XoaNguoiChiuTrachNhiem = 0;
        var PhieuBaoTri            = [];
        var data                   = {};
        var SoPhieu                = '';

        if($('#XoaNguoiGiaoViec').is(':checked')) {
            XoaNguoiGiaoViec = 1;
        }

        if($('#XoaNguoiChiuTrachNhiem').is(':checked')) {
            XoaNguoiChiuTrachNhiem = 1;
        }

        $('.grid_selected').each(function () {
            SoPhieu = $.trim($(this).find('td[name="OPhieuBaoTri_SoPhieu"]').text());
            PhieuBaoTri.push(SoPhieu);
        });

        data = {
            NguoiGiao : NguoiGiao
            , NguoiChiuTrachNhiem : NguoiChiuTrachNhiem
            , XoaNguoiGiaoViec : XoaNguoiGiaoViec
            , XoaNguoiChiuTrachNhiem : XoaNguoiChiuTrachNhiem
            , PhieuBaoTri : PhieuBaoTri
            , NgayBatDau : $('#NgayBatDau').val()
            , NgayHoanThanh : $('#NgayHoanThanh').val()
        }

        qssAjax.call(url, data, function(jreturn) {
            if(jreturn.message != '')
            {
                qssAjax.alert(jreturn.message);
            }
            $('#qss_trace').dialog('close');
            $('#qss_combo').dialog('close');
            opener.rowSearch('M759');

        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
    }
};
var m759_employee_plan = {
	change: false,
	showMonth : function(m,y)
    {
    	$('#cal_view').attr('style', 'opacity : 0.4');
    	var url = sz_BaseUrl + '/button/m759/employee/reload';
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
		var url = sz_BaseUrl + '/button/m759/employee/reload';
		var data = $('#cal_form').serialize();
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#cal_view').html(jreturn);
			$('#cal_view').attr('style', 'opacity : 1');
		});
	}
    ,
	showDate : function(eid,date)
	{
		var data = {eid:eid,date:date};
		var url = sz_BaseUrl + '/button/m759/employee/date';
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#qss_dialog').html(jreturn);
			$('#qss_dialog').dialog({ width: 700,height:450,close: function(){
				if(m759_employee_plan.change == true){
					m759_employee_plan.change = false;
					m759_employee_plan.reloadCalendar();
	        	}
			} });
		});
	},
	showTaskDate : function(eid,date)
	{
		var data = {eid:eid,date:date};
		var url = sz_BaseUrl + '/button/m759/employee/taskdate';
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#qss_dialog').html(jreturn);
			$('#qss_dialog').dialog({ width: 700,height:450,close: function(){
				if(m759_employee_plan.change == true){
					m759_employee_plan.change = false;
					m759_employee_plan.reloadCalendar();
	        	}
			} });
		});
	},
	showWOByDate : function(date)
	{
		var type = $('#type').val();
		if(type == 0){
			var data = {date:date};
		}
		else{
			var data = {};			
		}
		var url = sz_BaseUrl + '/button/m759/employee/date/workorder';
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#dialog_m759_employee_plan').html(jreturn);
		});
	},
	saveWOByDate : function(eid,date)
	{
		var url = sz_BaseUrl + '/button/m759/employee/date/workorder/save';
		var data = $('#form_button_m759_employee_plan').serialize();
		data = data + '&eid='+eid+'&date='+date;
		qssAjax.call(url, data, function(jreturn) {
			m759_employee_plan.change = true;
			m759_employee_plan.showDate(eid,date);
        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
	},
	cancelWOByDate : function(ifid,eid,date)
	{
		var url = sz_BaseUrl + '/button/m759/employee/date/workorder/cancel';
		var data ={ifid:ifid};
		qssAjax.call(url, data, function(jreturn) {
			m759_employee_plan.change = true;
			m759_employee_plan.showDate(eid,date);
        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
	},
	showTaskByDate : function(date)
	{
		var type = $('#type').val();
		if(type == 0){
			var data = {date:date};
		}
		else{
			var data = {};			
		}
		var url = sz_BaseUrl + '/button/m759/employee/date/task';
		qssAjax.getHtml(url, data, function(jreturn) {
			$('#dialog_m759_employee_plan').html(jreturn);
		});
	},
	saveTaskByDate : function(eid,date)
	{
		var url = sz_BaseUrl + '/button/m759/employee/date/task/save';
		var data = $('#form_button_m759_employee_plan').serialize();
		data = data + '&eid='+eid+'&date='+date;
		qssAjax.call(url, data, function(jreturn) {
			m759_employee_plan.change = true;
			m759_employee_plan.showTaskDate(eid,date);
        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
	},
	cancelTaskByDate : function(ifid,ioid,eid,date)
	{
		var url = sz_BaseUrl + '/button/m759/employee/date/task/cancel';
		var data ={ifid:ifid,ioid:ioid};
		qssAjax.call(url, data, function(jreturn) {
			m759_employee_plan.change = true;
			m759_employee_plan.showTaskDate(eid,date);
        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
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