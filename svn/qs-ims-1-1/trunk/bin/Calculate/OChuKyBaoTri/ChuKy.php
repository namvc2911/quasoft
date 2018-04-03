<?php
class Qss_Bin_Calculate_OChuKyBaoTri_ChuKy extends Qss_Lib_Calculate
{
	public function __doExecute()
	{


        $retval = '';
        $cancu = $this->_object->getFieldByCode('CanCu')->getValue();
        $ky = $this->_object->getFieldByCode('KyBaoDuong')->getValue();
        $thu = $this->_object->getFieldByCode('Thu')->sz_fGetDisplay();
        $ngay = $this->_object->getFieldByCode('Ngay')->getValue();
        $thang = $this->_object->getFieldByCode('Thang')->getValue();
        $chiso = $this->_object->getFieldByCode('ChiSo')->getValue();
        $giatri = $this->_object->getFieldByCode('GiaTri')->getValue();
        $laplai = $this->_object->getFieldByCode('LapLai')->getValue();
        if($cancu)
        {
            $retval = sprintf('%1$s %2$s',$giatri,$chiso);
        }
        else
        {
            $sql = sprintf('select month(NgayBatDau) as thang from OBaoTriDinhKy where IFID_M724=%1$d',$this->_object->i_IFID);
            $dataSQL = $this->_db->fetchOne($sql);
            switch ($ky)
            {
                case 'D';
                    if($laplai == 1)
                    {
                        $retval = sprintf('Hàng ngày');
                    }
                    else
                    {
                        $retval = sprintf('%1$s ngày một lần',$laplai);
                    }
                    break;
                case 'W';
                    $retval = sprintf('%1$s hàng tuần',$thu);
                    break;
                case 'M';
                    if($laplai == 1)
                    {
                        $retval = sprintf('Ngày %2$s%1$s hàng tháng',
                            $ngay,
                            ($ngay <= 10)?'mùng ':'');
                    }
                    else
                    {
                        $retval = sprintf('%1$s tháng một lần vào ngày %3$s%2$s',$laplai,$ngay,
                            ($ngay <= 10)?'mồng ':'');
                    }
                    break;
                case 'Y';
                    if($laplai == 1)
                    {
                        $retval = sprintf('Ngày %1$s/%2$s hàng năm',$ngay,$thang);
                    }
                    else
                    {
                        $retval = sprintf('%1$s năm một lần vào ngày %2$s/%3$s',$laplai,$ngay,$thang);
                    }
                    break;
            }
        }
        return $retval;

        /*
        $retval = '';
        $cancu = $this->_object->getFieldByCode('CanCu')->getValue();
        $ky = $this->_object->getFieldByCode('KyBaoDuong')->getValue();
        $thu = $this->_object->getFieldByCode('Thu')->sz_fGetDisplay();
        $ngay = $this->_object->getFieldByCode('Ngay')->getValue();
        $thang = $this->_object->getFieldByCode('Thang')->getValue();
        $chiso = $this->_object->getFieldByCode('ChiSo')->getValue();
        $giatri = $this->_object->getFieldByCode('GiaTri')->getValue();
        $laplai = $this->_object->getFieldByCode('LapLai')->getValue();
        if($cancu)
        {
            $retval = sprintf('%1$s %2$s',$giatri,$chiso);
        }
        else
        {
            $sql = sprintf('select month(NgayBatDau) as thang from OBaoTriDinhKy where IFID_M724=%1$d',$this->_object->i_IFID);
            $dataSQL = $this->_db->fetchOne($sql);
            switch ($ky)
            {
                case 'D';
                    if($laplai == 1)
                    {
                        $retval = sprintf('Hàng ngày');
                    }
                    else
                    {
                        $retval = sprintf('%1$s ngày một lần',$laplai);
                    }
                    break;
                case 'W';
                    if($laplai == 1)
                    {
                        $retval = sprintf('%1$s hàng tuần',$thu);
                    }
                    else
                    {
                        $retval = sprintf('%1$s tuần một lần vào %2$s',$laplai,strtolower ($thu));
                    }
                    break;
                case 'M';
                    if($laplai == 1)
                    {
                        $retval = sprintf('Ngày %2$s%1$s hàng tháng',
                                $ngay,
                                ($ngay <= 10)?'mồng ':'');
                    }
                    else
                    {
                        if(12 % $laplai)//có dư
                        {
                            $retval = sprintf('%1$s tháng một lần vào ngày %3$s%2$s',$laplai,$ngay,
                                ($ngay <= 10)?'mồng ':'');
                        }
                        else
                        {
                            $batdau = $dataSQL->thang;
                            while($batdau - $laplai > 0)
                            {
                                $batdau -= $laplai;
                            }
                            $retval = sprintf('%1$s tháng một lần vào các ngày ',$laplai);
                            while($batdau <= 12)
                            {
                                $retval .= sprintf('%1$d/%2$d ',$ngay,$batdau);
                                $batdau += $laplai;
                            }
                        }
                    }
                    break;
                case 'Y';
                    if($laplai == 1)
                    {
                        $retval = sprintf('Ngày %1$s/%2$s hàng năm',$ngay,$thang);
                    }
                    else
                    {
                        $retval = sprintf('%1$s năm một lần vào ngày %2$s/%3$s',$laplai,$ngay,$thang);
                    }
                    break;
            }
        }
        return $retval;
        */
	}
}
?>
