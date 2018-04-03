/**
 * Các ham xử lý trong common.js
 * 
 * 1. Chọn tất cả option trong một dialbox 
 * - common_selectDialBox(id)
 * 2. Trả về một thẻ td theo định dạng 
 * - common_printCellData(data, cssClass, attribute)
 * 3. Trả về ngày hiện tại theo định dạng 
 * - common_getCurrentDate(spare, format) 
 * - Lưu ý chỉ thêm được tham số đầu tiên là dấu phân cách
 * 4. Chuyển từ dạng ngày YYYY-mm-dd sang dd-mm-YYYY 
 * - common_mysqlDateToDisplay(mysqldate)
 * 5. Chuyển từ dạng ngày dd-mm-YYYY sang YYYY-mm-dd 
 * - common_displayDateToMysql(displaydate)
 * 6. Kiểm tra ngày có phải dạng dd-mm-YYYY 
 * - common_isValidDate(date)
 * 7. Kiểm tra ngày có phải dạng YYYY-mm-dd 
 * - common_isValidMysqlDate(date)
 * 8. So sánh hai ngày dạng dd-mm-YYYY trả về -1,0,1 
 * - common_compareDate(day1, day2)
 * 9. So sánh hai ngày dạng dd-mm-YYYY đưa ra confirm ngày bắt đầu phải lớn hơn ngày kết thúc
 * - common_compareStartAndEnd(start, end, startFieldTranslate, endFieldTranslate)
 * 10. Trừ xem giữa hai ngày cách nhau bao nhiêu ngày 
 * - common_diffDate(start, end)
 * 11. Kiểm tra xem giá trị truyền vào có phải rỗng hay không? 
 * - common_checkEmpty(val, fieldTran)
 * 12. Đưa ra cảnh báo dạng confirm nếu số ngày (từ ngày bắt đầu đến ngày kết thúc) vượt quá số ngày giới hạn 
 * - common_dateWarning(period, timeLimit, ngaybd, ngaykt)
 * 13. Thêm vào trước một số dải ký tự giống hàm str_pad trong php 
 * - common_str_pad(input, pad_length, pad_string, pad_type)
 * 14. Chọn một dòng tr trong một bảng với định dạng table và thẻ hidden có sẵn 
 * - common_check_table_line(ele, KeepValInput, sFunc)
 * 15. Chọn tất cả dòng tr trong một bảng với định dạng table và thẻ hidden có sẵn 
 * - common_check_all_table_line(ele, CheckBoxInput, KeepValInput, sFunc)
 * 16. Đánh dấu một dòng của bảng khi click chuột vào 
 * - common_select_table_line(ele, pre, sFunc)
 * 17. Đánh dấu một dòng của bảng khi di chuột vào 
 * - common_hover_table_line(ele, pre, sFunc)
 * 18. Loại bỏ thẻ html 
 * - common_strip(html)
 * 19. Định dạng tiền 
 * - common_format_money(money) 
 * - Hiện tại chỉ chia cho 1000
 * 20. Chuyển url a=10&b=say sang object {a:10,b:say} 
 * - common_url_to_array(url)
 * - Sử dụng hàm common_ends_with(str, suffix)
 * 21. Chuyển object {a:10,b:say} sang url a=10&b=say 
 * - common_array_to_url(array)
 * 
 * 
 * 
 */

var marker_check = 'marker_check';
var marker_click = 'marker_click';
var marker_hover = 'marker_hover';

function common_selectDialBox(id)
{
	$('#'+id+' option').attr('selected','selected');
}


function common_printCellData(data, cssClass, attribute)
{
    html = '';

    if (cssClass == undefined)
    {
        cssClass = 'left';
    }
    
    if(attribute == undefined)
    {
        attribute = '';
    }

    if (data)
    {
        html += '<td class="' + cssClass + '" '+attribute+' >' + data + '</td>';
    }
    else
    {
        html += '<td class="' + cssClass + '" '+attribute+' > </td>';
    }
    return html;
}

