<!DOCTYPE html>
<?php 

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1" />
<title><?php echo $this->_title?></title>
<link rel="shortcut icon" href="/images/qs-ims.ico"/>
<?php $option = Qss_Register::get('configs');
$chat = isset($option->php->chat) && $option->php->chat?>
<?php
$this->headLink($this->params->requests->getBasePath() . '/css/template.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.all.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.datepicker.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.dialog.css');
$this->headLink($this->params->requests->getBasePath() . "/css/ddsmoothmenu.css");
$this->headLink($this->params->requests->getBasePath() . "/css/layout.css");
$this->headLink($this->params->requests->getBasePath() . "/css/dashboad.css");
if($chat)
{
	$this->headLink($this->params->requests->getBasePath() . "/css/chatbox.css");
}
echo  $this->headLink($this->params->requests->getBasePath() . "/css/form.css");

echo $this->headStyle();
$arr = array($this->params->requests->getBasePath() . '/js/' . Qss_Translation::getInstance()->getLanguage() . '.js',/* */
$this->params->requests->getBasePath() . '/js/jquery.min.1.8.2.js',/* */
$this->params->requests->getBasePath() . '/js/jquery.cookie.js',/* */
$this->params->requests->getBasePath() . '/js/ui/jquery-ui-1.8.1.custom.js',
$this->params->requests->getBasePath() . '/js/core-ajax.js',/* */
$this->params->requests->getBasePath() . '/js/index.js',/* */
$this->params->requests->getBasePath() . '/js/jquery.tablescroll.js',/* */
$this->params->requests->getBasePath() . '/js/jquery.mask.number.js',/* cho các loại tiền*/
$this->params->requests->getBasePath() . '/js/tag.js');/* */
if($chat)
{
	$arr[] = $this->params->requests->getBasePath() . '/js/chat.js';
}
echo $this->headScript($arr,true);
?>
<script type="text/javascript">
$(document).ready(function() {
	$('#none').datepicker();
	$("#ui-datepicker-div").wrap('<div style="position:absolute;top:0px;"></div>')
							.hide();
});
</script>
</head>
<body>
<div id='mask'></div>  
<?php
$userinfo = $this->params->registers->get('userinfo',1);
/* Display the result of the view */
//echo $this->content;
?>
<table height="100%" style="border-top: 1px solid #aaa; width: 100%; table-layout: fixed;">
	<tr>
		<td valign="top">
		<div id="rightside" class="background">
		<?php echo $this->content;?>
		</div>
		</td>
	</tr>
</table>
<div id="qss_dialog" title="<?php echo $this->_title?>"></div>
<div id="qss_alert" title="<?php echo $this->_title?>"></div>
<div id="qss_notice" title="<?php echo $this->_title?>"></div>
<div id="qss_confirm" title="<?php echo $this->_title?>"></div>
<?php if($chat):?>
	<div style="display: none" id="data-common" data-base-url="<?php echo QSS_BASE_URL?>"
             data-current-user-id="<?php echo $userinfo->user_id ?>"
             data-current-user-name="<?php echo $userinfo->user_name?>"></div>
	<div class="pull-right" style="position: fixed;bottom:0;right:20px;">
		<div id="divChatList" class="scroller" style="height: 200px;background:white;display: none;">
        
        </div>
		<div id="divBlink" class="chat-box blink_me container-fluid">
				<div class="pull-left">
					<i class="fa fa-users"></i>
                        Cửa sổ chat
				</div>
				<div class="pull-right">
					<i class="fa fa-comment-o"></i>
                        Online (<span id="spanOnline"></span>)
				</div>
			
		</div>
	</div>
	
	<div id="qss_chat" title="<?php echo $userinfo->user_name?>">
	 	<div class="modal-body" id="divModalBody">
			<div class="scroller" id="divScrollerChat" style="height:400px"
	        	data-always-visible="1" data-rail-visible="1" data-rail-color="blue" data-handle-color="red">
				<div class="row">
					<div id="divContentChat" class="col-md-12">
					
					</div>
				</div>
			</div>
		</div>
	    <div class="">
	    	<form id="frmChat">
	        	<input type="hidden" value="" name="sender" id="txtSender"/>
	            <input type="hidden" value="" name="receiver" id="txtReceiver"/>
	            <input type="hidden" value="" name="status" id="txtStatus"/>
	            <div class="chat-form">
		            <input id="txtChatText" class="text-chat" type="text" name="title" tabindex="-1"
		                                            placeholder="Nhập tin nhắn..."/>
					<button type="button" id="ahrefEnter" class="btn" tabindex="-1">Gửi</button>
				</div>
	        </form>
		</div>
	</div>
<?php endif;?>               
</body>
</html>