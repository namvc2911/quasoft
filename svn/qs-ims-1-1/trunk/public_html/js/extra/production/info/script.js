/**
 * @author Thinh
 * @description: Module chay tu ham pinfo_filter()
 * 
 * Ham chay dau tien
 * => pinfo_filter()
 * 	=> pinfo_set_cookies() // set cac dieu  kien loc vao cookie
 * 	=> pinfo_set_pagination(); // lay dieu kien phan trang 
 */

/* --- Bien toan cuc --- */
var start;// ngay bat dau
var end; // ngay ket thuc
var po      = []; // production order
var display ;
var page    ;
var last_active = 0;

// Tao dieu kien ban dau de luu cookie thoi gian, thoi gian luon chuyen ve ngay hien tai khi 
// sang mot ngay moi
var date = new Date();
var hoursleft = 23-date.getHours();
var minutesleft = 59-date.getMinutes();
var secondsleft = 59-date.getSeconds();
var seconds = (hoursleft * 3600) + (minutesleft * 60) + secondsleft;
date.setTime(date.getTime() + (seconds  * 1000));

/* --- Khoi tao, goi ham ban dau --- */
jQuery(document).ready(function($){
    // set datepicker
    $('.datepicker').each(function() {
        $(this).datepicker({dateFormat: "dd-mm-yy"});
        $(this).attr('placeholder', 'dd-mm-yyyy');
    });/* Datepicker*/
    
	pinfo_filter(); // loc va hien thi toan bo module
});  

/* --- Loc theo lenh san xuat --- */
/**
 * @description: Them loc theo lenh san xuat
 */
function pinfo_add_po_filter(ioid, code)
{
    var html = '';
    html += '<div class="pinfo_filter_by_po_ele pinfo_filter_by_po_ele_'+ioid+' pointer" >';
    html += '<div class="pinfo_filter_order_label"> '+ code;
    html += '<input type="hidden" name="pinfo_filter_order[]" class="pinfo_filter_order" value="'+ioid+'" />';
    html += '<a href="#1" onclick="pinfo_remove_po_filter('+ioid+')" class="pinfo_filter_order_remove">';
    html += '<img src ="/images/event/close.png" /></a>';
    html += '</div>';
	html += '</div>';
	
	
	var i,add;
    
	if(po.length == 0)
	{
		$('#pinfo_filter_orders').html('');
	    $('#pinfo_filter_orders').append(html);
	    last_active = ioid;
	    pinfo_set_production_order_cookies();
	}
	else
	{
		add = true;
		for(i = 0; i<= po.length; i++)
		{
			if(ioid == parseInt(po[i]))
			{
				add = false;
			}
		}
		
		if(add)
		{
			$('#pinfo_filter_orders').html('');
		    $('#pinfo_filter_orders').append(html);
		    last_active = ioid;
		    pinfo_set_production_order_cookies();
		}
	}
	pinfo_filter();
}

/**
 * @description: Xoa loc theo lenh san xuat
 */
function pinfo_remove_po_filter(ioid)
{
	po = []; // reset
	$('.pinfo_filter_by_po_ele_'+ioid).remove();
	$('#pinfo_po_by_line_'+ioid).removeClass('marker_click pinfo_po_by_line_active');
	pinfo_set_production_order_cookies();
	pinfo_filter();
}

/* --- Loc theo dieu kien khac --- */
/**
 * @description: Hien thi so trang cua phieu giao viec
 */
function pinfo_show_number_page_of_wo()
{
}

/**
 * (*) HAM (*) CHINH (*)
 * @description: Ham hien thi cac thong tin cua module
 * nhu phieu giao viec, bieu do, bao cao
 */
function pinfo_filter()
{
	pinfo_set_cookies(); // set cac dieu kien loc vao cookie
	pinfo_set_pagination(); // lay dieu kien phan trang 
	pinfo_po_by_line(); // Hien thi lenh san xuat theo day chuyen loc theo ngay
	pinfo_wo(); // Hien thi phieu giao viec
	
}

/**
 * @description: Set pagination
 */
