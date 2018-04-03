var chat = function () {
    var baseURL = $("#data-common").data("base-url");
    var currentUserId = $("#data-common").attr("data-current-user-id");
    var currentUserName = $("#data-common").data("current-user-name");
    var websocket = null;
    var receiverIdGlobal = '';
    var receiverName = '';
    var globalInterval = 0;

    var handleChatWindow = function () {
        $(".chat-box").click(function () {
            $("#divChatList").toggle();
            getShowStatusOfChatWindow();
            //window.scrollTo(0, 10000);
        });

    }

    var handleShowHideChatWindow = function () {
        var currentStatus = localStorage.getItem("divChatListStatus");
        if (typeof (currentStatus) !== "undefined") {
            if(currentStatus=='show'){
                $("#divChatList").show();
            }else{
                $("#divChatList").hide();
            }
        }
        else{
            $("#divChatList").hide();
        }
    }

    var getShowStatusOfChatWindow = function () {
        if ($('#divChatList').css('display') == 'none') {
            localStorage.setItem("divChatListStatus", "hide");
        } else {
            localStorage.setItem("divChatListStatus", "show");
        }
    }

    var twinlinkUserChat = function (userId) {
        if ($("#qss_chat").dialog('isOpen')!==true) {
            $('#divForChatUser' + userId).addClass("bgpink");
            $('#divBlink').addClass('blink_me');
            globalInterval = setInterval(blinker, 1000); //Runs every second
        }
    }
    var blinker = function () {
        $('.blink_me').fadeOut(500);
        $('.blink_me').fadeIn(500);
    }

    var stopTwinlinkUserChat = function (userId) {

        /*jQuery('#divForChatUser' + userId).pulsate({
            color: "#bf1c56",
            repeat: false
        });*/
        $('#divForChatUser' + userId).removeClass("bgpink");
        $('#divBlink').removeClass('blink_me');
        clearInterval(globalInterval);
    }
    var handleClickOnUserChat = function () {
        $(".rowchat").live("click", function () {
        	var userId = $(this).data("messageid");
        	
            receiverIdGlobal = userId;
            receiverName = $(this).data("receiver-name");
            $('.modal-title').html(receiverName);
            $('#txtSender').val(currentUserId);
            $('#txtReceiver').val(receiverIdGlobal);
            $('#txtStatus').val('2');// by default status is sent
            displayConversationForFirstLoad(currentUserId, receiverIdGlobal);
            //$('#responsive').find('.scroller') @todo cuộn xuống dưới
            $('#qss_chat').dialog({width:400,height:500,close: function(event, ui) {
            		updateChat(receiverIdGlobal);
		       	}
            });
            stopTwinlinkUserChat(receiverIdGlobal);

        });
    }
    //display message of receiver
    var updateMessage = function (cmd) {
        updateConversation(cmd.message, receiverIdGlobal, receiverName);
        twinlinkUserChat(cmd.sender);
    }

    var handleEnterKey = function () {
        $('#txtChatText').keypress(function (e) {
            if (e.which == 13) {
                if (receiverIdGlobal != '' && currentUserId != receiverIdGlobal) {
                    e.preventDefault();
                    var text = $('#txtChatText').val();
                    if (text != '') {
                        updateConversation(text, currentUserId, currentUserName);
                        var request = {command: 'send', userid: receiverIdGlobal, message: text, sender: currentUserId};
                        websocket.send(JSON.stringify(request));
                        $('#txtChatText').val('');
                        stopTwinlinkUserChat(receiverIdGlobal);

                    }
                }

                //$('#responsive').find('.scroller')@todo cuộn xuống d
                return false; //<---- Add this line
            }
        });
    }

    var callSocketToGetListChatHistory = function (sender, receiver) {
        var request = {command: 'list', receiver: receiver, sender: sender};
        websocket.send(JSON.stringify(request));
    }

    var displayConversationForFirstLoad = function (sender, receiver) {
        callSocketToGetListChatHistory(sender, receiver);


    }

    /*var callSocketToGetListUserChat = function () {
     
     var request = {command: 'displayUserChat'};
     websocket.send(JSON.stringify(request));
     
     }*/

    var ajaxDisplayChatHistory = function (myData) {
        var getConversation = $.ajax({
            type: "POST",
            async: false,
            url: baseURL + "/chat/chat/ajaxGetConversationForFirstLoad",
            data: {myData: myData}

        });
        getConversation.done(function (data) {
            //var returnAjax = jQuery.parseJSON(data);
            $('#divContentChat').html(data);
            $('#divScrollerChat').scrollTop($('#divScrollerChat')[0].scrollHeight);
        });
        getConversation.fail(function (jqXHR, textStatus) {
            return false;
        });
    }



    var updateChat = function (id) {
        var saveNewChat = $.ajax({
            type: "POST",
            url: baseURL + "/chat/chat/update",
            data: {receiver:id}

        });
        saveNewChat.done(function (data) {
            var returnAjax = jQuery.parseJSON(data);

        });
        saveNewChat.fail(function (jqXHR, textStatus) {
            return false;
        });
    }

    var updateConversation = function (text, userIdForImg, Name) {
        var className = getClassToDisplayChatMessage(userIdForImg);
        var time = new Date();
        var time_str = (time.getHours() + ':' + time.getMinutes());
        var imgUrl = '';//baseURL + '/user/picture/show/?id=' + userIdForImg;
        var tpl = '<ul class="chats">';
        tpl += '<li class="' + className + '">';
        //tpl += '<img class="avatar" alt="" src="' + imgUrl + '"/>';
        tpl += '<div class="message">';
        tpl += '<span class="arrow"></span>';
        tpl += '<a href="#" class="name">' + Name + '</a>&nbsp;';
        tpl += '<span class="datetime">Lúc ' + time_str + '</span>';
        tpl += '<span class="body">';
        tpl += text;
        tpl += '</span>';
        tpl += '</div>';
        tpl += '</li>';
        tpl += '</ul>';
        $('#divContentChat').append(tpl);
        //$('#divScrollerChat').scrollTop($('#divScrollerChat')[0].scrollHeight);
    }

    var handleClickOnSend = function () {
        $("#ahrefEnter").click(function () {
            if (receiverIdGlobal != '' && currentUserId != receiverIdGlobal) {
                var text = $('#txtChatText').val();
                if (text != '') {
                    updateConversation(text, currentUserId, currentUserName);
                    var request = {command: 'send', userid: receiverIdGlobal, message: text, sender: currentUserId};
                    websocket.send(JSON.stringify(request));
                    $('#txtChatText').val('');
                }
            }
        });
    }
    var getLastPostPos = function () {

        var height = 0;
        $("#ulChats li").each(function (index, value) {
            height += 300;

        });
        return height;
    }
    //return "in" or "out"
    var getClassToDisplayChatMessage = function (fromUserId) {
        var lastLi = $("#ulChats li:last-child");
        //console.log(lastLi);
        var userId = lastLi.data('chat-user-id');
        var classDisplay = lastLi.data('class-display');
        if (fromUserId == userId) {
            return classDisplay;
        } else {
            if (classDisplay == 'out') {
                return 'in';
            } else {
                return 'out';
            }
        }
    }

    var drawChatWindow = function (dataFromSocket) {

        var listChats = dataFromSocket.data;
        var html = '';
        var online = 0;
        var unread = false;
        $.each(listChats, function (key, value) {
        	if (currentUserId != value.UID) {
	        	if(value.unread == 1){
	        		unread = true;
	        	}
	            var classOl = 'offline';
	            if (typeof value.online != "undefined" && value.online == 1) {
	                online++;
	                classOl = 'online';
	            }
	            
	            html += '<div class="row rowchat" data-receiver-name="' + value.UserName + '" data-messageid="' + value.UID + '" data-toggle="modal" href="#responsive">';
	            if(value.unread == 1){	
	            	html += '<div class="col-md-12 ' + classOl + ' bgpink" id="divForChatUser' + value.UID + '">';
	            }
	            else{
	            	html += '<div class="col-md-12 ' + classOl + '" id="divForChatUser' + value.UID + '">';
	            }
	            html += value.UserName;
	            if (typeof value.online != "undefined" && value.online == 1) {
	                html += '<i class="fa fa-smile-o pull-right"></i>';
	            }
	            html += '</div>';
	            html += '</div>';
            }

        });
        $('#divChatList').html(html);
        $('#spanOnline').html(online);
        if(unread){
        	twinlinkUserChat(receiverIdGlobal);	
        }
    }

    var sz_WSUrl = 'ws://' + location.hostname + ':9300/server.php';
    var handleConnection = function () {
        try {
            websocket = new WebSocket(sz_WSUrl);
            //Connected to server
            websocket.onopen = function (ev) {
                //readyChat();
                //callSocketToGetListUserChat();
            }

            //Connection close
            websocket.onclose = function (ev) {
                //disableChat();
            };

            //Message Received
            websocket.onmessage = function (ev) {
                var dataFromSocket = JSON.parse(ev.data);
                //console.log(dataFromSocket.command);

                switch (dataFromSocket.command) {
                    case 'list':
                        ajaxDisplayChatHistory(dataFromSocket.data);
                        //$('#responsive').find('.scroller')@todo 
                        break;
                    case 'send':
                        updateMessage(dataFromSocket);
                        break;
                    case 'displayUserChat':
                    	drawChatWindow(dataFromSocket);
                        break;
                    default:

                }

            };

            //Error
            websocket.onerror = function (ev) {
                //disableChat();
            };
        }
        catch (e) {
            //disableChat();
        }

    }
    return {
        //main function to initiate the module
        init: function () {
        	baseURL = $("#data-common").data("base-url");
            currentUserId = $("#data-common").data("current-user-id");
            currentUserName = $("#data-common").data("current-user-name");

            handleChatWindow();
            handleClickOnUserChat();
            handleEnterKey();
            handleConnection();
            handleClickOnSend();
            stopTwinlinkUserChat();
            handleShowHideChatWindow();

        }
    };
}();

$(document).ready(function () {
    chat.init();
});