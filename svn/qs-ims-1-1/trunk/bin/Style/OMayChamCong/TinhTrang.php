<?php
//
class Qss_Bin_Style_OMayChamCong_TinhTrang extends Qss_Lib_Style
{
    public function __doExecute()
    {
        if($this->_data->TinhTrang == 1)
        {
            $bg = 'green bold left';
        }
        else 
        {
        	$bg = 'red bold left';
        }
        return $bg;
    }

}
?>