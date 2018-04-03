<?php
class Qss_Model_Extra_Sale extends Qss_Model_Abstract
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
	
	// @todo: Gộp các hàm từ 1 đến thành một hàm getTaxs($ifid) và gộp cả phần getTax của mua hàng
	// thành một hàm extra để xử lý chung.
	//1
	public function getTaxsOrder($ifid)
	{
		$sql = sprintf('SELECT dm . * , SUM( ds.ThanhTien ) AS ThanhTien
						FROM ODSDonBanHang AS ds
						INNER JOIN OSanPham AS sp ON sp.IOID = ds.Ref_MaSP
						INNER JOIN ONhomThue AS nt ON nt.IOID = sp.Ref_ThueMuaVao
						INNER JOIN ODanhMucThue AS dm ON dm.IFID_M207 = nt.IFID_M207
						WHERE ds.IFID_M505 = %1$d
						GROUP BY dm.IOID
						', $ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	//2
	public function getTaxsByShipment($ifid)
	{
		$sql = sprintf('SELECT dm . * , SUM( ds.ThanhTien ) AS ThanhTien
						FROM ODanhSachXuatKho AS ds
						INNER JOIN OSanPham AS sp ON sp.IOID = ds.Ref_MaSP
						INNER JOIN ONhomThue AS nt ON nt.IOID = sp.Ref_ThueMuaVao
						INNER JOIN ODanhMucThue AS dm ON dm.IFID_M207 = nt.IFID_M207
						WHERE ds.IFID_M506 = %1$d
						GROUP BY dm.IOID
						', $ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	//3
	public function getTaxsByReturnFromCustomer($ifid)
	{
		$sql = sprintf('SELECT dm . * , SUM( ds.ThanhTien ) AS ThanhTien
						FROM ODanhSachHangTL AS ds
						INNER JOIN OSanPham AS sp ON sp.IOID = ds.Ref_MaSP
						INNER JOIN ONhomThue AS nt ON nt.IOID = sp.Ref_ThueMuaVao
						INNER JOIN ODanhMucThue AS dm ON dm.IFID_M207 = nt.IFID_M207
						WHERE ds.IFID_M507 = %1$d
						GROUP BY dm.IOID
						', $ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	//4
	public function getTaxsByInvoice($ifid)
	{
		$sql = sprintf('SELECT dm . * , SUM( ds.ThanhTien ) AS ThanhTien
						FROM ODSHDBanHang AS ds
						INNER JOIN OSanPham AS sp ON sp.IOID = ds.Ref_MaSP
						INNER JOIN ONhomThue AS nt ON nt.IOID = sp.Ref_ThueMuaVao
						INNER JOIN ODanhMucThue AS dm ON dm.IFID_M207 = nt.IFID_M207
						WHERE ds.IFID_M508 = %1$d
						GROUP BY dm.IOID
						', $ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	//5
	public function getTaxsByQuatation($ifid)
	{
		$sql = sprintf('select dm.*, sum(ds.ThanhTien) as ThanhTien
						from ODSBGBanHang as ds
						inner join OSanPham as sp on sp.IOID = ds.Ref_MaSP
						inner join ONhomThue as nt on nt.IOID = sp.Ref_ThueMuaVao
						inner join ODanhMucThue as dm on dm.IFID_M207 = nt.IFID_M207
						where ds.IFID_M509 = %1$d
						group by dm.IOID 
						', $ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	/* @todo: Waiting for approve remove function */
	public function updateAttrDetail($module, $ifid, $itemCode)
	{
		$sql = sprintf('SELECT IOID 
						FROM OThuocTinhChiTiet 
						WHERE IFID_%3$s = %1$d
						AND MaSanPham = %2$s ', 
						$ifid, $this->_o_DB->quote($itemCode), $module);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getIssueDateForDeliveryPlan($mainView, $subView, $dateAlias, $ifidAlias,$itemAlias, $ifid)
	{
		$sql = sprintf('select 
				min(DATE_SUB(dbh.%3$s ,INTERVAL ifnull(sp.ThoiGianCho,0) DAY)) as NgayXuatHang
				from %2$s as dsdbh
				inner join OSanPham as sp on dsdbh.Ref_%5$s = sp.IOID
				inner join %1$s as dbh on dsdbh.IFID_%4$s = dbh.IFID_%4$s
				where dsdbh.IFID_%4$s = %6$d
				',$mainView, $subView, $dateAlias, $ifidAlias, $itemAlias, $ifid);
		return $this->_o_DB->fetchOne($sql);
	}
}
?>