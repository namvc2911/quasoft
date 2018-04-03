var static_m052 = {
    // Tải khung cây phòng ban (view)
    // @note: Cây phòng ban lọc theo cả search không?
    // Tải cây phòng ban
    loadDepartmentTree : function () {
        var search = $('#m052_search_dept').val();

        if(search) {
            $('#m052_search_dept').addClass('bgyellow');
        }
        else {
            $('#m052_search_dept').removeClass('bgyellow');
        }

        $('#m052_department_tree')
            .jstree({
                "plugins": ["themes", "json_data", "ui", "state", "cookie","dnd"],
                'core': {
                    'check_callback': function(operation, node, node_parent, node_position, more) {
                        var ret = true;
                        return true;
                    },
                    'data': {
                        "url": function(node) {
                            var nodeid = 0;


                            if (node.original && node.original.attr && node.original.attr.nodeid) {
                                nodeid = node.original.attr.nodeid;
                            }

                            return sz_BaseUrl + "/static/m052/department?nodeid=" + nodeid + '&search=' + search;
                        },
                        "dataType": "json"// needed only if you do not supply JSON headers
                    }
                }
            });

        $('#m052_department_tree')
            .bind("select_node.jstree", function(e, data) {
                if (data.node && data.node.original && data.node.original.attr && data.node.original.attr.nodeid) {
                    static_m052.loadEmployees(data.node.original.attr.nodeid);
                    static_m052.showDepartmentDetail(data.node.original.attr.nodeid);
                    $('#m052_nodeid').val(data.node.original.attr.nodeid);
                }
            });
    },
    // Tai lai cay phong ban
    reloadDepartmentTree : function() {
        $('#m052_department_tree').jstree('destroy');//.destroy();
        static_m052.loadDepartmentTree();
    },
    // Tải khung danh sách nhân viên (view)
    // @note: Danh sách nhân viên sẽ lọc theo search, cần truyền thêm tham số <<< (@)
    // @note: Thông tin nhân viên sẽ thay đổi khi chọn phòng ban hoặc khi search
    // , điều kiện hiển thị sẽ là kết hợp search và department
    loadEmployees : function (nodeid, search) {
        if(!search) {
            search = $('#m052_search_empl').val();
        }

        if(!nodeid) {
            nodeid = parseInt($('#m052_nodeid').val());
        }

        var pageno = parseInt($('#m052_employee_pageno').val());

        var url  = sz_BaseUrl + '/static/m052/employee';
        var data = {nodeid:nodeid, search: search, pageno: pageno};

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#m052_employee').html(jreturn);

            if(search) {
                $('#m052_search_empl').addClass('bgyellow');
            }
            else {
                $('#m052_search_empl').removeClass('bgyellow');
            }
        });
    },
    // Tải nội dung chi tiết khi chọn phòng ban hoặc nhân viên
    // @note: Phân này sẽ load theo đang chọn phòng ban nào, nhân viên nào, ưu tiên hiển thị thông tin nhân viên
    loadContent : function () {
        var url  = sz_BaseUrl + '/static/m052/content/index';
        var data = {};

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#m052_content').html(jreturn);
        });
    },
    showDepartmentDetail : function (nodeid) {
        var url  = sz_BaseUrl + '/static/m052/content/department/index';
        var data = {nodeid:nodeid};

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#m052_content_wrap').html(jreturn);
        });
    },
    // Hiển thị thông tin chi tiết của nhân viên
    showEmployeeDetail : function (ele, emp) {
        $('.m052_employee_line').each(function () {
           $(this).removeClass('bgonahau') ;
        });
        $(ele).addClass('bgonahau');

        var url  = sz_BaseUrl + '/static/m052/content/employee/index';
        var data = {emp:emp};

        qssAjax.getHtml(url, data, function(jreturn) {
            $('#m052_content_wrap').html(jreturn);
        });
    }
};

$(document).ready(function() {
    // Load các khung màn hình ban đầu
    static_m052.loadEmployees(); // load danh sách nhân viên
    static_m052.loadContent(); // Hiển thị thông tin chi tiết của nhân viên hoặc phòng ban
    static_m052.loadDepartmentTree(); // load cây phòng ban

    $('#m052_search_dept').keypress(function(e){
        if(e.keyCode == 13) {
            static_m052.reloadDepartmentTree();
        }
    });

    $('#m052_search_empl').keypress(function(e){
        if(e.keyCode == 13) {
            static_m052.loadEmployees();
        }
    });

    $('#m052_employee_pageno').keypress(function(e){
        if(e.keyCode == 13) {
            static_m052.loadEmployees();
        }
    });
});