// *****************************************************************************
// === get current date
// *****************************************************************************
function common_getCurrentDate(spare, format)
{
    if (spare == undefined)
    {
        spare = '-';
    }

    if (format == undefined)
    {
        format = 'vn';
    }

    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //January is 0!
    var yyyy = today.getFullYear();

    if (dd < 10) {
        dd = '0' + dd
    }

    if (mm < 10) {
        mm = '0' + mm
    }

    if (format == 'vn')
    {
        today = dd + spare + mm + spare + yyyy;
    }
    else
    {
        today = mm + spare + dd + spare + yyyy;
    }
    return today;
}

function common_mysqlDateToDisplay(mysqldate)
{
    if(common_isValidMysqlDate(mysqldate))
    {
        var matches = /^(\d{4})[-\/](\d{2})[-\/](\d{2})$/.exec(mysqldate);
        return matches[3] + '-' + matches[2] + '-' + matches[1];
    }
    return '00-00-0000'; 
}


function common_displayDateToMysql(displaydate)
{
    if(common_isValidDate(displaydate))
    {
        var matches = /^(\d{2})[-\/](\d{2})[-\/](\d{4})$/.exec(displaydate);
        return matches[3] + '-' + matches[2] + '-' + matches[1];
    }
	return '0000-00-00';
}

function common_displayDateToGeneral(displayDate)
{
    if(common_isValidDate(displayDate))
    {	
		$split = displayDate.split('-');
		$day   = $split[0];
		$month = $split[1];
		$year  = $split[2];
		$date  = $month +'/' + $day + '/' + $year;
		return $date;
    }
	return '00/00/0000'; // month/day/year	
}


// *****************************************************************************
// === check date format is dd-mm-YYYY
// *****************************************************************************
function common_isValidDate(date)
{
    var matches = /^(\d{2})[-\/](\d{2})[-\/](\d{4})$/.exec(date);
    if (matches == null) return false;
    var d = matches[1];
    var m = matches[2] - 1;
    var y = matches[3];
    var composedDate = new Date(y, m, d);
    return composedDate.getDate() == d &&
            composedDate.getMonth() == m &&
            composedDate.getFullYear() == y;
}

// *****************************************************************************
// === check date format is YYYY-mm-dd
// *****************************************************************************
function common_isValidMysqlDate(date)
{
    var matches = /^(\d{4})[-\/](\d{2})[-\/](\d{2})$/.exec(date);
    if (matches == null) return false;
    var d = matches[3];
    var m = matches[2] - 1;
    var y = matches[1];
    var composedDate = new Date(y, m, d);
    return composedDate.getDate() == d &&
            composedDate.getMonth() == m &&
            composedDate.getFullYear() == y;
}

// *****************************************************************************
// === compare start date and end date, date format is dd-mm-YYYY
// *****************************************************************************
function common_compareDate(day1, day2)
{
    var firstValue = day1.split('-');
    var secondValue = day2.split('-');
    var firstDate = new Date();
    firstDate.setFullYear(firstValue[2], (firstValue[1] - 1), firstValue[0]);
    var secondDate = new Date();
    secondDate.setFullYear(secondValue[2], (secondValue[1] - 1), secondValue[0]);

    if (firstDate > secondDate)
    {
        return -1;
    }
    else if (+firstDate == +secondDate)
    {
        return 0;
    }
    else if (firstDate < secondDate)
    {
        return 1;
    }
}

function common_compareStartAndEnd(start, end, startFieldTranslate, endFieldTranslate)
{
    var msg = '';
    if (common_compareDate(start, end) == -1)
    {
        if(startFieldTranslate && endFieldTranslate )
        {
			msg += '\"'+startFieldTranslate+'\"';
            msg += Language.translate('CONFIRM_GREATER_OR_EQUAL');
			msg += '\"'+endFieldTranslate+'\"';
        }
        else
        {
            msg += Language.translate('CONFIRM_START_GREATER_THAN_END');
        }
    }
    return msg;
}

