/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// *****************************************************************************
// === compare start date and end date, date format is dd-mm-YYYY
// *****************************************************************************
function common_compareStartAndEnd(start, end, errorText)
{
    var msg = '';
    var firstValue = start.split('-');
    var secondValue = end.split('-');
    var firstDate=new Date();
    firstDate.setFullYear(firstValue[2],(firstValue[1] - 1 ),firstValue[0]);
    var secondDate=new Date();
    secondDate.setFullYear(secondValue[2],(secondValue[1] - 1 ),secondValue[0]);  
    
    if(firstDate > secondDate)
    {
        if(errorText)
        {
                msg += errorText;
        }
        else
        {
                msg += Language.translate('CONFIRM_START_GREATER_THAN_END');
        }
    }
    
    return msg;
}

function common_checkEmpty(val, fieldTran)
{
    var msg = '';
    if(val == '')
    {
        msg += '"'+fieldTran+'"' + Language.translate('CONFIRM_EMPTY');
    }
    return msg;
}


// var warning = common_dateWarning(ky, '<?php echo json_encode($this->limit);?>', ngaybd, ngaykt);
function common_dateWarning(period, timeLimit, ngaybd, ngaykt)
{

        limitJson = JSON.parse(timeLimit);
	var message   = '';

	if(period != '')
	{
                var limit;
		var retval;
		var suffix;
		var prefix = Language.translate('CONFIRM_DATE_OVER');
		var startArr  = ngaybd.split("-");
		var endArr    = ngaykt.split("-");
		var start     = new Date( parseInt(startArr[2]), parseInt(startArr[1]) - 1, parseInt(startArr[0]));
		var secondDate  = new Date();
		var secondDateLimit = new Date();
		
		switch(period)
		{
			case 'D':
                                limit = (limitJson['D'] != undefined && !isNaN(limitJson['D']))?limitJson['D']:0;
                                limit = parseInt(limit);
				start.setDate(start.getDate() + limit - 1); 
				suffix = Language.translate('CONFIRM_DATE_OVER_DAYS');
			break;
			case 'W':
                                limit = (limitJson['W'] != undefined && !isNaN(limitJson['W']))?limitJson['W']:0;
                                limit = parseInt(limit);
				start.setDate(start.getDate() + (limit *7 ) -1);
				suffix = Language.translate('CONFIRM_DATE_OVER_WEEKS');
			break;
			case 'M':
                                limit = (limitJson['M'] != undefined && !isNaN(limitJson['M']))?limitJson['M']:0;
                                limit = parseInt(limit);
				start.setMonth(start.getMonth() + limit);
				start.setDate(start.getDate() - 1);
				suffix = Language.translate('CONFIRM_DATE_OVER_MONTHS');
			break;
			case 'Q':
                                limit = (limitJson['Q'] != undefined && !isNaN(limitJson['Q']))?limitJson['Q']:0;
                                limit = parseInt(limit);
				start.setMonth(start.getMonth() + limit);
				start.setDate(start.getDate() - 1);
				suffix = Language.translate('CONFIRM_DATE_OVER_QUARTERS');;
			break;
			case 'Y':
                                limit = (limitJson['Y'] != undefined && !isNaN(limitJson['Y']))?limitJson['Y']:0;
                                limit = parseInt(limit);
				start.setYear(start.getFullYear() + limit); 
				start.setDate(start.getDate() - 1); 
				suffix = Language.translate('CONFIRM_DATE_OVER_YEARS');
			break;								
		}
		secondDate.setFullYear(parseInt(endArr[2]), parseInt(endArr[1]) - 1, parseInt(endArr[0]));
		secondDateLimit.setFullYear(start.getFullYear(), start.getMonth(), start.getDate());

		if( (secondDateLimit < secondDate ) && (limit > 0) )
		{
			message = prefix + ' ' + limit + ' ' + suffix;
		}
	}
	return message;
} 
