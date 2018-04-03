<?php
class Qss_Bin_Onload_ODanhSachThietBi extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();
	}

	public function HangBaoHanh()
    {
        $this->_object->getFieldByCode('HangBaoHanh')->arrFilters[] = sprintf('
            v.IOID in
            (
                SELECT IOID
                FROM ODoiTac
                WHERE IFNULL(NhaCungCapDichVu, 0) = 1
            )'
        );
    }

    public function NhaCungCapThietBi()
    {
        $this->_object->getFieldByCode('NhaCungCapThietBi')->arrFilters[] = sprintf('
            v.IOID in
            (
                SELECT IOID
                FROM ODoiTac
                WHERE IFNULL(NhaCungCapThietBi, 0) = 1
            )'
        );
    }
}