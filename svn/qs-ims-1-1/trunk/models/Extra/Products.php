<?php

class Qss_Model_Extra_Products extends Qss_Model_Abstract
{

	public $_common;

	public function init()
	{
		parent::init();
		$this->_common = new Qss_Model_Extra_Extra();
	}

	/**
	 * Lay tong so luong san pham trong bang "Trang thai luu tru" theo don vi tinh co so
	 * Lay so luong hien co theo don vi tinh co so
	 * @param int $refWarehouse IOID cua kho chua mat hang can lay
	 * @param int $refItem IOID cua mat hang can lay
	 */
	public function getTotalItemQty($refWarehouse, $refItem)
	{
		$sql = sprintf('
				SELECT 
				sp1.`MaSanPham`, sp1.`TenSanPham`, k.IFID_M602
				, (ifnull(k.SoLuongHC,0) * ifnull(dvt1.HeSoQuyDoi,0)) AS SoLuongHCQuyDoi
				, sum((ifnull(ct.SoLuong,0) * ifnull(dvt2.HeSoQuyDoi,0))) AS TongSoLuongQuyDoi
				FROM OKho AS k
				LEFT JOIN OSanPham AS sp1 ON sp1.IOID = k.Ref_MaSP
				LEFT JOIN ODonViTinhSP AS dvt1 ON dvt1.IFID_M113 = sp1.IFID_M113
				LEFT JOIN OThuocTinhChiTiet AS ct ON ct.IFID_M602 = k.IFID_M602
				LEFT JOIN OSanPham AS sp2 ON sp2.IOID = ct.Ref_MaSanPham
				LEFT JOIN ODonViTinhSP AS dvt2 ON dvt2.IFID_M113 = sp2.IFID_M113	
				WHERE k.Ref_Kho = %1$d AND k.Ref_MaSP = %2$d
				GROUP BY k.IFID_M602
				LIMIT 1
			', $refWarehouse, $refItem);
		return $this->_o_DB->fetchOne($sql);

	}

	public function changeQtyBaseOnBaseUOM($refItem, $refUOM, $qty = 0)
	{
		$sql = sprintf('
				SELECT (%1$d * ifnull(dvt.HeSoQuyDoi,0)) AS SoLuongQuyDoi
				FROM OSanPham AS sp
				INNER JOIN ODonViTinhSP AS dvt ON sp.IFID_M113 = dvt.IFID_M113
				WHERE sp.IOID = %2$d
				AND dvt.IOID = %3$d
			', $qty, $refItem, $refUOM);
		$dataSql = $this->_o_DB->fetchOne($sql);
		return $dataSql ? $dataSql->SoLuongQuyDoi : 0;

	}

	/**
	 * Lấy ra số serial cuối cùng được nhập
	 */
	public function getLastSerial($preSerial)
	{
		$sql = "
            SELECT SoSerial as lastSerial
            FROM  `OThuocTinhChiTiet`
            WHERE (SoSerial REGEXP  '^" . $preSerial . "[0-9]+')
            ORDER BY cast(substr(SoSerial,length('{$preSerial}')+1) as signed) DESC
            LIMIT 1
        ";
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL ? $dataSQL->lastSerial : 0;
	}

	/**
	 * Lấy ra số lô cuối cùng được nhập 
	 * $moduleFilterArr array( 1=>(module=>'M402', ifid=>ifid) )
	 */
	public function getLastLot($preLot)
	{
		$sql = "
            SELECT SoLo as lastLot
            FROM  `OThuocTinhChiTiet`
            WHERE (SoLo REGEXP  '^" . $preLot . "[0-9]+')
            ORDER BY cast(substr(SoLo,length('{$preLot}')+1) as signed) DESC
            LIMIT 1
        ";
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL ? $dataSQL->lastLot : 0;
	}

	/**
	 * Lấy thuộc tính của sản phẩm
	 */
	public function getAttributes($module, $ioid, $ifid = 0, $getDataObject = '')
	{
		$idAlias = 'IOID';
		$idVal = $ioid;
		switch ($module)
		{
			/* Nhận hàng */
			case 'M402':
				$object = 'ODanhSachNhapKho';
				break;

			/* Trả hàng cho nhà cung cấp */
			case 'M403':
				$object = 'ODanhSachTraHang';
				break;

			/* Chuyển hàng */
			case 'M506':
				$object = 'ODanhSachXuatKho';
				break;

			/* Nhận hàng trả lại */
			case 'M507':
				$object = 'ODanhSachHangTL';
				break;

			/* Chuyển kho */
			case 'M603':
				$object = 'ODanhSachCK';
				break;

			/* Xuất kho bảo trì */
			case 'M605':
				$object = 'ODanhSachXuatKho';
				break;

			/* Xuất kho bảo trì */
			case 'M606':
				$object = 'ODanhSachNhapKho';
				break;

			/* Hang doi nhap */
			case 'M610':
				$object = 'OHangDoiNhap';
				break;

			/* Hang doi xuat */
			case 'M611':
				$object = 'OHangDoiXuat';
				break;

			case 'M706':
				$object = $getDataObject;
				break;

			case 'M758':
				$object = $getDataObject;
				break;

			case 'M759':
				$object = $getDataObject;
				break;

			/* Thống kê sản lượng */
			case 'M717':
				$object = ($getDataObject) ? $getDataObject : 'OThongKeSanLuong';

				if ($object == 'OThongKeSanLuong')
				{
					$idAlias = 'IFID_M717';
					$idVal = $ifid;
				}
				break;

			case 'M712':
				$object = ($getDataObject) ? $getDataObject : 'OPhieuGiaoViec';

				if ($object == 'OPhieuGiaoViec')
				{
					$idAlias = 'IFID_M717';
					$idVal = $ifid;
				}
				break;

			default:
				$idAlias = 'IOID';
				$idVal = $ioid;
				break;
		}
		$sql = sprintf('SELECT nh.ThuocTinh as attributes
                                FROM %1$s as nh
                                WHERE nh.%2$s = %3$d
                                ', $object, $idAlias, $idVal);
		$data = $this->_o_DB->fetchOne($sql);
		return $data ? $data->attributes : '';

	}

	/**
	 *  Hàm trả về IOID của đối tượng chính từ IFID truyền vào 
	 */
	public function getIOIDfromIFID($module, $ifid)
	{
		switch ($module)
		{
			case 'M610':
				$sql = sprintf('SELECT ifnull(IOID,0) AS IOID from OHangDoiNhap '
					. 'WHERE IFID_M610 = %1$d', $ifid);
				break;

			case 'M611':
				$sql = sprintf('SELECT ifnull(IOID,0) AS IOID from OHangDoiXuat '
					. 'WHERE IFID_M611 = %1$d', $ifid);
				break;
			case 'M717':
				$sql = sprintf('SELECT ifnull(IOID,0) AS IOID from OThongKeSanLuong '
					. 'WHERE IFID_M717 = %1$d', $ifid);
				break;

			case 'M718':
				$sql = sprintf('SELECT ifnull(IOID,0) AS IOID from ONVLDauVao '
					. 'WHERE IFID_M718 = %1$d', $ifid);
				break;

			default :
				$sql = 'SELECT 0 AS IOID';
				break;
		}

		$data = $this->_o_DB->fetchOne($sql);
		return $data ? $data->IOID : 0;

	}

	/**
	 * @todo: Đã chuyển sang Inventory và tách thành 2 hàm cho input (getStockStatusOfInput) và output (getStockStatusOfOutput), sau cần tìm chỗ sử dụng của hàm này để xóa đi
	 * Giá trị đã được cập nhật vào bảng trạng thái lưu trữ.
	 */
	public function getOldStockStatus($module, $ioid, $ifid, $getDataObject = '', $extStock = '')
	{
		$sanPham = 'MaSP';
		$stockStatusTable = 'OThuocTinhChiTiet';
		$kho = 'Kho';
		$soLuong = 'SoLuong';

		switch ($module)
		{
			case 'M402':
				$table = 'ODanhSachNhapKho';
				$sanPham = 'MaSanPham';
				$kho = 'KhoNhanHang';
				break;
			case 'M403':
				$table = 'ODanhSachTraHang';
				break;
			case 'M506':
				$table = 'ODanhSachXuatKho';
				break;
			case 'M507':
				$table = 'ODanhSachHangTL';
				break;
			case 'M603':
				$stockStatusTable = 'OTrangThaiLuuTruCK';
				$table = 'ODanhSachCK';
				$kho = 'KhoCD';
				break;
			case 'M605':
				$table = 'ODanhSachXuatKho';
				$sanPham = 'MaMatHang';
				break;
			case 'M606':
				$table = 'ODanhSachNhapKho';
				$sanPham = 'MaMatHang';
				break;
			case 'M610':
				$table = 'OHangDoiNhap';
				break;
			case 'M611':
				$table = 'OHangDoiXuat';
				break;


			case 'M712':
				$stockStatusTable = 'OTrangThaiLuuTruNVL';
				$table = $getDataObject ? $getDataObject : 'OPhieuGiaoViec';
				switch ($getDataObject)
				{
					case 'ONVLDauVao': // Output statistics
						$stockStatusTable = 'OTrangThaiLuuTruNVL';
						break;
					default:
						$stockStatusTable = 'OThuocTinhChiTiet';
						break;
				}

			case 'M717':

				$table = $getDataObject ? $getDataObject : 'OThongKeSanLuong';
				switch ($getDataObject)
				{
					case 'OSanLuong': // Output statistics
						$stockStatusTable = 'OTrangThaiLuuTruTP';
						break;
					case 'ONVLDauVao': // Material consumption
						$stockStatusTable = 'OTrangThaiLuuTruNVL';
						break;
					case 'OSanPhamLoi':// Defect statistics
						$stockStatusTable = 'OTrangThaiLuuTruPL';
						break;
					case 'OCongCuCauThanh'://toolset statistics
						$stockStatusTable = 'OTrangThaiLuuTruCCDC';
						break;
					default:
						$stockStatusTable = 'OThuocTinhChiTiet';
						break;
				}
				break;
			default:
				$sanPham = 'MaSP';
				$kho = 'Kho';
				$soLuong = 'SoLuong';
				break;
		}


		// Ngoai le: truong hop m717
		if ($extStock)
		{
			$stockStatusTable = $extStock;
		}

		//$kho = ($module!='M603')?sprintf('and tt.Ref_Kho = ds.Ref_%1$s',$kho):'';
		$kho = '';
		$sql = sprintf('SELECT tt.* FROM %7$s AS tt
                        INNER JOIN %1$s AS ds
                        ON ds.IFID_%2$s = tt.IFID_%2$s 
                        and tt.MaSanPham = ds.%3$s 
                        %4$s
                        INNER JOIN qsioidlink AS qsl
                        ON qsl.FromIOID = ds.IOID AND qsl.FromIFID = ds.IFID_%2$s
                        WHERE
                        qsl.FromIOID = %5$d
                        AND qsl.FromIFID = %6$d
                        AND qsl.ToIOID = tt.IOID
                        AND qsl.ToIFID = tt.IFID_%2$s
                        AND tt.IFID_%2$s = %6$d
						ORDER BY tt.IOID
                        ', $table, $module, $sanPham, $kho, $ioid, $ifid, $stockStatusTable);
		return $this->_o_DB->fetchAll($sql);

	}


	

	public function getItemValueByIFID($szFieldID, $code, $ifid)
	{
		$sql = sprintf('select * from %1$s where IFID_%2$s = %3$d ', $szFieldID, $code, $ifid);
		return $this->_o_DB->fetchOne($sql);

	}

	/** @Edit */
	public function getItemValueByIOID($szFieldID, $ioid)
	{
		$sql = sprintf('select * from %1$s where IOID = %2$d ', $szFieldID, $ioid);
		return $this->_o_DB->fetchOne($sql);

	}

	// Lấy ra thuộc tính và giá trị
	public function getAttributeByItemCode($item, $uomIOID)
	{
		$sql = sprintf('SELECT tt.MaThuocTinh, tt.ThuocTinh,tt.IOID, tt.IFID_M111 as TTIFID
                                , gttt.GiaTri as Ma, gttt.GiaTri
                                , ttsp.NhapTuDo, ttsp.CongThuc, ttsp.KieuSo, ttsp.Checkbox
                                , ttsp.BatBuoc, ttsp.HoatDong
                                , dvtsp.SuDungTTSL as QtyAttribute
                                , dvtsp.HeSoQuyDoi as ConversionRate
                                , dvtsp.MacDinh as uomDefautl
                                FROM  OThuocTinh AS tt
                                LEFT JOIN  OGiaTriThuocTinh AS gttt
                                    ON tt.IFID_M111 = gttt.IFID_M111
                                INNER JOIN OThuocTinhSanPham AS ttsp
                                    ON ttsp.Ref_MaThuocTinh = tt.IOID
                                INNER JOIN OSanPham as sp
                                    ON ttsp.IFID_M113 = sp.IFID_M113
                                INNER JOIN ODonViTinhSP as dvtsp
                                    on dvtsp.IFID_M113 = sp.IFID_M113
                                        and dvtsp.IOID = %2$d
                                WHERE sp.MaSanPham = %1$s
                                AND ttsp.HoatDong =1
                                ORDER BY ttsp.ThuTu, ttsp.IOID
                                #ORDER BY IOID DESC
                                                ',
			/* Lưu ý : ORDER BY IOID DESC sửa phải sử ngược lại với order của getItemAttributesConfig() */ $this->_o_DB->quote($item), $uomIOID);
		return $this->_o_DB->fetchAll($sql);

	}

	// Lay don vi tinh
	/**
	 * @param type description's
	 * Qss_Bin_Trigger_OThanhToanHoaDon@return 
	 */
	public function getUomsByItemCode($productcode)
	{
		$sql = sprintf('select dvtsp.*
                    from ODonViTinhSP as dvtsp
                    inner join OSanPham as sp on dvtsp.IFID_M113 = sp.IFID_M113
                    where sp.MaSanPham = %1$s 
                    ', $this->_o_DB->quote($productcode));
		return $this->_o_DB->fetchAll($sql);

	}

	// Chỉ lấy cấu hình thuộc tính của sản phẩm 
	public function getItemAttributesConfig($itemCode)
	{
		$sql = sprintf('
                                                SELECT ttsp.* FROM OThuocTinhSanPham as ttsp
                                                INNER JOIN OSanPham as sp
                                                ON sp.IFID_M113 = ttsp.IFID_M113
                                                WHERE sp.MaSanPham = %1$s
                                                AND ttsp.HoatDong =1
                                                ORDER BY ttsp.ThuTu, ttsp.IOID
                                                #ORDER BY ttsp.IOID ASC
                ', $this->_o_DB->quote($itemCode));
		/* Lưu ý : ORDER BY IOID ASC sửa phải sửa ngược lại với order của getAttributeByItemCode() */
		return $this->_o_DB->fetchAll($sql);

	}

	public function getOldAttributeValues($ioid)
	{
		$sql = sprintf('SELECT 
                                *, btt.MaThuocTinh as BTTMaThuocTinh
                                , btt.SoLuong as BTTSoLuong
                                , gt.MaThuocTinh as GTMaThuocTinh
                                , gt.GiaTri as GTGiaTri
                                FROM OBangThuocTinh AS btt
                                INNER JOIN OGiaTriBTT AS gt ON btt.IFID_M119 = gt.IFID_M119
                                WHERE btt.IOID = %1$d ', $ioid);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);

	}

	/* Todo: kiem tra lai ham nay */
	/* Kiểm tra xem thuộc tính có hợp lý để tồn tại ko ? */

	public function checkAttributesAvailable($itemCode, $refAttributes)
	{
		$sql = sprintf('SELECT 1 FROM OBangThuocTinh 
                                WHERE MaSP = %1$s
                                AND IOID = %2$d', $this->_o_DB->quote($itemCode), $refAttributes);
		//echo $sql; die;
		if ($this->_o_DB->fetchOne($sql))
		{
			return true;
		} else
		{
			return false;
		}

	}

	/* Kiểm tra xem trong tất cả thuộc tính của sản phẩm, có thuộc tính nào bắt buộc hay không? */

	public function checkAttributeRequires($itemCode)
	{
		if (!Qss_Lib_System::objectInForm('M113', 'OThuocTinhSanPham'))
		{
			return false;
		}
		$sql = sprintf('SELECT 1
                FROM OThuocTinhSanPham as ttsp
                INNER JOIN OSanPham as sp
                ON ttsp.IFID_M113 = sp.IFID_M113
                WHERE sp.MaSanPham = %1$s
                AND ttsp.HoatDong = 1
                AND ttsp.BatBuoc = 1
                LIMIT 1
                ', $this->_o_DB->quote($itemCode));
		//echo $sql;die;
		if ($this->_o_DB->fetchOne($sql))
		{
			return true;
		} else
		{
			return false;
		}

	}

	/**
	 * 
	 * Lấy các lines (lô, serial, thuộc tính) đã tồn tại trong bảng lot & serial
	 * @param unknown_type $ifid
	 * @param unknown_type $itemCode
	 * @param unknown_type $module
	 * @param unknown_type $warehouse
	 */
	public function getExistsIOIDFromAttrTable($ifid, $itemCode, $module, $warehouse, $attributes)
	{
		$sql = sprintf('SELECT tt.IOID 
                                                FROM OThuocTinhChiTiet as tt
                                                WHERE tt.MaSanPham = %1$s
                                                AND tt.IFID_%3$s = %2$d', $this->_o_DB->quote($itemCode), $ifid, $module);
		$sql .= ($warehouse) ? ' AND tt.Kho =' . $this->_o_DB->quote($warehouse) : '';
		$sql .= ($attributes) ? ' AND tt.MaThuocTinh =' . $this->_o_DB->quote($attributes) : '';
		return $this->_o_DB->fetchAll($sql);

	}

	public function searchMovementAttrCount($ifid, $itemCode, $warehouse
	, $beginLot, $endLot, $beginSerial
	, $endSerial, $module, $attributes
	, $bin, $getDataObject = '')
	{
//            echo 'ifid:'.$ifid.'- item code'.$itemCode.'- $warehouse'.$warehouse
//                    .'- $beginLot'.$beginLot.'- $endLot'.$endLot.'- $beginSerial'. $beginSerial
//                    .'- $endSerial'.$endSerial.'- $module'. $module.'- $attributes'.$attributes
//                    .'- $bin'.$bin.'- $getDataObject'.$getDataObject;
		switch ($module)
		{

			/* Module tra hang cho nha cung cap */
			case 'M403':
				$sql = sprintf('SELECT Count(1) as SoBanGhi
                                                FROM ODanhSachTraHang as ds
                                                INNER JOIN OKho as k 
                                                ON  ds.MaSP = k.MaSP 
                                                AND ds.Kho = k.Kho 
                                                AND ifnull(ds.Ref_ThuocTinh,0) = ifnull(k.Ref_ThuocTinh,0) 
                                                INNER JOIN OThuocTinhChiTiet as tt
                                                ON k.IFID_M602 = tt.IFID_M602
                                                WHERE
                                                tt.IFID_M602 is not null
                                                AND tt.SoLuong > 0
                                                AND ds.IFID_M403 = %3$d 
                                                AND ds.Kho = %2$s
                                                AND ds.MaSP = %1$s
                                                ', $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse), $ifid);
				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = '
					. $this->_o_DB->quote($attributes) . ' ' : '';
				$sql.= $this->getSearchCondition($beginLot, $endLot, $beginSerial
					, $endSerial, $bin);
				break;

			/* Module chuyen hang */
			case 'M506':
				$sql = sprintf('SELECT Count(*) as SoBanGhi
                                                FROM ODanhSachXuatKho as ds
                                                inner join OXuatKho as ch 
                                                    on ch.IFID_M506 = ds.IFID_M506
                                                INNER JOIN OKho as k 
                                                ON ds.Ref_MaSP = k.Ref_MaSP 
                                                and ch.Ref_Kho = k.Ref_Kho
                                                AND ifnull(ds.Ref_ThuocTinh,0) = ifnull(k.Ref_ThuocTinh,0)
                                                INNER JOIN OThuocTinhChiTiet as tt
                                                ON k.IFID_M602 = tt.IFID_M602
                                                WHERE
                                                ds.IFID_M506 = %3$d 
                                                AND ds.MaSP = %1$s
                                                AND ch.Kho = %2$s
                                                AND tt.IFID_M602 is not null
                                                AND tt.SoLuong > 0
                                                ', $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse), $ifid);
				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = '
					. $this->_o_DB->quote($attributes) . ' ' : '';
				$sql.= $this->getSearchCondition($beginLot, $endLot, $beginSerial
					, $endSerial, $bin);
				break;

			/* Module chuyen kho */
			case 'M603':
				$sql = sprintf('SELECT Count(*) as SoBanGhi
                                                FROM ODanhSachCK as ds 
                                                INNER JOIN OKho as k 
                                                ON ds.Ref_MaSP = k.Ref_MaSP
                                                AND ifnull(ds.Ref_ThuocTinh,0) = ifnull(k.Ref_ThuocTinh,0)  
                                                INNER JOIN OThuocTinhChiTiet as tt
                                                ON k.IFID_M602 = tt.IFID_M602
                                                WHERE
                                                ds.IFID_M603 = %3$d 
                                                AND ds.MaSP = %1$s
                                                AND tt.Kho = %2$s
                                                AND tt.IFID_M602 is not null
                                                AND tt.SoLuong > 0
                                                ', $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse), $ifid);
				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = '
					. $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial
					, $endSerial, $bin);

				break;

			/* Module hang doi xuat */
			case 'M611':
				$sql = sprintf('SELECT Count(*) as SoBanGhi
                                                FROM OHangDoiXuat as ds 
                                                INNER JOIN OKho as k 
                                                ON ds.MaSP = k.MaSP 
                                                INNER JOIN OThuocTinhChiTiet as tt
                                                ON k.IFID_M602 = tt.IFID_M602
                                                WHERE
                                                ds.IFID_M611 = %3$d 
                                                AND ds.MaSP = %1$s
                                                AND tt.Kho = %2$s
                                                AND tt.IFID_M602 is not null
                                                AND tt.SoLuong > 0
                                                ', $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse), $ifid);

				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = '
					. $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial
					, $endSerial, $bin);


				break;

			case 'M712':
				$getDataObject = $getDataObject ? $getDataObject : 'OThongKeSanLuong';
				$sql = sprintf('SELECT Count(*) as SoBanGhi
                                                FROM ONVLDauVao as ds 
                                                INNER JOIN OKho as k 
                                                ON ds.MaSP = k.MaSP 
                                                INNER JOIN OThuocTinhChiTiet as tt
                                                ON k.IFID_M602 = tt.IFID_M602
                                                WHERE
                                                ds.IFID_M712 = %3$d 
                                                AND ds.MaSP = %1$s
                                                AND ifnull(tt.Kho,\'\') = %2$s
                                                AND tt.IFID_M602 is not null
                                                AND tt.SoLuong > 0
                                                ', $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse)
					, $ifid, $getDataObject);

				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = '
					. $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial
					, $endSerial, $bin);
				break;
			/* Module phieu su dung nvl */
			case 'M717':
				$stockStatusTable = 'OThuocTinhChiTiet';
				switch ($getDataObject)
				{
					case 'OSanLuong': // Output statistics
						$stockStatusTable = 'OTrangThaiLuuTruTP';
						break;
					case 'ONVLDauVao': // Material consumption
						$stockStatusTable = 'OTrangThaiLuuTruNVL';
						break;
					case 'OSanPhamLoi':// Defect statistics
						$stockStatusTable = 'OTrangThaiLuuTruPL';
						break;
					case 'OCongCuCauThanh'://toolset statistics
						$stockStatusTable = 'OTrangThaiLuuTruCCDC';
						break;
				}
				$getDataObject = $getDataObject ? $getDataObject : 'OThongKeSanLuong';
				$sql = sprintf('SELECT Count(*) as SoBanGhi
                                                FROM %4$s as ds 
                                                INNER JOIN OKho as k 
                                                ON ds.MaSP = k.MaSP 
                                                INNER JOIN OThuocTinhChiTiet as tt
                                                ON k.IFID_M602 = tt.IFID_M602
                                                WHERE
                                                ds.IFID_M717 = %3$d 
                                                AND ds.MaSP = %1$s
                                                AND ifnull(tt.Kho,\'\') = %2$s
                                                AND tt.IFID_M602 is not null
                                                AND tt.SoLuong > 0
                                                ', $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse)
					, $ifid, $getDataObject);

				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = '
					. $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial
					, $endSerial, $bin);


				break;

			default:
				$sql = 'SELECT 0 as SoBanGhi';
				break;
		}
		
		$dataSql = $this->_o_DB->fetchOne($sql);
		$data = $dataSql ? $dataSql->SoBanGhi : 0;
		return $data;

	}

	public function searchMovementAttrLimit($ifid, $itemCode, $warehouse, $beginLot, $endLot
	, $beginSerial, $endSerial, $current, $number, $module
	, $attributes, $bin, $toWarehouse = '', $getDataObject = ''
	)
	{

		switch ($module)
		{

			/* Module tra hang cho nha cung cap */
			case 'M403':
				$sql = sprintf('SELECT tt.*,ifnull(existstt.IOID,0) as existsIOID 
                                                                ,tt.SoLuong as SoLuongCo, ifnull(existstt.SoLuong,0) as SoLuongLay
                                                                FROM ODanhSachTraHang as ds
                                                                INNER JOIN OKho as k 
                                                                ON ds.Ref_MaSP = k.Ref_MaSP 
                                                                and ds.Kho = k.Kho
                                                                AND ( ds.ThuocTinh is null or ds.ThuocTinh = k.ThuocTinh )
                                                                INNER JOIN OThuocTinhChiTiet as tt
                                                                ON k.IFID_M602 = tt.IFID_M602
                                                                LEFT JOIN OThuocTinhChiTiet as existstt
                                                                ON existstt.IFID_M403 = ds.IFID_M403
                                                                and (existstt.Ref_MaSanPham = tt.Ref_MaSanPham)								
                                                                and (existstt.SoLo is null OR existstt.SoLo = tt.SoLo)
                                                                and (existstt.SoSerial is null OR existstt.SoSerial = tt.SoSerial)
                                                                and (existstt.MaThuocTinh is null OR existstt.MaThuocTinh = tt.MaThuocTinh)
                                                                and (existstt.Bin is null OR existstt.Bin = tt.Bin) 
                                                                WHERE 
                                                                ds.IFID_M403 = %3$d
                                                                AND (tt.SoLuong is null or tt.SoLuong > 0)
                                                                AND tt.Kho = %2$s
                                                                AND ds.MaSP = %1$s
                                                                ', $this->_o_DB->quote($itemCode), $this->_o_DB->quote($warehouse), $ifid);
				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = ' . $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial, $endSerial, $bin);
				$sql .= ' ORDER BY existstt.IOID desc, tt.SoSerial asc, tt.SoLo asc, tt.MaThuocTinh asc ';
				$sql .= ' LIMIT ' . $current . ',' . $number;
				break;

			/* Module chuyen hang */
			case 'M506':
				$sql = sprintf('SELECT tt.*,ifnull(existstt.IOID,0) as existsIOID ,tt.SoLuong as SoLuongCo, ifnull(existstt.SoLuong,0) as SoLuongLay
                                                                FROM ODanhSachXuatKho as ds
                                                                inner join OXuatKho as ch on ds.IFID_M506 = ch.IFID_M506
                                                                INNER JOIN OKho as k 
                                                                ON ds.Ref_MaSP = k.Ref_MaSP 
                                                                and ch.Ref_Kho = k.Ref_Kho
                                                                AND ( ds.ThuocTinh is null or ds.ThuocTinh = k.ThuocTinh )
                                                                INNER JOIN OThuocTinhChiTiet as tt
                                                                ON k.IFID_M602 = tt.IFID_M602
                                                                LEFT JOIN OThuocTinhChiTiet as existstt
                                                                ON existstt.IFID_M506 = ds.IFID_M506
                                                                and (existstt.Ref_MaSanPham = tt.Ref_MaSanPham)	
                                                                and (existstt.SoLo is null OR existstt.SoLo = tt.SoLo)
                                                                and (existstt.SoSerial is null OR existstt.SoSerial = tt.SoSerial)
                                                                and (existstt.MaThuocTinh is null OR existstt.MaThuocTinh = tt.MaThuocTinh) 
                                                                and (existstt.Bin is null OR existstt.Bin = tt.Bin)
                                                                WHERE 
                                                                ds.IFID_M506 = %3$d
                                                                AND (tt.SoLuong is null or tt.SoLuong > 0)
                                                                AND tt.Kho = %2$s
                                                                AND ds.MaSP = %1$s
                                                                ', $this->_o_DB->quote($itemCode), $this->_o_DB->quote($warehouse), $ifid);
				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = ' . $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial, $endSerial, $bin);
				$sql .= ' ORDER BY existstt.IOID desc, tt.SoSerial asc, tt.SoLo asc, tt.MaThuocTinh asc ';
				$sql .= ' LIMIT ' . $current . ',' . $number;
				break;

			/* Module chuyen kho */
			case 'M603':
				$sql = sprintf('SELECT  tt.*,ifnull(existstt.IOID, 0) as existsIOID,
                                                                #ifnull(existstt.DenZone, null) as toZone,
                                                                ifnull(existstt.DenBin, null) as toBin,
                                                                ifnull(existstt.SoLuong, 0) as SoLuongLay
                                                                FROM ODanhSachCK as ds
                                                                INNER JOIN OKho as k 
                                                                ON 

                                                            ds.Ref_MaSP = k.Ref_MaSP 
                                                                AND ( ds.ThuocTinh is null or ds.ThuocTinh = k.ThuocTinh )
                                                                INNER JOIN OThuocTinhChiTiet as tt
                                                                ON 
                                                                k.IFID_M602 = tt.IFID_M602
                                                                LEFT JOIN OTrangThaiLuuTruCK as existstt
                                                                ON 
                                                                existstt.IFID_M603 = ds.IFID_M603
                                                                and existstt.MaSanPham = tt.MaSanPham
                                                                and existstt.KhoNhanHang = ds.KhoCD
                                                                and (existstt.SoLo is null OR existstt.SoLo = tt.SoLo)
                                                                and (existstt.SoSerial is null OR existstt.SoSerial = tt.SoSerial)
                                                                and (existstt.MaThuocTinh is null OR existstt.MaThuocTinh = tt.MaThuocTinh)
                                                                and (existstt.TuBin is null OR existstt.TuBin = tt.Bin)
                                                                WHERE 
                                                                ds.IFID_M603 = %3$d
                                                                AND (tt.SoLuong is null or tt.SoLuong > 0)
                                                                AND tt.Kho = %2$s
                                                                and ds.KhoCD = %4$s
                                                                AND ds.MaSP = %1$s
                                                                ', $this->_o_DB->quote($itemCode), $this->_o_DB->quote($warehouse), $ifid, $this->_o_DB->quote($toWarehouse));
				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = ' . $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial, $endSerial, $bin);
				$sql .= ' ORDER BY existstt.IOID desc,  tt.SoSerial asc, tt.SoLo asc
                                                  ,tt.MaThuocTinh asc';
				#,tt.Zone , tt.Bin
				#,existstt.Zone, existstt.Bin ';

				$sql .= ' LIMIT ' . $current . ',' . $number;
				break;

			/* Module xuat kho bao tri */
			case 'M605':
				$sql = sprintf('SELECT tt.*,ifnull(existstt.IOID,0) as existsIOID 
                                                                ,tt.SoLuong as SoLuongCo, ifnull(existstt.SoLuong,0) as SoLuongLay
                                                                FROM ODanhSachXuatKho as ds
                                                                INNER JOIN OKho as k 
                                                                ON ds.Ref_MaMatHang = k.Ref_MaSP 
                                                                and ds.Ref_Kho = k.Ref_Kho
                                                                AND ( ds.Ref_ThuocTinh is null or ds.Ref_ThuocTinh = k.Ref_ThuocTinh )
                                                                INNER JOIN OThuocTinhChiTiet as tt
                                                                ON k.IFID_M602 = tt.IFID_M602
                                                                LEFT JOIN OThuocTinhChiTiet as existstt
                                                                ON existstt.IFID_M605 = ds.IFID_M605
                                                                and (existstt.Ref_MaSanPham = tt.Ref_MaSanPham)	
                                                                and (existstt.SoLo is null OR existstt.SoLo = tt.SoLo)
                                                                and (existstt.SoSerial is null OR existstt.SoSerial = tt.SoSerial)
                                                                and (existstt.MaThuocTinh is null OR existstt.MaThuocTinh = tt.MaThuocTinh) 
                                                                and (existstt.Bin is null OR existstt.Bin = tt.Bin)
                                                                WHERE 
                                                                ds.IFID_M605 = %3$d
                                                                AND (tt.SoLuong is null or tt.SoLuong > 0)
                                                                AND tt.Kho = %2$s
                                                                AND ds.MaMatHang = %1$s
                                                                ', $this->_o_DB->quote($itemCode), $this->_o_DB->quote($warehouse), $ifid);
				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = ' . $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial, $endSerial, $bin);
				$sql .= ' ORDER BY existstt.IOID desc, tt.SoSerial asc, tt.SoLo asc, tt.MaThuocTinh asc ';
				$sql .= ' LIMIT ' . $current . ',' . $number;
				break;

			/* Module hang doi xuat  */
			case 'M611':
				$sql = sprintf('SELECT tt.*,ifnull(existstt.IOID,0) as existsIOID ,tt.SoLuong as SoLuongCo, ifnull(existstt.SoLuong,0) as SoLuongLay
                                                                FROM OHangDoiXuat as ds
                                                                INNER JOIN OKho as k 
                                                                ON ds.MaSP = k.MaSP 
                                                                AND ( ds.ThuocTinh is null or ds.ThuocTinh = k.ThuocTinh ) 
                                                                INNER JOIN OThuocTinhChiTiet as tt
                                                                ON k.IFID_M602 = tt.IFID_M602
                                                                LEFT JOIN OThuocTinhChiTiet as existstt
                                                                ON existstt.IFID_M611 = ds.IFID_M611
                                                                and (existstt.Ref_MaSanPham = tt.Ref_MaSanPham)	
                                                                and (existstt.SoLo is null OR existstt.SoLo = tt.SoLo)
                                                                and (existstt.SoSerial is null OR existstt.SoSerial = tt.SoSerial)
                                                                and (existstt.MaThuocTinh is null OR existstt.MaThuocTinh = tt.MaThuocTinh) 
                                                                and (existstt.Bin is null OR existstt.Bin = tt.Bin) 
                                                                WHERE 
                                                                ds.IFID_M611 = %3$d
                                                                AND (tt.SoLuong is null or tt.SoLuong > 0)
                                                                AND tt.Kho = %2$s
                                                                AND ds.MaSP = %1$s
                                                                ', $this->_o_DB->quote($itemCode), $this->_o_DB->quote($warehouse), $ifid);
				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = ' . $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial, $endSerial, $bin);
				$sql .= ' ORDER BY existstt.IOID desc, tt.SoSerial asc, tt.SoLo asc, tt.MaThuocTinh asc ';
				$sql .= ' LIMIT ' . $current . ',' . $number;
				break;

			/* Module nhật trình máy móc */
			case 'M712':
				$sql = sprintf('SELECT tt.*,ifnull(existstt.IOID,0) as existsIOID ,tt.SoLuong as SoLuongCo, ifnull(existstt.SoLuong,0) as SoLuongLay
                                                                FROM ONVLDauVao as ds
                                                                INNER JOIN OKho as k 
                                                                ON ds.MaSP = k.MaSP 
                                                                AND ifnull(ds.Ref_ThuocTinh,0) = ifnull(k.Ref_ThuocTinh,0)
                                                                INNER JOIN OThuocTinhChiTiet as tt
                                                                ON k.IFID_M602 = tt.IFID_M602
                                                                LEFT JOIN OTrangThaiLuuTruNVL as existstt
                                                                ON existstt.IFID_M712 = ds.IFID_M712
                                                                and (existstt.Ref_MaSanPham = tt.Ref_MaSanPham)	
                                                                and (existstt.SoLo is null OR existstt.SoLo = tt.SoLo)
                                                                and (existstt.SoSerial is null OR existstt.SoSerial = tt.SoSerial)
                                                                and (existstt.MaThuocTinh is null OR existstt.MaThuocTinh = tt.MaThuocTinh) 
                                                                and (existstt.Bin is null OR existstt.Bin = tt.Bin) 
                                                                WHERE 
                                                                ds.IFID_M712 = %3$d
                                                                AND (tt.SoLuong is null or tt.SoLuong > 0)
                                                                AND tt.Kho = %2$s
                                                                AND ds.MaSP = %1$s
                                                                ', $this->_o_DB->quote($itemCode), $this->_o_DB->quote($warehouse), $ifid);
				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = ' . $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial, $endSerial, $bin);
				$sql .= ' ORDER BY existstt.IOID desc, tt.SoSerial asc, tt.SoLo asc, tt.MaThuocTinh asc ';
				$sql .= ' LIMIT ' . $current . ',' . $number;
				break;



			/* Module phieu su dung nvl  */
			case 'M717':
				$stockStatusTable = 'OThuocTinhChiTiet';
				switch ($getDataObject)
				{
					case 'OSanLuong': // Output statistics
						$stockStatusTable = 'OTrangThaiLuuTruTP';
						break;
					case 'ONVLDauVao': // Material consumption
						$stockStatusTable = 'OTrangThaiLuuTruNVL';
						break;
					case 'OSanPhamLoi':// Defect statistics
						$stockStatusTable = 'OTrangThaiLuuTruPL';
						break;
					case 'OCongCuCauThanh'://toolset statistics
						$stockStatusTable = 'OTrangThaiLuuTruCCDC';
						break;
				}
				$getDataObject = $getDataObject ? $getDataObject : 'OThongKeSanLuong';
				$sql = sprintf('SELECT tt.*,ifnull(existstt.IOID,0) as existsIOID ,tt.SoLuong as SoLuongCo, ifnull(existstt.SoLuong,0) as SoLuongLay
                                                                FROM %4$s as ds
                                                                INNER JOIN OKho as k 
                                                                ON ds.MaSP = k.MaSP 
                                                                AND ifnull(ds.Ref_ThuocTinh,0) = ifnull(k.Ref_ThuocTinh,0)
                                                                INNER JOIN OThuocTinhChiTiet as tt
                                                                ON k.IFID_M602 = tt.IFID_M602
                                                                LEFT JOIN %5$s as existstt
                                                                ON existstt.IFID_M717 = ds.IFID_M717
                                                                and (existstt.Ref_MaSanPham = tt.Ref_MaSanPham)	
                                                                and (existstt.SoLo is null OR existstt.SoLo = tt.SoLo)
                                                                and (existstt.SoSerial is null OR existstt.SoSerial = tt.SoSerial)
                                                                and (existstt.MaThuocTinh is null OR existstt.MaThuocTinh = tt.MaThuocTinh) 
                                                                and (existstt.Bin is null OR existstt.Bin = tt.Bin) 
                                                                WHERE 
                                                                ds.IFID_M717 = %3$d
                                                                AND (tt.SoLuong is null or tt.SoLuong > 0)
                                                                AND tt.Kho = %2$s
                                                                AND ds.MaSP = %1$s
                                                                ', $this->_o_DB->quote($itemCode), $this->_o_DB->quote($warehouse), $ifid, $getDataObject, $stockStatusTable);
				$sql .= ($attributes) ? ' AND tt.MaThuocTinh = ' . $this->_o_DB->quote($attributes) . ' ' : '';
				$sql .= $this->getSearchCondition($beginLot, $endLot, $beginSerial, $endSerial, $bin);
				$sql .= ' ORDER BY existstt.IOID desc, tt.SoSerial asc, tt.SoLo asc, tt.MaThuocTinh asc ';
				$sql .= ' LIMIT ' . $current . ',' . $number;
				break;

			default:
				$sql = 'SELECT NULL';
				break;
		}
		return $this->_o_DB->fetchAll($sql);

	}

	public function existsMovement($ifid, $itemCode, $warehouse, $module = '')
	{
		// vẫn được dùng trong nhận hàng trả lại
		switch ($module)
		{

			/* Trả hàng cho nhà cung cấp */

			case 'M403':
				$sql = sprintf('SELECT tt . *
                                                , tt.SoLuong as SoLuongLay 
                                                , existstt.SoLuong as SoLuongKho
                                                FROM ODanhSachTraHang AS ds
                                                INNER JOIN OThuocTinhChiTiet AS tt
                                                LEFT JOIN OThuocTinhChiTiet as existstt
                                                ON existstt.IFID_M403 = tt.IFID_M403
                                                WHERE tt.IFID_M403 = %1$d
                                                AND tt.MaSanPham = %2$s
                                                AND ds.Kho = %3$s
                                                GROUP BY tt.IOID ', $ifid, $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse));
				break;
			// Chuyển hàng
			case 'M506':
				$sql = sprintf('SELECT tt . *
                                                , tt.SoLuong as SoLuongLay 
                                                , existstt.SoLuong as SoLuongKho
                                                FROM ODanhSachXuatKho AS ds
                                                INNER JOIN OThuocTinhChiTiet AS tt
                                                LEFT JOIN OThuocTinhChiTiet as existstt
                                                ON existstt.IFID_M506 = tt.IFID_M506
                                                WHERE tt.IFID_M506 = %1$d
                                                AND tt.MaSanPham = %2$s
                                                AND ds.Kho = %3$s
                                                GROUP BY tt.IOID ', $ifid, $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse));
				break;
			// Nhận trả
			case 'M507':
				$sql = sprintf('SELECT tt . *
                                                , tt.SoLuong as SoLuongLay 
                                                , existstt.SoLuong as SoLuongKho
                                                FROM ODanhSachHangTL AS ds
                                                INNER JOIN OThuocTinhChiTiet AS tt
                                                LEFT JOIN OThuocTinhChiTiet as existstt
                                                ON existstt.IFID_M506 = tt.IFID_M506
                                                WHERE tt.IFID_M506 = %1$d
                                                AND tt.MaSanPham = %2$s
                                                AND ds.Kho = %3$s
                                                GROUP BY tt.IOID ', $ifid, $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse));
				break;
			/* Module chuyen kho */
			case 'M603':
				$sql = sprintf('SELECT tt . *
                                        , tt.SoLuong as SoLuongLay 
                                        , existstt.SoLuong as SoLuongKho
                                        FROM ODanhSachCK AS ds
                                        INNER JOIN OThuocTinhChiTiet AS tt
                                        LEFT JOIN OThuocTinhChiTiet as existstt
                                        ON existstt.IFID_M603 = tt.IFID_M603
                                        WHERE tt.IFID_M603 = %1$d
                                        AND tt.MaSanPham = %2$s
                                        AND tt.Kho = %3$s
                                        GROUP BY tt.IOID ', $ifid, $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse));
				break;

			/* Module Thong ke san luong */
			case 'M717':
				$sql = sprintf('SELECT tt . *
                                        , tt.SoLuong as SoLuongLay 
                                        , existstt.SoLuong as SoLuongKho
                                        FROM OThongKeSanLuong AS ds
                                        INNER JOIN OThuocTinhChiTiet AS tt
                                        LEFT JOIN OThuocTinhChiTiet as existstt
                                        ON existstt.IFID_M717 = tt.IFID_M717
                                        WHERE tt.IFID_M717 = %1$d
                                        AND tt.MaSanPham = %2$s
                                        AND tt.Kho = %3$s
                                        GROUP BY tt.IOID ', $ifid, $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse));
				break;


			/* Module phieu su dung nvl */
			case 'M718':
				$sql = sprintf('SELECT tt . *
                                        , tt.SoLuong as SoLuongLay 
                                        , existstt.SoLuong as SoLuongKho
                                        FROM ONVLDauVao AS ds
                                        INNER JOIN OThuocTinhChiTiet AS tt
                                        LEFT JOIN OThuocTinhChiTiet as existstt
                                        ON existstt.IFID_M718 = tt.IFID_M718
                                        WHERE tt.IFID_M718 = %1$d
                                        AND tt.MaSanPham = %2$s
                                        AND tt.Kho = %3$s
                                        GROUP BY tt.IOID ', $ifid, $this->_o_DB->quote($itemCode)
					, $this->_o_DB->quote($warehouse));
				break;



			default:
				$sql = 'SELECT NULL';
				break;
		}
		return ($sql != 'SELECT NULL') ? $this->_o_DB->fetchAll($sql) : array();

	}

	/**
	 * 
	 * Trả về điều kiện tìm kiếm thích hợp 
	 * @param unknown_type $beginLot
	 * @param unknown_type $endLot
	 * @param unknown_type $beginSerial
	 * @param unknown_type $endSerial
	 * @param unknown_type $attribute
	 */
	private function getSearchCondition($beginLot, $endLot, $beginSerial, $endSerial, $bin)
	{
		$beginLot = trim($beginLot);
		$endLot = trim($endLot);
		$beginSerial = trim($beginSerial);
		$endSerial = trim($endSerial);

		// tt la alias cua bang
		if ($beginLot && $endLot)
		{
			$lotCondition = ' AND (tt.SoLo BETWEEN ' . $this->_o_DB->quote($beginLot)
				. ' AND ' . $this->_o_DB->quote($endLot) . ' )';
		} elseif ($beginLot)
		{
			$lotCondition = ' AND tt.SoLo =' . $this->_o_DB->quote($beginLot);
		} else
		{
			$lotCondition = '';
		}

		if ($beginSerial && $endSerial)
		{
			$serialCondition = ' AND (tt.SoSerial BETWEEN ' . $this->_o_DB->quote($beginSerial)
				. ' AND ' . $this->_o_DB->quote($endSerial) . ' )';
		} elseif ($beginSerial)
		{
			$serialCondition = ' AND tt.SoSerial =' . $this->_o_DB->quote($beginSerial);
		} else
		{
			$serialCondition = '';
		}

		$binCondition = '';
		if ($bin)
		{
			$binCondition = ' AND tt.Bin = ' . $this->_o_DB->quote($bin);
		}

		return $lotCondition . $serialCondition . $binCondition;

	}

	/**
	 * @param $warehouseID num, filter by warehouse   
	 * @return true/false
	 */
	public function checkHasBin($warehouseID)
	{
		$sql = sprintf('select 1 
                    from ODanhSachKho as dsk
                    inner join OBin as b
                    on dsk.IFID_M601 = b.IFID_M601
                    where dsk.IOID = %1$d', $warehouseID);
		$dataSql = $this->_o_DB->fetchOne($sql);
		return $dataSql ? true : false;

	}
	/**
	 *  Function: Kiem tra cac gia tri thuoc tinh cua mot ma da ton tai hay chua
	 * */
	public function checkAttrValueExists($attr, $itemCode)
	{
		$i = 0;
		$check = '';
		foreach ($attr as $key => $val)
		{
			$check .= sprintf(' inner join (select * from OGiaTriBTT 
                                                                where MaThuocTinh = %1$s 
                                                                and GiaTri = %2$s) as t%3$d
                                                                on btt.IFID_M119 = t%3$d.IFID_M119'
				, $this->_o_DB->quote($key), $this->_o_DB->quote($val), $i++);
		}

		$sql = sprintf(' select btt.MaThuocTinh, btt.IFID_M119, btt.IOID as BTTIOID
                                                 from OBangThuocTinh as btt %1$s
                                                 where btt.MaSP = %2$s
                                                 ', $check, $this->_o_DB->quote($itemCode));
		return $this->_o_DB->fetchOne($sql);

	}

	/* End Function: Kiem tra cac gia tri thuoc tinh cua mot ma da ton tai hay chua */

	public function checkAttributeAndProductExists($attrTableIOID)
	{
		$sql = sprintf('select 1
                                from OBangThuocTinh
                                where IOID = %1$d 
                                limit 1', $attrTableIOID);
		$dataSql = $this->_o_DB->fetchOne($sql);
		return $dataSql ? true : false;

	}

	/**
	 * @param type description's
	 * Qss_Bin_Trigger_OThanhToanHoaDon@return 
	 */
	public function checkLotExists($lotArr)
	{
		$temp = '';
		foreach ($lotArr as $item)
		{
		    $temp .= $temp?' or ':'';
		    $temp .= sprintf(' SoLo = %1$s ', $this->_o_DB->quote($item) );
		}
		$temp = $temp?sprintf(' and (%1$s) ', $temp):' and 1 = 0 ';
		
		
		$sql = sprintf('
                        select distinct(SoLo) as SoLo
                        from OThuocTinhChiTiet
                        where IFID_M602 is not null %1$s
                        ', $temp);
		return $this->_o_DB->fetchAll($sql);

	}

	/**
	 * @param type description's
	 * Qss_Bin_Trigger_OThanhToanHoaDon@return 
	 */
	public function checkSerialExists($serialArr)
	{
		$temp = '';
		foreach ($serialArr as $item)
		{
		    $temp .= $temp?' or ':'';
		    $temp .= sprintf(' SoSerial = %1$s ', $this->_o_DB->quote(trim($item)) );
		}
		$temp = $temp?sprintf(' and (%1$s) ', $temp):' and 1 = 0 ';		
		
		$sql = sprintf('
                        select * 
                        from OThuocTinhChiTiet
                        where IFID_M602 is not null %1$s
                        ', $temp);
		return $this->_o_DB->fetchAll($sql);

	}
	
	/**
	 * *******************************************************
	 * Refactor: Chinh ly lai cac ham co mat o tren tu phan nay
	 * *******************************************************
	 */

	/**
	 * Tra ve cau sql cau hinh san pham (Nhan hang)
	 * @param int $ioid IOID cua dong danh sach nhan hang
	 * @return string sql 
	 */
	public function getSerialLotSqlOfInputModule($ioid)
	{
		return sprintf('SELECT dh.MaSanPham AS itemCode
									, dh.Ref_MaSanPham AS refItemCode
									, dh.TenSanPham AS itemName
									, dh.SoLuong AS itemQty
									, dh.DonViTinh as itemUOM
									, d.Kho AS warehouse
									, d.Ref_Kho as refWarehouse
									, sp.QuanLyTheoMa AS serial
									, sp.DanhMaTuDong AS autoSerial
									, sp.KyTuDauMa AS preSerial
									, sp.DoDaiMa AS serialLength
									, sp.QuanLyTheoLo AS lot
									, sp.DanhLoTuDong AS autoLot
									, sp.KyTuDauLo AS preLot
									, sp.DoDaiLo AS lotLength
									, sp.IOID as refItem
									, 0 as SelectWarehouse
								FROM ONhapKho as d
								INNER JOIN ODanhSachNhapKho AS dh on dh.IFID_M402 = d.IFID_M402
								INNER JOIN OSanPham AS sp ON dh.Ref_MaSanPham = sp.IOID
								WHERE dh.IOID = %1$d', $ioid);
	}
	
	/**
	 * Tra ve cau sql cau hinh san pham (Chuyen hang)
	 * @param int $ioid IOID cua dong danh sach chuyen hang
	 * @return string sql 
	 */	
	public function getSerialLotSqlOfOutputModule($ioid)
	{
		return sprintf('SELECT dh.MaSP AS itemCode
									, dh.Ref_MaSP AS refItemCode
									, dh.TenSP AS itemName
									, dh.SoLuong AS itemQty
									, dh.DonViTinh as itemUOM
									, sp.QuanLyTheoMa AS serial
									, sp.DanhMaTuDong AS autoSerial
									, sp.KyTuDauMa AS preSerial
									, sp.DoDaiMa AS serialLength
									, sp.QuanLyTheoLo AS lot
									, sp.DanhLoTuDong AS autoLot
									, sp.KyTuDauLo AS preLot
									, sp.DoDaiLo AS lotLength
									, d.Kho AS warehouse
									, d.Ref_Kho as refWarehouse
									, sp.IOID as refItem
									, 0 as SelectWarehouse
							   FROM OXuatKho as d 
							   INNER JOIN ODanhSachXuatKho AS dh on dh.IFID_M506 = d.IFID_M506
							   INNER JOIN OSanPham AS sp ON dh.Ref_MaSP = sp.IOID
							   WHERE dh.IOID = %1$d', $ioid);
	}
	
	/**
	 * Tra ve cau sql cau hinh san pham (Chuyen kho)
	 * @param int $ioid IOID cua dong danh sach chuyen kho
	 * @param int $ifid IFID_M603 cua chuyen kho
	 * @return string sql 
	 */		
	public function getSerialLotSqlOfMovementModule($ioid, $ifid)
	{
		return sprintf('SELECT  dh.MaSP AS itemCode
									, dh.Ref_MaSP AS refItemCode
									, dh.TenSP AS itemName
									, dh.SoLuong AS itemQty
									, dh.DonViTinh as itemUOM
									, sp.QuanLyTheoMa AS serial
									, sp.DanhMaTuDong AS autoSerial
									, sp.KyTuDauMa AS preSerial
									, sp.DoDaiMa AS serialLength
									, sp.QuanLyTheoLo AS lot
									, sp.DanhLoTuDong AS autoLot
									, sp.KyTuDauLo AS preLot
									, sp.DoDaiLo AS lotLength
									, dh.KhoCD AS toWarehouse
									, dh.KhoLH AS warehouse
									, sp.IOID as refItem
									, dh.Ref_KhoCD as refToWarehouse
									, dh.Ref_KhoLH AS refWarehouse
									, 0 as SelectWarehouse
								FROM OChuyenKho as ck
								INNER JOIN ODanhSachCK AS dh ON ck.IFID_M603 = dh.IFID_M603
								INNER JOIN OSanPham AS sp ON dh.Ref_MaSP = sp.IOID
								WHERE dh.IOID = %1$d
								AND ck.IFID_M603 = %2$d', $ioid, $ifid);
	}
	
	
	/**
	 * Tra ve cau sql cau hinh san pham (Hang doi nhap)
	 * @param int $ifid ifid_M610
	 * @return type
	 */
	public function getSerialLotSqlOfIncommingModule($ifid)
	{
		return sprintf('SELECT dh.MaSP AS itemCode
									, dh.Ref_MaSP AS refItemCode
									, dh.TenSP AS itemName
									, dh.SoLuong AS itemQty
									, dh.DonViTinh as itemUOM
									, sp.QuanLyTheoMa AS serial
									, sp.DanhMaTuDong AS autoSerial
									, sp.KyTuDauMa AS preSerial
									, sp.DoDaiMa AS serialLength
									, sp.QuanLyTheoLo AS lot
									, sp.DanhLoTuDong AS autoLot
									, sp.KyTuDauLo AS preLot
									, sp.DoDaiLo AS lotLength
									, dh.Kho AS warehouse
									, sp.IOID as refItem
									, dh.Ref_Kho as refWarehouse
									, 0 as SelectWarehouse
							   FROM OHangDoiNhap AS dh
							   INNER JOIN OSanPham AS sp ON dh.Ref_MaSP = sp.IOID
							   WHERE dh.IFID_M610 = %1$d', $ifid);
	}

	/**
	 * Tra ve cau sql cau hinh san pham (Hang doi xuat)
	 * @param int $ifid ifid_M611
	 * @return type
	 */
	public function getSerialLotSqlOfOutgoingModule($ifid)
	{
		return sprintf('SELECT dh.MaSP AS itemCode
									, dh.Ref_MaSP AS refItemCode
									, dh.TenSP AS itemName
									, dh.SoLuong AS itemQty
									, dh.DonViTinh as itemUOM
									, sp.QuanLyTheoMa AS serial
									, sp.DanhMaTuDong AS autoSerial
									, sp.KyTuDauMa AS preSerial
									, sp.DoDaiMa AS serialLength
									, sp.QuanLyTheoLo AS lot
									, sp.DanhLoTuDong AS autoLot
									, sp.KyTuDauLo AS preLot
									, sp.DoDaiLo AS lotLength
									, dh.Kho AS warehouse
									, sp.IOID as refItem
									, dh.Ref_Kho as refWarehouse
									, 0 as SelectWarehouse
							   FROM OHangDoiXuat AS dh
							   INNER JOIN OSanPham AS sp ON dh.Ref_MaSP = sp.IOID
							   WHERE dh.IFID_M611 = %1$d', $ifid);
	}
	
	/**
	 * Phieu giao viec, M712
	 * @param type $ioid 
	 * @param type $ifid
	 * @return type
	 */
	public function getSerialLotSqlOfWorkOrderModuleOfProduction($ioid, $ifid)
	{
		return 	sprintf('SELECT dh.MaSP AS itemCode
							, dh.Ref_MaSP AS refItemCode
							, dh.TenSP AS itemName
							, dh.SoLuong AS itemQty
							, dh.DonViTinh as itemUOM
							, sp.QuanLyTheoMa AS serial
							, sp.DanhMaTuDong AS autoSerial
							, sp.KyTuDauMa AS preSerial
							, sp.DoDaiMa AS serialLength
							, sp.QuanLyTheoLo AS lot
							, sp.DanhLoTuDong AS autoLot
							, sp.KyTuDauLo AS preLot
							, sp.DoDaiLo AS lotLength
							, dvsx.KhoVatTu AS warehouse
							, sp.IOID as refItem
							, ifnull(dvsx.Ref_KhoVatTu,0) as refWarehouse
							, 1 as SelectWarehouse
						FROM ONVLDauVao AS dh
						INNER JOIN OSanPham AS sp ON dh.Ref_MaSP = sp.IOID
						INNER JOIN OPhieuGiaoViec as pgv on pgv.IFID_M712 = dh.IFID_M712
						LEFT JOIN ODonViSanXuat as dvsx on pgv.Ref_DonViThucHien = dvsx.IOID
						WHERE dh.IFID_M712 = %1$d
						and dh.IOID = %2$d', $ifid, $ioid);
	}
	
	/**
	 * M717, thong ke san xuat
	 * @param type $ioid IOID cua doi tuong phu neu co
	 * @param type $ifid IFID_M717 dung khi lay doi tuong chinh
	 * @param type $getDataObject Object lay du lieu (obj chinh hoac phu)
	 * @return type
	 */
	public function getSerialLotSqlOfProductionStatisticModule($getDataObject, $ioid = 0, $ifid = 0)
	{
		$sql = '';
		if ($getDataObject && $getDataObject != 'OThongKeSanLuong') // Doi tuong phu 
		{
			switch ($getDataObject)
			{
				case 'OSanLuong': // Output statistics
					$warehouseField = 'KhoSanXuat';
					$refWarehouse = 'Ref_KhoSanXuat';
					break;
				case 'ONVLDauVao': // Material consumption
					$warehouseField = 'KhoSanXuat';
					$refWarehouse = 'Ref_KhoSanXuat';
					break;
				case 'OSanPhamLoi':// Defect statistics
					$warehouseField = 'KhoSanXuat';
					$refWarehouse = 'Ref_KhoSanXuat';
					break;
				case 'OCongCuCauThanh'://toolset statistics
					$warehouseField = 'KhoSanXuat';
					$refWarehouse = 'Ref_KhoSanXuat';
					break;
			}

			$sql = sprintf('SELECT dh.MaSP AS itemCode
								, dh.Ref_MaSP AS refItemCode
								, dh.TenSP AS itemName
								, dh.SoLuong AS itemQty
								, dh.DonViTinh as itemUOM
								, dc.%3$s AS warehouse
								, dc.%4$s as refWarehouse
								, sp.QuanLyTheoMa AS serial
								, sp.DanhMaTuDong AS autoSerial
								, sp.KyTuDauMa AS preSerial
								, sp.DoDaiMa AS serialLength
								, sp.QuanLyTheoLo AS lot
								, sp.DanhLoTuDong AS autoLot
								, sp.KyTuDauLo AS preLot
								, sp.DoDaiLo AS lotLength
								, sp.IOID refItem
								, 1 as SelectWarehouse
						   FROM OThongKeSanLuong as d
						   inner JOIN OSanXuat as sx 
							   on d.Ref_MaLenhSX = sx.IOID
						   INNER JOIN ODayChuyen as dc 
							   on sx.Ref_DayChuyen = dc.IOID
						   INNER JOIN %2$s AS dh 
							   on dh.IFID_M717 = d.IFID_M717
						   INNER JOIN OSanPham AS sp 
							   ON dh.Ref_MaSP = sp.IOID
						   WHERE dh.IOID = %1$d', $ioid, $getDataObject
				, $warehouseField, $refWarehouse);
		} 
		else // Doi tuong chinh
		{
			$warehouseField = 'KhoSanXuat';
			$refWarehouse   = 'Ref_KhoSanXuat';
			$sql = sprintf('SELECT dh.MaSP AS itemCode
								, sx.Ref_MaSP AS refItemCode
								, dh.TenSP AS itemName
								, dh.SoLuong AS itemQty
								, dh.DonViTinh as itemUOM
								, dc.%2$s AS warehouse
								, dc.%3$s as refWarehouse
								, dc.%2$s AS toWarehouse
								, dc.%3$s as refToWarehouse
								, sp.QuanLyTheoMa AS serial
								, sp.DanhMaTuDong AS autoSerial
								, sp.KyTuDauMa AS preSerial
								, sp.DoDaiMa AS serialLength
								, sp.QuanLyTheoLo AS lot
								, sp.DanhLoTuDong AS autoLot
								, sp.KyTuDauLo AS preLot
								, sp.DoDaiLo AS lotLength
								, sp.IOID refItem
								, 1 as SelectWarehouse
						   FROM OThongKeSanLuong as dh
						   inner JOIN OSanXuat as sx 
							   on dh.Ref_MaLenhSX = sx.IOID
						   inner JOIN ODayChuyen as dc 
							   on sx.Ref_DayChuyen = dc.IOID
						   INNER JOIN OSanPham AS sp ON sx.Ref_MaSP = sp.IOID
						   WHERE dh.IFID_M717 = %1$d', $ifid, $warehouseField, $refWarehouse);
		}
		return $sql;
	}
	
	/**
	 * 
	 * @return default sql
	 */
	public function getSerialLotSqlDefault()
	{
		return 'SELECT null AS itemCode
					, 0 AS refItemCode
					, null AS itemName
					, 0 AS itemQty
					, null as itemUOM
					, 0 AS serial
					, 0 AS autoSerial
					, null AS preSerial
					, 0 AS serialLength
					, 0 AS lot
					, 0 AS autoLot
					, null AS preLot
					, 0 AS lotLength
					, null AS warehouse
					, 0 as refWarehouse
					, 0 as refItem
					, 0 as SelectWarehouse';
	}		
	
	/**
	 * Lay thong tin cau hinh san pham bao gom cac thong tin 
	 * - ma sp, ten sp 
	 * - cau hinh lot
	 * - cau hinh serial
	 * - thong tin kho cua dong san pham. (Kho chuyen den, chuyen di)
	 * @param string $module  ma module lay cau hinh san pham
	 * @param int $ioid ioid cua dong san pham
	 * @param int $ifid ifid cua dong san pham
	 * @param string $getDataObject ma object lay cau hinh san pham
	 * @return object thong tin cau hinh san pham
	 */
	public function getSerialLot($module, $ioid = 0, $ifid = 0, $getDataObject = '')
	{
		switch ($module)
		{

			// NHAP KHO - INPUT
			case 'M402':
				$sql = $this->getSerialLotSqlOfInputModule($ioid);
			break;

			// XUAT KHO - OUTPUT
			case 'M506':
				$sql = $this->getSerialLotSqlOfOutputModule($ioid);
			break;

			// CHUYEN KHO - MOVEMENT
			case 'M603':
				$sql = $this->getSerialLotSqlOfMovementModule($ioid, $ifid);
			break;

			// HANG DOI NHAP - INCOMMING
			case 'M610':
				$sql = $this->getSerialLotSqlOfIncommingModule($ifid);
			break;

			// HANG DOI XUAT - OUTGOING
			case 'M611':
				$sql = $this->getSerialLotSqlOfOutgoingModule($ifid);
			break;

			// PHIEU GIAO VIEC - PRODUCTION WORK ORDER
			case 'M712':
				$sql = $this->getSerialLotSqlOfWorkOrderModuleOfProduction($ioid, $ifid);
			break;

			// THONG KE SAN XUAT - PRODUCTION STATISTIC
			case 'M717':
				$sql = $this->getSerialLotSqlOfProductionStatisticModule($getDataObject, $ioid, $ifid);
			break;

			// DEFAULT
			default:
				$sql = $this->getSerialLotSqlDefault();
			break;
		}
		return $this->_o_DB->fetchOne($sql);
	}
	
	/**
	 * Lay cau hinh bin cua kho (neu kho co cho chua san pham) @#$%&
	 * @param int $refWarehouse
	 * @param int $refItem
	 * @param string $uom
	 * @return type
	 */
	public function getBinByWarehouse($refWarehouse, $refItem, $uom = '')
	{
		$sql = sprintf('
				SELECT
					/*kho.MaKho AS warehouse,*/
					bin.MaBin AS binCode,
					bin.TenBin AS binName,
					/*ifnull(bin.MaSP, \'\') AS binItem,*/
					/*ifnull(bin.Ref_MaSP, 0) AS binRefItem,*/
					/*ifnull(bin.DonViTinh, \'\') AS binUOM,*/
					/*ifnull(bin.SucChua, 0) AS binCapacity,*/
					(
					SELECT
						count(1)
					FROM
						OBin AS u
					LEFT JOIN 
						ODanhSachKho AS k1 ON k1.IFID_M601 = u.IFID_M601
					WHERE
						u.lft <= bin.lft
					AND u.rgt >= bin.rgt
					AND k1.IOID = %1$s
					) AS LEVEL
				FROM
					OBin AS bin
				INNER JOIN ODanhSachKho AS kho ON bin.IFID_M601 = kho.IFID_M601
				WHERE
					kho.IOID = %1$s
				AND (
					bin.Ref_MaSP = %2$d
					OR CONVERT (
						CAST(bin.DonViTinh AS BINARY) USING utf8
					) = %3$s
					OR bin.Ref_MaSP IS NULL
				)
				ORDER BY
					bin.lft,
					bin.IOID ASC
				'
			, @(int) $refWarehouse
			, $refItem
			, $this->_o_DB->quote($uom));
		
		return $this->_o_DB->fetchAll($sql);
	}
	
	/**
	 * Lay ra thong tin thuoc tinh cua dong san pham 
	 * @todo: doi ten ham thanh getAttributeTable
	 * @param type $module
	 * @param type $ioid
	 * @param type $ifid
	 * @param type $getDataObject
	 * @return type
	 */
	public function getAttributeTable($module, $ioid = 0, $ifid = 0, $getDataObject = '')
	{
		if (!Qss_Lib_System::objectInForm('M113', 'OThuocTinhSanPham')
			|| !$module)
		{
			return;
		}
		
		switch ($module)
		{
			// NHAN HANG
			case 'M402':
				$objectCode    = 'ODanhSachNhapKho';
				$itemCodeField = 'MaSanPham';
			break;
		
			// TRA HANG
			case 'M403':
				$objectCode    = 'ODanhSachTraHang';
				$itemCodeField = 'MaSP';				
			break;
		
			// CHUYEN HANG
			case 'M506':
				$objectCode    = 'ODanhSachXuatKho';
				$itemCodeField = 'MaSP';					
			break;		
		
			// NHAN HANG TRA LAI
			case 'M507':
				$objectCode    = 'ODanhSachHangTL';
				$itemCodeField = 'MaSP';				
			break;
		
			// CHUYEN KHO
			case 'M603':
				$objectCode    = 'ODanhSachCK ';
				$itemCodeField = 'MaSP';				
			break;		
		
			// PHIEU GIAO VIEC
			case 'M712':
				$objectCode    = $getDataObject ? $getDataObject : 'OPhieuGiaoViec';
				$itemCodeField = 'MaSP';				
			break;		
		
			// THONG KE SAN LUONG
			case 'M717':
				$objectCode    = $getDataObject ? $getDataObject : 'OThongKeSanLuong';
				$itemCodeField = 'MaSP';					
			break;


			// MAC DINH
			default:
				return;
			break;		
		}
		
		$sql = sprintf('
			SELECT
				ttsp.*
			FROM
				%1$s AS nh
			INNER JOIN OSanPham AS sp ON nh.Ref_%2$s = sp.IOID
			INNER JOIN OThuocTinhSanPham AS ttsp ON ttsp.IFID_M113 = sp.IFID_M113
			WHERE
				nh.IFID_%3$s = %4$d
			AND nh.IOID = %5$d
			AND ttsp.HoatDong = 1
		'
			, $objectCode
			, $itemCodeField
			, $module
			, $ifid
			, $ioid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	
	/**
	 * Kiem tra xem dong san pham co thuoc tinh hay khong
	 * @param type $module
	 * @param type $ioid
	 * @param type $ifid
	 * @param type $getDataObject
	 * @return type
	 */
	public function checkAttributeExists($module, $ioid = 0, $ifid = 0, $getDataObject = '')
	{
		$dataSql = $this->getAttributeTable($module, $ioid, $ifid, $getDataObject);
		return $dataSql?true:false;
	}

}
