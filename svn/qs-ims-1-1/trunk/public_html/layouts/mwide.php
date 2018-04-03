<!DOCTYPE html>
<?php 

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1" />
<title><?php echo $this->_title?></title>
<link rel="shortcut icon" href="/images/qs-ims.ico"/>
<?php
$this->headLink($this->params->requests->getBasePath() . '/css/template.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.all.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.datepicker.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.dialog.css');
$this->headLink($this->params->requests->getBasePath() . "/css/layout.css");
$this->headLink($this->params->requests->getBasePath() . "/css/mobile.css");
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
<body style="background: #fff; height: 100%;">
	<div id='mask'></div>
<?php
echo $this->views->Mobile->Header($this->params->registers->get('userinfo',1),$this->_title);
/* Display the result of the view */
//echo $this->content;
?>
	<div class="mobile">
		<div id="pagewrap">
			

			<div id="content">
				<?php echo $this->content;?>
			</div>

			<div id="footer">
				Copyright© by QUASOFT 
			</div>
		</div>

	</div>
<div id="qss_dialog" title="<?php echo $this->_title?>"></div>
<div id="qss_alert" title="<?php echo $this->_title?>"></div>
<div id="qss_notice" title="<?php echo $this->_title?>"></div>
<div id="qss_confirm" title="<?php echo $this->_title?>"></div>
<div id="qss_login" title="User login"></div>
</body>
</html>