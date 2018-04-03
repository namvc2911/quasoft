var calendar_index_file = {
    material: function()
    {
        var url = sz_BaseUrl + '/static/m729/material/';
        var data = $('#cal_form').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#qss_trace').dialog('close');
            $('#qss_trace').html(jreturn);
            $('#qss_trace').dialog({width: 800, height: 400});
        });
    },

    general_plans : function()
    {
        var url = sz_BaseUrl + '/static/m729/generalplans/';
        var data = $('#M729_General_Plan_Form').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#qss_trace').html(' ');
            $('#qss_trace').dialog('close');
            $('#qss_trace').html(jreturn);
            $('#qss_trace').dialog({width: 800, height: 400});
        });
    },

    general_plans1 : function()
    {
        var url = sz_BaseUrl + '/static/m729/generalplans1/';
        var data = $('#M729_General_Plan_Form').serialize();
        var n = $( "#qss_trace" ).length;
        var m = $( "#M729_General_Plan_Form .popup_content" ).length;

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#M729_General_Plan_Form .popup_content').html(jreturn);
        });
    },

    plans : function()
    {
        var url = sz_BaseUrl + '/static/m729/plans/';
        var data = $('#M729_Plans_Form').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#qss_trace').html(' ');
            $('#qss_trace').dialog('close');
            $('#qss_trace').html(jreturn);
            $('#qss_trace').dialog({width: 1000, height: 400});
        });
    },

    plansLoadEquip : function()
    {
        var url = sz_BaseUrl + '/static/m729/equip/';
        var data = $('#M729_Plans_Form').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#plansLoadEquip').html(jreturn);
        });
    },


    plans1 : function()
    {
        var url = sz_BaseUrl + '/static/m729/plans1/';
        var data = $('#M729_Plans_Form').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#m729_create_from_plan_show_table').html(jreturn);
        });
    },

    create_order_from_plans : function()
    {
        var data = $('#M729_Plans_Form').serialize();
        var url  = sz_BaseUrl + '/static/m729/createfromplans2/';

        qssAjax.call(url, data, function(jreturn) {
            if(jreturn.message != '')
            {
                qssAjax.alert(jreturn.message);
            }
            $('#qss_trace').dialog('close');
            $('#qss_combo').dialog('close');

            if (typeof reloadCalendar === "function") {
                // safe to use the function
                reloadCalendar();
            }

            if(typeof rowSearch === "function")
            {
                opener.rowSearch('M759')
            }



        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
    },




    create_order_from_general_plans : function()
    {
        var data = $('#M729_General_Plan_Form').serialize();
        var url  = sz_BaseUrl + '/static/m729/createfromgeneralplans/';

        qssAjax.call(url, data, function(jreturn) {
            if(jreturn.message != '')
            {
                qssAjax.alert(jreturn.message);
            }
            $('#qss_trace').dialog('close');

            if (typeof reloadCalendar === "function") {
                // safe to use the function
                reloadCalendar();
            }

            if(typeof rowSearch === "function")
            {
                opener.rowSearch('M759')
            }

        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
    },

    assign: function()
    {

        var cWorkorder = $('.workorder_select:checked').length;

        if(cWorkorder)
        {
            var url  = sz_BaseUrl + '/static/m729/assign/';
            var data = $('.workorder_select').serialize();

            qssAjax.getHtml(url, data, function(jreturn) {
                $('#qss_trace').dialog('close');
                $('#qss_trace').html(jreturn);
                $('#qss_trace').dialog({width: 500, height: 300});
            });
        }
        else
        {
            qssAjax.alert('Cần ít nhất một bản ghi được chọn!');
        }
    },

    save_assign : function()
    {



        var data = $('#m729_assign_form').serialize();//{equip:equip, component:component};
        var url  = sz_BaseUrl + '/static/m729/assignto/';

        qssAjax.call(url, data, function(jreturn) {
            if(jreturn.message != '')
            {
                qssAjax.alert(jreturn.message);
            }

            reloadCalendar();
            calendar_index_file.removeEffectCheckAll();
            $('#qss_trace').dialog('close');
        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
    },

    param: function()
    {
        var url = sz_BaseUrl + '/static/m729/param/';
        var data = $('#cal_form').serialize();

        if($('#start').val() != undefined && $('#start').val())
        {
            data += '&start=' + $('#start').val();
        }

        if($('#end').val() != undefined && $('#end').val()  )
        {
            data += '&end=' + $('#end').val();
        }

        qssAjax.getHtml(url, data, function(jreturn) {
            //$('#qss_trace').dialog('close');
            $('#qss_trace').html('');
            $('#qss_trace').html(jreturn);
            $('#qss_trace').dialog({width: 800, height: 400});
        });
    },

    createOrder : function(equip, component)
    {
        var data = $('#m729_failure_param').serialize();//{equip:equip, component:component};
        var url  = sz_BaseUrl + '/static/m729/createorder/';

        qssAjax.confirm('Bạn muốn tạo phiếu bảo trì hay không?', function(){
            qssAjax.call(url, data, function(jreturn) {
                if(jreturn.message != '')
                {
                    qssAjax.alert(jreturn.message);
                }
            }, function(jreturn) {
                qssAjax.alert(jreturn.message);
            });
        });
    },

    showWo: function()
    {
        var data = $('#cal_form').serialize();//{equip:equip, component:component};
        var url  = sz_BaseUrl + '/static/m729/createfromplans/';

        qssAjax.confirm('Bạn muốn tạo phiếu bảo trì từ kế hoạch hay không?', function(){
            qssAjax.call(url, data, function(jreturn) {
                if(jreturn.message != '')
                {
                    qssAjax.alert(jreturn.message);
                }

                reloadCalendar();
                calendar_index_file.removeEffectCheckAll();
            }, function(jreturn) {
                qssAjax.alert(jreturn.message);
            });
        });



//            var url = sz_BaseUrl + '/extra/maintenance/createwo/';
//            var data = {fid:'M729'};
//
//            qssAjax.getHtml(url, data, function(jreturn) {
//                $('#qss_trace').dialog('close');
//                $('#qss_trace').html(jreturn);
//                $('#qss_trace').dialog({width: 800, height: 400});
//            });
    },

    removeEffectCheckAll : function()
    {
        $('#checkOrUnCheckAllWo').text('Chọn tất cả');
        $('#checkOrUnCheckAllWo').attr('ctype', 1);
    },

    checkOrUnCheckAllWo: function(ele)
    {
        var check = $(ele).attr('ctype');
        var cWorkorder = $('.workorder_select').length;

        if(!cWorkorder)
        {
            alert('Không có phiếu bảo trì ở tình trạng soạn thảo trong khoảng thời gian lập lịch!')
            return;
        }
        if(check == 1)
        {
            $('#checkOrUnCheckAllWo').text('Bỏ chọn tất cả');
            $(ele).attr('ctype', 0);


            $('.workorder_select').each(function(){
                if($(this).is(':checked'))
                {

                }
                else
                {
                    $(this).attr('checked', true);
                }
            });
        }
        else
        {
            $('#checkOrUnCheckAllWo').text('Chọn tất cả');
            $(ele).attr('ctype', 1);

            $('.workorder_select').each(function(){
                if($(this).is(':checked'))
                {
                    $(this).removeAttr('checked');
                }
                else
                {

                }
            });
        }
    },

    showSearchBox: function()
    {
        if($('#search-button').hasClass('extra-selected'))
        {
            $('#div-search').css({'display':'none'});
            $('#search-button').removeClass('extra-selected');
        }
        else
        {
            $('#div-search').css({'display':'block'});
            $('#search-button').addClass('extra-selected');
        }

    },

    cleanSearch: function()
    {
        $('#responseid_select').val(0);
        $('#workcenter_select').val(0);

        reloadCalendar();
        calendar_index_file.removeEffectCheckAll();
    },

    removeOrders: function()
    {
        var cWorkorder = $('.workorder_select:checked').length;


        if(cWorkorder)
        {
            qssAjax.confirm(Language.translate('CONFIRM_DELETE'),function(){
                var data = $('.workorder_select').serialize();
                var url  = sz_BaseUrl + '/static/m729/remove';

                qssAjax.call(url, data, function(jreturn) {
                    if(jreturn.message != '')
                    {
                        qssAjax.alert(jreturn.message);
                    }
                    reloadCalendar();
                    calendar_index_file.removeEffectCheckAll();
                }, function(jreturn) {
                    qssAjax.alert(jreturn.message);
                });
            });

        }
        else
        {
            qssAjax.alert('Cần ít nhất một bản ghi được chọn!');
        }
    },

    sendRequest: function(stepno)
    {
        var cWorkorder = $('.workorder_select:checked').length;


        if(cWorkorder)
        {
            qssAjax.confirm('Bạn thực sự muốn chuyển tình trạng thực hiện bản ghi này?',function(){
                var data = $('.workorder_select').serialize() + '&stepno=' + stepno;
                var url  = sz_BaseUrl + '/static/m729/status';

                qssAjax.call(url, data, function(jreturn) {
                    if(jreturn.message != '')
                    {
                        qssAjax.alert(jreturn.message);
                    }

                    reloadCalendar();
                    calendar_index_file.removeEffectCheckAll();
                }, function(jreturn) {
                    qssAjax.alert(jreturn.message);
                });
            });
        }
        else
        {
            alert('Cần ít nhất một bản ghi được chọn!');
        }


    },

    showStatus : function(ele)
    {
        if(!$(ele).parent().find('.dropdown-content').is(':visible')){
            setTimeout(function(){
                $(ele).parent().find('.dropdown-content').show();
            }, 100);
        }
    }

};

$(document).ready(function(){
});