<?php
class Qss_Bin_Onload_ODanhSachNhapKho extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();
		/*$sql = sprintf('select OLoaiNhapKho.* from OLoaiNhapKho
					inner join ONhapKho on ONhapKho.Ref_LoaiNhapKho = OLoaiNhapKho.IOID    
					where ONhapKho.IFID_M402 = %1$d',$this->_object->i_IFID);
		$dataSQL = $this->_db->fetchOne($sql);
		$loai = '';
		if($dataSQL)
		{
			$loai = $dataSQL->Loai;
		}
		$this->_object->getFieldByCode('MaThietBi')->bReadOnly = true;
		$this->_object->getFieldByCode('ViTri')->bReadOnly = true;
		$this->_object->getFieldByCode('SoLuongMat')->bReadOnly = true;
        
        if($this->_object->intStatus == 1)
        {
            switch ($loai)
            {
                case 'BAOTRI':
                    $this->_object->getFieldByCode('MaThietBi')->bReadOnly = false;
                    $this->_object->getFieldByCode('ViTri')->bReadOnly = false;
                    $this->_object->getFieldByCode('SoLuongMat')->bReadOnly = false;
                    break;
                default:
                    break;
            }
        }*/
	}
	
}