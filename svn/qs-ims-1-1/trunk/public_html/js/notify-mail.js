function editNotify(fid)
{
    var url = sz_BaseUrl + '/notify/mail/edit?action=1&fid=' + fid;
    qssAjax.getHtml(url, {}, function(jreturn) {
            if(jreturn!=''){
                    $('#qss_trace').html(jreturn);
                    $('#qss_trace').dialog({ width: 800,height:400 });	
            }
    });
}

function reloadNotify()
{
    $('.btn-custom').attr('disabled', true);
    window.location.reload();   
    $('#qss_trace').dialog('close');
}


function deleteNotify(fid)
{
	var url = sz_BaseUrl + '/notify/mail/delete';
	var data = {FID:fid,ifid:0};
	qssAjax.confirm(Language.translate('CONFIRM_DELETE_NOTIFY'),function(){
		qssAjax.call(url, data, function(jreturn) {
			//reloadNotify();
			$('#qss_trace').dialog('close');
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	}); 
}


function saveNotify()
{
	var url = sz_BaseUrl + '/notify/mail/save';
	var data = $('#qss_form').serialize();
        var countTime = $('.times').length;
        
        //if(countTime )
        {
            qssAjax.call(url, data, function(jreturn) {
                    //reloadNotify();
            	$('#qss_trace').dialog('close');
            }, function(jreturn) {
                    qssAjax.alert(jreturn.message);
            });
        }
        /*else
        {
            qssAjax.alert(Language.translate('ALERT_ADD_NOTIFY_EMPTY_TIME'));
        }*/
        

}

function runNotify(fid) {
	qssAjax.confirm(Language.translate('CONFIRM_PROCESS'),function(){
		var url = sz_BaseUrl + '/notify/mail/run';
		var data = {fid:fid};
		qssAjax.call(url, data, function(jreturn) {
			
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	}); 
}