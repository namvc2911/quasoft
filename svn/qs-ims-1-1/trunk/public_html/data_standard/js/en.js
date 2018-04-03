Language = function()
{

  //This keeps phraseBook "private"
  var phraseBook = {
		  CONFIRM_START_GREATER_THAN_END: 'End date must be equal or greater than start date.\n'
                  , CONFIRM_EMPTY: ' is required.\n'
                  , CONFIRM_DATE_OVER: 'Time period is too long.Report will display a limitation of '
                  , CONFIRM_DATE_OVER_DAYS: 'days. '
                  , CONFIRM_DATE_OVER_WEEKS: 'weeks. '
                  , CONFIRM_DATE_OVER_MONTHS: 'months. '
                  , CONFIRM_DATE_OVER_QUARTERS: 'quarters. '
                  , CONFIRM_DATE_OVER_YEARS: 'years. '
  };

  return {
    translate: function( phrase)
    {
      return phraseBook[ phrase];
    }
  };
}();