function pinfo_set_pagination()
{
	display = $('#pinfo-display').val();
	page    = $('#pinfo-page').val();

}

/**
 * @description: Set cac cookies
 */
function pinfo_set_cookies()
{
	// Luu cac cookie
	pinfo_set_other_cookies();
	pinfo_set_production_order_cookies();
}

function pinfo_set_other_cookies()
{
	start = $('#pinfo_start').val(); // ngay bat dau
	end = $('#pinfo_end').val(); // ngay ket thuc	
	
    $.cookie('pinfo_start', start, { expires: date }) ; // loc thoi gian - start
    $.cookie('pinfo_end', end, { expires: date }) ; // loc thoi gian - end
}

function pinfo_set_production_order_cookies()
{
	var i,add;
	po = []; // reset
    $('.pinfo_filter_order').each(function(){
    	if(po.length == 0)
    	{
			po.push($(this).val());
    	}
    	else
		{
    		add = true;
    		for(i = 0; i<= po.length; i++)
			{
    			if(parseInt($(this).val()) == parseInt(po[i]))
				{
    				add = false;
				}
			}
    		
    		if(add)
    		{
    			po.push($(this).val());
    		}
		}
    });
    $.cookie('pinfo_po', po, { expires: date }) ; // loc theo production order- end
}

/* --- Hien thi phieu giao viec --- */
/**
 * @description: Hien thi phieu giao viec
 */
function pinfo_wo()
{
    var url     = sz_BaseUrl + '/static/m758/workorder'; 
    var data = {
            page:page,
            display:display,
            start:start,
            end:end,
            po:po
    }; 
    qssAjax.getHtml(url, data, function(jreturn) {   
        $('#pinfo_work_orders').html(jreturn);
    });   
}

/**
 * @description: Hien thi so trang phieu giao viec
 */
function pinfo_wo_page()
{
	
}

/**
 * @description: Hien thi comment
 */
function pinfo_comment(ifid, deptid, uid)
{
    var url = sz_BaseUrl + '/user/form/comments';
    var data = {
                    ifid : ifid,
                    deptid : deptid,
                    uid:uid
            };
    qssAjax.getHtml(url, data, function(jreturn) {
            $('#qss_trace').html(jreturn);
            $('#qss_trace').dialog({ width: 400,height:400 });
    });
}

/**
 * @description: Hien thi chi tiet ban ghi
 */
function pinfo_detail(ifid, deptid)
{
        // xem chi tiet ban ghi
        var url = sz_BaseUrl + '/user/form/detail';
        var data = {ifid:ifid,deptid:deptid};
        qssAjax.getHtml(url, data, function(jreturn) {
               $('#qss_trace').html(jreturn);
               $('#qss_trace').dialog({ width: 900,height:450 });
        });
}

/* --- Hien thi lenh sann xuat theo day chuyen --- */
/**
 * @description: Hien thi lenh san xuat nhom theo day chuyen
 */
function pinfo_po_by_line()
{
    var url     = sz_BaseUrl + '/static/m758/po'; 
    var data = {
            start:start,
            end:end
    }; 
    qssAjax.getHtml(url, data, function(jreturn) {   
        $('#pinfo_production_order_by_line').html(jreturn);
        pinfo_get_line_last_active();
    });  
}

function pinfo_get_line_last_active()
{
	//qssAjax.alert(last_active);
    if(last_active )
	{
		var i,add;
		for(i = 0; i<= po.length; i++)
		{
			if(last_active == parseInt(po[i]))
			{
				$('#pinfo_po_by_line_'+last_active).addClass('marker_click');
			}
			
		}
	}
}

/* --- Bao cao --- */

/**
 * @description: Bieu do tron ve so phieu giao viec theo tinh trang
 * , loc theo dieu kien
 */
function pinfo_work_orders_by_step()
{
}

/**
 * @descrition: Bieu do cot ve so luong san pham san xuat: yeu cau, da lam
 * , sp loi duoc loc theo dieu kien
 */
function pinfo_items_report()
{
}