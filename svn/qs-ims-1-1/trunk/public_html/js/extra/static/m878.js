

var m878 = {
    show : function () {
        var url  = sz_BaseUrl + '/static/m878/show';
        var data = $('#m878_filter_form').serialize();

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#m878_table').html(jreturn);
        });
    },
    
    createClass : function (blockID, loaiKhoaHocID) {
        var len = parseInt($('.cwpo_line_checkbok'+blockID+':checked').length);

        if(len > 0) {
            popupFormInsert(
                'M328'
                , {'OLopHoc_LoaiKhoaHoc': loaiKhoaHocID}
                , function() {}
                , function(el) {
                    if(el.ifid) {
                        var data = $('.m878Form'+blockID+':enabled').serialize() + "&ifid=" + el.ifid;
                        var url  = sz_BaseUrl + '/static/m878/save';

                        qssAjax.call(url, data, function(jreturn){
                            if(jreturn.message != '') {
                                qssAjax.notice(jreturn.message);
                            }

                            $('.m878Form'+blockID+':enabled').each(function () {
                               $(this).parent().find('.m878Checkbox'+blockID).remove();
                            });

                        }, function(jreturn){
                            qssAjax.alert(jreturn.message);
                        });
                    }
                }
            );
        }
        else {
            qssAjax.alert('Chưa chọn học viên!');
        }

    }
}

$('document').ready(function () {
    m878.show();

    var he = $('#view').height() - $('#horizontal-toolbar').height() + 30;
    $('#view').css({'height': he+'px'});
});