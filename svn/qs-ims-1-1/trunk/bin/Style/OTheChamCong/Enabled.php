<?php
//
class Qss_Bin_Style_OTheChamCong_Enabled extends Qss_Lib_Style
{
    public function __doExecute()
    {
    	$bg = '';
        if(!$this->_data->Enabled)
        {
            $bg = 'bgpink';
        }
        return $bg;
    }

}
?>