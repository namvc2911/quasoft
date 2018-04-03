<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $this->_title?></title>
<link rel="shortcut icon" href="/images/qs-ims.ico"/>
<?php
$this->headLink($this->params->requests->getBasePath() . '/css/template.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.all.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.datepicker.css');
$this->headLink($this->params->requests->getBasePath() . '/css/themes/base/jquery.ui.dialog.css');
$this->headLink($this->params->requests->getBasePath() . "/css/ddsmoothmenu.css");
$this->headLink($this->params->requests->getBasePath() . "/css/layout.css");
echo  $this->headLink($this->params->requests->getBasePath() . "/css/print.css");
echo $this->headStyle();
$arr = array($this->params->requests->getBasePath() . '/js/' . Qss_Translation::getInstance()->getLanguage() . '.js',/* */
$this->params->requests->getBasePath() . '/js/jquery.min.1.8.2.js',/* */
$this->params->requests->getBasePath() . '/js/jquery.cookie.js',/* */
 $this->params->requests->getBasePath() . '/js/ui/jquery-ui-1.8.1.custom.js',/**/
$this->params->requests->getBasePath() . '/js/core-ajax.js',/* */
$this->params->requests->getBasePath() . '/js/index.js',/* */
$this->params->requests->getBasePath() . '/js/print.js',/* */
$this->params->requests->getBasePath() . '/js/hightchart/highcharts.js',/* */
$this->params->requests->getBasePath() . '/js/hightchart/highcharts-more.js',/* */
$this->params->requests->getBasePath() . '/js/hightchart/themes/grid.js',/* */
//$this->params->requests->getBasePath() . '/js/hightchart/modules/exporting.js',
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
<style type="text/css">
#print-content{
	position: relative;
	top: 5px;
	background-color: white;
	border: 1px solid;
	overflow: auto;
	min-height:300px;
	padding-bottom: 20px;
	height: calc(100vh - 80px);
}
</style>
</head>
<body>
<div id='mask'></div>  
<?php
//echo $this->views->Common->Header($this->params->registers->get('userinfo',1));
/* Display the result of the view */
//echo $this->content;
?>
<!--<table height="100%">
	<tr>
		<td valign="top">
		<div id="rightside" class="background">
		<?php //echo $this->content;?>
		</div>
		</td>
	</tr>
</table>-->
<table height="100%" width="100%" style="border-top: 1px solid #aaa;">
	<tr>
		<td valign="top">
                <div id="rightside" class=""> <!-- .background -->
                    <?php echo $this->views->Report->PrintToolbar(array('show'=>true, 'close'=>false)
                                                    , array('all'=>false));?>
                    <div id="print-content">
                            <div id="print-area"><?php echo $this->content;?></div>		
                    </div>
		</div>
		</td>
	</tr>
</table>
<div id="qss_event" title="Tạo nhanh sự kiện"></div>
<div id="qss_alert" title="<?php echo $this->_title?>"></div>
<div id="qss_confirm" title="<?php echo $this->_title?>"></div>
<div id="qss_login" title="User login"></div>
</body>
</html>