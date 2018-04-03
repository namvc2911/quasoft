var qssAjax = {
	/**
	 * Request a server to execute a function (save, modify, validate data, etc)
	 * The return data is in JSON format and structured
	 */
	call : function (url, data, sFunction, fFunction,async) {
		// Show the waiting symbol
		//jQuery('#loading').removeClass('hide');
		disabledLayout();
		/*$('.btn-custom').attr('disabled',true);*/
		qssAjax.disableButton();
		data.path = url;
		if(data.length != undefined){
			/* Is array */
			data[data.length] = { name: 'datatype', value : 'JSON'};
		} else {
			/* Is object */
			data.datatype = 'JSON';
		}
		if(async == undefined){
			async = true;
		}
		return jQuery.ajax(
			{
				type: 'POST',
				url: url,
				dataType: "json",
				data: data,
				async : async,
				success: function(jreturn){
					// Redirect
					if(jreturn.redirect != null) {
						qssAjax.redirect(jreturn.redirect);
					} 
					else {
						// If success, check the return code
						switch(jreturn.error){
							default:
							case false:
									// Action is success, execute the callback
									// sucess function
									if(sFunction){
										sFunction(jreturn);
									}
								break;
							case true:
									// Action is error, execute the callback
									// error
									if(fFunction){
										fFunction(jreturn);
									}
								break;
						}
					}
					// Hide the waiting symbol
					//jQuery('#loading').addClass('hide');
					//enabledLayout();
					/*$('.btn-custom').removeAttr('disabled');*/
					qssAjax.enableButton();
				},
				error: function() {
					// In case of system error or network error
					qssAjax.alert(Language.translate('SYSTEM_ERROR'));
					//jQuery('#loading').addClass('hide');
					//enabledLayout();
					/*$('.btn-custom').removeAttr('disabled');*/
					qssAjax.enableButton();
				}
			});
	},
	silient : function (url, data, sFunction, fFunction) {
		// Show the waiting symbol
		data.path = url;
		if(data.length != undefined){
			/* Is array */
			data[data.length] = { name: 'datatype', value : 'JSON'};
		} else {
			/* Is object */
			data.datatype = 'JSON';
		}
		return jQuery.ajax(
			{
				type: 'POST',
				url: url,
				dataType: "json",
				data: data,
				success: function(jreturn){
					// Redirect
					if(jreturn.redirect != null) {
						qssAjax.redirect(jreturn.redirect);
					} 
					else {
						// If success, check the return code
						switch(jreturn.error){
							default:
							case false:
									// Action is success, execute the callback
									// sucess function
									if(sFunction){
										sFunction(jreturn);
									}
								break;
							case true:
									// Action is error, execute the callback
									// error
									if(fFunction){
										fFunction(jreturn);
									}
								break;
						}
					}
				},
				error: function() {
					// In case of system error or network error
				}
			});
	},
	/**
	 * Get HTML data using ajax
	 */
	getHtml : function (url, data, sFunction,fFunction) {
		//jQuery('#loading').removeClass('hide');
		disabledLayout();
		/*$('.btn-custom').attr('disabled',true);*/
		data.path = url;
		if(data.length != undefined){
			/* Is array */
			data[data.length] = { name: 'datatype', value : 'HTML'};
		} else {
			data.datatype = 'HTML';
		}
		return jQuery.ajax(
			{
				type: 'POST',
				url: url,
				dataType: "html",
				data: data,
				success: function(jreturn){
					try {
						json = jQuery.parseJSON(jreturn);
						// Redirect
						if(json.redirect != null) {
							qssAjax.redirect(json.redirect);
						} 
						else if(fFunction){
							fFunction(json);
						}
					} 
					catch (e) {
						// not json
						// In case of success, execute callback function
						if (sFunction){
							sFunction(jreturn);
						}
					}
					// Reload common js to re-inintialize the javascript effect
					//jQuery('#loading').addClass('hide');
					//enabledLayout();
					/*$('.btn-custom').removeAttr('disabled');*/
				},
				error: function() {
					// In case of system error or network error
					qssAjax.alert(Language.translate('SYSTEM_ERROR'));
					//jQuery('#loading').addClass('hide');
					//enabledLayout();
					/*$('.btn-custom').removeAttr('disabled');*/
				}
			});
	},

	/**
	 * Get JSON data using ajax The same function like v_fGetHtml but in JSON
	 * format
	 */
	getJson : function (url, data, sFunction) {
		//jQuery('#loading').removeClass('hide');
		disabledLayout();
		data.path = url;
		if(data.length != undefined){
			/* Is array */
			data[data.length] = { name: 'datatype', value : 'JSON'};
		} else {
			/* Is object */
			data.datatype = 'JSON';
		}
		return jQuery.ajax(
			{
				type: 'POST',
				url: url,
				// Set datatype is Json
				dataType: "json",
				data: data,
				success: function(jreturn){
					// Redirect
					if(jreturn.redirect != null) {
						qssAjax.redirect(jreturn.redirect);
					} 
					else {
						if (sFunction){
							sFunction(jreturn);
						}
						//jQuery('#loading').addClass('hide');
						//enabledLayout();
					}
				},
				error: function() {
					// In case of system error or network error
					qssAjax.alert(Language.translate('SYSTEM_ERROR'));
					//jQuery('#loading').addClass('hide');
					//enabledLayout();
				}
			});
	},
	disableButton : function (){
		$('.btn_main_top').each(function(){
	        if(!$(this).hasClass('btn_disabled')){
	        	$(this).addClass('btn_disabled');
	        	$(this).addClass('called');
	        }
	     });
	},
	enableButton : function (){
		$('.btn_main_top').each(function(){
	        if($(this).hasClass('called')){
	        	$(this).removeClass('btn_disabled');
	        	$(this).removeClass('called');
	        }
	     });
	},
	alert : function (message,sFunction) {
		$("#qss_alert" ).html(message);
		var buttonsOpts = {};
		buttonsOpts[Language.translate('DIALOG_CLOSE')] = function() {
			 $( this ).dialog( "close" );
		};
		$( "#qss_alert" ).dialog({
			resizable: false,
			 modal: true,
			 width:350,
			 minHeight:150,
			 buttons:buttonsOpts,
			 open: function() {
				 $(this).parent().find('.ui-dialog-buttonpane button:eq(0)').focus(); 
			 },
			 close:sFunction
		});
	},
	notice : function (message,sFunction) {
		$("#qss_notice" ).html(message);
		var buttonsOpts = {};
		buttonsOpts[Language.translate('DIALOG_CLOSE')] = function() {
			 $( this ).dialog( "close" );
		};
		$( "#qss_notice" ).dialog({
			resizable: false,
			 modal: true,
			 width:350,
			 minHeight:150,
			 buttons:buttonsOpts,
			 open: function() {
				 $(this).parent().find('.ui-dialog-buttonpane button:eq(0)').focus(); 
			 },
			 close:sFunction
		});
	},
	confirm : function (message,sFunction, fFunction) {
		$("#qss_confirm" ).html(message);
		var buttonsOpts = {};
		buttonsOpts[Language.translate('CONFIRM_CANCEL')] = function() {
			 $( this ).dialog( "close" );
			 if(fFunction){
				fFunction();
			 }
		};
		buttonsOpts[Language.translate('CONFIRM_OK')] = function() {
			 $( this ).dialog( "close" );
			 if(sFunction){
				sFunction();
			}
		};
		$("#qss_confirm" ).dialog({
			resizable: false,
			 modal: true,
			 width:350,
			 minHeight:150,
			 buttons:buttonsOpts,
			 open: function() {
				 $(this).parent().find('.ui-dialog-buttonpane button:eq(0)').focus(); 
			 }
		});
	},
	logs : function (message,sFunction, fFunction) {
		message = message + '<br><textarea id="qss_logs" style="width:90%"></textarea>';
		$("#qss_confirm" ).html(message);
		var buttonsOpts = {};
		buttonsOpts[Language.translate('CONFIRM_CANCEL')] = function() {
			 $( this ).dialog( "close" );
			 if(fFunction){
				fFunction();
			 }
		};
		buttonsOpts[Language.translate('CONFIRM_OK')] = function() {
			 $( this ).dialog( "close" );
			 if(sFunction){
				sFunction($('#qss_logs').val());
			}
		};
		$("#qss_confirm" ).dialog({
			resizable: false,
			 modal: true,
			 width:350,
			 minHeight:150,
			 buttons:buttonsOpts,
			 open: function() {
				 $(this).parent().find('.ui-dialog-buttonpane button:eq(0)').focus(); 
			 }
		});
	},
	nl2br: function(text){
	    return text.replace(/(\r\n|\n\r|\r|\n)/g, "<br>");
	},
	br2nl: function(text){
	    return text.replace(/<br>/g, "\r");
	},
	redirect: function(url){
		if(url == '/user/index/loginform?err=ERR_SESSION' || url == '/user/index/loginform'){
			url = '/user/index/loginajax';
			qssAjax.getHtml(url, {}, function(jreturn){
				$('#qss_login').html(jreturn);
				$('#qss_login').dialog({ width: 350,height:250 });
			}, function(){
				
			})
		}
		else{
			window.location.href = url;
		}
		
	}
};
