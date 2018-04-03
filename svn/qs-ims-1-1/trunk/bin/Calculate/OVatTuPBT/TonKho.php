<?php
class Qss_Bin_Calculate_OVatTuPBT_TonKho extends Qss_Lib_Calculate
{
    protected static $_arrTonKho = array();

    public function __doExecute()
    {
        $mInout   = new Qss_Model_Warehouse_Inout();
        $iItemCode = @(int)$this->_object->getFieldByCode('MaVatTu')->getRefIOID();
        $iOUM      = @(int)$this->_object->getFieldByCode('DonViTinh')->getRefIOID();

        $mThisLine = Qss_Model_Db::Table('OVatTuPBT');
        $mThisLine->where(sprintf('IOID = %1$d', $this->_object->i_IOID));

        if($iItemCode == 0)
        {
            $oThisLine = $mThisLine->fetchOne();
            $iItemCode = @(int)$oThisLine->Ref_MaVatTu;
            $iOUM      = @(int)$oThisLine->Ref_DonViTinh;
        }

        if($iItemCode != 0)
        {
            if(isset(self::$_arrTonKho[$iItemCode][$iOUM]))
            {
                return Qss_Lib_Util::formatNumber(self::$_arrTonKho[$iItemCode][$iOUM]);
            }
            else
            {
                $dInv = $mInout->getInventoryOfItem($iItemCode, $iOUM);
                self::$_arrTonKho[$iItemCode][$iOUM] = $dInv;
                return Qss_Lib_Util::formatNumber($dInv);
            }
        }

        return null;
    }
}
?>

