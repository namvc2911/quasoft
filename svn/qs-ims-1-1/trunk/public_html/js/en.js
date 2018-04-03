Language = function()
{

  //This keeps phraseBook "private"
  var phraseBook = {
		  DIALOG_CLOSE: 'Close',
		  CONFIRM_OK: 'OK',
		  CONFIRM_CANCEL: 'Cancel',
		  PROFILE_CHANGED: 'Profile was changed.',
		  CONFIRM_DELETE: 'Are you sure to delete the record?',
		  SELECT_ROW: 'Please select one row',
		  SYSTEM_ERROR : 'System error, please contact to administrator.',
		  CONFIRM_DELETE_TEMPLATE: 'Are you sure to delete the template file?',
		  SELECT_ACTION: 'Please select action.',
		  ACTION_DONE: 'You did: ',
		  SELECT_IMPORT_MODULE: 'Please select module to import.',
		  SELECT_TEMPLATE_MODULE: 'Please select module to download.',
		  CONFIRM_SAVE: 'You data have not been saved, do you want to leave the page?',
		  SELECT_REFER_OBJECT: 'Please selecte reference object.',
		  DOCUMENT_SAVE: 'The document was saved. <a href="#" onclick="openModule(\'M016\')">Go to document management</a>',
		  FUNCTION_NOT_SUPPORT: 'This function was not support.',
		  CONFIRM_PROCESS: 'Do you want to run process immediately?',
		  SAVE_FIRST: 'You must save data first',
		  CONFIRM_DELETE_NOTIFY: 'Are you sure to delete the calendar?',
          ALERT_ADD_NOTIFY_EMPTY_TIME: 'You must insert at least one time',
          SEARCH_PLACEHOLDER: 'Input keyword & enter'
                  , CONFIRM_START_GREATER_THAN_END: 'End date must be equal or greater than start date.\n'
				  , CONFIRM_GREATER_OR_EQUAL: '  must be equal or greater than '
                  , CONFIRM_EMPTY: ' is required.\n'
                  , CONFIRM_DATE_OVER: 'Time period is too long.Report will display a limitation of '
                  , CONFIRM_DATE_OVER_DAYS: 'days. '
                  , CONFIRM_DATE_OVER_WEEKS: 'weeks. '
                  , CONFIRM_DATE_OVER_MONTHS: 'months. '
                  , CONFIRM_DATE_OVER_QUARTERS: 'quarters. '
                  , CONFIRM_DATE_OVER_YEARS: 'years. '
                  , CONFIRM_LOGS: 'Enter note'
  };

  return {
    translate: function( phrase)
    {
      return phraseBook[ phrase];
    }
  };
}();
