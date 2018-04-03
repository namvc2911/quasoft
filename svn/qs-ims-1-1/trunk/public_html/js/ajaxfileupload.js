/**
 * Upload file by ajax
 * @author FPT.LuuND
 */
jQuery.extend({
    createUploadIframe: function(id, uri)
	{
		/* create frame and append it to body page */
        var frameId = 'jUploadFrame' + id;
        
        /* If ActiveX Object is supported */
        if(window.ActiveXObject) {
            var io = document.createElement('<iframe id="' + frameId + '" name="' + frameId + '" />');
            if(typeof uri== 'boolean'){
                io.src = 'javascript:false';
            } else if(typeof uri== 'string'){
                io.src = uri;
            }
        /* If ActiveX Object is not supported */
        } else {
            var io = document.createElement('iframe');
            io.id = frameId;
            io.name = frameId;
        }
        /* Set style for frame */
        io.style.position = 'absolute';
        io.style.top = '-1000px';
        io.style.left = '-1000px';
        
        /* Append frame to body */
        document.body.appendChild(io);            
        return io;		
    },
    createUploadForm: function(id, fileElementId)
	{
		/* create upload form */	
		var formId = 'jUploadForm' + id;
		var fileId = 'jUploadFile' + id;
		var form = $('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');	
		var oldElement = fileElementId;//$('#' + fileElementId);
		var newElement = $(oldElement).clone();
		$(oldElement).attr('id', fileId);
		$(oldElement).before(newElement);
		$(oldElement).appendTo(form);
		/* set tyle attributes */
		$(form).css('position', 'absolute');
		$(form).css('top', '-1200px');
		$(form).css('left', '-1200px');
		/* Append upload form to body tag */
		$(form).appendTo('body');		
		return form;
    },

    ajaxFileUpload: function(s) {
        /* introduce global settings, allowing the client to modify them for all requests, not only timeout */		
        s = jQuery.extend({}, jQuery.ajaxSettings, s);
        var id = new Date().getTime()      ;  
		var form = jQuery.createUploadForm(id, s.fileElementId);
		var io = jQuery.createUploadIframe(id, s.secureuri);
		var frameId = 'jUploadFrame' + id;
		var formId = 'jUploadForm' + id;		
        /* Watch for a new set of requests */
        if ( s.global && ! jQuery.active++ )
		{
			jQuery.event.trigger( "ajaxStart" );
		}            
        var requestDone = false;
        /* Create the request object */
        var xml = {}  ;
        if ( s.global ) jQuery.event.trigger("ajaxSend", [xml, s]);            
        /* Wait for a response to come back */
        var uploadCallback = function(isTimeout)
		{			
			var io = document.getElementById(frameId);
			/* Get frame content */
            try {				
				if(io.contentWindow) {
					 xml.responseText = io.contentWindow.document.body?io.contentWindow.document.body.innerHTML:null;
                	 xml.responseXML = io.contentWindow.document.XMLDocument?io.contentWindow.document.XMLDocument:io.contentWindow.document;					 
				}else if(io.contentDocument) {
					xml.responseText = io.contentDocument.document.body?io.contentDocument.document.body.innerHTML:null;
                	xml.responseXML = io.contentDocument.document.XMLDocument?io.contentDocument.document.XMLDocument:io.contentDocument.document;
				}						
            } catch( e ) {
            	/* Handle upload error  */
				jQuery.handleError(s, xml, null, e);
			}
            if ( xml || isTimeout == "timeout") 
			{				
                requestDone = true;
                var status;
                try {
                    status = isTimeout != "timeout" ? "success" : "error";
                    /* Make sure that the request was successful or notmodified */
                    if ( status != "error" )
					{
                        /* process the data (runs the xml through httpData regardless of callback) */
                        var data = jQuery.uploadHttpData( xml, s.dataType );    
                        /* If a local callback was specified, fire it and pass it the data */
                        if ( s.success ) s.success( data, status );   
                        /* Fire the global callback */
                        if( s.global ) jQuery.event.trigger( "ajaxSuccess", [xml, s] );                            
                    } else {
                        jQuery.handleError(s, xml, status);
                    }
                } catch(e) {
                	/* Handle upload error */
                    status = "error";
                    jQuery.handleError(s, xml, status, e);
                }

                /* The request was completed */
                if( s.global )
                    jQuery.event.trigger( "ajaxComplete", [xml, s] );

                /* Handle the global AJAX counter */
                if ( s.global && ! --jQuery.active )
                    jQuery.event.trigger( "ajaxStop" );

                /* Process result */
                if ( s.complete )
                    s.complete(xml, status);
                
                /* Clear frame event after uploading - Advoid cache */
                jQuery(io).unbind();
                
                /* Set upload timeout */
                setTimeout(function(){	
                	try	{
                		/* After upload successully, remove the upload form */
						$(io).remove();
						$(form).remove();							
					} catch(e) {
						/* Handle error */
						jQuery.handleError(s, xml, null, e);
					}									
				}, 100);
                xml = null;                
            }
        };
        /* Timeout checker */
        if ( s.timeout > 0 ) {
            setTimeout(function(){
                /* Check to see if the request is still happening */
                if( !requestDone ) uploadCallback( "timeout" );
            }, s.timeout);
        } 
        try {   
        	/* Set upload attribute - url, action, url */
			var form = $('#' + formId);
			$(form).attr('action', s.url);
			$(form).attr('method', 'POST');
			$(form).attr('target', frameId);
            if(form.encoding) {
            	/* Set endcoding attribute for upload form */
                form.encoding = 'multipart/form-data';				
            } else {				
            	/* Set type attribute for upload form */
                form.enctype = 'multipart/form-data';
            }
            /* Submit data */
            $(form).submit();

        } catch(e) {
        	/* Handle error */
            jQuery.handleError(s, xml, null, e);
        }
        /* Attach upload event on page onload */
        if(window.attachEvent){
            document.getElementById(frameId).attachEvent('onload', uploadCallback);
        } else {
            document.getElementById(frameId).addEventListener('load', uploadCallback, false);
        } 		
        return {abort: function () {}};	
    },

    uploadHttpData: function( r, type ) {
        var data = !type;
        data = type == "xml" || data ? r.responseXML : r.responseText;
        /* Execuse javascript text */
        if ( type == "json" ){
            eval( "data = " + data );
        }
        return data;
    }
});

