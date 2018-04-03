<?php
//
class Qss_Bin_Style_OPhieuBaoTri_LoaiBaoTri extends Qss_Lib_Style
{
	public static $loaibaotri;
    public function __doExecute()
    {
        $bg = '';
        if(!isset(self::$loaibaotri[$this->_data->Ref_LoaiBaoTri]))
        {
        self::$loaibaotri = array();
        	$data = Qss_Model_Db::Table('OPhanLoaiBaoTri')->fetchAll();
        	foreach ($data as $item)
        	{
        		self::$loaibaotri[$item->IOID] = $item; 
        	}
        }
        $loai = @self::$loaibaotri[$this->_data->Ref_LoaiBaoTri];
        if($loai && $loai->LoaiBaoTri == 'B')
        {
            $bg = 'red bold left';
        }
        return $bg;
    }

}
?>