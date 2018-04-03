var m182_handover_button = {
    printPreview : function () {
        var url  = sz_BaseUrl + '/button/m182_handover/show';
        var ifid = parseInt($('#_ifid').val());
        var deptid = parseInt($('#_deptid').val());
        var emp    = parseInt($('#_emp').val());

        var url = sz_BaseUrl + '/button/m182_handover/show?popup=1&ifid=' + ifid + '&deptid=' + deptid + '&eid='+ emp;
        window.open(url, '_blank');
    }
}