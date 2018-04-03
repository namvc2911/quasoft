<?php
class Qss_View_Common_Landscape extends Qss_View_Abstract
{

	public function __doExecute ()
	{
        $this->html->ini = Qss_Lib_System::loadHeaderIniFile();
	}
}
?>