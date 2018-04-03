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
echo  $this->headLink($this->params->requests->getBasePath() . "/css/print.css");
echo $this->headStyle();
$arr = array($this->params->requests->getBasePath() . '/js/' . Qss_Translation::getInstance()->getLanguage() . '.js',/* */
$this->params->requests->getBasePath() . '/js/' . 'common.js',
$this->params->requests->getBasePath() . '/js/jquery.min.1.8.2.js',/* */
/*$this->params->requests->getBasePath() . '/js/jquery-1.4.2.js', */
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
function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    if(window.location.href.indexOf('?') != -1){
	    for(var i = 0; i < hashes.length; i++){
	        hash = hashes[i].split('=');
	        vars.push(hash[0]);
	        vars[hash[0]] = hash[1];
	    }
    }
    return vars;
}
$(document).ready(function() {
	$('#none').datepicker();
	$("#ui-datepicker-div").wrap('<div style="position:absolute;top:0px;"></div>')
							.hide();
	fedit = false;
	var lastActiveModule = $.cookie('lastActiveModule');
	var params =  getUrlVars();
	if(params.length > 0){
		$(params).map(function(){
			$('#'+this).val(params[this]);	
		});
		fedit = true;
		printPreview();
		var code = $.cookie('lastActiveModule');
		var url = window.location.href.split('?')[0];
		$.cookie(code,url,{path:'/'});
	}
	else if(bLS){
		var data = localStorage.getItem(lastActiveModule);
		if(data != null){
			data = JSON.parse(data);
			$.each(data, function() {
				if(this.name == 'print-area'){
					$('#'+this.name).html(this.value);
				}
				else{
					if($('[name="'+this.name+'"]').is(':checkbox')){
						$('[name="'+this.name+'"]').prop('checked',this.value);
					}
					else if($('[name="'+this.name+'"]').is(':radio')){
						$('[name="'+this.name+'"][value="'+this.value+'"]').prop('checked',true);
					}
					else if($('[name="'+this.name+'"]').is('select[multiple]')){
						var ele_name = this.name.replace(/(:|\.|\[|\])/g,'');
						var ele = $('#select_'+ele_name+'_box select option[value="' + this.value + '"]');
						$('#'+ele_name+'_box select').append(ele);
					}
					else{
						$('[name="'+this.name+'"]').val(this.value);
					}
				}
				
			});
			removePrintToolbarDisabled();
		}
	}
});
$( document ).ajaxStop(function() {
	if(!fedit){
		var lastActiveModule = $.cookie('lastActiveModule');
		if(bLS){
			var data = localStorage.getItem(lastActiveModule);
			if(data != null){
				data = JSON.parse(data);
				$.each(data, function() {
					if(this.name == 'print-area'){
						$('#'+this.name).html(this.value);
					}
					else{
						if($('[name="'+this.name+'"]').is(':checkbox')){
							$('[name="'+this.name+'"]').prop('checked',this.value);
						}
						else if($('[name="'+this.name+'"]').is(':radio')){
							$('[name="'+this.name+'"][value="'+this.value+'"]').prop('checked',true);
						}
						else if($('[name="'+this.name+'"]').is('select[multiple]')){
							var ele_name = this.name.replace(/(:|\.|\[|\])/g,'');
							var ele = $('#select_'+ele_name+'_box select option[value="' + this.value + '"]');
							$('#'+ele_name+'_box select').append(ele);
						}
						else{
							$('[name="'+this.name+'"]').val(this.value);
						}
					}
					
				});
			}
		}
		fedit = true;
	}
});
$('#pt-showreport').click(function(){
	fedit = true;
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
<table height="100%">
	<tr>
		<td valign="top">
		<div id="rightside" class="background">
		<?php echo $this->content;?>
		</div>
		</td>
	</tr>
</table>
<div id="qss_event" title="Tạo nhanh sự kiện"></div>
<div id="qss_alert" title="<?php echo $this->_title?>"></div>
<div id="qss_confirm" title="<?php echo $this->_title?>"></div>
</body>
</html>