function common_diffDate(start, end)
{
	start        = common_displayDateToGeneral(start);
	end          = common_displayDateToGeneral(end);
	var date1    = new Date(start);
	var date2    = new Date(end);
	var timeDiff = Math.abs(date2.getTime() - date1.getTime());
	var diffDays = parseInt(timeDiff / (1000 * 60 * 60 * 24)); 
	return diffDays;
}



function common_checkEmpty(val, fieldTran)
{
    var msg = '';
    if (val == '' || val == 0)
    {
        msg += '"' + fieldTran + '"' + Language.translate('CONFIRM_EMPTY');
    }
    return msg;
}


// var warning = common_dateWarning(ky, '<?php echo json_encode($this->limit);?>', ngaybd, ngaykt);
function common_dateWarning(period, timeLimit, ngaybd, ngaykt)
{
    limitJson = JSON.parse(timeLimit);
    var message = '';

    if (period != '')
    {
        var limit;
        var retval;
        var suffix;
        var prefix = Language.translate('CONFIRM_DATE_OVER');
        var startArr = ngaybd.split("-");
        var endArr = ngaykt.split("-");
        var start = new Date(parseInt(startArr[2]), parseInt(startArr[1]) - 1, parseInt(startArr[0]));
        var secondDate = new Date();
        var secondDateLimit = new Date();

        switch (period)
        {
            case 'D':
                limit = (limitJson['D'] != undefined && !isNaN(limitJson['D'])) ? limitJson['D'] : 0;
                limit = parseInt(limit);
                start.setDate(start.getDate() + limit - 1);
                suffix = Language.translate('CONFIRM_DATE_OVER_DAYS');
                break;
            case 'W':
                limit = (limitJson['W'] != undefined && !isNaN(limitJson['W'])) ? limitJson['W'] : 0;
                limit = parseInt(limit);
                start.setDate(start.getDate() + (limit * 7) - 1);
                suffix = Language.translate('CONFIRM_DATE_OVER_WEEKS');
                break;
            case 'M':
                limit = (limitJson['M'] != undefined && !isNaN(limitJson['M'])) ? limitJson['M'] : 0;
                limit = parseInt(limit);
                start.setMonth(start.getMonth() + limit);
                start.setDate(start.getDate() - 1);
                suffix = Language.translate('CONFIRM_DATE_OVER_MONTHS');
                break;
            case 'Q':
                limit = (limitJson['Q'] != undefined && !isNaN(limitJson['Q'])) ? limitJson['Q'] : 0;
                limit = parseInt(limit);
                start.setMonth(start.getMonth() + limit);
                start.setDate(start.getDate() - 1);
                suffix = Language.translate('CONFIRM_DATE_OVER_QUARTERS');
                ;
                break;
            case 'Y':
                limit = (limitJson['Y'] != undefined && !isNaN(limitJson['Y'])) ? limitJson['Y'] : 0;
                limit = parseInt(limit);
                start.setYear(start.getFullYear() + limit);
                start.setDate(start.getDate() - 1);
                suffix = Language.translate('CONFIRM_DATE_OVER_YEARS');
                break;
        }
        secondDate.setFullYear(parseInt(endArr[2]), parseInt(endArr[1]) - 1, parseInt(endArr[0]));
        secondDateLimit.setFullYear(start.getFullYear(), start.getMonth(), start.getDate());

        if ((secondDateLimit < secondDate) && (limit > 0))
        {
            message = prefix + ' ' + limit + ' ' + suffix;
        }
    }
    return message;
}


/**
 * @description tuong tu ham str_pad cua php
 * @param {string} input
 * @param {int} pad_length
 * @param {string} pad_string
 * @param {CONST} pad_type
 * @returns 
 */
