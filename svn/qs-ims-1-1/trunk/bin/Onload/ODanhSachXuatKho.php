<?php
class Qss_Bin_Onload_ODanhSachXuatKho extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();
		/*$sql = sprintf('select OLoaiXuatKho.* from OLoaiXuatKho
					inner join OXuatKho on OXuatKho.Ref_LoaiXuatKho = OLoaiXuatKho.IOID    
					where OXuatKho.IFID_M506 = %1$d',$this->_object->i_IFID);
		$dataSQL = $this->_db->fetchOne($sql);
		$loai = '';
		if($dataSQL)
		{
			$loai = $dataSQL->Loai;
		}
		//$this->_object->getFieldByCode('MaThietBi')->bReadOnly = true;
		//$this->_object->getFieldByCode('ViTri')->bReadOnly = true;
		//$this->_object->getFieldByCode('TrangThaiLuuTru')->bReadOnly = false;
        
        if($this->_object->intStatus == 1)
        {
            switch ($loai)
            {
                case 'XUATMOI':
                case 'THAYTHE':
                    $this->_object->getFieldByCode('MaThietBi')->bReadOnly = false;
                    $this->_object->getFieldByCode('ViTri')->bReadOnly = false;
                    break;
                default:
                    break;
            }
        }*/
	}
	
}