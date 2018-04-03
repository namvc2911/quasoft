<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
<title><?php echo $this->_title?></title>
<link rel="shortcut icon" href="/images/qs-ims.ico"/>
<?php
$this->headLink($this->params->requests->getBasePath() . '/css/template.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.all.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.datepicker.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.dialog.css');
//$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery-ui.css');
//$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery-ui.structure.css');
//$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery-ui.theme.css');
$this->headLink($this->params->requests->getBasePath() . "/css/layout.css");
echo  $this->headLink($this->params->requests->getBasePath() . "/css/form.css");

echo $this->headStyle();
$arr = array($this->params->requests->getBasePath() . '/js/' . Qss_Translation::getInstance()->getLanguage() . '.js',/* */
$this->params->requests->getBasePath() . '/js/jquery.min.1.8.2.js',/* */
/*$this->params->requests->getBasePath() . '/js/jquery-1.4.2.js', */
$this->params->requests->getBasePath() . '/js/jquery.cookie.js',/* */
 $this->params->requests->getBasePath() . '/js/ui/jquery-ui-1.8.1.custom.js',/**/
$this->params->requests->getBasePath() . '/js/core-ajax.js',/* */
$this->params->requests->getBasePath() . '/js/index.js',/* */
$this->params->requests->getBasePath() . '/js/jquery.tablescroll.js',/* */
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
<body>
<div id='mask'></div>  
<?php
echo $this->views->Common->Header($this->params->registers->get('userinfo',1));
/* Display the result of the view */
//echo $this->content;
?>
<?php echo $this->content;?>
<div id="qss_event" title="Tạo nhanh sự kiện"></div>
<div id="qss_alert" title="<?php echo $this->_title?>"></div>
<div id="qss_confirm" title="<?php echo $this->_title?>"></div>
</body>
</html>