function common_str_pad(input, pad_length, pad_string, pad_type) {
    //  discuss at: http://phpjs.org/functions/str_pad/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Michael White (http://getsprink.com)
    //    input by: Marco van Oort
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    //   example 1: str_pad('Kevin van Zonneveld', 30, '-=', 'STR_PAD_LEFT');
    //   returns 1: '-=-=-=-=-=-Kevin van Zonneveld'
    //   example 2: str_pad('Kevin van Zonneveld', 30, '-', 'STR_PAD_BOTH');
    //   returns 2: '------Kevin van Zonneveld-----'

    var half = '',
    pad_to_go;

    var str_pad_repeater = function(s, len) {
        var collect = '',
            i;

        while (collect.length < len) {
                collect += s;
        }
        collect = collect.substr(0, len);

        return collect;
    };

    input += '';
    pad_string = pad_string !== undefined ? pad_string : ' ';

    if (pad_type !== 'STR_PAD_LEFT' && pad_type !== 'STR_PAD_RIGHT' && pad_type !== 'STR_PAD_BOTH') {
        pad_type = 'STR_PAD_RIGHT';
    }
    if ((pad_to_go = pad_length - input.length) > 0) {
        if (pad_type === 'STR_PAD_LEFT') {
                input = str_pad_repeater(pad_string, pad_to_go) + input;
        } else if (pad_type === 'STR_PAD_RIGHT') {
                input = input + str_pad_repeater(pad_string, pad_to_go);
        } else if (pad_type === 'STR_PAD_BOTH') {
                half = str_pad_repeater(pad_string, Math.ceil(pad_to_go / 2));
                input = half + input + half;
                input = input.substr(0, pad_length);
        }
    }

    return input;
}

function common_check(ele, KeepValInput, sFunc)
{
    $(ele).attr('checked', true);
    $(ele).parent().parent().find('.table_line_disabled').each(function(){ $(this).removeAttr('disabled'); });
    $(ele).parent().find(KeepValInput).val(1); // Luu lai gia tri de truyen len
    $(ele).parent().parent().find('td').addClass(marker_check);

    if(sFunc)
    {
        sFunc();
    }
}

function common_uncheck(ele, KeepValInput, sFunc)
{
    var not_disabled = $(ele).attr('notdisable');
    not_disabled = (not_disabled == undefined || not_disabled == 0)?0:1;

    $(ele).removeAttr('checked');

    if(not_disabled)
    {
        $(ele).parent().find(KeepValInput).val(0); // Luu lai gia tri de truyen len
        $(ele).parent().parent().find('td').removeClass(marker_check);
    }
    else
    {
        $(ele).parent().parent().find('.table_line_disabled').each(function(){ $(this).attr('disabled', true); });
        $(ele).parent().find(KeepValInput).val(0); // Luu lai gia tri de truyen len
        $(ele).parent().parent().find('td').removeClass(marker_check);
    }

    if(sFunc)
    {
        sFunc();
    }
}

/**
 * @description check vao mot checkbox de chon dong(tr) trong table
 * @todo xu ly truong hop dong da duoc chon (co trong csdl) ma gio bo chon
 * @todo cho mau danh dau dong duoc sua
 * @example
 * <tr>
 * <td class="center">
 * <?php echo Qss_Lib_Date::mysqltodisplay($item->Ngay);?>
 * <input class="table_line_disabled" type="hidden" value="<?php echo $item->Ngay;?>" name="Date[]" disabled>
 * <input class="table_line_disabled" type="hidden" value="<?php echo $item->NoiDung;?>" name="Content[]" disabled>
 * <input class="table_line_disabled" type="hidden" value="<?php echo $item->ThoiGianThucHien;?>" name="ExecuteTime[]" disabled>
 * <input class="table_line_disabled" type="hidden" value="<?php echo $item->ThoiGianDungMay;?>" name="BreakdownTime[]" disabled>
 * </td>
 * <td><?php echo $item->NoiDung;?></td>
 * <td class="center">
 * <input type="checkbox" class="cwpo_line_checkbok " onclick="common_check_table_line(this, '.cwpo_line_checkbok_val')">
 * <input type="hidden" class="table_line_disabled cwpo_line_checkbok_val" name="CheckBox[]"  value="0">
 * </td>
 * </tr>
 *
 * Thêm thuộc tính notdisable="1" vào checkbox khi không muốn disable giá trị của dòng (Dòng đã được save vào nhưng muốn bỏ tick)
 */
