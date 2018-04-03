$(document).ready(function() {
        $('.hidden_div').hide();
        $('#general_plan').show();
});

/** General plan */
function generalPlan()
{
        // Danh dau buoc da chon
        $('.middle').removeClass('middle_step_selected').addClass('middle_step');
        $('.first_left').removeClass('first_step_selected').addClass('first_step');
        $('.last_right').removeClass('last_step_selected').addClass('last_step');
        $('.first_left').addClass('first_step_selected');

        // Hien thi ke hoach tong the
        $('.hidden_div').hide();
        $('#general_plan').show();
}

// Danh sach don hang 
function searchGeneralItem(lamLai)
{
        var ext = {lamLai: lamLai};
        var url = sz_BaseUrl + '/extra/mrp/primary/search';
        var data = $('#search_item').serialize() + '&' + $.param(ext);
        ;
        qssAjax.getHtml(url, data, function(jreturn) {
                $('#choose_item_table').html(jreturn);
        });
}

// End searchGeneralItem

// Khi nut kiem tra duoc an 
// Chay cho het cac cau thanh con
// Cho chon cau thanh, so luong san xuat, so luong mua hang
// Khi chay het chuyen sang in thong ke thoi gian yeu cau, kha nang, da dat truoc
// @Note: GroupID la dang ngay thang
// @Note: lineNo la like tiep theo, giup xac dinh cac ban ghi nao la ban ghi truoc do
function validateGeneralItems(groupID, lineNo)
{
        // Thiet ke yeu cau bat buoc
        var msg = '';
        //var level = $('#current_level').val();
        var endValidation = $('#end-validation-' + groupID).val();

        // Thong bao loi neu thieu thiet ke tren dong nao do
        $('.bom-' + groupID).each(function() {
                if ($(this).val() == '')
                {
                        msg += 'Thiết kế trên dòng ' + $(this).attr('lineno') + ' yêu cầu bắt buộc!\n';
                }
        });

        // Khong the hoan thanh do co san pham co cau thanh khong co thanh phan
        var bomNoChildren = $('.bom-no-children-' + groupID).length;
        if (bomNoChildren > 0)
        {
                $('#end-validation-' + groupID).val(0);
                msg += 'Thiết kế trên dòng ' + $('.bom-no-children-' + groupID).attr('lineno') + ' chưa được thêm thành phần sản phẩm!\n';
        }

        // Bao loi neu co
        if (msg != '')
        {
                qssAjax.alert(msg);
                return;
        }

        // Neu chua ket thuc viec in danh sach con
        // Thi tiep tuc
        // Neu da ket thuc in ra bang thong ke thoi gian
        if (endValidation == 0)
        {
                showChildrenOfGeneralItem(groupID, lineNo);
        }
        else if (endValidation == 1)
        {
                showTimeAnalytics(groupID, lineNo);
        }
}
// End validateGeneralItems

function checkEndMrpGeneralRun(groupID, lineNo)
{
        var endValidation = $('#end-validation-' + groupID).val();
        if (endValidation == 1)
        {
                //validateGeneralItems(groupID, lineNo);
        }
}

function showChildrenOfGeneralItem(groupID, lineNo)
{
        var url = sz_BaseUrl + '/extra/mrp/primary/children';
        //var ext = {issueDatex:groupID, lineNo: lineNo};
        var data = $('.date-' + groupID + ', .filter').serialize(); //+  '&' + $.param(ext);

        qssAjax.getHtml(url, data, function(jreturn) {
                //qssAjax.alert(jreturn);
                $('.line-' + groupID).remove();
                $('#mrp_primary_table #heading-' + groupID).after(jreturn);
                checkEndMrpGeneralRun(groupID, lineNo);
        });
}
// End showChildrenOfGeneralItem()

function showTimeAnalytics(groupID, lineNo)
{
        var url = sz_BaseUrl + '/extra/mrp/primary/change';
        var ext = {issueDatex: groupID, lineNo: lineNo};
        var data = $('.date-' + groupID + ', .filter').serialize() + '&' + $.param(ext);
        var finishedAndChooseSave = true;

        qssAjax.getHtml(url, data, function(jreturn) {
                $('#group-' + groupID).html('');
                $('#group-' + groupID).append(jreturn);

                $('.line-' + groupID).each(function() {
                        if (!$(this).hasClass('bggrey2'))
                        {
                                $(this).addClass('bggrey2');
                                $(this).find('.production_qty_box').attr('readonly', true).addClass('.readonly');
                                $(this).find('.purchase_qty_box').attr('readonly', true).addClass('.readonly');
                        }

                });
                $('#qss_trace').scrollTop($('#group-' + groupID).position().top);

                $('.end_validation').each(function() {
                        if ($(this).val == 0)
                                finishedAndChooseSave == false;
                });
                ;

                if (finishedAndChooseSave)
                {
                        if ($('#save_item_plan_button').length == 0)
                        {
                                $('#general_main_control').append('<button type="button" id="save_item_plan_button" class="btn-custom" onclick="saveGeneralPlan()"> Lưu lại </button>');
                                $('#qss_trace').delay(1000).scrollTop($('#save_item_plan_button').position().top);
                        }
                }
        });
}
// End showTimeAnalytics()

