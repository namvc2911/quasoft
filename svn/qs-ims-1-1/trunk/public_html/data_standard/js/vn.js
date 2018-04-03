Language = function()
{

  //This keeps phraseBook "private"
  var phraseBook = {
		  CONFIRM_START_GREATER_THAN_END: 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.\n'
                  , CONFIRM_EMPTY: ' yêu cầu bắt buộc.\n'
                  , CONFIRM_DATE_OVER: 'Khoảng thời gian bạn chọn quá dài. Báo cáo sẽ hiện giới hạn trong '
                  , CONFIRM_DATE_OVER_DAYS: ' ngày.'
                  , CONFIRM_DATE_OVER_WEEKS: ' tuần.'
                  , CONFIRM_DATE_OVER_MONTHS: ' tháng.'
                  , CONFIRM_DATE_OVER_QUARTERS: ' quý.'
                  , CONFIRM_DATE_OVER_YEARS: ' năm.'
		  
  };

  return {
    translate: function( phrase)
    {
      return phraseBook[ phrase];
    }
  };
}();