function common_check_table_line(ele, KeepValInput, sFunc)
{
	var not_disabled = $(ele).attr('notdisable');
	
	not_disabled = (not_disabled == undefined || not_disabled == 0)?0:1;
	
	
    if($(ele).is(':checked'))
    {
       $(ele).parent().parent().find('.table_line_disabled').each(function(){ $(this).removeAttr('disabled'); });
       $(ele).parent().find(KeepValInput).val(1); // Luu lai gia tri de truyen len
       $(ele).parent().parent().find('td').addClass(marker_check);
    }
    else
    {
    	if(not_disabled)
		{
	       $(ele).parent().find(KeepValInput).val(0); // Luu lai gia tri de truyen len
            $(ele).parent().parent().removeClass(marker_check);
            $(ele).parent().parent().find('td').removeClass(marker_check);
		}
    	else
		{
    		$(ele).parent().parent().find('.table_line_disabled').each(function(){ $(this).attr('disabled', true); });
			$(ele).parent().find(KeepValInput).val(0); // Luu lai gia tri de truyen len
            $(ele).parent().parent().removeClass(marker_check);
			$(ele).parent().parent().find('td').removeClass(marker_check);      		
		}
    }
    
    if(sFunc)
    {
       sFunc();
    }
}

/**
 * 
 * Tham khao ham common_check_table_line()
 * <input type="checkbox" onclick="common_check_all_table_line(this, '.cwpo_line_checkbok', '.cwpo_line_checkbok_val')"/>
 * Thêm common_no_check = 1 vào tr để bỏ qua chọn dòng 
 * Luon co mot dong tieu de duoc bo qua not(':eq(0)')
 */
function common_check_all_table_line(ele, CheckBoxInput, KeepValInput, sFunc)
{
	var common_no_check;
	var checked = $(ele).is(':checked');
    var find;

    if($(ele).is(':checked'))
    {
    	$(ele).parent().parent().parent().find('tr').not(':eq(0)').each(function(){
    		common_no_check = $(this).attr('common_no_check');
    		
    		if(common_no_check == undefined)
    		{
    			common_no_check = 0;
    		}
    		
    		if(common_no_check == 0)
			{
    			$(this).find('.table_line_disabled').each(function(){ $(this).removeAttr('disabled'); });
    			$(this).find(KeepValInput).val(1);//.each(function(){ $(this).val(1);}); // Luu lai gia tri de truyen len
    			$(this).find(CheckBoxInput).attr('checked', true);
    			$(this).find(CheckBoxInput).each(function(){$(this).parent().parent().addClass(marker_check)});
			}

    	});	    	

    	// Giải quyết trường hợp sử dụng table scroll, code giống hệt phần trên
        $(CheckBoxInput).each(function(){
            common_no_check = $(this).attr('common_no_check');
            find = $(this).parent().parent();

            if(common_no_check == undefined)
            {
                common_no_check = 0;
            }

            if(common_no_check == 0)
            {

                find.find('.table_line_disabled').each(function(){ $(this).removeAttr('disabled'); });
                find.find(KeepValInput).val(1);//.each(function(){ $(this).val(1);}); // Luu lai gia tri de truyen len
                find.find(CheckBoxInput).attr('checked', true);
                find.find(CheckBoxInput).each(function(){$(this).parent().parent().addClass(marker_check)});
            }
        });
    }
    else
    {
    	$(ele).parent().parent().parent().find('tr').not(':eq(0)').each(function(){
    		common_no_check = $(this).attr('common_no_check');

    		
    		if(common_no_check == undefined)
    		{
    			common_no_check = 0;
    		}

    		if(common_no_check == 0)
			{
    			$(this).find('.table_line_disabled').each(function(){ $(this).attr('disabled', true); });
    			$(this).find(KeepValInput).val(0);//.each(function(){ $(this).val(1);}); // Luu lai gia tri de truyen len
    			$(this).find(CheckBoxInput).removeAttr('checked');
    			$(this).find(CheckBoxInput).each(function(){
                    $(this).parent().parent().removeClass(marker_check);
                    $(this).parent().parent().find('td').removeClass(marker_check);
    			});
			}
    	});

        // Giải quyết trường hợp sử dụng table scroll, code giống hệt phần trên
        $(CheckBoxInput).each(function(){
            common_no_check = $(this).attr('common_no_check');
            find = $(this).parent().parent();

            if(common_no_check == undefined)
            {
                common_no_check = 0;
            }

            if(common_no_check == 0)
            {
                find.find('.table_line_disabled').each(function(){ $(this).attr('disabled', true); });
                find.find(KeepValInput).val(0);//.each(function(){ $(this).val(1);}); // Luu lai gia tri de truyen len
                find.find(CheckBoxInput).removeAttr('checked');
                find.find(CheckBoxInput).each(function(){
                    $(this).parent().parent().removeClass(marker_check);
                    $(this).parent().parent().find('td').removeClass(marker_check);
                });
            }
        });
    }
    
    if(sFunc)
    {
        sFunc();
    }	
}

