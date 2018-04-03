var imported = document.createElement('script');
imported.src = '/js/form-list.js';
document.head.appendChild(imported);

$(document).ready(function () {
    var onclickVal = '';
    var temp       = '';
    $('.equipment_control_button').each(function () {
        onclickVal = $(this).find('button').attr('onclick');

        temp  = "var promise = [];";
        temp += "var p = "+onclickVal+";";
        temp += "promise.push(p);";
        temp += "$.when.apply($, promise).done(function(){";
        temp += "m765_params_button.loadParams();";
        temp += "});";
        $(this).find('button').attr('onclick', temp);
    });
});

var m765_params_button = {
    loadParams : function () {
        $('#equipment option').attr('selected','selected');
        var url  = sz_BaseUrl + '/button/m765/param/params';
        var data = $('#m765_param_export_form').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#m765_export_params').html('');
            $('#m765_export_params').html(jreturn);
        });
    },

    download : function () {
        $('#equipment option').each(function () {
           $(this).attr('selected','selected');
        });
        var hasEquip = $('#equipment option').length;

        if(hasEquip) {
            $('#m765_param_export_form').submit();
        }
        else {
            qssAjax.alert('Thiết bị yêu cầu bắt buộc.');
        }
    },
    
    import : function () {
        var url = sz_BaseUrl + '/button/m765/param/import';
        var data = $('#m765_param_button_form').serialize();

        if($('#deleteOld').is(':checked')) {
            qssAjax.confirm(
                'Những giá trị đã cập nhật trước đó theo ngày và điểm đo sẽ bị xóa đi trước khi import lại dữ liệu. Bạn có muốn tiếp tục thực hiện không?'
                , function () {
                    qssAjax.getHtml(url, data, function(jreturn) {
                        $('#qss_import').html('');
                        $('#qss_import').html(jreturn);
                        rowSearch('M765');
                    });
                }
            );
        }
        else {
            qssAjax.getHtml(url, data, function(jreturn) {
                $('#qss_import').html('');
                $('#qss_import').html(jreturn);
                rowSearch('M765');
            });
        }

    },

    export : function () {
        var url  = sz_BaseUrl + '/button/m765/param/export';
        var data = {};

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#m765_export').html(jreturn);
            $('#m765_export').dialog({ width: 800,height:400 });
        });
    }
};