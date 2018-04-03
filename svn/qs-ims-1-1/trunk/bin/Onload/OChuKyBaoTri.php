<?php
class Qss_Bin_Onload_OChuKyBaoTri extends Qss_Lib_Onload
{
    /**
     * Dua vao can cu an hien chi so va ky bao duong
     */
    public function __doExecute()
    {
        parent::__doExecute();
		$common = new Qss_Model_Extra_Extra();
        $dk     = $common->getTableFetchOne('OBaoTriDinhKy', array('IFID_M724'=>$this->_object->i_IFID));
        $date = $dk->NgayBatDau;
        $date = date_create($date);
        $thu = $this->_object->getFieldByCode('Thu')->getValue();
        $ngay = $this->_object->getFieldByCode('Ngay')->getValue();
        $thang = $this->_object->getFieldByCode('Thang')->getValue();
        // readonly all
        //$this->_object->getFieldByCode('KyBaoDuong')->bReadOnly = true;
        $this->_object->getFieldByCode('LapLai')->bReadOnly     = true;
        $this->_object->getFieldByCode('ChiSo')->bReadOnly      = true;
        $this->_object->getFieldByCode('GiaTri')->bReadOnly     = true;
        $this->_object->getFieldByCode('Thu')->bReadOnly        = true;
        $this->_object->getFieldByCode('Ngay')->bReadOnly       = true;
        $this->_object->getFieldByCode('Thang')->bReadOnly      = true;

        $ky  = $this->_object->getFieldByCode('KyBaoDuong')->getValue();
        $canCu  = $this->_object->getFieldByCode('CanCu')->getValue();
        
        $common = new Qss_Model_Extra_Extra();
        //$ky     = $common->getTableFetchOne('OKy', array('IOID'=>$refKy));
        
        // Tinh theo dinh KyBaoDuong
        if($canCu == Qss_Lib_Extra_Const::CAUSE_PERIOD)
        {
        	
            $this->_object->getFieldByCode('ChiSo')->bReadOnly      = true;
            $this->_object->getFieldByCode('GiaTri')->bReadOnly     = true;
            $this->_object->getFieldByCode('ChiSo')->setRefIOID(0);
            $this->_object->getFieldByCode('GiaTri')->setValue('');

            //$this->_object->getFieldByCode('KyBaoDuong')->bReadOnly = false;
            $this->_object->getFieldByCode('KyBaoDuong')->bRequired = true;
            $this->_object->getFieldByCode('LapLai')->bReadOnly     = false;


            switch ($ky)
            {
                case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
                    $this->_object->getFieldByCode('Thu')->bReadOnly = false;
                    $this->_object->getFieldByCode('Thu')->bRequired = true;
                    if(!$thu)
                    	$this->_object->getFieldByCode('Thu')->setValue($date->format('w'));
                    $this->_object->getFieldByCode('Ngay')->setValue('');
                    $this->_object->getFieldByCode('Thang')->setValue('');
                break;

                case Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY:
                    $this->_object->getFieldByCode('Ngay')->bReadOnly = false;
                    $this->_object->getFieldByCode('Ngay')->bRequired = true;
                    $this->_object->getFieldByCode('Thu')->setValue('');
                    if(!$ngay)
						$this->_object->getFieldByCode('Ngay')->setValue($date->format('d'));
					$this->_object->getFieldByCode('Thang')->setValue('');
                break;

                case Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY:
                    $this->_object->getFieldByCode('Ngay')->bReadOnly  = false;
                    $this->_object->getFieldByCode('Thang')->bReadOnly = false;
                    $this->_object->getFieldByCode('Ngay')->bRequired  = true;
                    $this->_object->getFieldByCode('Thang')->bRequired = true;
                    $this->_object->getFieldByCode('Thu')->setValue('');
                    if(!$ngay)
                    	$this->_object->getFieldByCode('Ngay')->setValue($date->format('d'));
                    if(!$thang)
                    	$this->_object->getFieldByCode('Thang')->setValue($date->format('m'));
                break;
                default:
                   // Khong lam gi @todo: Cần bỏ kỳ S, kỳ đặc biệt đi vì trong kế hoạch sẽ là định kỳ hoặc chỉ số
                    $this->_object->getFieldByCode('Thu')->setValue('');
                    $this->_object->getFieldByCode('Ngay')->setValue('');
                    $this->_object->getFieldByCode('Thang')->setValue('');
                break;
                
            }
        }

        // Tinh theo chi so
        if($canCu == Qss_Lib_Extra_Const::CAUSE_PARAM)
        {
        	$this->_object->getFieldByCode('LapLai')->bReadOnly     = true;
            $this->_object->getFieldByCode('ChiSo')->bReadOnly      = false;
            $this->_object->getFieldByCode('ChiSo')->bRequired      = true;
            $this->_object->getFieldByCode('GiaTri')->bReadOnly     = false;
            $this->_object->getFieldByCode('GiaTri')->bRequired     = true;
            $this->_object->getFieldByCode('KyBaoDuong')->bReadOnly = true;
            $this->_object->getFieldByCode('KyBaoDuong')->bRequired = false;
            $this->_object->getFieldByCode('KyBaoDuong')->setRefIOID(0);
        }

    }
    public function ChiSo()
    {
    	$this->_doFilter($this->_object->getFieldByCode('ChiSo'));
    	//$this->_object->getFieldByCode('ChiSo')->arrFilters[] = sprintf(' v.DongHo = "COUNTER"');
    }
}