<?php /*
class Qss_Bin_Calculate_OVatTuPBT_TonKho extends Qss_Lib_Calculate
{
    protected static $_arrTonKho = array();
    public function __doExecute()
    {
    	$common  = new Qss_Model_Extra_Extra();
        $masp    = @(int)$this->_object->getFieldByCode('MaVatTu')->intRefIOID;
        $dvt     = @(int)$this->_object->getFieldByCode('DonViTinh')->intRefIOID;
    	if(!$masp)
        {
        	$line = $common->getTableFetchOne('OVatTuPBT', array('IOID'=>$this->_object->i_IOID));
			$masp    = @(int)$line->Ref_MaVatTu;
	        $dvt     = @(int)$line->Ref_DonViTinh;
        }
        if($masp)
        {
            if(isset(self::$_arrTonKho[$masp][$dvt]))
            {
                return Qss_Lib_Util::formatNumber(self::$_arrTonKho[$masp][$dvt]);
            }
            else
            {
            	$month = date('m');
				$year = date('Y');
				$table = sprintf('tblcost%1$s%1$s',str_pad($month, 2,'0',STR_PAD_LEFT),str_pad($year, 4,'0',STR_PAD_LEFT));
				if($this->_db->tableExists($table))
				{
					$sql = sprintf('select 
					case when donvinhtinhquydoi.IOID is null then
                    	sum(kho.TonKhoCK) 
                    else
                    	sum((kho.TonKhoCK * ifnull(donvitinh.HeSoQuyDoi, 1)/ donvinhtinhquydoi.HeSoQuyDoi))
                    end AS TonKhoCK 
					from %1$s as kho
					INNER JOIN OSanPham AS sanpham ON kho.Ref_MaSanPham = sanpham.IOID
                    LEFT JOIN ODonViTinhSP AS donvitinh ON sanpham.Ref_DonViTinh = donvitinh.IOID
                        AND sanpham.IFID_M113 = donvitinh.IFID_M113
                    LEFT JOIN (
                        SELECT * FROM ODonViTinhSP WHERE IOID = %4$d
                    ) AS donvinhtinhquydoi ON sanpham.IFID_M113 = donvinhtinhquydoi.IFID_M113
					where Ref_MaSanPham=%2$d
					and (%4$d = 0 or sanpham.Ref_DonViTinh = %4$d)',
					$table,
					$masp,
					1,//kho
					$dvt);
					$dataSQL = $this->_db->fetchOne($sql);
					if($dataSQL)
					{
						self::$_arrTonKho[$masp][$dvt] = $dataSQL->TonKhoCK;
						return Qss_Lib_Util::formatNumber($dataSQL->TonKhoCK);
					}
				}
				else 
				{
					$last = '';
					if($month==1)
					{
						$last = sprintf('tblcost12%1$s',str_pad($year-1, 4,'0',STR_PAD_LEFT));
					}
					else
					{
						$last = sprintf('tblcost%1$s%2$s',str_pad($month-1, 2,'0',STR_PAD_LEFT),str_pad($year, 4,'0',STR_PAD_LEFT));
					}
					if($this->_db->tableExists($last))
					{
						$sql = sprintf('select 
								case when donvinhtinhquydoi.IOID is null then
			                    	sum(kho.TonKhoCK) 
			                    else
			                    	sum((kho.TonKhoCK * ifnull(donvitinh.HeSoQuyDoi, 1)/ donvinhtinhquydoi.HeSoQuyDoi))
			                    end AS TonKhoCK 
								from %1$s as kho
								INNER JOIN OSanPham AS sanpham ON kho.Ref_MaSanPham = sanpham.IOID
			                    LEFT JOIN ODonViTinhSP AS donvitinh ON sanpham.Ref_DonViTinh = donvitinh.IOID
			                        AND sanpham.IFID_M113 = donvitinh.IFID_M113
			                    LEFT JOIN (
			                        SELECT * FROM ODonViTinhSP WHERE IOID = %4$d
			                    ) AS donvinhtinhquydoi ON sanpham.IFID_M113 = donvinhtinhquydoi.IFID_M113
								where Ref_MaSanPham=%2$d
								and (%4$d = 0 or sanpham.Ref_DonViTinh = %4$d)',
								$last,
								$masp,
								1,//kho
								$dvt);
						$dataSQL = $this->_db->fetchOne($sql);
						if($dataSQL)
						{
							self::$_arrTonKho[$masp][$dvt] = $dataSQL->TonKhoCK;
							return Qss_Lib_Util::formatNumber($dataSQL->TonKhoCK);
						}
					}
					else 
					{
					 	$sql = sprintf('
			                    SELECT
			                    case when donvinhtinhquydoi.IOID is null then
			                    	sum(kho.SoLuongHC)
			                    else
			                    	sum((kho.SoLuongHC * ifnull(donvitinh.HeSoQuyDoi, 1)/ donvinhtinhquydoi.HeSoQuyDoi))
			                    end AS TonKho
			                    FROM OKho AS kho
			                    INNER JOIN OSanPham AS sanpham ON kho.Ref_MaSP = sanpham.IOID
			                    LEFT JOIN ODonViTinhSP AS donvitinh ON kho.Ref_DonViTinh = donvitinh.IOID
			                        AND sanpham.IFID_M113 = donvitinh.IFID_M113
			                    LEFT JOIN (
			                        SELECT * FROM ODonViTinhSP WHERE IOID = %3$d
			                    ) AS donvinhtinhquydoi ON sanpham.IFID_M113 = donvinhtinhquydoi.IFID_M113
			                    WHERE kho.Ref_MaSP = %1$d
			                    and (%3$d = 0 or kho.Ref_DonViTinh = %3$d)
			                    GROUP BY kho.Ref_MaSP, kho.Ref_DonViTinh, kho.Ref_Kho', 
					 			$masp, 
					 			1, //kho
					 			$dvt);
						$dataSQL = $this->_db->fetchOne($sql);
						if($dataSQL)
						{
							self::$_arrTonKho[$masp][$dvt] = $dataSQL->TonKho;
							return Qss_Lib_Util::formatNumber($dataSQL->TonKho);
						}
					}
				}
            }
        }
    }
} */
?>