/**
 * @description tick hoac hover vao mot dong cua table
 * @param {object} the tr duoc click 
 * @returns {undefined}
 */
function common_select_table_line(ele, pre, sFunc)
{
	if (!pre)
		pre = '';
	var active_class = pre + '_active';

	// Xoa bo danh dau tren dong khac
	$(ele).parent().find('tr').each(function() {
		$(this).removeClass(marker_click);
		$(this).removeClass(active_class);
	});

	// Them danh dau vao dong duoc chon
	$(ele).addClass(marker_click);
	$(ele).addClass(active_class);

	if (sFunc)
	{
		sFunc();
	}
}

/**
 * @description xu ly khi hover vao mot dong cua table
 * @param {type} ele
 * @param {type} pre
 * @param {type} sFunc
 * @returns {undefined}
 */
function common_hover_table_line(ele, pre, sFunc)
{
	if (!pre)
		pre = '';
	var active_class = pre + '_active';

	$(ele).hover(
		function() {
			// Xoa bo danh dau tren dong khac
			$(ele).parent().find('tr').each(function() {
				$(this).removeClass(marker_hover);
				$(this).removeClass(active_class);
			});

			// Them danh dau vao dong duoc chon
			$(ele).addClass(marker_hover);
			$(ele).addClass(active_class);
		},
		function() {
			// Xoa bo danh dau tren dong khac
			$(ele).parent().find('tr').each(function() {
				$(this).removeClass(marker_hover);
				$(this).removeClass(active_class);
			});
		});
            
	if (sFunc)
	{
		sFunc();
	}
}


/**
 * @description loai bo the html
 * @param {type} html
 * @returns 
 */
function common_strip(html)
{
	var tmp = document.createElement("DIV");
	tmp.innerHTML = html;
	return tmp.textContent || tmp.innerText || "";
}

function common_format_money(money)
{
	return money/1000;
}


function common_url_to_array(url) {
    var request = {};
    var arr = [];
    var pairs = url.substring(url.indexOf('?') + 1).split('&');
    for (var i = 0; i < pairs.length; i++) {
      var pair = pairs[i].split('=');

      //check we have an array here - add array numeric indexes so the key elem[] is not identical.
      if(common_ends_with(decodeURIComponent(pair[0]), '[]') ) {
          var arrName = decodeURIComponent(pair[0]).substring(0, decodeURIComponent(pair[0]).length - 2);
          if(!(arrName in arr)) {
              arr.push(arrName);
              arr[arrName] = [];
          }

          arr[arrName].push(decodeURIComponent(pair[1]));
          request[arrName] = arr[arrName];
      } else {
        request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
      }
    }
    return request;
}

function common_ends_with(str, suffix) {
    return str.indexOf(suffix, str.length - suffix.length) !== -1;
}

function common_array_to_url(array) 
{
	var pairs = [];
	for (var key in array)
    if (array.hasOwnProperty(key))
    	pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(array[key]));
	return pairs.join('&');
}

function common_scroll_to_top(id)
{
    $("#"+id).animate({ scrollTop: 0 }, "slow");
    return false;
}

function common_get_url_parameter(sParam)
{
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }

    return '';
};