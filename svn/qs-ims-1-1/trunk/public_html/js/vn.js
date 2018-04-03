Language = function()
{

  //This keeps phraseBook "private"
  var phraseBook = {
		  DIALOG_CLOSE: 'Đóng lại',
		  CONFIRM_OK: 'Đồng ý',
		  CONFIRM_CANCEL: 'Từ chối',
		  PROFILE_CHANGED: 'Hồ sơ đã được thay đổi.',
		  CONFIRM_DELETE: 'Bạn có thực sự muốn xóa bản ghi này?',
		  SELECT_ROW: 'Hãy lựa chọn một dòng',
		  SYSTEM_ERROR: 'Đã có lỗi hệ thống, xin liên hệ với quản trị',
		  CONFIRM_DELETE_TEMPLATE: 'Bạn có muốn xóa file mẫu?',
		  SELECT_ACTION: 'Hãy lựa chọn hành động.',
		  ACTION_DONE: 'Bạn vừa thực hiện: ',
		  SELECT_IMPORT_MODULE: 'Hãy chọn mô đun để import.',
		  SELECT_TEMPLATE_MODULE: 'Hãy chọn mô đun để tải mẫu.',
		  CONFIRM_SAVE: 'Dữ liệu của bạn chưa được lưu lại, bạn có muốn thoát ra không?',
		  SELECT_REFER_OBJECT: 'Hãy lựa chọn đối tượng tham chiếu.',
		  DOCUMENT_SAVE: 'Tài liệu đã được lưu. <a href="#" onclick="openModule(\'M016\')">Chuyến đến quản lý tài liệu</a>',
		  FUNCTION_NOT_SUPPORT: 'Chức này chưa hỗ trợ',
		  CONFIRM_PROCESS: 'Bạn có muốn chạy tiến trình ngay bây giờ?',
		  SAVE_FIRST: 'Bạn phải ghi dữ liệu trước',
		  CONFIRM_DELETE_NOTIFY: 'Bạn có thực sự muốn xóa đặt lịch cho module này?',
          ALERT_ADD_NOTIFY_EMPTY_TIME: 'Bạn phải chọn ít nhật một thời gian chạy!',
          SEARCH_PLACEHOLDER: 'Nhập từ khóa & enter', 
          CONFIRM_START_GREATER_THAN_END: 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.\n', 
          CONFIRM_GREATER_OR_EQUAL: ' phải lớn hơn hoặc bằng ', 
          CONFIRM_EMPTY: ' yêu cầu bắt buộc.\n', 
          CONFIRM_DATE_OVER: 'Khoảng thời gian bạn chọn quá dài. Báo cáo sẽ hiện giới hạn trong ', 
          CONFIRM_DATE_OVER_DAYS: ' ngày.', 
          CONFIRM_DATE_OVER_WEEKS: ' tuần.', 
          CONFIRM_DATE_OVER_MONTHS: ' tháng.', 
          CONFIRM_DATE_OVER_QUARTERS: ' quý.', 
          CONFIRM_DATE_OVER_YEARS: ' năm.',
          CONFIRM_LOGS: 'Nhập nội dung ghi chú'
  };

  return {
    translate: function( phrase)
    {
      return phraseBook[ phrase];
    }
  };
}();
