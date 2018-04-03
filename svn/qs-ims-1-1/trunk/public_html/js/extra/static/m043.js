var Static_M043 = {
    // Hiển thị nội dung form bên phải
    getForm : function(id) {
        switch (id) {
            case 'DANG_KY_NGHI':
                var url = sz_BaseUrl + '/static/m043/dangkynghi/form';
                var dat = {};
                break;
            case 'DANG_KY_LAM_THEM':
                var url = sz_BaseUrl + '/static/m043/dangkylamthem/form';
                var dat = {};
                break;
            case 'MY_TIME_CARD':
                var url = sz_BaseUrl + '/static/m043/mytimecard/form';
                var dat = {};
                break;
        }

        qssAjax.getHtml(url, dat, function(jreturn) {
            $('#m043_register_form').html(jreturn);
        });
    },

    // Hiển thị nội dung bảng bên dưới
    getDataTable : function (id) {
        switch (id) {
            case 'DANG_KY_NGHI':
                var url = sz_BaseUrl + '/static/m043/dangkynghi/detail';
                var dat = $('#filter_dangkynghi').serialize();
                break;
            case 'DANG_KY_LAM_THEM':
                var url = sz_BaseUrl + '/static/m043/dangkylamthem/detail';
                var dat = $('#filter_dangkylamthem').serialize();
                break;
            case 'MY_TIME_CARD':
                var url = sz_BaseUrl + '/static/m043/mytimecard/index';
                var dat = {};
                break;
        }

        qssAjax.getHtml(url, dat, function(jreturn) {
            $('#data_table').html(jreturn);
        });
    },

    // Hiển thị thông tin trong "My time card" (My time card bao gồm hai tab con)
    showTimeCard : function (id) {
        if(!id) {
            $('.active').children().click();
        }
        else {
            // Thêm style vào tab đang chọn
            $('.einfo_tab').each(function () {
                $(this).parent().removeClass('active');
            });
            $('#einfo_tab_'+id).parent().addClass('active');

            switch (id) {
                case 'MY_TIME_CARD':
                    var url = sz_BaseUrl + '/static/m043/mytimecard/detail';
                    var dat = $('#filter_mytimecard_detail').serialize();
                break;

                case 'LEAVE':
                    var url = sz_BaseUrl + '/static/m043/mytimecard/leave';
                    var dat = $('#filter_mytimecard_leave').serialize();
                break;

                case 'OT':
                    var url = sz_BaseUrl + '/static/m043/mytimecard/ot';
                    var dat = $('#filter_mytimecard_ot').serialize();
                break;
            }

            qssAjax.getHtml(url, dat, function(jreturn) {
                $('#mytimecard_data_table').html(jreturn);
            });
        }
    },

    // Hiển thị view theo lựa chọn
    // gồm hiển thị form bên phải và bảng bên dưới
    show : function (id, ele) {
        $(ele).find('input[type="radio"]').attr('checked', true);
        Static_M043.getForm(id);
        Static_M043.getDataTable(id);
    },

    // Button: Đăng ký nghỉ
    registerLeave : function () {
        var data = $('#form_dangkynghi').serialize();
        var url  = sz_BaseUrl + '/static/m043/dangkynghi/save';

        qssAjax.call(url, data, function(jreturn) {
            if(jreturn.message != '') {
                qssAjax.alert(jreturn.message);
            }
            Static_M043.show('DANG_KY_NGHI');
        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
    },

    // Button: Đăng ký làm thêm
    registerOT : function () {
        var data = $('#form_dangkylamthem').serialize();
        var url  = sz_BaseUrl + '/static/m043/dangkylamthem/save';

        qssAjax.call(url, data, function(jreturn) {
            if(jreturn.message != '') {
                qssAjax.alert(jreturn.message);
            }
            Static_M043.show('DANG_KY_LAM_THEM');
        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
    },

    // huy yeu cau dang ky nghi
    cancelLeaveRequest : function (ifid, deptid) {
        var data = {ifids:ifid, deptids:deptid};
        var url  = sz_BaseUrl + '/static/m043/dangkylamthem/cancel';

        qssAjax.confirm('Bạn có chắc chắn muốn hủy?', function(){
            qssAjax.call(url, data, function(jreturn) {
                if(jreturn.message != '') {
                    qssAjax.alert(jreturn.message);
                }

                Static_M043.show('DANG_KY_NGHI');
            }, function(jreturn) {
                qssAjax.alert(jreturn.message);
            });
        });
    },

    // huy yeu cau dang ky lam them
    cancelOTRequest : function (ifid, deptid) {
        var data = {ifids:ifid, deptids:deptid};
        var url  = sz_BaseUrl + '/static/m043/dangkylamthem/cancel';

        qssAjax.confirm('Bạn có chắc chắn muốn hủy?', function(){
            qssAjax.call(url, data, function(jreturn) {
                if(jreturn.message != '') {
                    qssAjax.alert(jreturn.message);
                }

                Static_M043.show('DANG_KY_LAM_THEM');
            }, function(jreturn) {
                qssAjax.alert(jreturn.message);
            });
        });
    }
};

jQuery(document).ready(function($) {
    $('#m043_dial_box div:first').click();
});