// Ham dung de lay ten bom theo gia tri select box
// Dien vao thao ro hay lap dat
function fillBomName(ele)
{
        var selected = $(ele).find('option:selected');
        var selectedValue = selected.val();
        var addingText = (selectedValue != 0) ? selected.text() : '';
        var realRefAttr = $(ele).parent().parent().find('.real_ref_attributes').val();
        var allAttrOption = selected.attr('all');

        $(ele).parent().parent().find('.bom_name').val($.trim(addingText));
        $(ele).parent().parent().find('.assembly').val(selected.attr('assembly'));

        // Dien ref thuoc tinh bang 0 neu cau thanh ap dung cho tat ca thuoc tinh
        if (allAttrOption == 1)
        {
                $(ele).parent().parent().find('.ref_attributes').val(0);
        }
        else
        {
                $(ele).parent().parent().find('.ref_attributes').val(realRefAttr);
        }
}
// End fillBomName


function changeQtyOfGeneralLine(ele, defaultLineQty, typeCheker)
{
        var parent = $(ele).parent().parent();
        var muaqty = parseInt(parent.find('.purchase_qty_box').val());
        var sxqty = parseInt(parent.find('.production_qty_box').val());
        var total = muaqty + sxqty;
        if (total <= defaultLineQty)
        {
                if (typeCheker == 1) // Sx
                {
                        parent.find('.production_qty_box').val(sxqty);
                        parent.find('.purchase_qty_box').val(defaultLineQty - sxqty);
                }
                else if (typeCheker == 2) // Mh
                {
                        parent.find('.purchase_qty_box').val(muaqty);
                        parent.find('.production_qty_box').val(defaultLineQty - muaqty);
                }
        }
        else if (total > defaultLineQty)
        {
                //$(ele).val(defaultLineQty);
                if (typeCheker == 1) // Sx
                {
                        parent.find('.production_qty_box').val(muaqty);
                        parent.find('.purchase_qty_box').val(defaultLineQty - muaqty);
                }
                else if (typeCheker == 2) // Mh
                {
                        parent.find('.production_qty_box').val(sxqty);
                        parent.find('.purchase_qty_box').val(defaultLineQty - sxqty);
                }
        }
}
// End changeQtyOfGeneralLine


function saveGeneralPlan()
{
        var data = $('#search_item').serialize();
        var url = sz_BaseUrl + '/extra/mrp/primary/save';
        qssAjax.call(url, data, function(jreturn) {
                if (jreturn.message != '')
                {
                        qssAjax.alert(jreturn.message);
                }
                detailPlan();
        }, function(jreturn) {
                qssAjax.alert(jreturn.message);
        });
}


function getOldGeneralPlan(groupID)
{
        var url = sz_BaseUrl + '/extra/mrp/primary/old';
        //var ext = {issueDatex:groupID, lineNo: lineNo};
        var data = $('.date-' + groupID + ', .filter').serialize(); //+  '&' + $.param(ext);

        qssAjax.getHtml(url, data, function(jreturn) {
                //qssAjax.alert(jreturn);
                if ($.trim(jreturn) != 'false')
                {
                        $('.line-' + groupID).remove();
                        $('#mrp_primary_table #heading-' + groupID).after(jreturn);
                }
        });
}

function changeQtyAvailable(ele)
{
        var total = 0;
        var changeQty = $(ele).val();
        var warehouse = $(ele).attr('warehouseQty');
        var defaultQty = $(ele).attr('defaultQty');
        var classes = $(ele).attr('marker');
        var val = 0;
        // warehouse_marker

        // Tong so bao gom ca dong nay
        $('.' + classes).each(function() {
                val = (isNaN(parseFloat($(this).val()))) ? 0 : parseFloat($(this).val());
                $(this).val(val);
                total += val;
        });

        if (total > warehouse)
        {
                $(ele).val(defaultQty);
                qssAjax.alert('Trong kho không đủ số lượng!');
                // co the bao loi rang ko du so luong o day
        }
        else
        {
                if (total == warehouse)
                {
                        $('.' + classes).each(function() {
                                if ($(this).val() == 0)
                                {
                                        $(this).attr('readonly', true);
                                        $(this).addClass('readonly');
                                }
                        });
                }
                else
                {
                        $('.' + classes).each(function() {
                                $(this).removeAttr('readonly', true);
                                $(this).removeClass('readonly');
                        });
                }
                $(ele).attr('defaultQty', changeQty);
                $('.warehouse_marker_' + classes).text(total + '/' + warehouse);
        }
}

/** Tab2: Detail plan */

// @todo: Truoc khi chuyen buoc phai kiem tra ke hoach da duoc hoan tong the da duoc hoan thien chua
function detailPlan()
{
        // Chọn bước này trên thanh chuyển bươc
        $('.middle').removeClass('middle_step_selected').addClass('middle_step');
        $('.first_left').removeClass('first_step_selected').addClass('first_step');
        $('.last_right').addClass('last_step_selected');

        // Hiển thị kế hoạch chi tiết
        $('.hidden_div').hide();
        $('#detail_plan').show();
}
// End detailPlan

function runDetailMrp()
{
        var url = sz_BaseUrl + '/extra/mrp/detail/run';
        var ifid = $('#mrp_ifid').val();
        var startDate = $('#mrp_startdate').val();
        var data = {ifid: ifid, startDate: startDate};
        qssAjax.getHtml(url, data, function(jreturn) {
                $('#detail_show_request').html(jreturn);
        });
}
// End runDetailMrp

function saveMrp()
{
        var data = $('#detail_mrp_form').serialize();
        var url = sz_BaseUrl + '/extra/mrp/detail/save';
        qssAjax.call(url, data, function(jreturn) {
                if (jreturn.message != '')
                {
                        qssAjax.alert(jreturn.message);
                }
        }, function(jreturn) {
                qssAjax.alert(jreturn.message);
        });
}