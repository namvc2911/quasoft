<?php
class Qss_View_Common_Portrait extends Qss_View_Abstract
{

	public function __doExecute ($code='',$date='')
	{
        $this->html->ini = Qss_Lib_System::loadHeaderIniFile();
	}

}
?>