var M173 = {

    TAB_LOCATION   : 'LOCATION',
    TAB_LINE       : 'LINE',
    TAB_COSTCENTER : 'COSTCENTER',
    TAB_MANAGER    : 'MANAGER',

    getFilter : function(tab)
    {
        M173.highlightTab(tab);
        M173.hideSelectParentEquipBox(tab);

        var data = {tab:tab};
        var url  = sz_BaseUrl + '/static/m173/filter';
        var html = '';//'<option value="0">--- Chọn ---</option>';

        qssAjax.call(url, data, function(jreturn) {
            for(i in jreturn.data)
            {
                html += '<option ';
                html += 'class="'+jreturn.data[i].class+'" ';
                html += 'value="'+jreturn.data[i].id+'">';
                html += jreturn.data[i].display;
                html += '</option>';
            }

            $('#m173_from_filter_select_box').html('');
            $('#m173_from_filter_select_box').html(html);
            $('#m173_from_filter_select_box').attr('onchange', 'M173.getDataLeftSide();');


            $('#m173_to_filter_select_box').html('');
            $('#m173_to_filter_select_box').html(html);
            $('#m173_to_filter_select_box').attr('onchange', 'M173.getDataRightSide();');

            M173.getDataLeftSide();
            M173.getDataRightSide();
        });
    },

    getDataRightSideByParentEquips : function()
    {
        var data            = {tab:$('#m173_current_tab').val(), filter:$('#m173_to_filter_select_box').val(), parent_equip: $('#m173_select_parent_equip').val()};
        var url             = sz_BaseUrl + '/static/m173/get';
        var parent_eq_level = parseInt($('#m173_select_parent_equip option:selected').attr('level'));
        var html            = '';
        var groupID         = 0;

        if(isNaN(parent_eq_level))
        {
            parent_eq_level = 0;
        }

        qssAjax.call(url, data, function(jreturn) {
            for(i in jreturn.data)
            {
                if(parent_eq_level == 0   || ((parent_eq_level + 1) == jreturn.data[i].level ))
                {
                    if(jreturn.data[i].level == 1)
                    {
                        groupID = jreturn.data[i].id;
                    }

                    html += '<option ';
                    html += 'class="'+jreturn.data[i].class+'" ';
                    html += 'value="'+jreturn.data[i].id+'"  ';
                    html += 'side="TO" ';
                    html += 'parent="'+jreturn.data[i].parent+'" ';
                    html += 'lft="'+jreturn.data[i].lft+'" ';
                    html += 'rgt="'+jreturn.data[i].rgt+'" ';
                    html += 'level="'+jreturn.data[i].level+'" ';
                    html += 'group="'+groupID+'" ';
                    html += 'data-percentage="'+jreturn.data[i].lft+'" ';
                    html += 'ifid="'+jreturn.data[i].ifid+'">';
                    html += jreturn.data[i].display;
                    html += '</option>';
                }
            }

            $('#m173_to_select_dial_box').html('');
            $('#m173_to_select_dial_box').html(html);

            M173.getDataLeftSide();
            M173.disabled();
        });
    },

    getDataLeftSide : function()
    {
        var data    = {tab:$('#m173_current_tab').val(), filter:$('#m173_from_filter_select_box').val()};
        var url     = sz_BaseUrl + '/static/m173/get';
        var html    = '';
        var groupID = 0;

        qssAjax.call(url, data, function(jreturn) {
            for(i in jreturn.data)
            {
                if(jreturn.data[i].level == 1)
                {
                    groupID = jreturn.data[i].id;
                }

                html += '<option ';
                html += 'class="'+jreturn.data[i].class+'" ';
                html += 'value="'+jreturn.data[i].id+'"  ';
                html += 'side="FROM" ';
                html += 'parent="'+jreturn.data[i].parent+'" ';
                html += 'lft="'+jreturn.data[i].lft+'" ';
                html += 'rgt="'+jreturn.data[i].rgt+'" ';
                html += 'level="'+jreturn.data[i].level+'" ';
                html += 'group="'+groupID+'" ';
                html += 'data-percentage="'+jreturn.data[i].lft+'" ';
                html += 'ifid="'+jreturn.data[i].ifid+'">';
                html += jreturn.data[i].display;
                html += '</option>';
            }

            $('#m173_from_select_dial_box').html('');
            $('#m173_from_select_dial_box').html(html);

            M173.disabled();
        });
    },

    getDataRightSide : function()
    {
        var data    = {tab:$('#m173_current_tab').val(), filter:$('#m173_to_filter_select_box').val()};
        var url     = sz_BaseUrl + '/static/m173/get';
        var html    = '';
        var html1   = '';
        var groupID = 0;

        qssAjax.call(url, data, function(jreturn) {
            for(i in jreturn.data)
            {
                if(jreturn.data[i].level == 1)
                {
                    groupID = jreturn.data[i].id;
                }

                html += '<option  ';
                html += 'class="'+jreturn.data[i].class+'" ';
                html += 'value="'+jreturn.data[i].id+'"  ';
                html += 'side="TO" ';
                html += 'parent="'+jreturn.data[i].parent+'" ';
                html += 'lft="'+jreturn.data[i].lft+'" ';
                html += 'rgt="'+jreturn.data[i].rgt+'" ';
                html += 'level="'+jreturn.data[i].level+'" ';
                html += 'group="'+groupID+'" ';
                html += 'data-percentage="'+jreturn.data[i].lft+'" ';
                html += 'ifid="'+jreturn.data[i].ifid+'">';
                html += jreturn.data[i].display;
                html += '</option>';
            }

            $('#m173_to_select_dial_box').html('');
            $('#m173_to_select_dial_box').html(html);

            // Them ca vao trong phan chon thiet bi cha neu la khu vuc
            if($('#m173_current_tab').val() == M173.TAB_LOCATION)
            {
                html1  = '<option value="0" parent="0" ifid="0" lft="" rgt="" level="" group="" data-percentag="" side="">--- Chọn thiết bị cha ---</option>';
                html1 += html

                $('#m173_select_parent_equip').html('');
                $('#m173_select_parent_equip').html(html1);
                $('#m173_select_parent_equip').attr('onchange', 'M173.getDataRightSideByParentEquips();');
            }

            M173.disabled();
        });
    },

    /**
     * Đánh dấu và ghi lại tab đã chọn
     */
    highlightTab : function(tab_no)
    {
        $('.m173_tab').parent().removeClass('active');
        $('#m173_tab_' + tab_no).parent().addClass('active');
        $('#m173_current_tab').val(tab_no);

        // Danh cho phien ban cu hon
        $('.m173_tab').removeClass('active');
        $('#m173_tab_' + tab_no).addClass('active');
        $('#m173_current_tab').val(tab_no);
    },

    /**
     * Ẩn select box chọn thiết bị cha khi chọn các tab khác tab khu vực
     */
    hideSelectParentEquipBox : function(tab)
    {
        if(tab != M173.TAB_LOCATION)
        {
            $('#m173_select_parent_equip').hide();
            $('#m173_to_select_dial_box').css({'height':'400px'});
        }
        else
        {
            $('#m173_select_parent_equip').show();
            $('#m173_to_select_dial_box').css({'height':'375px'});
        }
    },

    disabled : function()
    {
        var from             = parseInt($('#m173_from_filter_select_box').val());
        var to               = parseInt($('#m173_to_filter_select_box').val());
        var tab              = $('#m173_current_tab').val();
        var parent_equip     = parseInt($('#m173_select_parent_equip').val());
        var parent_equip_lft = parseInt($('#m173_select_parent_equip option:selected').attr('lft'));
        var parent_equip_rgt = parseInt($('#m173_select_parent_equip option:selected').attr('rgt'));

        $('#m173_from_select_dial_box').removeAttr('disabled', true);
        $('#m173_from_select_dial_box option').removeAttr('disabled', true);

        $('#m173_to_select_dial_box').removeAttr('disabled', true);
        $('#m173_to_select_dial_box option').attr('disabled', true);

        if(from == to || (from == to && tab != M173.TAB_LOCATION))
        {
            $('#m173_from_select_dial_box').attr('disabled', true);
            $('#m173_to_select_dial_box').attr('disabled', true);
        }

        if(tab == M173.TAB_LOCATION)
        {
            if(to && from != to)
            {
                $('#m173_from_select_dial_box option[parent="'+parseInt(to)+'"][level="1"]').attr('disabled', true);
            }

            if(parent_equip)
            {
                // Disabled level cha (disable phia ben trai)
                $('#m173_from_select_dial_box option').each(function(){
                    if( parseInt($(this).attr('lft')) < parent_equip_lft && parseInt($(this).attr('rgt')) > parent_equip_rgt)
                    {
                        $(this).attr('disabled', true);
                    }
                });

                // Disable cung level = level cua thiet bi cha dang chon tru di 1 (disable phia ben trai)
                $('#m173_to_select_dial_box option').each(function(){
                    $('#m173_from_select_dial_box option[value="'+parseInt($(this).val())+'"]').attr('disabled', true);
                });
            }

            // Disabled cac thiet bi level 1 khi hai ben loc khu vuc giong nhau
            // Do o day chi cho chuyen thiet bi con ra khoi thiet bi cha <O phia ben trai>
            if(from == to)
            {
                if(parent_equip == 0)
                {
                    $('#m173_from_select_dial_box option[level="1"]').each(function(){
                        $(this).attr('disabled', true);
                    });
                }
            }
        }

    },

    /**
     * Danh dau cac dong da chuyen
     */
    highlightMove : function()
    {
        var side;

        // Chi highlight bên phía chuyển đến <Lưu ý: không cho chuyển ngược lại nếu chưa chuyển qua trước>
        $('#m173_to_select_dial_box option').each(function(){
            side = $(this).attr('side');
            if(side == 'FROM')
            {
                $(this).addClass('move');
            }
        });
    },

    /**
     * Lưu lại thông tin di chuyển thiết bị
     */
    save : function(not_confirm)
    {
        var data;
        var url      = sz_BaseUrl + '/static/m173/save';
        var fromifid = '';
        var toifid   = '';
        var tab              = $('#m173_current_tab').val();

        // Chon tat ca dong thay doi
        $('.move').each(function(){
            $(this).attr('selected', true);
            $(this).removeAttr('disabled', true);

            // neu la from thi cho vao phan toifid va nguoc lai
            if($(this).attr('side') == 'FROM')
            {
                toifid += '&toifid%5B%5D='+$(this).attr('ifid');
            }
            else
            {
                fromifid += '&fromifid%5B%5D='+$(this).attr('ifid');
            }
        });

        // Loai bo cac dong chon ma khong duoc chuyen sang
        $('#m173_from_select_dial_box option').not('.move').each(function(){
            $(this).removeAttr('selected');
        });

        $('#m173_to_select_dial_box option').not('.move').each(function(){
            $(this).removeAttr('selected');
        });


        data  = $('#m173_form').serialize();
        data += toifid;
        data += fromifid;

        if(not_confirm)
        {
            qssAjax.call(url, data, function(jreturn) {
                if(jreturn.message != '')
                {
                    qssAjax.alert(jreturn.message, function () {
                        if(tab != M173.TAB_LOCATION)
                        {
                            M173.getDataLeftSide();
                            M173.getDataRightSide();
                        }
                        else
                        {
                            M173.getDataLeftSide();
                            M173.getDataRightSideByParentEquips();
                        }
                    });
                }


            }, function(jreturn) {
                qssAjax.alert(jreturn.message);
            });
        }
        else
        {
            qssAjax.confirm('Bạn muốn lưu cài đặt di chuyển thiết bị?', function(){
                qssAjax.call(url, data, function(jreturn) {
                    if(jreturn.message != '')
                    {
                        qssAjax.alert(jreturn.message);
                    }


                    if(tab != M173.TAB_LOCATION)
                    {
                        M173.getDataLeftSide();
                        M173.getDataRightSide();
                    }
                    else
                    {
                        M173.getDataLeftSide();
                        M173.getDataRightSideByParentEquips();
                    }



                }, function(jreturn) {
                    qssAjax.alert(jreturn.message);
                });
            });
        }
    },

    /**
     * Chuyển dữ liệu từ bên lấy sang bên cập nhật
     * @param all
     */
    add : function(all)
    {
        var tab    = $('#m173_current_tab').val();

        if(tab == M173.TAB_LOCATION)
        {
            M173.addInLocationTab(all);
        }
        else
        {
            M173.addInOtherTab(all);
        }

        M173.highlightMove();
    },

    addInLocationTab : function(all)
    {
        var from               = parseInt($('#m173_from_filter_select_box').val());
        var to                 = parseInt($('#m173_to_filter_select_box').val());
        var parent_equip       = parseInt($('#m173_select_parent_equip').val());
        var parent_equip_lft   = parseInt($('#m173_select_parent_equip option:selected').attr('lft'));
        var parent_equip_rgt   = parseInt($('#m173_select_parent_equip option:selected').attr('rgt'));
        var parent_equip_level = parseInt($('#m173_select_parent_equip option:selected').attr('rgt'));
        var html               = '';
        var add, sub_Level     = 0;

        parent_equip_level     = parent_equip_level?parent_equip_level:0;
        sub_Level              = parent_equip_level+1;


        if(all != undefined && all)
        {
            add = $('#m173_from_select_dial_box option[value!="0"]').filter(":not(:hidden)").filter(":not(:disabled)");
        }
        else
        {
            add = $('#m173_from_select_dial_box option[value!="0"]').filter(":selected").filter(":not(:hidden)").filter(":not(:disabled)");
        }



        // Neu chuyen cha sang thi an thang con di
        // Neu chuyen con xong chuyen cha thi ca hai deu vao cung mot level cua thiet bị khu vuc

        add.each(function(){
            lft   = $(this).attr('lft');
            rgt   = $(this).attr('rgt');

            html += '<option  ';
            html += 'class="tree_level_' + (sub_Level-1)  + '" ';
            html += 'value="' + $(this).val() + '"  ';
            html += 'side="' + $(this).attr('side') + '" ';
            html += 'parent="' + $(this).attr('parent') + '" ';
            html += 'lft="' + $(this).attr('lft') + '" ';
            html += 'rgt="' + $(this).attr('rgt') + '" ';
            html += 'level="' + $(this).attr('level') + '" ';
            html += 'data-percentage="' + $(this).attr('lft') + '" ';
            html += 'group="' + $(this).attr('group') + '" ';
            html += 'ifid="' + $(this).attr('ifid') + '">';
            html += $(this).text();
            html += '</option>';

            if(lft && rgt) {
                $('#m173_from_select_dial_box option[ifid="'+$(this).attr('ifid')+'"]').hide();
                $('#m173_to_select_dial_box option[ifid="'+$(this).attr('ifid')+'"]').hide();

                $('#m173_from_select_dial_box option').each(function(){
                    if( parseInt($(this).attr('lft')) >= lft && parseInt($(this).attr('rgt')) <= rgt)
                    {
                        $(this).hide();
                    }
                });

                $('#m173_to_select_dial_box option').each(function(){
                    if( parseInt($(this).attr('lft')) >= lft && parseInt($(this).attr('rgt')) <= rgt)
                    {
                        $(this).hide();
                    }
                });
            }

        });

        $('#m173_to_select_dial_box').prepend(html);




    },

    addInOtherTab : function(all)
    {
        var from   = parseInt($('#m173_from_filter_select_box').val());
        var to     = parseInt($('#m173_to_filter_select_box').val());
        var html   = '';

        if(all != undefined && all)
        {
            add = $('#m173_from_select_dial_box option[value!="0"]').filter(":not(:hidden)").filter(":not(:disabled)");
        }
        else
        {
            add = $('#m173_from_select_dial_box option[value!="0"]').filter(":selected").filter(":not(:hidden)").filter(":not(:disabled)");
        }

        add.each(function(){
            if ($(this).attr('parent') != to) // Khong cho chuyen cung vi tri
            {
                html += '<option  ';
                html += 'class="' + $(this).attr('class') + '" ';
                html += 'value="' + $(this).val() + '"  ';
                html += 'side="' + $(this).attr('side') + '" ';
                html += 'parent="' + $(this).attr('parent') + '" ';
                html += 'lft="' + $(this).attr('lft') + '" ';
                html += 'rgt="' + $(this).attr('rgt') + '" ';
                html += 'level="' + $(this).attr('level') + '" ';
                html += 'group="' + $(this).attr('group') + '" ';
                html += 'data-percentage="' + $(this).attr('lft') + '" ';
                html += 'ifid="' + $(this).attr('ifid') + '">';
                html += $(this).text();
                html += '</option>';

                // An neu ton tai tu hai the, lam truoc khi them the
                $('#m173_from_select_dial_box option[ifid="'+$(this).attr('ifid')+'"]').hide();
                $('#m173_to_select_dial_box option[ifid="'+$(this).attr('ifid')+'"]').hide();
            }
        });

        $('#m173_to_select_dial_box').prepend(html);
    },

    /**
     * Chuyển dữ liệu ngược từ bên cập nhật về lại bên lấy
     * @param all
     */
    remove : function(all)
    {
        var tab    = $('#m173_current_tab').val();
        var from   = parseInt($('#m173_from_filter_select_box').val());
        var to     = parseInt($('#m173_to_filter_select_box').val());

        if(all != undefined && all)
        {
            M173.getDataLeftSide();
            M173.getDataRightSide();
        }
        else
        {
            add = $('#m173_to_select_dial_box option[value!="0"]').filter(":selected").filter(":not(:hidden)").filter(":not(:disabled)");

            add.each(function(){
                $(this).remove();

                $('#m173_from_select_dial_box option[ifid="'+parseInt($(this).attr('ifid'))+'"]').show();
                $('#m173_to_select_dial_box option[ifid="'+parseInt($(this).attr('ifid'))+'"]').show();
            });
        }
    }
};

$(document).ready(function() {
    $('.m173_tab').eq(0).click();
});