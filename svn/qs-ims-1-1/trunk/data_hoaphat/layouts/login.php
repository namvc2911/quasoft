<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="/images/qs-ims.ico"/>
<title>QS-IMS Login</title>
<?php
echo $this->headLink($this->params->requests->getBasePath() . '/css/login_page.css');
?>
</head>
<script	type="text/javascript" src="/js/<?php echo Qss_Translation::getInstance()->getLanguage()?>.js"></script>
<body>

		<?php echo $this->content;?>


</body>
</html>