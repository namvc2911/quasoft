<?php
class Qss_Bin_Onload_ODanhSachNhanVien extends Qss_Lib_Onload
{
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();
		$truycap = $this->_object->getFieldByCode('TenTruyCap');
		if($truycap->intRefIOID)
		{
			$sql = sprintf('select * from qsusers where UID = %1$d',$truycap->intRefIOID);
			$dataSQL = $this->_db->fetchOne($sql);
			if($dataSQL)
			{
				if(!$this->_object->getFieldByCode('TenNhanVien')->getValue())
				{
					$this->_object->getFieldByCode('TenNhanVien')->setValue($dataSQL->UserName);
				}
				if(!$this->_object->getFieldByCode('Email')->getValue())
				{
					$this->_object->getFieldByCode('Email')->setValue($dataSQL->EMail);
				}
			}
		}

		if(Qss_Lib_System::fieldActive('ODanhSachNhanVien', 'MaPhongBan')
        && Qss_Lib_System::fieldActive('ODanhSachNhanVien', 'MaBoPhan'))
        {
            $curPhongBan = (int)$this->_object->getFieldByCode('MaPhongBan')->getRefIOID();
            $curBoPhan   = (int)$this->_object->getFieldByCode('MaBoPhan')->getRefIOID();
            $mTable      = Qss_Model_Db::Table('OBoPhan');
            $mTable->where(sprintf('IOID = %1$d', $curBoPhan));
            $oBoPhan     = $mTable->fetchOne();

            if(!$oBoPhan || $oBoPhan->Ref_MaPhongBan != $curPhongBan)
            {
                $this->_object->getFieldByCode('MaBoPhan')->setValue('');
                $this->_object->getFieldByCode('MaBoPhan')->setRefIOID(0);
                $this->_object->getFieldByCode('TenBoPhan')->setValue('');
                $this->_object->getFieldByCode('TenBoPhan')->setRefIOID(0);
            }
        }

	}

    public function MaBoPhan()
    {
        if(Qss_Lib_System::fieldActive('ODanhSachNhanVien', 'MaPhongBan')
            && Qss_Lib_System::fieldActive('ODanhSachNhanVien', 'MaBoPhan'))
        {
            $nhaMay = $this->_object->getFieldByCode('MaPhongBan')->getRefIOID();

            $this->_object->getFieldByCode('MaBoPhan')->arrFilters[] =
                sprintf('
                ifnull(v.IOID, 0) in (
                    SELECT IOID 
                    FROM OBoPhan
                    WHERE IFNULL(Ref_MaPhongBan, 0) = %1$d
                )
            ', (int)$nhaMay);
        }

    }
	
}