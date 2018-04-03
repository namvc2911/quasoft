<?php
class Qss_View_Report_PrintToolbar extends Qss_View_Abstract
{
        // $hidden and $disable 
        // html => t/f ($disable/$hidden)
        // excel => t/f ($disable/$hidden)
        // pdf => t/f ($disable/$hidden)
        // pdfdoc => t/f ($disable/$hidden)
        // exceldoc => t/f ($disable/$hidden)
        // close => t/f, alway enable, default hidden false ($hidden)
        // show => t/f, alway enable, default hidden true ($hidden)
        // $hidden default: false
        // $disable default: true (show and close alway enable, not in this array)
        // $disable all: t/f
	public function __doExecute ($hidden = array(), $disabled = array())
	{

            
	}
}

?>