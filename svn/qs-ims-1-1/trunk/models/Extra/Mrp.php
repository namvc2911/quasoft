<?php
class Qss_Model_Extra_Mrp extends Qss_Model_Abstract
{
	//-----------------------------------------------------------------------
	/**
	 * Build constructor
	 *'
	 * @return void
	 */
	public $_common;
	public function __construct ()
	{
		parent::__construct();
		$this->_common = new Qss_Model_Extra_Extra;
	}


	/**
	 * Function Group: Dùng chung
	 *
	 */
	public function getCurrencies()
	{
		$sql = sprintf('select * from qscurrencies');
		return $this->_o_DB->fetchAll($sql);
	}


	// Use getTable
	public function getMRPInfo($ifid, $extend = 0) 
	{
		$sql = sprintf('select *
						from OKeHoachCungUng 
						where IFID_M901 = %1$d',$ifid);
		return $this->_o_DB->fetchOne($sql);
	}

	public function getSuppliers()
	{
		$sql = 'select * from ODoiTac where NhaCungCap = 1 order by MaDoiTac, TenDoiTac';
		return $this->_o_DB->fetchAll($sql);
	}

	public function getProducts($itemgroup = 0,$items = array() ,$itemtype = array())
	{
		$where   = ' where 1=1 ';
		if(count($items))
		{
			$items[]  = 0;
			$items    = implode(',', $items);
			$where   .= sprintf(' and OSanPham.IOID in (%1$s)',$items);
		}
		else
		{
			if($itemgroup)
			{
				$where .= sprintf(' and OSanPham.Ref_NhomSanPham = %1$d ',$itemgroup);
			}
				
			$typeTmp = '';
			if(count($itemtype))
			{
				foreach ($itemtype as $key=>$val)
				{
					switch ($key)
					{
						case 'Purchase': $column = 'OSanPham.MuaVao'; break;
						case 'Sale': $column = 'OSanPham.BanRa'; break;
						case 'Production': $column = 'OSanPham.SanXuat'; break;
						case 'Material': $column = 'OSanPham.VatTu'; break;
					}
						
					$typeTmp .= $typeTmp?' and ':'';
					$typeTmp .= sprintf(' %1$s = 1 ',$column);
				}
				$where   .= sprintf(' and (%1$s)',$typeTmp);
			}
		}

		$sql = sprintf('SELECT OSanPham.*,  ODonViTinhSP.DonViTinh
                                FROM OSanPham 
                                LEFT Join ODonViTinhSP on OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113
                                %1$s 
                                and ODonViTinhSP.MacDinh = 1',$where);
		return $this->_o_DB->fetchAll($sql);
	}


	/* End Funtion Group: Dùng chung */

	/* @Remove */
	public function getInProducts($startdate, $enddate)
	{
		$sql = sprintf('select Ref_MaSanPham as IOID, day(Ngay) as DayNo, sum(SoLuong) as SoLuong, Ngay
				from OGiaoDichKho 
				where Ngay between %1$s and %2$s and NhapXuat=1
				group by Ref_MaSanPham, Ngay',
		$startdate,
		$enddate);
		return $this->_o_DB->fetchAll($sql);
	}

	/* @Remove */
	/*public function getOutProducts($startdate, $enddate)
	{
		$sql = sprintf('select Ref_MaSanPham as IOID, day(Ngay) as DayNo, sum(SoLuong) as SoLuong, Ngay
				from OGiaoDichKho 
				where Ngay between %1$s and %2$s and NhapXuat=0
				group by Ref_MaSanPham, Ngay',
		$startdate,
		$enddate);
		return $this->_o_DB->fetchAll($sql);
	}*/
	
	
	public function getAvaiProducts($startdate, $enddate)
	{
		$sql = sprintf('select Ref_MaSanPham as IOID, day(Ngay) as DayNo, Ngay,
				Case When NhapXuat = 1 Then ifnull(HienCo,0) + ifnull(SoLuong,0)
				when NhapXuat = 0 Then ifnull(HienCo,0) - ifnull(SoLuong,0) end as HienCo
				from OGiaoDichKho
				where IOID in 
				(select max(IOID) from OGiaoDichKho where Ngay between %1$s and %2$s
				group by Ref_MaSanPham, Ngay)',
		$startdate,
		$enddate);
		return $this->_o_DB->fetchAll($sql);
	}


	// @todo: Đảm bảo không có hai kế hoạch cung ứng có giao khoảng thời gian với nhau
	/* @Remove */
	public function getPurchasePlan($start, $end)
	{
		$sql = sprintf('select *, sum(ifnull(SoLuong,0)) as SoLuong from OKeHoachCungUng as khcu
						inner join OKeHoachMuaHang as khmh
						on khcu.IFID_M901 = khmh.IFID_M901
						where (khcu.NgayBatDau >= %1$s
						and khcu.NgayKetThuc <= %2$s)
						group by khmh.Ngay, khmh.Ref_MaSP', $this->_o_DB->quote($start)
		,$this->_o_DB->quote($end) );
		return $this->_o_DB->fetchAll($sql);
	}

	/* @Remove */
	public function getProductionPlan($start, $end)
	{
		$sql = sprintf('select *, sum(ifnull(SoLuong,0)) as SoLuong from OKeHoachCungUng as khcu
						inner join OKeHoachSanXuat as khsx
						on khcu.IFID_M901 = khsx.IFID_M901
						where (khcu.NgayBatDau >= %1$s
						and khcu.NgayKetThuc <= %2$s)
						group by khsx.Ngay, khsx.Ref_MaSP', $this->_o_DB->quote($start)
		,$this->_o_DB->quote($end) );
		return $this->_o_DB->fetchAll($sql);
	}
	
	/* @Remove */
	public function getSalePlan($start, $end)
	{
		$sql = sprintf('select *, sum(ifnull(dsdbh.SoLuong,0)) as SoLuong from ODonBanHang as dbh
						inner join ODSDonBanHang as dsdbh
						on dbh.IFID_M505 = dsdbh.IFID_M505
						where ( dbh.NgayYCNH between %1$s and %2$s )
						group by dbh.Ref_MaKhachHang, dsdbh.Ref_MaSP', $this->_o_DB->quote($start)
		,$this->_o_DB->quote($end));
		return $this->_o_DB->fetchAll($sql);
	}
	
	/* @Remove */
	public function getSaleContracts($start, $end)
	{
		$sql = sprintf('select *, sum(ifnull(ccdk.SoLuong,0)) as SoLuong, t.GiaTri as ThuTrongTuan
						from OHopDongBanHang as hdbh
						inner join qsiforms as qsi
						on qsi.IFID = hdbh.IFID_M510
						inner join OKy as k
						on k.IOID = hdbh.Ref_Ky
						left join OThu as t
						on t.IOID = hdbh.Ref_Thu
						inner join OCungCapDinhKy as ccdk
						on hdbh.IFID_M510 = ccdk.IFID_M510
						where (hdbh.NgayBatDau >= %1$s
						and hdbh.NgayKetThuc <= %2$s)
						and qsi.Status = 2
						group by hdbh.Ref_MaDoiTac, ccdk.Ref_MaSanPham'
						, $this->_o_DB->quote($start)
						, $this->_o_DB->quote($end) );
						return $this->_o_DB->fetchAll($sql);
	}

	//@todo : Cần kiểm tra thêm điều kiện về thuộc tính và BOM trong các sub query
	// @Remove
	public function getOldMrp($ifid, $statusSO = array('2'),
	$statusPO = array('2'), $statusMO = array('2'))
	{
		$mrp         = $this->getMRPInfo($ifid);
		$startDate 	 = $mrp->NgayBatDau;
		$endDate     = $mrp->NgayKetThuc;
		$statusSO    = implode(',', $statusSO);
		$statusPO    = implode(',', $statusPO);
		$statusMO    = implode(',', $statusMO);

		$sql         = sprintf('select
								khsp.MaSP as MaSanPham
								, khsp.Ref_MaSP as Ref_MaSanPham
								, khsp.SanPham as TenSanPham
								, khsp.ThuocTinh as ThuocTinh
								, khsp.SLKeHoach as KeHoach
								, khsp.BOM as BOM
								, ifnull(khsp.IOID,0) as KHSPIOID 
								, khsp.Ref_BOM as RefBOM
								## Sale orders
								, ifnull((select sum(ifnull(dsdbh.SoLuong,0))
								from ODSDonBanHang as dsdbh 
								inner join ODonBanHang as dbh
								on dsdbh.IFID_M505 = dbh.IFID_M505
								inner join qsiforms as qsi on qsi.IFID = dbh.IFID_M505 
								where dsdbh.Ref_MaSP = khsp.Ref_MaSP 
								and qsi.Status = %4$d 
								and  (dbh.NgayDatHang between %2$s and %3$s) 
								),0)
								as DatBan
								## Warehouse
								, ifnull((select sum(ifnull(k.SoLuongHC,0)) from OKho as k
								where k.Ref_MaSP = khsp.Ref_MaSP),0) as TonKho
								## Incomming : Purchase orders
								, ifnull((select sum(ifnull(dsdmh.SoLuong,0)) from ODSDonMuaHang as dsdmh
								inner join ODonMuaHang as dmh on dmh.IFID_M401 = dsdmh.IFID_M401
								inner join qsiforms as qsi1 on qsi1.IFID = dsdmh.IFID_M401
								where dsdmh.Ref_MaSP = khsp.Ref_MaSP and (dmh.NgayDatHang between %2$s and %3$s)
								and qsi1.Status = %5$d),0) as DatMua
								## Incomming : Manufacturing
								, ifnull((select sum(ifnull(sx.SoLuong,0)) from OSanXuat as sx
								inner join qsiforms as qsi2 on qsi2.IFID = sx.IFID_M710
								where (sx.NgayYeuCau between %2$s and %3$s) 
								and sx.MaSP = khsp.MaSP
								and qsi2.Status = %6$d),0) as DatSanXuat
								
								from OKeHoachSanPham as khsp 
								left join OCauThanhSanPham as ctsp on ctsp.Ref_MaSanPham = khsp.Ref_MaSP 
								and (khsp.Ref_BOM is null or khsp.Ref_BOM = ctsp.IOID)
								and ctsp.HoatDong = 1
								where khsp.IFID_M901 = %1$d
								group by  khsp.Ref_MaSP desc, khsp.Ref_ThuocTinh, ctsp.IOID
								order by  khsp.Ref_MaSP desc, khsp.IOID desc',
		$ifid,
		$this->_o_DB->quote($startDate),
		$this->_o_DB->quote($endDate),
		$statusSO,
		$statusPO,
		$statusMO
		);
		return $this->_o_DB->fetchAll($sql);
	}

	// @Remove
	/*public function getOldGeneralPlan($mrpIFID, 
									  $start, 
									  $end, 
									  $poStatus = array(2), 
									  $soStatus = array(2), 
									  $moStatus = array(2))
	{
		$sql = sprintf(' select khsp.*, so.DatBan 
						 from OKeHoachSanPham as khsp 
						 left join 
						 (
							# lay dat ban, da chuyen va nhan lai
							# dat ban = (dat ban + nhan lai) - da chuyen
						 	select 
							dsdbh.Ref_MaSp, dsdbh.Ref_ThuocTinh, 
							sum(dsdbh.SoLuong) as DatBan,
							sum(ifnull(dsch.SoLuong,0)) as DaChuyen, 
							sum(ifnull(dsth.SoLuong,0)) as NhanLai
						 	from ODonBanHang as dbh
							inner join qsiforms as qsi on qsi.IFID = dbh.IFID_M505 and qsi.Status in (%4$s)
							inner join ODSDonBanHang as dsdbh on dbh.IFID_M505 = dsdbh.IFID_M505
							inner join ODanhSachXuatKho as dsch on dbh.IOID = dsch.Ref_SoDonHang 
							and dsdbh.Ref_MaSP = dsch.Ref_MaSP and ifnull(dsdbh.Ref_ThuocTinh,0) = ifnull(dsch.Ref_ThuocTinh,0)
							inner join ODanhSachHangTL as dsth on dbh.IOID = dsth.Ref_SoDonHang
							and dsdbh.Ref_MaSP = dsth.Ref_MaSP and ifnull(dsdbh.Ref_ThuocTinh,0) = ifnull(dsth.Ref_ThuocTinh,0)
						 	where dbh.NgayYCNH between %2$s and %3$s
							group by dsdbh.Ref_MaSP, dsdbh.Ref_ThuocTinh
						 ) as so on khsp.Ref_MaSP = so.Ref_MaSP and ifnull(khsp.Ref_ThuocTinh,0) = ifnull(so.Ref_ThuocTinh,0)
						 left join
						 (
							# lay dat mua va da nhan
							select *
							from ODonMuaHang as dmh
							where 
						 ) as po on
						 left join
						 (
						 
						 ) as mo on
						 where IFID_M901 = %1$d', 
						 $mrpIFID, 
						 $this->_o_DB->quote($start), 
						 $this->_o_DB->quote($end),
						 implode(',', $soStatus));
						 echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}
	*/
	
	// @Remove
	// @todo : Do có nhiều dòng cho một sản phẩm nên phân trang không đúng nữa.
	// @todo : Order theo kế hoạch sản phẩm
	// @todo : Sau khi có link cách nào đó đến đối tượng cha đổi dòng này thành IOID : and sx.MaSP = sp.MaSanPham
	// @todo : Cần kiểm tra thêm điều kiện về thuộc tính và BOM trong các sub query
	public function getItemsMRP($count = 1, $start = 0, $qty = 0,
	$ifid = 0, $statusSO = array('2'),
	$statusPO = array('2'), $statusMO = array('2'))
	{
		$mrp         = $this->getMRPInfo($ifid);
		$startDate 	 = $mrp->NgayBatDau;
		$endDate     = $mrp->NgayKetThuc;
		$statusSO    = implode(',', $statusSO);
		$statusPO    = implode(',', $statusPO);
		$statusMO    = implode(',', $statusMO);
		
		if($count)
		{
			$sql     = sprintf('select count(IOID) as qty from OSanPham
								where MuaVao = 1 or SanXuat = 1 or BanRa = 1 ');
			$dataSql = $this->_o_DB->fetchOne($sql);
			//echo $sql; die;
			return $dataSql?$dataSql->qty:0;
		}
		else
		{
			$sql = sprintf('select
							sp.IOID as SPIOID
							, sp.MaSanPham
							, sp.TenSanPham 
							, sp.MuaVao as ChonMuaHang
							, sp.SanXuat as ChonSanXuat
							, khsp.Ref_BOM as KHSPRefBOM
							## Sale orders
							, ifnull((select sum(ifnull(dsdbh.SoLuong,0))
							from ODSDonBanHang as dsdbh 
							inner join ODonBanHang as dbh
							on dsdbh.IFID_M505 = dbh.IFID_M505
							inner join qsiforms as qsi on qsi.IFID = dbh.IFID_M505 
							where dsdbh.Ref_MaSP = sp.IOID 
							and qsi.Status = %6$d
							and (khsp.Ref_ThuocTinh is null or khsp.Ref_ThuocTinh = dsdbh.Ref_ThuocTinh) 
							and  (dbh.NgayDatHang between %4$s and %5$s) 
							),0)
							as DatBan
							## Warehouse
							, ifnull((select sum(ifnull(k.SoLuongHC,0)) from OKho as k
							where k.Ref_MaSP = sp.IOID
							and (khsp.Ref_ThuocTinh is null or khsp.Ref_ThuocTinh = k.Ref_ThuocTinh) ),0) as TonKho
							## Incomming : Purchase orders
							, ifnull((select sum(ifnull(dsdmh.SoLuong,0)) from ODSDonMuaHang as dsdmh
							inner join ODonMuaHang as dmh on dmh.IFID_M401 = dsdmh.IFID_M401
							inner join qsiforms as qsi1 on qsi1.IFID = dsdmh.IFID_M401
							where dsdmh.Ref_MaSP = sp.IOID 
							and (khsp.Ref_ThuocTinh is null or khsp.Ref_ThuocTinh = dsdmh.Ref_ThuocTinh) 
							and (dmh.NgayDatHang between %4$s and %5$s)
							and qsi1.Status = %7$d),0) as DatMua
							## Incomming : Manufacturing
							, ifnull((select sum(ifnull(sx.SoLuong,0)) from OSanXuat as sx
							inner join qsiforms as qsi2 on qsi2.IFID = sx.IFID_M710
							where 
							(sx.NgayYeuCau between %4$s and %5$s) 
							and sx.MaSP = sp.MaSanPham 
							and qsi2.Status = %8$d),0) as DatSanXuat
							, ifnull(khsp.IOID,0) as KHSPIOID
							, ifnull(khsp.SLKeHoach,0) as SoLuongKeHoach
							, ctsp.TenCauThanhSanPham
							, ctsp.ThuocTinh as CTSPThuocTinh
							, khsp.ThuocTinh as KHSPThuocTinh
							, GROUP_CONCAT(khsp.ThuocTinh SEPARATOR \'^-^\') as ThuocTinhDaChon
							from OSanPham as sp
							## BOM 
							left join OCauThanhSanPham as ctsp on ctsp.Ref_MaSanPham = sp.IOID and ctsp.HoatDong = 1
							## Exists
							left join OKeHoachSanPham as khsp on IFID_M901 = %3$d and sp.IOID = khsp.Ref_MaSP 
							and (ctsp.IOID is null or ctsp.IOID = khsp.Ref_BOM)
							where sp.MuaVao = 1 or sp.SanXuat = 1 or sp.BanRa = 1
							group by  sp.IOID desc, khsp.Ref_ThuocTinh, ctsp.IOID
							order by sp.IOID desc, khsp.IOID desc
							limit %1$d, %2$d', 
			$start, $qty, $ifid,
			$this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate),
			$statusSO, $statusPO, $statusMO
			);
			//echo $sql; die;
			///echo '<pre>'; print_r( $this->_o_DB->fetchAll($sql) ); die;
			return $this->_o_DB->fetchAll($sql);
		}
	}
	
	/**
	 * Function: getPlanItems, lay tong so ban ghi
	 * Search: getPlanItems
	 * Place: /extra/mrp/primary/search
	 */
	
	// Remove
	function countPlanItems($startDate, $endDate, $statusSO = array('2') )
	{
		$sql = sprintf('select count(IOID) as TongSo from (select 
						  dsdbh.IOID
						  from ODSDonBanHang as dsdbh
						  inner join ODonBanHang as dbh on dsdbh.IFID_M505 = dbh.IFID_M505 
						  inner join qsiforms as qsi on qsi.IFID = dbh.IFID_M505
						  where qsi.Status in (%1$s)
						  and dbh.NgayYCNH between %2$s and %3$s
						  group by dsdbh.Ref_MaSP, dsdbh.Ref_ThuocTinh) as sale '
				, implode(',',$statusSO)
				, $this->_o_DB->quote($startDate)
				, $this->_o_DB->quote($endDate));
				//echo $sql; die;
		$dataSql = $this->_o_DB->fetchOne($sql);
		return $dataSql?$dataSql->TongSo:0;
	}
	/* End Function: getPlanItems, lay tong so ban ghi */	
	
	/**
	 * Function: getInventoryAndSaleOrder, Lay ton kho // @Remove
	 */
	
	function getInventory($mrpifid, $startDate, $endDate,$start, $perPage,  $statusSO = array('2'))
	{
		$sql = sprintf(' select 
						sp.IOID as SPIOID
						, sp.MaSanPham
						, sp.TenSanPham 
						, sp.MuaVao as ChonMuaHang
						, sp.SanXuat as ChonSanXuat
						, sum(ifnull(k.SoLuongHC,0)) as TonKho, sale.SPIOID 
						, sale.DatBan 
						, sale.DaChuyen 
						, sale.NhanLai
						, sale.Ref_ThuocTinh
						, sale.ThuocTinh
						, khsp.IOID as KHSPIOID
						, ifnull(khsp.SLKeHoach,0) as SoLuongKeHoach
						, khsp.Ref_BOM as KHSPRefBOM
						 from 
						 (select 
						 dsdbh.ThuocTinh , dsdbh.MaSP,
						 sum( ifnull(dsdbh.SoLuong,0) ) as DatBan,
						 sum(ifnull(dsch.SoLuong,0)) as DaChuyen,
						 sum(ifnull(dsth.SoLuong,0)) as NhanLai,
						 dsdbh.Ref_MaSP as SPIOID, dsdbh.Ref_ThuocTinh 
						  from ODSDonBanHang as dsdbh
						  inner join ODonBanHang as dbh on dsdbh.IFID_M505 = dbh.IFID_M505 
						  inner join qsiforms as qsi on qsi.IFID = dbh.IFID_M505 and qsi.Status in (%1$s)
						  left join ODanhSachXuatKho as dsch on dbh.IOID = dsch.Ref_SoDonHang 
						  and dsdbh.Ref_MaSP = dsch.Ref_MaSP and ifnull(dsdbh.Ref_ThuocTinh,0) = ifnull(dsch.Ref_ThuocTinh,0)
						  left join qsiforms as qsi2 on qsi2.IFID = dsch.IFID_M506 and qsi2.Status = 2
						  left join ODanhSachHangTL as dsth on dbh.IOID = dsth.Ref_SoDonHang
						  and dsdbh.Ref_MaSP = dsth.Ref_MaSP and ifnull(dsdbh.Ref_ThuocTinh,0) = ifnull(dsth.Ref_ThuocTinh,0)
						  left join qsiforms as qsi3 on qsi3.IFID = dsth.IFID_M507 and qsi3.Status = 2
						  where 
						  dbh.NgayYCNH between %2$s and %3$s
						  group by dsdbh.Ref_MaSP, dsdbh.Ref_ThuocTinh
						  ) as sale
						  left join OKho as k on k.Ref_MaSP = sale.SPIOID
						  and ( sale.Ref_ThuocTinh is null or (sale.Ref_ThuocTinh = k.Ref_ThuocTinh ))
						  left join OKeHoachSanPham as khsp on khsp.IFID_M901 = %4$d
						  and sale.SPIOID = khsp.Ref_MaSP 
						  and (sale.Ref_ThuocTinh is null or sale.Ref_ThuocTinh = khsp.Ref_ThuocTinh)
						  left join OSanPham as sp on sp.IOID = sale.SPIOID
						  group by sale.SPIOID, sale.Ref_ThuocTinh	
						  limit %5$d, %6$d
						  '
						  , implode(',',$statusSO)
				, $this->_o_DB->quote($startDate)
				, $this->_o_DB->quote($endDate)
				, $mrpifid
				,$start, $perPage
				);
				//echo $sql; die;
				return $this->_o_DB->fetchall($sql);
	}
	/* End Function: getInventory, Lay ton kho // @Remove*/
	
	
	function getPurchase($startDate, $endDate,$start, $perPage, $statusSO = array('2'), $statusPO = array('2'))
	{
		$sql = sprintf(' select sum(ifnull(dsdmh.SoLuong,0)) as DatMua
						, sum( ifnull(dsnh.SoLuong,0)) as DaNhan
						, sum( ifnull(dsth.SoLuong,0)) as DaTra
						 from 
						 (select 
						 sum( ifnull(dsdbh.SoLuong,0) ) as DatBan,
						 dsdbh.Ref_MaSP as SPIOID, dsdbh.Ref_ThuocTinh 
						  from ODSDonBanHang as dsdbh
						  inner join ODonBanHang as dbh on dsdbh.IFID_M505 = dbh.IFID_M505 
						  inner join qsiforms as qsi on qsi.IFID = dbh.IFID_M505
						  where qsi.Status in (%3$s)
						  and dbh.NgayYCNH between %1$s and %2$s
						  group by dsdbh.Ref_MaSP, dsdbh.Ref_ThuocTinh
						  ) as sale
						  left join ODSDonMuaHang as dsdmh on dsdmh.Ref_MaSP = sale.SPIOID
						  and ( ifnull(sale.Ref_ThuocTinh,0) = ifnull(dsdmh.Ref_ThuocTinh,0) )
						  left join ODonMuaHang as dmh on dmh.IFID_M401 = dsdmh.IFID_M401
						  and (dmh.NgayYCNH between %1$s and %2$s)		
						  left join qsiforms as qsi1 on qsi1.IFID = dmh.IFID_M401 and qsi1.Status in (%4$s) 
						  left join ODanhSachNhapKho as dsnh on dmh.IOID = dsnh.Ref_SoDonHang
						  and dsnh.Ref_MaSanPham = dsdmh.Ref_MaSP 
						  and ( ifnull(dsdmh.Ref_ThuocTinh,0) = ifnull(dsnh.Ref_ThuocTinh,0) )
						  left join qsiforms as qsi2 on qsi2.IFID = dsnh.IFID_M402 and qsi2.Status = 2
						  left join ODanhSachTraHang as dsth on  dmh.IOID = dsth.Ref_SoDonHang
						  and dsth.Ref_MaSP = dsdmh.Ref_MaSP 
						  and ( ifnull(dsdmh.Ref_ThuocTinh,0) = ifnull(dsth.Ref_ThuocTinh,0) )
						  left join qsiforms as qsi3 on qsi3.IFID = dsth.IFID_M403 and qsi3.Status = 2
						  group by sale.SPIOID, sale.Ref_ThuocTinh	
						  limit %5$d, %6$d
						  '
				, $this->_o_DB->quote($startDate)
				, $this->_o_DB->quote($endDate)
				, implode(',',$statusSO)
				, implode(',',$statusPO)
				,$start, $perPage);
				//echo $sql; die;
				return $this->_o_DB->fetchall($sql);
	}
	
	// @Remove
	function getManufacturing($startDate, $endDate,$start, $perPage,$statusSO = array('2'), $statusMO = array('2'))
	{
		$sql = sprintf(' select sum(ifnull(sx.SoLuong,0)) as DatSanXuat 
						, ctsp.TenCauThanhSanPham, ctsp.IOID as CTSPIOID
						, ctsp.ThuocTinh as CTSPThuocTinh
						
						 from 
						 (select 
						 sum( ifnull(dsdbh.SoLuong,0) ) as DatBan,
						 dsdbh.Ref_MaSP as SPIOID, dsdbh.Ref_ThuocTinh 
						  from ODSDonBanHang as dsdbh
						  inner join ODonBanHang as dbh on dsdbh.IFID_M505 = dbh.IFID_M505 
						  inner join qsiforms as qsi on qsi.IFID = dbh.IFID_M505
						  where qsi.Status in (%3$s)
						  and dbh.NgayYCNH between %1$s and %2$s
						  group by dsdbh.Ref_MaSP, dsdbh.Ref_ThuocTinh
						  ) as sale
						  left join OCauThanhSanPham as ctsp on ctsp.Ref_MaSanPham = sale.SPIOID
						  and ctsp.HoatDong = 1
						  and ( ifnull(sale.Ref_ThuocTinh ,0) = ifnull(ctsp.Ref_ThuocTinh,0) )
						  left join OSanXuat as sx on sx.Ref_MaSP = ctsp.IOID 
						  and sx.NgayYeuCau between %1$s and %2$s
						  left join qsiforms as qsi1 on sx.IFID_M710 = qsi1.IFID 
						  and qsi1.Status in (%4$s) 
						  group by sale.SPIOID, sale.Ref_ThuocTinh	
						  limit %5$d, %6$d
						  '
				, $this->_o_DB->quote($startDate)
				, $this->_o_DB->quote($endDate)
				, implode(',',$statusSO)
				, implode(',',$statusMO)
				,$start, $perPage);
				
				//echo $sql; die;
				return $this->_o_DB->fetchall($sql);
	}
	
	/* Kiểm tra xem trong tất cả thuộc tính của sản phẩm, có thuộc tính nào bắt buộc hay không?*/
	public function checkAttributeRequires($itemIOID, $refAttribute)
	{
		$sql = sprintf('SELECT 1
		FROM OThuocTinhSanPham as ttsp
		INNER JOIN OSanPham as sp
		ON ttsp.IFID_M113 = sp.IFID_M113
		WHERE sp.IOID = %1$d
		AND ttsp.Ref_ThuocTinh = %2$d
		AND ttsp.HoatDong = 1
		AND ttsp.BatBuoc = 1
		LIMIT 1
		', $itemIOID, $refAttribute);
		//echo $sql;die;
		if($this->_o_DB->fetchOne($sql))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function getChildrenMrp($refBomArr)
	{
		// , tpsp.SoLuong as SoLuongYeuCau # su dung cho lap dat
		$sql = sprintf(' select
                                ctsp.IOID as CauThanhIOID
                                , ifnull(ctsp.Ref_MaSanPham,0) as RefSanPham
                                , ifnull(ctsp.Ref_ThuocTinh,0) as RefThuocTinh
                                , ifnull(ctsp.ThaoRoLapDat,0) as ParentAssembly
                                , ctsp.SoLuong as SoLuongCha
                                , ifnull(ctsp.TatCaThuocTinh,0) as AllAttributesParent
                                , tpsp.MaThanhPhan  as MaThanhPhan
                                , ifnull(tpsp.Ref_MaThanhPhan,0)   as RefThanhPhan
                                , tpsp.TenThanhPhan   as TenThanhPhan
                                , tpsp.ThuocTinh   as ThuocTinhThanhPhan
                                , ifnull(tpsp.Ref_ThuocTinh,0)   as RefThuocTinhThanhPhan
                                , tpsp.DonViTinh   as DonViTinhThanhPhan
                                , sp.MuaVao   as MuaVaoThanhPhan
                                , sp.SanXuat   as SanXuatThanhPhan
                                , tpsp.Ref_CongDoan as CongDoanThanhPhan
                                , tpsp.SoLuong as SoLuongThanhPhan
                                , ctsp2.TenCauThanhSanPham as CauThanhThanhPhan
                                , ifnull(ctsp2.TatCaThuocTinh,0) as AllAttributesChildren
                                , ifnull(ctsp2.IOID,0) as RefCauThanhThanhPhan
                                , ifnull(ctsp2.ThaoRoLapDat,0) as ChildAssembly

                                from OCauThanhSanPham as ctsp 
                                left join OThanhPhanSanPham as tpsp on ifnull(ctsp.ThaoRoLapDat,0) != 1
                                        and tpsp.IFID_M114 = ctsp.IFID_M114
                                left join OCongDoanBOM as cd on cd.Ref_Ten = tpsp.Ref_CongDoan
                                        and cd.IFID_M114 = ctsp.IFID_M114
                                left join OSanPham as sp on tpsp.Ref_MaThanhPhan = sp.IOID
                                left join OCauThanhSanPham as ctsp2 on tpsp.IFID_M114 = ctsp.IFID_M114
                                        and ctsp2.Ref_MaSanPham = tpsp.Ref_MaThanhPhan
                                        and ifnull(ctsp2.HoatDong, 0) = 1
                                        and (
                                        (ifnull(ctsp2.Ref_ThuocTinh,0) = ifnull(tpsp.Ref_ThuocTinh,0))
                                        or
                                        (ifnull(ctsp2.TatCaThuocTinh, 0) = 1) 
                                )
                            left join OSanPham as sp1 on sp1.IOID = ctsp2.Ref_MaSanPham
                                where ifnull(ctsp.HoatDong, 0) = 1 and ifnull(tpsp.BanTP, 0) != 1
                                ');
		$start  = true;
		$tmp    = '';
		foreach ($refBomArr as $item)
		{
			$tmp .= (($start)?($start = false):' or ')
			.sprintf(' (ctsp.IOID = %1$d) ',  $item);
		}
		$sql .= ($tmp)?' and '.$tmp:'';
		//$sql .= ' group by k.Ref_MaSP, k.Ref_ThuocTinh ';
		$sql .= sprintf(' order by ctsp.IOID, tpsp.IOID, cd.STT,  ctsp2.TatCaThuocTinh ');
		//echo $sql; die;
		return ($tmp)?$this->_o_DB->fetchAll($sql):array();
	}

	// $aItemAndBOM = array( 0=>array(item=>number, BOM=>number)  )
	public function getManufacturingLineByItemAndBOM($aItemAndBOM = array())
	{
		$tmp = '';
		$sql = sprintf('select
						dc.IOID as DCIOID,
						dc.MaDayChuyen,
						dc.TenDayChuyen,
						dc.Ref_LichLamViec as RefLich,
						spcdc.SoLuongTrenGio,
						spcdc.Ref_MaSanPham as RefSanPham,
						ifnull(spcdc.Ref_CauThanhSanPham,0) as RefCauThanh,
						c.MaCa as Ca,
						c.Ref_MaCa as RefCa
						from ODayChuyen as dc
						inner join OSanPhamCuaDayChuyen as spcdc
						on spcdc.IFID_M702 = dc.IFID_M702
						left join OCaDayChuyen as c
						on c.IFID_M702 = dc.IFID_M702
						');
		foreach ($aItemAndBOM as $ele)
		{
			$tmp .= (($tmp)?' or ':'').sprintf(' ( spcdc.Ref_MaSanPham = %1$d
			and  ', $ele['item']);
			$tmp .= ($ele['BOM'])?
			sprintf(' spcdc.Ref_CauThanhSanPham = %1$d ) ' , $ele['BOM']):
			sprintf(' ( spcdc.Ref_CauThanhSanPham = %1$d or spcdc.Ref_CauThanhSanPham is null) )' , $ele['BOM']);
		}
		$sql .= ($tmp)?sprintf(' where %1$s ',$tmp):'';
		$sql .= ' order by dc.IOID, spcdc.Ref_MaSanPham, spcdc.Ref_CauThanhSanPham, c.IOID ';
		//echo $sql;die;
		return ($tmp)?$this->_o_DB->fetchAll($sql):array();
	}

	/**
	 * Function: Kiểm tra mã lệnh sản xuất đã tồn tại hay chưa?
	 *
	 */
	public function checkManufacturingDocumentNoExists($documentNo)
	{
		$sql = sprintf('select 1 from OSanXuat where MaLenhSX = %1$s', $this->_o_DB->quote($documentNo) );
		$dataSql = $this->_o_DB->fetchOne($sql);
		return $dataSql?true:false;
	}
	/* End Function: Kiểm tra mã lệnh sản xuất đã tồn tại hay chưa?*/

	public function checkPurchaseDocumentNoExists($documentNo)
	{
		$sql = sprintf('select 1 from ODonMuaHang where SoDonHang = %1$s', $this->_o_DB->quote($documentNo) );
		$dataSql = $this->_o_DB->fetchOne($sql);
		return $dataSql?true:false;
	}

	public function getPurchaseOrdersByPlan($ifid, $getMain = 0)
	{
		$sql = sprintf('select
						dmh.*, dsdmh.*,ifnull(dsdmh.Ref_ThuocTinh,0) as Ref_ThuocTinh,
						khmh.IOID as KHIOID, dmh.IFID_M401 as OIFIDX,
						dmh.IOID as OIOID, qsif.Status  
						from OKeHoachCungUng as khcu
						inner join qsioidlink as link on khcu.IOID = link.FromIOID AND khcu.IFID_M901 = link.FromIFID
						inner join ODonMuaHang as dmh on link.ToIOID = dmh.IOID  AND  link.ToIFID = dmh.IFID_M401
						inner join qsiforms as qsif on qsif.IFID = dmh.IFID_M401
						left join ODSDonMuaHang as dsdmh on dmh.IFID_M401 = dsdmh.IFID_M401
						left join qsioidlink as link2 on link2.FromIOID = dsdmh.IOID
						left join OKeHoachMuaHang as khmh on khmh.IOID = link2.FromIOID
						where khcu.IFID_M901 = %1$d
						', $ifid);
		if($getMain === 1)
		{
			$sql .= ' group by dmh.IOID ';
		}
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}

	public function getPurchaseDeleteIOIDLink($purchaseifid, $planifid)
	{
		$sql = sprintf('select qsio.*
						from ODonMuaHang as dmh
						inner join ODSDonMuaHang as dsdmh
						on dmh.IFID_M401 = dsdmh.IFID_M401
						inner join qsioidlink as qsio
						on dsdmh.IOID = qsio.ToIOID AND dsdmh.IFID_M401 = qsio.ToIFID
						inner join OKeHoachMuaHang as khmh
						on khmh.IOID = qsio.FromIOID AND khmh.IFID_M901 = qsio.FromIFID
						where 
						dmh.IFID_M401 = %1$d
						and khmh.IFID_M901 = %2$d', $purchaseifid, $planifid);
		return $this->_o_DB->fetchAll($sql);
	}

	public function getIOIDLinkFromToIOID($toIOID)
	{
		$sql = sprintf('select * from qsioidlink where ToIOID = %1$d', $toIOID);
		return $this->_o_DB->fetchOne($sql);
	}
	
        /**
         * 
         * @param number $mrpIFID
         * @return object chi tiet yeu cau
         */
	public function getRequireDetail($mrpIFID)
	{
		$sql = sprintf('select 
					
                                                khgh.*,khgh.IOID as KHIOID, khgh.Ref_MaSP, khgh.MaSP
                                                , khgh.NgayKetThuc as NgayXuatHang
                                                , khgh.SoLuong as SoLuong, khgh.ThuocTinh, khgh.Ref_ThuocTinh
                                                , khgh.DonViTinh as DonViTinh
                                                , cd.Ten as Operation, cd.Ref_Ten as RefOperation, ifnull(cd.SoGio, 0) as OperationTime
                                                , ctsp.SoLuong as BOMQty,  ifnull(ctsp.TatCaThuocTinh,0) as AllAttributes
                                                , ctsp.TenCauThanhSanPham as BomName, ctsp.IOID as BomID
                                                , ifnull(ctsp.ThaoRoLapDat,0) as Assembly
                                                , sp.MuaVao, sp.SanXuat
                                                from OKeHoachCungUng  as khcu
                                                inner join OYeuCauSanXuat as dbh on khcu.Ref_SoDonHang = dbh.IOID
                                                inner join OChiTietYeuCau as khgh on dbh.IFID_M764 = khgh.IFID_M764
                                                left join OSanPham as sp on sp.IOID = khgh.Ref_MaSP
                                                left join OCauThanhSanPham as ctsp on 
                                                ifnull(ctsp.HoatDong,0) = 1
                                                and ctsp.Ref_MaSanPham = khgh.Ref_MaSP
                                                and (
                                                      (ifnull(ctsp.Ref_ThuocTinh,0) = ifnull(khgh.Ref_ThuocTinh,0))
                                                      or
                                                      (ifnull(ctsp.TatCaThuocTinh, 0) = 1) 
                                                )
                                                left join OCongDoanBOM as cd on cd.IFID_M114 = ctsp.IFID_M114
                                                where khcu.IFID_M901 = %1$d
                                                and sp.SanXuat = 1
                                                order by khgh.NgayKetThuc, khgh.Ref_MaSP, 
                                                khgh.Ref_ThuocTinh, ctsp.IOID, cd.STT', $mrpIFID);
                //echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}

        /**
         * @param array(id) $refBOMArr
         * @return object, all operations from BOMs
         */
        public function getOperationsByBOM($refBOMArr) {
            
            $sql = sprintf('select cd.*
                    from OCauThanhSanPham as ct
                    inner join OCongDoanBOM as cd on ct.IFID_M114 = cd.IFID_M114
                    where ct.IOID in (%1$s) 
                    group by cd.Ref_Ten', implode(' , ', $refBOMArr));
            return $this->_o_DB->fetchAll($sql);
        }
        
	public function getKhaNangSanXuat($refOperationArr)
	{
		$tmp = '';
		foreach ($refOperationArr as $item)
		{
			$tmp .= $tmp?' or ':'';
			$tmp .= sprintf(' cddc.Ref_CongDoan = %1$d ', $item);
		}
		$sql = sprintf('select *, dc.IOID as DCIOID
						from OCongDoanDayChuyen as cddc
						inner join ODayChuyen as dc on cddc.IFID_M702 = dc.IFID_M702
						where %1$s
						order by dc.IOID, cddc.Ref_CongDoan', $tmp);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getDaNenKeHoach($refOperationArr, $startDate, $endDate)
	{
		$tmp = '';
		foreach ($refOperationArr as $item)
		{
			$tmp .= $tmp?' or ':'';
			$tmp .= sprintf(' cddc.Ref_CongDoan = %1$d ', $item);
		}
		$sql = sprintf('select *, cdb.Ref_Ten as RefOperation, ifnull(cddc.HieuSuat,0) as Performance
						, cdb.SoGio as Time
						from OKeHoachSanXuat as khsx
						inner join OKeHoachCungUng as khcu on khsx.IFID_M901 = khcu.IFID_M901
						inner join OCauThanhSanPham as ctsp on ctsp.IOID = khsx.Ref_ThietKe
						inner join OCongDoanBOM as cdb on ctsp.IFID_M114 = cdb.IFID_M114
						inner join ODayChuyen as dc on dc.IOID = khsx.Ref_DayChuyen
						left join OCongDoanDayChuyen as cddc on cddc.IFID_M702 = dc.IFID_M702
						and cdb.Ref_Ten = cddc.Ref_CongDoan
						where (khcu.NgayBatDau <= %1$s and khcu.NgayKetThuc >= %2$s)
						order by dc.IOID, cdb.Ref_Ten'
						, $this->_o_DB->quote($startDate)
						, $this->_o_DB->quote($endDate)
						);
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getInventoryOfMrpItem($mrpIFID)
	{
		$sql = sprintf('select sum(ifnull(k.SoLuongHC,0)) as Inventory, khgh.Ref_MaSP as RefItem
						, ifnull(khgh.Ref_ThuocTinh,0) as RefAttr 
						from OKeHoachGiaoHang as khgh
						inner join ODonBanHang as dbh on khgh.IFID_M505 = dbh.IFID_M505
						inner join OKeHoachCungUng as khcu on khcu.Ref_SoDonHang = dbh.IOID
						inner join OKho as k on k.Ref_MaSP = khgh.Ref_MaSP
						and ifnull(k.Ref_ThuocTinh,0) = ifnull(khgh.Ref_ThuocTinh,0)
						where khcu.IFID_M901 = %1$d
						group by khgh.Ref_MaSP,khgh.Ref_ThuocTinh', $mrpIFID);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getTonKhoBanDau()
	{
		$sql = sprintf('select Sum(ifnull(SoLuongHC,0)) as SoLuong 
						, Ref_MaSP
						, ifnull(Ref_ThuocTinh, 0) as Ref_ThuocTinh
						from OKho
						group by Ref_MaSP, Ref_ThuocTinh');
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getStockUsedFromPurchasePlan($mrpIFID)
	{
		$sql = sprintf('select sum(ifnull(khmh.KhauTruKho, 0)) as KhauTruKho, 
						khmh.Ref_MaSP, 
						ifnull(khmh.Ref_ThuocTinh,0) as Ref_ThuocTinh
						from OKeHoachMuaHang as khmh
						where khmh.IFID_M901 <> %1$d and khmh.Ngay >= CURDATE()
						group by Ref_MaSP, Ref_ThuocTinh', $mrpIFID);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);		
	}
	
	public function getManufacturingOfChild($itemArray, $startDate)
	{
		$tmp = '';
		$sql = sprintf('select ctsx.Ref_MaSP, ifnull(ctsx.Ref_ThuocTinh,0) as Ref_ThuocTinh, sum(ctsx.SoLuong) as SoLuong 
						from OChiTietSanXuat as ctsx
						inner join OSanXuat as sx on ctsx.IFID_M710 = sx.IFID_M710
						inner join qsiforms as forms on forms.IFID = sx.IFID_M710
						where 
						forms.Status in (2)
						and sx.NgayYeuCau >= %1$s
						', $this->_o_DB->quote($startDate));
		foreach ($itemArray as $item)
		{
			$tmp .= $tmp?' or ':'';
			$tmp .= sprintf(' (ctsx.Ref_MaSP = %1$d and ifnull(ctsx.Ref_ThuocTinh,0) = %2$d )'
							, $item['Item'], $item['Attr']);
		}
		$sql .= " and ($tmp) ";
		$sql .= ' group by ctsx.Ref_MaSP, ctsx.Ref_ThuocTinh ';
		return (count($itemArray))?$this->_o_DB->fetchAll($sql):array();	
	}
	
	/// @Note: Hàm này cần đổi tên vì nó còn được dùng cho lấy sản phẩm cũ của primary trong mrp
	public function getInventoryOfChild($itemArray)
	{
		$tmp = '';
		$sql = sprintf('select Ref_MaSP, ifnull(Ref_ThuocTinh,0) as Ref_ThuocTinh, sum(SoLuongHC) as SoLuong 
						from OKho as k
						where ');
		foreach ($itemArray as $item)
		{
			$tmp .= $tmp?' or ':'';
			$tmp .= sprintf(' (k.Ref_MaSP = %1$d and ifnull(Ref_ThuocTinh,0) = %2$d )'
							, $item['Item'], $item['Attr']);
		}
		$sql .= $tmp;
		$sql .= ' group by Ref_MaSP, Ref_ThuocTinh ';
		return (count($itemArray))?$this->_o_DB->fetchAll($sql):array();
	}
	
	public function getPurchaseOfChild($itemArray, $startDate)
	{
		$tmp = '';
		$sql = sprintf('select dsdmh.Ref_MaSP, ifnull(dsdmh.Ref_ThuocTinh,0) as Ref_ThuocTinh, sum(dsdmh.SoLuong) as SoLuong 
						from ODSDonMuaHang as dsdmh
						inner join ODonMuaHang as dmh on dsdmh.IFID_M401 = dmh.IFID_M401
						inner join qsiforms as forms on forms.IFID = dmh.IFID_M401
						where 
						forms.Status in (2)
						and dmh.NgayDatHang >= %1$s
						', $this->_o_DB->quote($startDate));
		foreach ($itemArray as $item)
		{
			$tmp .= $tmp?' or ':'';
			$tmp .= sprintf(' (dsdmh.Ref_MaSP = %1$d and ifnull(dsdmh.Ref_ThuocTinh,0) = %2$d )'
							, $item['Item'], $item['Attr']);
		}
		$sql .= " and ($tmp) ";
		$sql .= ' group by dsdmh.Ref_MaSP, dsdmh.Ref_ThuocTinh ';
		//echo $sql; die;
		return (count($itemArray))?$this->_o_DB->fetchAll($sql):array();	
	}
	
	public function getOldGeneralPlan($mrpIFID, $receivePlanIOID)
	{
		$receivePlanIOID[] = 0;
		$sql = sprintf('select khsp.*, khgh.NgayXuatHang , khgh.NgayGiaoHang
						from OKeHoachGiaoHang as khgh
						inner join qsioidlink as link on khgh.IOID = link.FromIOID AND khgh.IFID_M403 = link.FromIFID
						inner join OKeHoachSanPham as khsp on khsp.IOID = link.ToIOID AND khsp.IFID_M901 = link.ToIFID
						where
						khsp.IFID_M901 = %2$d 
						and khgh.IOID in (%1$s)
						order by khsp.No, khsp.Level', implode(',', $receivePlanIOID), $mrpIFID);
		return $this->_o_DB->fetchAll($sql);
	}

	// Remove
	// Lay cac san pham trong kh sp tat ca level
	public function getAllLevelFromItemPlan($mrpIFID) 
	{
		$sql = sprintf('select khsp.*, ctsp.SoLuongToiThieu, ctsp.SoLuong as SoLuongCauThanh, sum(cd.SoGio) as SoGio 
						from OKeHoachSanPham as khsp
						inner join OCauThanhSanPham as ctsp on khsp.Ref_BOM = ctsp.IOID
						left join OCongDoanBOM as cd on ctsp.IFID_M114 = cd.IFID_M114
						where khsp.IFID_M901 = %1$d 
						group by cd.IFID_M114
						order by khsp.No, khsp.Level DESC, khsp.IOID DESC', $mrpIFID);
		return $this->_o_DB->fetchAll($sql);		
	}
		
	// Lay ra lich lam viec cua cac day chuyen 
	public function getCalendarsByItemsOfLine($mrpIFID )
	{
		// testing
		// , dc.MaDayChuyen
		// , cddc.CongDoan
		// , cddc.MaDonViThucHien
		$sql = sprintf('select 
						dc.Ref_LichLamViec
						, dc.IOID as DCIOID
						, cddc.Ref_CongDoan as CDIOID
						, cddc.Ref_MaDonViThucHien as DVIOID
						, cddc.HieuSuat
						from OKeHoachSanPham as khsp 
						inner join OSanPhamCuaDayChuyen as spdc on khsp.Ref_MaSP = spdc.Ref_MaSanPham
						inner join ODayChuyen as dc on dc.IFID_M702 = spdc.IFID_M702
						inner join OCongDoanDayChuyen as cddc on dc.IFID_M702 = cddc.IFID_M702
						where khsp.IFID_M901 = %1$d
						#group by dc.IOID', $mrpIFID);
		return $this->_o_DB->fetchAll($sql);
	}
	
	// Thoi gian da dat san xuat cua cac xu ly don hang khac
	public function getDaLay($mrpIFID, $startDate)
	{
		$sql = sprintf('select khsx.*
						, cddc.Ref_CongDoan as RefOperation
						, cddc.HieuSuat as Performance
						, cd.Ref_Ten as RefBomOperation
						, ifnull(cd.SoGio,0) as SoGio
						, (TIME_TO_SEC(ifnull(TIMEDIFF(khsx.GioKetThuc, khsx.GioBatDau),0))/3600) as SoGioKeHoach
						, ctsp.SoLuong as SoLuongBom
						from OKeHoachSanXuat as khsx
						inner join ODayChuyen as dc on khsx.Ref_DayChuyen = dc.IOID
						inner join OCongDoanDayChuyen as cddc on cddc.IOID = khsx.Ref_CongDoan 
							and dc.IFID_M702 = cddc.IFID_M702
						inner join OCauThanhSanPham as ctsp on khsx.Ref_ThietKe = ctsp.IOID
						left join OCongDoanBOM as cd on ctsp.IFID_M114 = cd.IFID_M114
							and cd.Ref_Ten = cddc.Ref_CongDoan
						where khsx.Ngay >= %1$s and khsx.IFID_M901 != %2$d
						#group by khsx.IOID, cddc.IOID', $this->_o_DB->quote($startDate), $mrpIFID);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}

	// Luu y doan nay 
	public function getGeneralPlanForDetail($mrpIFID)
	{
		//MaSanPham	Ref_MaSanPham ThuocTinh	Ref_ThuocTinh
		$sql = sprintf('select khsp.*, khsp.IOID as KHSPIOID, khsp.DonViTinh as DonViKH
						, ctsp.SoLuong as SoLuongBOM, ctsp.SoLuongToiThieu, ifnull(ctsp.ThaoRoLapDat,0) as ThaoRoLapDat
						, ctsp.MaSanPham as MainItem, ctsp.Ref_MaSanPham as RefMainItem, ctsp.ThuocTinh as MainAttribute
						, ifnull(ctsp.Ref_ThuocTinh, 0) as RefMainAttribute
						, cd.Ref_Ten as RefOperation, ifnull(cd.SoGio, 0) as SoGioCongDoan, cd.Ten as Operation
						, ifnull(cd.STT, 0) as OperationLevel
						, tpsp.*, tpsp.SoLuong as SoLuongThanhPhan
						, tpsp.CongDoan as CongDoanThanhPhan, tpsp.ThuocTinh as ThuocTinhThanhPhan
						, tpsp.BanTP as BanThanhPham, tpsp.Ref_CongDoan as Ref_CongDoanThanhPhan
						, ifnull(tpsp.Ref_ThuocTinh, 0) as RefThuocTinhThanhPhan
						, tpsp.DonViTinh as DonViThanhPhan, tpsp.SoLuong as SoLuongThanhPhan
						, sp.SanXuat as ChinhSanXuat, sp.MuaVao as ChinhMuaHang
						, sp2.SanXuat as ThanhPhanSanXuat, sp2.MuaVao as ThanhPhanMuaHang
						, ifnull(sp2.IOID,0) as CoThanhPhan 
						from OKeHoachSanPham as khsp 
						inner join OSanPham as sp on khsp.Ref_MaSP = sp.IOID
						inner join OCauThanhSanPham as ctsp on khsp.Ref_BOM = ctsp.IOID
						inner join OCongDoanBOM as cd on ctsp.IFID_M114 = cd.IFID_M114
						left join OThanhPhanSanPham as tpsp on ctsp.IFID_M114 = tpsp.IFID_M114
							and tpsp.Ref_CongDoan = cd.Ref_Ten
						left join OSanPham as sp2 on tpsp.Ref_MaThanhPhan = sp2.IOID
						and (ifnull(tpsp.Ref_CongDoan,0) = 0 or cd.Ref_Ten = tpsp.Ref_CongDoan)
						where khsp.IFID_M901 = %1$d 
						#order by khsp.No, khsp.Level, khsp.IOID, cd.STT
						order by khsp.Level DESC, khsp.No ASC, khsp.IOID, ctsp.IOID, cd.STT
						#order by  khsp.Level DESC, khsp.No ASC, khsp.IOID, ctsp.IOID, cd.STT', $mrpIFID);
		//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getGeneralPlanOutputItemsForDetail($mrpIFID)
	{
		$sql = sprintf('select  
						ifnull(sp2.IOID, 0) as HasOutput, ctsp.IOID as RefBOM, cd.Ref_Ten as RefOperation
						, spdr.Ref_MaSP as RefOutputItem, ifnull(spdr.Ref_ThuocTinh, 0) as RefOutputAttribute
						, spdr.MaSP as ItemCode, spdr.TenSP as ItemName
						, spdr.ThuocTinh as Attribute, spdr.SoLuong as Qty
						, spdr.BatDauTruoc as QtyToNext, spdr.DonViTinh as UOM
						from OKeHoachSanPham as khsp
						inner join OCauThanhSanPham as ctsp on khsp.Ref_BOM = ctsp.IOID
						inner join OCongDoanBOM as cd on ctsp.IFID_M114 = cd.IFID_M114
						left join OSanPhamDauRa as spdr on ctsp.IFID_M114 = spdr.IFID_M114
							and spdr.Ref_CongDoan = cd.Ref_Ten
						left join OSanPham as sp2 on spdr.Ref_MaSP = sp2.IOID
						and (ifnull(spdr.Ref_CongDoan,0) = 0 or cd.Ref_Ten = spdr.Ref_CongDoan)
						where khsp.IFID_M901 = %1$d
						order by khsp.Level DESC, khsp.No ASC, khsp.IOID, ctsp.IOID, cd.STT
						#order by  khsp.Level DESC, khsp.No ASC, khsp.IOID, ctsp.IOID, cd.STT', $mrpIFID);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);	
	}
	
		public function getInventoryByRangeTime($itemArr, $start, $end) // Thuc te
		{
			$tempSql = '';
			foreach ($itemArr as $item)
			{
				$tempSql .= $tempSql?' or ':'';	
				$tempSql .= sprintf(' gdk.Ref_MaSanPham = %1$d', $item['ID']);
			}
			$tempSql = $tempSql?sprintf(' and (%1$s) ', $tempSql):''; 
			
			$sql = sprintf('select sum(
							case when NhapXuat = 1 then TongHC + gdk.SoLuong
							else TongHC - gdk.SoLuong end ) as TongSo, gdk.Ngay as Date
							, gdk.Ref_MaSanPham as RefItem
							from OGiaoDichKho as gdk
							where 1=1 %1$s
							and (gdk.Ngay between %2$s and %3$s) 
							group by gdk.Ref_MaSanPham, gdk.Ngay'
							, $tempSql, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
			return $this->_o_DB->fetchAll($sql);
		}
		
		public function getProductionPlanForMonitoring($itemArr, $start, $end) // Ke hoach
		{
                                        // @todo: khsx.Ngay da chuyen thanh tungay den ngay , hien tai thay tam = TuNgay
                    // can chinh lai
			$tempSql = '';
			foreach ($itemArr as $item)
			{
				$tempSql .= $tempSql?' or ':'';	
				$tempSql .= sprintf(' khsx.Ref_MaSP = %1$d', $item['ID']);
			}
			$tempSql = $tempSql?sprintf(' and (%1$s) ', $tempSql):''; 
			
			$sql = sprintf('select sum(khsx.SoLuong) as TongSo, khsx.TuNgay as Date
                                        , khsx.Ref_MaSP as RefItem, dvt.HeSoQuyDoi as Rate
                                        from OKeHoachSanXuat as khsx
                                        inner join OSanPham as sp on khsx.Ref_MaSP = sp.IOID
                                        inner join ODonViTinhSP as dvt on sp.IFID_M113 = dvt.IFID_M113
                                            and dvt.MacDinh = 1
                                        where (khsx.TuNgay  between %2$s and %3$s) %1$s
                                        group by khsx.Ref_MaSP, khsx.TuNgay'
							, $tempSql, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
			return $this->_o_DB->fetchAll($sql);			
		}
		
		public function getPurchasePlanForMonitoring($itemArr, $start, $end) // Ke hoach
		{
			$tempSql = '';
			foreach ($itemArr as $item)
			{
				$tempSql .= $tempSql?' or ':'';	
				$tempSql .= sprintf(' khmh.Ref_MaSP = %1$d', $item['ID']);
			}
			$tempSql = $tempSql?sprintf(' and (%1$s) ', $tempSql):''; 
			
			$sql = sprintf('select sum(khmh.SoLuong)  as TongSo, 
                                        khmh.Ngay as Date, khmh.Ref_MaSP as RefItem, dvt.HeSoQuyDoi as Rate
                                        from OKeHoachMuaHang as khmh
                                        inner join OSanPham as sp on khmh.Ref_MaSP = sp.IOID
                                        inner join ODonViTinhSP as dvt on sp.IFID_M113 = dvt.IFID_M113
                                            and dvt.MacDinh = 1
                                        where (khmh.Ngay  between %2$s and %3$s) %1$s
                                        group by khmh.Ref_MaSP, khmh.Ngay'
							, $tempSql, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
			return $this->_o_DB->fetchAll($sql);			
		}		

		
	// Lay dong chinh cua ke hoach giao hang 
	public function getRequirementInfo($module, $count = false, $pagination = array('page'=>1,'display'=>10))
	{
		// Dem so luong ban ghi
		$sql = '';
		if($count)
		{
			switch ($module) {
				case 'M505':
					$sql = sprintf('select count(1) as NumberOfRecord
									from ODonBanHang as dbh
									inner join qsiforms as forms on dbh.IFID_M505 = forms.IFID 
									where forms.Status = 1 or forms.Status = 2
									limit 1');
				break;
			}

			$dataSql = $this->_o_DB->fetchOne($sql);
			return $dataSql?$dataSql->NumberOfRecord:0;
		} // Lay ban ghi
		else
		{
			$startPosition = ($pagination['page'] - 1) * $pagination['display'];
			switch ($module) {
				case 'M505':
					$sql = sprintf('select dbh.IFID_M505 as ID, dbh.SoDonHang as DocumentNo
									, dbh.NgayDatHang as BeginDate
									from ODonBanHang as dbh
									inner join qsiforms as forms on dbh.IFID_M505 = forms.IFID 
									where forms.Status = 1 or forms.Status = 2
									order by dbh.SoDonHang
									limit %1$d, %2$d', $startPosition, $pagination['display']);
				break;
			}
			return $this->_o_DB->fetchAll($sql);
		}
	}
	
	private function getConditionWithColumnAndKey($keyArr, $column)
	{
		$retval = '';
		foreach ($keyArr as $key) 
		{
			$retval .= $retval?' or ':'';
			$retval .= sprintf(' %1$s = %2$d ', $column, $key);
		}
		$retval = $retval?sprintf(' (%1$s ) ', $retval):'  1 = 0 ';
		return $retval;
	}
	
	// Lay chi tiet yeu cau cung ung theo don hang
	public function getRequirementDetail($module, $orderKeyArr)
	{
		switch ($module) 
		{
			case 'M505':
				$sql = sprintf('select khgh.IFID_M505 as ID, khgh.IOID as IOID
							 	, khgh.MaSP as ItemCode, khgh.TenSP as ItemName, khgh.ThuocTinh as Attribute
							 	, khgh.DonViTinh as ItemUOM, khgh.SoLuong as ItemQty
							 	, khgh.NgayXuatHang as EndDate
							 	, ifnull(link.ToIOID, 0) as ToIOID
							 	, ifnull(link.FromIOID, 0) as FromIOID
								from OKeHoachGiaoHang as khgh
								left join qsioidlink as link on link.FromIOID = khgh.IOID AND link.FromIFID = khgh.IFID_M505
								left join OChiTietYeuCau as ctyc on ctyc.IOID = link.ToIOID AND ctyc.IFID_M764 = link.ToIFID
								where %1$s
								', $this->getConditionWithColumnAndKey($orderKeyArr, ' khgh.IFID_M505 '));
			break;
		}
		return $this->_o_DB->fetchAll($sql);
	}

}
?>