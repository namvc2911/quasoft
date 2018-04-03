<?php
class Qss_Bin_Calculate_ODanhSachXuatKho_TonKho extends Qss_Lib_Calculate
{
    protected static $_arrTonKho = array();
    
    public function __doExecute()
    {
        $mInout   = new Qss_Model_Warehouse_Inout();
        $iItemCode = @(int)$this->_object->getFieldByCode('MaSP')->getRefIOID();
        $iOUM      = @(int)$this->_object->getFieldByCode('DonViTinh')->getRefIOID();

        $mOuput  = Qss_Model_Db::Table('OXuatKho');
        $mOuput->where(sprintf('IFID_M506 = %1$d', $this->_object->i_IFID));
        $oOutput = $mOuput->fetchOne();
        $iStock  = $oOutput?$oOutput->Ref_Kho:0;

        $mThisLine = Qss_Model_Db::Table('ODanhSachXuatKho');
        $mThisLine->where(sprintf('IOID = %1$d', $this->_object->i_IOID));

        if($iItemCode == 0)
        {
            $oThisLine = $mThisLine->fetchOne();
            $iItemCode = @(int)$oThisLine->Ref_MaSP;
            $iOUM      = @(int)$oThisLine->Ref_DonViTinh;
        }

        if($iItemCode != 0 && $iStock != 0)
        {
            if(isset(self::$_arrTonKho[$iItemCode][$iOUM]))
            {
                return Qss_Lib_Util::formatNumber(self::$_arrTonKho[$iItemCode][$iOUM]);
            }
            else
            {
                $dInv = $mInout->getInventoryOfItem($iItemCode, $iOUM, $iStock);
                self::$_arrTonKho[$iItemCode][$iOUM] = $dInv;
                return Qss_Lib_Util::formatNumber($dInv);
            }
        }

        return null;
    }
}
?>

<?php
/*
class Qss_Bin_Calculate_ODanhSachXuatKho_TonKho extends Qss_Lib_Calculate
{
	protected static $_arrTonKho = array();
	public function __doExecute()
	{
        $common  = new Qss_Model_Extra_Extra();
		$xuatkho = $common->getTableFetchOne('OXuatKho', array('IFID_M506'=>$this->_object->i_IFID));
		$line = $common->getTableFetchOne('ODanhSachXuatKho', array('IOID'=>$this->_object->i_IOID));
		$masp    = @(int)$this->_object->getFieldByCode('MaSP')->getRefIOID();
        $dvt     = @(int)$this->_object->getFieldByCode('DonViTinh')->getRefIOID();
        if(!$masp)
        {
        	$line = $common->getTableFetchOne('ODanhSachXuatKho', array('IOID'=>$this->_object->i_IOID));
			$masp    = @(int)$line->Ref_MaSP;
	        $dvt     = @(int)$line->Ref_DonViTinh;
        }
        $kho     = $xuatkho?$xuatkho->Ref_Kho:0;
        if($masp && $kho)
        {
            if(isset(self::$_arrTonKho[$masp][$dvt]))
            {
                return self::$_arrTonKho[$masp][$dvt];
            }
            else
            {
                $sql = sprintf('
                    SELECT
                    case when donvinhtinhquydoi.IOID is null then
                        kho.SoLuongHC
                    else
                        (kho.SoLuongHC * ifnull(donvitinh.HeSoQuyDoi, 1)/ donvinhtinhquydoi.HeSoQuyDoi)
                    end AS TonKho
                    FROM OKho AS kho
                    INNER JOIN OSanPham AS sanpham ON kho.Ref_MaSP = sanpham.IOID
                    LEFT JOIN ODonViTinhSP AS donvitinh ON kho.Ref_DonViTinh = donvitinh.IOID
                        AND sanpham.IFID_M113 = donvitinh.IFID_M113
                    LEFT JOIN (
                        SELECT * FROM ODonViTinhSP WHERE IOID = %3$d
                    ) AS donvinhtinhquydoi ON sanpham.IFID_M113 = donvinhtinhquydoi.IFID_M113
                    WHERE kho.Ref_MaSP = %1$d
                    AND kho.Ref_Kho = %2$d
                    and (%3$d = 0 or kho.Ref_DonViTinh = %3$d)',
                    $masp,
                    $kho,
                    $dvt);
                $dataSQL = $this->_db->fetchOne($sql);
                self::$_arrTonKho[$masp][$dvt] = $dataSQL?$dataSQL->TonKho:0;
                return $dataSQL?Qss_Lib_Util::formatNumber($dataSQL->TonKho):0;

            }
		}
	}
}
*/
?>