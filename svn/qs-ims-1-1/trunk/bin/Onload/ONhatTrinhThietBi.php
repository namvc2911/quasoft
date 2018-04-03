<?php
class Qss_Bin_Onload_ONhatTrinhThietBi extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();

		$iDiemDo = $this->_object->getFieldByCode('DiemDo')->getRefIOID();
        $iChiSo  = $this->_object->getFieldByCode('ChiSo')->getRefIOID();

        if(!$iDiemDo && !$iChiSo)
        {
            $mTable = Qss_Model_Db::Table('ONhatTrinhThietBi');
            $mTable->where(sprintf('IFID_M765 = %1$d', $this->_object->i_IFID));
            $oNhaTrinh = $mTable->fetchOne();

            $iDiemDo = @(int)$oNhaTrinh->Ref_DiemDo;
            $iChiSo  = @(int)$oNhaTrinh->Ref_ChiSo;
        }

        // Load thiet bi bo phan chi so theo diem do
		if($iDiemDo)
		{
            $mTable2 = Qss_Model_Db::Table('ODanhSachDiemDo');
            $mTable2->select('ODanhSachDiemDo.*, ODanhSachThietBi.IOID AS Ref_MaThietBi');
            $mTable2->join('INNER JOIN ODanhSachThietBi ON ODanhSachDiemDo.IFID_M705 = ODanhSachThietBi.IFID_M705');
            $mTable2->where(sprintf('ODanhSachDiemDo.IOID = %1$d', $iDiemDo));
            $oDiemDo = $mTable2->fetchOne();

			if($oDiemDo)
			{
                $iChiSo = $oDiemDo->Ref_ChiSo; // Set lai chi so theo diem do hien tai
				//$this->_object->getFieldByCode('MaTB')->setRefIOID($oDiemDo->Ref_MaThietBi);
				$this->_object->getFieldByCode('BoPhan')->setRefIOID($oDiemDo->Ref_BoPhan);
				$this->_object->getFieldByCode('ChiSo')->setRefIOID($oDiemDo->Ref_ChiSo);
			}
		}

		if($iChiSo)
        {
            $mTable3 = Qss_Model_Db::Table('OChiSoMayMoc');
            $mTable3->where(sprintf('IOID = %1$d', $iChiSo));
            $oChiSo = $mTable3->fetchOne();

            if($oChiSo && $oChiSo->DongHo == "ONOFF") // Dinh tinh hien dat
            {
                $this->_object->getFieldByCode('SoHoatDong')->setValue('');
                $this->_object->getFieldByCode('SoHoatDong')->bGrid = 12;
                $this->_object->getFieldByCode('Dat')->bGrid        = 3;
            }
            else // dinh luong hien cot ket qua
            {
                $this->_object->getFieldByCode('Dat')->setValue('');
                $this->_object->getFieldByCode('Dat')->bGrid = 12;
                $this->_object->getFieldByCode('SoHoatDong')->bGrid = 3;
            }
        }
	}
	public function MaTB()
	{
		$sql = sprintf(' v.IFID_M705 in (select IFID_M705 from ODanhSachDiemDo)');
		$this->_object->getFieldByCode('MaTB')->arrFilters[] = $sql;
	}
	
}


//$periodField = $this->_object->getFieldByCode('Ky');
//$periodVal   = '';
//		$sql         = sprintf('select OKy.* from OKy
//						inner join OChiSoHoatDongTB on OChiSoHoatDongTB.Ref_Ky =  OKy.IOID
//						where OChiSoHoatDongTB.IOID = %1$d',$periodField->intRefIOID);

/*$sql         = sprintf('select OKy.* from OKy
                where OKy.IOID = %1$d',$periodField->intRefIOID);
$dataSQL     = $this->_db->fetchOne($sql);


if($dataSQL)
{
    $periodVal = $dataSQL->MaKy;
}

$this->_object->getFieldByCode('NgayKT')->bReadOnly = true;

if($this->_object->intStatus == 1)
{
    switch ($periodVal)
    {
        case 'D':
        case 'H':
            $this->_object->getFieldByCode('NgayKT')->bReadOnly = true;
            break;
        default:
            $this->_object->getFieldByCode('NgayKT')->bReadOnly = false;
            break;
    }
}*/