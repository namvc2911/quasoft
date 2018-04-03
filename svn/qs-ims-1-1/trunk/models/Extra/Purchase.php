<?php
class Qss_Model_Extra_Purchase extends Qss_Model_Abstract
{
	//-----------------------------------------------------------------------
	/**
	* Build constructor
	*'
	* @return void
	*/
	public function __construct ()
	{
		parent::__construct();
	}
	
	public function getTaxs($ifid)
	{
		$sql = sprintf('select ODanhMucThue.*, sum(ODSDonMuaHang.ThanhTien) as ThanhTien
					from ODSDonMuaHang
					inner join OSanPham on OSanPham.IOID = ODSDonMuaHang.Ref_MaSP
					inner join ONhomThue on ONhomThue.IOID = OSanPham .Ref_ThueMuaVao					
					inner join ODanhMucThue on ODanhMucThue.IFID_M207 = ONhomThue.IFID_M207
					where ODSDonMuaHang.IFID_M401 = %1$d 
					group by ODanhMucThue.IOID',$ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getTaxsByReceipt($ifid)
	{
		$sql = sprintf('select dm.*, sum(ds.ThanhTien) as ThanhTien
						from ODanhSachNhapKho as ds
						inner join OSanPham as sp on sp.IOID = ds.Ref_MaSanPham
						inner join ONhomThue as nt on nt.IOID = sp.Ref_ThueMuaVao
						inner join ODanhMucThue as dm on dm.IFID_M207 = nt.IFID_M207
						where ds.IFID_M402 = %1$d
						group by dm.IOID 
						', $ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getTaxsByQuatation($ifid)
	{
		$sql = sprintf('select dm.*, sum(ds.ThanhTien) as ThanhTien
						from ODSBGMuaHang as ds
						inner join OSanPham as sp on sp.IOID = ds.Ref_MaSP
						inner join ONhomThue as nt on nt.IOID = sp.Ref_ThueMuaVao
						inner join ODanhMucThue as dm on dm.IFID_M207 = nt.IFID_M207
						where ds.IFID_M406 = %1$d
						group by dm.IOID 
						', $ifid);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}
	public function getTaxsByReturn($ifid)
	{
		$sql = sprintf('SELECT dm . * , SUM( ds.ThanhTien ) AS ThanhTien
						FROM ODanhSachTraHang AS ds
						INNER JOIN OSanPham AS sp ON sp.IOID = ds.Ref_MaSP
						INNER JOIN ONhomThue AS nt ON nt.IOID = sp.Ref_ThueMuaVao
						INNER JOIN ODanhMucThue AS dm ON dm.IFID_M207 = nt.IFID_M207
						WHERE ds.IFID_M403 = %1$d
						GROUP BY dm.IOID 
						', $ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getTaxsByInvoice($ifid)
	{
		$sql = sprintf('SELECT dm . * , SUM( ds.ThanhTien ) AS ThanhTien
						FROM ODanhSachHoaDon AS ds
						INNER JOIN OSanPham AS sp ON sp.IOID = ds.Ref_MaSP
						INNER JOIN ONhomThue AS nt ON nt.IOID = sp.Ref_ThueMuaVao
						INNER JOIN ODanhMucThue AS dm ON dm.IFID_M207 = nt.IFID_M207
						WHERE ds.IFID_M404 = %1$d
						GROUP BY dm.IOID 
						', $ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	/* Remove */
	public function getRequisition($formDate, $toDate, $page = '', $perpage = '',  $unlockStep = array(1))
	{
		$select = '';
		$order  = '';
		$limit  = '';
		$date   = ($formDate &&  $toDate)?sprintf('and (ycmh.NgayCanCo between %1$s and %2$s)', 
						$this->_o_DB->quote($formDate), 
						$this->_o_DB->quote($toDate)):'';
						
		if($page != '')
		{
			$select = 'ycmh.*, link.ToIOID as TranIOID, sp.GiaMua as Price, sp.ThueMuaVao as InTax';
			// Tinh vi tri bat dau
			$start  = ceil(($page - 1)*$perpage);
			// Sap xep theo ngay thang
			$order = ' order by ycmh.NgayCanCo ASC';
			// gioi han so ban ghi tim kiem
			$limit  = sprintf('limit %1$d, %2$d', $start, $perpage);
		}
		else 
		{
			$select = ' count(1) as SoBanGhi ';
		}
		
		$sql = sprintf('select %2$s
						from OYeuCauMuaHang as ycmh
						inner join qsiforms as iforms on ycmh.IFID_M405 = iforms.IFID
						inner join OSanPham as sp on sp.IOID = ycmh.Ref_MaSP
						left join qsioidlink as link on link.FromIOID = ycmh.IOID AND link.FromIFID = ycmh.IFID_M405
						where 
						iforms.Status in (%1$s)
						and ifnull(link.ToIOID,0) = 0
						%4$s
						%5$s
						%3$s
						', implode(',', $unlockStep), 
						$select,
						$limit,
						$date,
						$order );
						
		// Tra ve so ban ghi hoac tung dong ban ghi 
		if($page != '')
		{
			return $this->_o_DB->fetchAll($sql);
		}
		else
		{
			$dataSql = $this->_o_DB->fetchOne($sql);
			return $dataSql?$dataSql->SoBanGhi:0;
		}
	}
	
	
	/**
	 * 
	 * @param type $start
	 * @param type $end
	 * @param type $purchaseRequireIFID
	 * @return type
	 */
	public function getMaterialRequisitionByTime($start, $end, $purchaseRequireIFID)
	{
		$sql = sprintf(' 
			SELECT yc.SoPhieu as DocNo, yc.Ngay as DocDate, yc.IOID, ifnull(li.ToIOID,0) as HasExists
			FROM ONhuCauVatTu AS yc
			LEFT JOIN qsioidlink AS li ON li.`FromIOID` = yc.`IOID` AND li.FromIFID = yc.IFID_M709
			LEFT JOIN OYeuCauMuaSam AS ncmh ON ncmh.`IOID` = li.`ToIOID` AND li.ToIFID = ncmh.IFID_M412
			LEFT JOIN qsiforms as iform ON yc.`IFID_M709` = iform.`IFID`
			WHERE (yc.Ngay between %1$s and %2$s)
			AND iform.`Status` = 2 /* Chi tinh voi nhung phieu da duyet */
			AND (ncmh.`IOID` is null OR ncmh.`IFID_M412` = %3$s)
			/* yc vat tu chua duoc xu ly trong don yc mua hang khac (tru ifid cua yc mua hang dang xu ly) */
			ORDER BY yc.`Ngay`, yc.IOID
			'
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end)
			, $purchaseRequireIFID);	
		return $this->_o_DB->fetchAll($sql);
	}
	
	
	/**
	 * 
	 * @param type $start
	 * @param type $end
	 * @param type $purchaseRequireIFID
	 * @return type
	 */
	public function getMaterialRequisitionOfPurchaseRequire($purchaseRequireIFID)
	{
		$sql = sprintf(' 
			SELECT yc.SoPhieu as DocNo, yc.Ngay as DocDate, yc.IOID, ifnull(li.ToIOID,0) as HasExists
			FROM ONhuCauVatTu AS yc
			LEFT JOIN qsioidlink AS li ON li.`FromIOID` = yc.`IOID` AND li.FromIFID = yc.IFID_M709
			LEFT JOIN OYeuCauMuaSam AS ncmh ON ncmh.`IOID` = li.`ToIOID` AND ncmh.IFID_M412 = li.ToIFID
			LEFT JOIN qsiforms as iform ON yc.`IFID_M709` = iform.`IFID`
			WHERE 
			iform.`Status` = 2 /* Chi tinh voi nhung phieu da duyet */
			AND ncmh.`IFID_M412` = %1$s
			ORDER BY yc.`Ngay`, yc.IOID
			'
			, $purchaseRequireIFID);	
		return $this->_o_DB->fetchAll($sql);
	}
	
	
	/**
	 * Ham giup lay quan ly nhu cau vat tu (M709)
	 * Dung cho nut "Lay yeu cau vat tu" trong quan ly nhu cau mua hang (M716)
	 */
	public function getMaterialRequisitionDetail($ycIOID = array())
	{
		// Init
		$ycIOID[] = 0;
		
		$sql = sprintf(' 
			SELECT 
				yc.IOID
				, yc.IFID_M709 AS IFID
				, yc.Ngay AS `NgayYeuCau`
				, yc.SoPhieu
				, ifnull(sp.`SoLuongMua`, 0) as SoLuongMua
				, ds.IOID AS LineIOID
				, sp.IOID AS RefItem
				, sp.`MaSanPham` AS MaSP
				, sp.`TenSanPham` AS TenSP
				, sp.`DonViTinh` AS DonViTinhCoSo
				, sp.`DacTinhKyThuat`
				, sp.`SLToiThieu` AS SoLuongToiThieu
				, ds.Ref_DonViTinh
				, ds.DonViTinh
				, dvt.IOID as RefUOM
				, dvt.`HeSoQuyDoi`
				, ds.MucDich
				, sum( ifnull(ds.`SoLuong`, 0) *   ifnull(dvt.`HeSoQuyDoi`,0)) as SoLuong
			FROM ONhuCauVatTu AS yc
			LEFT JOIN qsiforms as iform ON yc.`IFID_M709` = iform.`IFID`
			LEFT JOIN ODSNhuCauVatTu AS ds ON yc.IFID_M709 = ds.IFID_M709
			LEFT JOIN OSanPham AS sp ON ds.`Ref_MaSP` = sp.`IOID`
			LEFT JOIN ODonViTinhSP AS dvt ON sp.`IFID_M113` = dvt.`IFID_M113`
				AND ds.`Ref_DonViTinh` = dvt.`IOID`
			WHERE iform.`Status` = 2 /* Chi tinh voi nhung phieu da duyet */
			AND yc.IOID in (%1$s) /* Lay yeu cau theo IOID */
			GROUP BY ds.Ref_MaSP
			
		', implode(',', $ycIOID));	
//		echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getInventoryOfMaterialRequisition($ycIOID = array())
	{
		// Init
		$ycIOID[] = 0;
		
		/// Cong don so luong theo san pham va don vi tinh, khong cong don theo 
		// don vi tinh chinh
		$sql = sprintf(' 
			SELECT 
				ds.`Ref_MaSP` AS RefItem
				, ds.`Ref_DonViTinh` AS RefUOM
				, sum( ifnull(k.`SoLuongHC`,0) * ifnull(dvt.`HeSoQuyDoi`,0 ) ) AS Inventory
			FROM ONhuCauVatTu AS yc
			LEFT JOIN qsiforms as iform ON yc.`IFID_M709` = iform.`IFID`
			LEFT JOIN ODSNhuCauVatTu AS ds ON yc.IFID_M709 = ds.IFID_M709
			LEFT JOIN OKho AS k ON k.`Ref_MaSP` = ds.`Ref_MaSP`
			LEFT JOIN ODanhSachKho AS dsk ON k.`Ref_Kho` = dsk.`IOID`
			LEFT JOIN OSanPham AS sp On sp.`IOID` = k.`Ref_MaSP`
			LEFT JOIN ODonViTinhSP AS dvt ON sp.`IFID_M113` = dvt.`IFID_M113`
				AND k.`Ref_MaSP` = dvt.`IOID`
			WHERE iform.`Status` = 2 /* Chi tinh voi nhung phieu da duyet */
			AND yc.IOID in (%1$s) /* Lay yeu cau theo IOID */
			AND ifnull(dsk.`LoaiKho`, \'\') != %2$s
			GROUP BY k.`Ref_MaSP`, k.`Ref_DonViTinh`
		', implode(',', $ycIOID)
		,$this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_DRAFT) );	
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getOldLinkFromMaterialRequireToPurchaseRequire($purchaseRequireIFID)
	{
		$sql = sprintf('
			SELECT li.* 
			FROM OYeuCauMuaSam AS ncmh
			INNER JOIN qsioidlink AS li ON li.`ToIOID` = ncmh.`IOID` AND li.ToIFID = ncmh.IFID_M412
			INNER JOIN ONhuCauVatTu AS ncvt ON li.`FromIOID` = ncvt.`IOID` AND li.FromIFID = ncvt.IFID_M709
			WHERE ncmh.`IFID_M412` = %1$d
			', $purchaseRequireIFID);
		return $this->_o_DB->fetchAll($sql);
	}
}
?>