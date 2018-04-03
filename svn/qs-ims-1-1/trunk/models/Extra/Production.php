<?php

/**
 * @author: ThinhTuan
 * @component: Production
 */
class Qss_Model_Extra_Production extends Qss_Model_Abstract {

    public function __construct() {
        parent::__construct();
    }
    
    /// * Function: mergeLinesWithShifts - trong day chuyen voi ca
    public function mergeLinesWithShifts()
    {
        $sql = sprintf('select dc.MaDayChuyen as LineCode, dc.IOID as LineID
                                        , c.MaCa as ShiftCode, c.IOID as ShiftID
                                        from ODayChuyen as dc
                                        inner join OCa as c on 1 = 1
                                        order by dc.MaDayChuyen, c.GioBatDau');
        return $this->_o_DB->fetchAll($sql);
    }
    /// End mergeLinesWithShifts    

    public function getNVL($item, $cauThanh) {
        $sql = sprintf('
						SELECT tpsp.* 
						FROM OCauThanhSanPham as ctsp
						INNER JOIN OThanhPhanSanPham as tpsp
						ON ctsp.IFID_M114 = tpsp.IFID_M114
						WHERE ctsp.MaSanPham = %1$s
						AND ctsp.IOID = %2$d
		', $this->_o_DB->quote($item), $cauThanh);
        //echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getSanPhamDauRa($item, $cauThanh) {
        $sql = sprintf('
						SELECT spdr.* 
						FROM OCauThanhSanPham as ctsp
						INNER JOIN OSanPhamDauRa as spdr
						ON ctsp.IFID_M114 = spdr.IFID_M114
						WHERE ctsp.MaSanPham = %1$s
						AND ctsp.IOID = %2$d
		', $this->_o_DB->quote($item), $cauThanh);
        return $this->_o_DB->fetchAll($sql);
    }

    /*
     * Get all prodution items
     */


    /*
     * Get defect items group by defect type 
     */

    public function getDefectItemsGroupByType($start, $end, $item = 0) {
        $item = $item ? " and tksl.Ref_MaSP = {$item}" : "";
        $sql = sprintf('
                            select *, sum(spl.SoLuong) as SoLuongLoi from OThongKeSanLuong as tk
                            inner join OSanLuong as tksl on tk.IFID_M717 = tksl.IFID_M717
                            inner join OSanPhamLoi as spl on tk.IFID_M717 = spl.IFID_M717 
                                and spl.Ref_MaSP = tksl.Ref_MaSP
                            where (tk.Ngay between %1$s and %2$s)	%3$s
                            group by spl.Ref_MaLoi
		', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $item);
        return $this->_o_DB->fetchAll($sql);
    }

    /*
     * Get defect items group by defect cause
     */

    public function getDefectItemsGroupByReason($start, $end, $item = 0) {
        $item = $item ? " and tksl.Ref_MaSP = {$item}" : "";
        $sql = sprintf('
                    select *, sum(spl.SoLuong) as SoLuongLoi from OThongKeSanLuong as tk
                    inner join OSanLuong as tksl on tk.IFID_M717 = tksl.IFID_M717
                    inner join OSanPhamLoi as spl on tk.IFID_M717 = spl.IFID_M717 
                        and spl.Ref_MaSP = tksl.Ref_MaSP
                    where (tk.Ngay between %1$s and %2$s) %3$s
                    group by spl.Ref_NguyenNhan		
		', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $item);
        return $this->_o_DB->fetchAll($sql);
    }

    /*
     * Return qty and total qty
     */

    public function getOutputStatistics($start, $end, $period, $items, $groupbyType) {
        $items[] = 0;
        $select  = '';
        $groupby = '';
        $orderby = '';
        $where   = ' where 1=1 ';
        //$where  .= sprintf(' and tk.Ref_MaSP in (%1$s)', implode(',',$items));
        $tempRI  = '';
        
        foreach ($items as $refItem) {
            $tempRI .= $tempRI ? ' or ' : '';
            $tempRI .= sprintf('  lsx.Ref_MaSP = %1$d', $refItem);
        }
        
        $where .= $tempRI ? sprintf(' and (%1$s)', $tempRI) : '';
        $where .= sprintf(' and (tk.Ngay between %1$s and %2$s) ', $this->_o_DB->quote($start)
                        , $this->_o_DB->quote($end));

        switch ($period) {
            case 'D':
                $groupby = ' group by tk.Ngay, lsx.Ref_MaSP';

                if ($groupbyType == 'time') {
                    $orderby = ' order by tk.Ngay, lsx.Ref_MaSP';
                } elseif ($groupbyType == 'item') {
                    $orderby = ' order by lsx.Ref_MaSP, tk.Ngay';
                }

                break;
            case 'W':
                $groupby = ' group by  year(tk.Ngay),week(tk.Ngay), lsx.Ref_MaSP';

                if ($groupbyType == 'time') {
                    $orderby = ' order by  year(tk.Ngay),week(tk.Ngay), lsx.Ref_MaSP';
                } elseif ($groupbyType == 'item') {
                    $orderby = ' order by lsx.Ref_MaSP, year(tk.Ngay), week(tk.Ngay)';
                }

                break;
            case 'M':
                $groupby = ' group by year(tk.Ngay),month(tk.Ngay), lsx.Ref_MaSP';

                if ($groupbyType == 'time') {
                    $orderby = ' order by year(tk.Ngay),month(tk.Ngay), lsx.Ref_MaSP';
                } elseif ($groupbyType == 'item') {
                    $orderby = ' order by lsx.Ref_MaSP,year(tk.Ngay), month(tk.Ngay)';
                }
                break;
            case 'Q':
                $groupby = ' group by year(tk.Ngay),quarter(tk.Ngay), lsx.Ref_MaSP';

                if ($groupbyType == 'time') {
                    $orderby = ' order by year(tk.Ngay),quarter(tk.Ngay), lsx.Ref_MaSP';
                } elseif ($groupbyType == 'item') {
                    $orderby = ' order by lsx.Ref_MaSP, year(tk.Ngay),quarter(tk.Ngay)';
                }
                break;
            case 'Y':
                $groupby = ' group by year(tk.Ngay), lsx.Ref_MaSP';

                if ($groupbyType == 'time') {
                    $orderby = ' order by year(tk.Ngay), lsx.Ref_MaSP';
                } elseif ($groupbyType == 'item') {
                    $orderby = ' order by lsx.Ref_MaSP, year(tk.Ngay)';
                }
                break;
        }
        $select = ', week(tk.Ngay) as WeekOf, month(tk.Ngay) as MonthOf'
                . ', quarter(tk.Ngay) as QuarterOf, year(tk.Ngay) as YearOf';

        
        // @Note: 13-08-2014: doi tk.SoLuongThucHien => tk.SoLuong
        $sql = sprintf('select * %4$s
                        , sum(tk.SoLuong) as SoLuong
                        , (sum(tk.SoLuong) + sum(ifnull(spl.SoLuong,0))) as TongSo
                        , sum(ifnull(spl.SoLuong,0)) as SoLuongLoi
                        , lsx.Ref_MaSP as Ref_MaSP , lsx.MaSP, tk.TenSP
                        from OThongKeSanLuong as tk
                        inner join OSanXuat as lsx on tk.Ref_MaLenhSX = lsx.IOID
                        left join OSanPhamLoi as spl on tk.IFID_M717 = spl.IFID_M717
                            and lsx.Ref_MaSP = spl.Ref_MaSP
                            and ifnull(lsx.Ref_ThuocTinh,0) = ifnull(spl.Ref_ThuocTinh,0)
                        %1$s %2$s %3$s', $where, $groupby, $orderby, $select);
        
        return $this->_o_DB->fetchAll($sql);
    }

    /*
     * Get deface qty by deface type or deface reason
     * $change = 1 => deface type
     * $change = 2 => deface reason
     */

    public function getDefaceOutputStatistics($start, $end, $period, $items, $groupbyType, $change = 1) {
        $items[] = 0;
        $select = '';
        $groupby = '';
        $orderby = '';
        $where = ' where 1=1 ';
        $where .= sprintf(' and lsx.Ref_MaSP in (%1$s)', implode(',', $items));
        $where .= sprintf(' and (tk.Ngay between %1$s and %2$s) ', $this->_o_DB->quote($start)
                    , $this->_o_DB->quote($end));

        switch ($period) {
            case 'D':
                $groupby = ' group by tk.Ngay, lsx.Ref_MaSP';

                if ($groupbyType == 'time') {
                    $orderby = ' order by tk.Ngay, lsx.Ref_MaSP';
                } elseif ($groupbyType == 'item') {
                    $orderby = ' order by lsx.Ref_MaSP, tk.Ngay';
                }
                break;
            case 'W':
                $groupby = ' group by year(tk.Ngay),week(tk.Ngay), lsx.Ref_MaSP';

                if ($groupbyType == 'time') {
                    $orderby = ' order by year(tk.Ngay),week(tk.Ngay), lsx.Ref_MaSP';
                } elseif ($groupbyType == 'item') {
                    $orderby = ' order by lsx.Ref_MaSP, year(tk.Ngay),week(tk.Ngay)';
                }
                break;
            case 'M':
                $groupby = ' group by year(tk.Ngay),month(tk.Ngay), lsx.Ref_MaSP';

                if ($groupbyType == 'time') {
                    $orderby = ' order by year(tk.Ngay),month(tk.Ngay), lsx.Ref_MaSP';
                } elseif ($groupbyType == 'item') {
                    $orderby = ' order by lsx.Ref_MaSP, year(tk.Ngay),month(tk.Ngay)';
                }
                break;
            case 'Q':
                $groupby = ' group by year(tk.Ngay),quarter(tk.Ngay), lsx.Ref_MaSP';

                if ($groupbyType == 'time') {
                    $orderby = ' order by year(tk.Ngay),quarter(tk.Ngay), lsx.Ref_MaSP';
                } elseif ($groupbyType == 'item') {
                    $orderby = ' order by lsx.Ref_MaSP, year(tk.Ngay),quarter(tk.Ngay)';
                }
                break;
            case 'Y':
                $groupby = ' group by year(tk.Ngay), lsx.Ref_MaSP';

                if ($groupbyType == 'time') {
                    $orderby = ' order by year(tk.Ngay), lsx.Ref_MaSP';
                } elseif ($groupbyType == 'item') {
                    $orderby = ' order by lsx.Ref_MaSP, year(tk.Ngay)';
                }
                break;
        }
        $select = ', week(tk.Ngay) as WeekOf, month(tk.Ngay) as MonthOf'
                . ' , quarter(tk.Ngay) as QuarterOf, year(tk.Ngay) as YearOf';

        switch ($change) {
            case 1:
                $groupby .= ' ,spl.Ref_MaLoi';
                $orderby .= ' ,spl.Ref_MaLoi';
                break;
            case 2:
                $groupby .= ' ,spl.Ref_NguyenNhan';
                $orderby .= ' ,spl.Ref_NguyenNhan';
                break;
        }


        $sql = sprintf(' select *, sum(ifnull(spl.SoLuong,0)) as SoLuongLoi %4$s, lsx.Ref_MaSP
                    from OThongKeSanLuong as tk 
                    inner join OSanXuat as lsx on tk.Ref_MaLenhSX = lsx.IOID
                    left join OSanPhamLoi as spl on tk.IFID_M717 = spl.IFID_M717
                        and lsx.Ref_MaSP = spl.Ref_MaSP
                        and ifnull(lsx.Ref_ThuocTinh,0) = ifnull(spl.Ref_ThuocTinh,0)
                    %1$s %2$s %3$s
						 ', $where, $groupby, $orderby, $select);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getMOLineUpdate($refItem, $refAttr, $refOperation, $statisticsIFID) {
        $sql = sprintf('select ctsx.* 
						from OThongKeSanLuong as tksl
						inner join OSanXuat as sx on tksl.Ref_MaLenhSX = sx.IOID
						inner join OChiTietSanXuat as ctsx on sx.IFID_M710 = ctsx.IFID_M710
						#inner join OSanPham as sp on ctsx.Ref_MaSP = sp.IOID
						where
						tksl.IFID_M717 = %4$s 
						and ctsx.Ref_MaSP = %1$d
						and ifnull(ctsx.Ref_ThuocTinh,0) = %2$d
						and ctsx.Ref_CongDoan = %3$d
						#and sp.Ref_DonViTinh = 
						', $refItem, $refAttr, $refOperation, $statisticsIFID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getPRLineUpdate($refItem, $refAttr, $refOperation, $statisticsIFID) {
        $sql = sprintf('select ycsx.* 
						from OThongKeSanLuong as tksl
						inner join OSanXuat as sx on tksl.Ref_MaLenhSX = sx.IOID
						inner join OChiTietSanXuat as ctsx on sx.IFID_M710 = ctsx.IFID_M710
						inner join qsioidlink as link on link.ToIOID = ctsx.IOID AND link.ToIFID = ctsx.IFID_M710
						inner join OYeuCauSanXuat as ycsx on link.FromIOID = ycsx.IOID AND link.FromIFID = ycsx.IFID_M764
						#inner join OSanPham as sp on ctsx.Ref_MaSP = sp.IOID
						where
						tksl.IFID_M717 = %4$s  
						and ctsx.Ref_MaSP = %1$d
						and ifnull(ctsx.Ref_ThuocTinh, 0) = %2$d
						and ctsx.Ref_CongDoan = %3$d
						#and sp.Ref_DonViTinh = 
						', $refItem, $refAttr, $refOperation, $statisticsIFID);
        return $this->_o_DB->fetchOne($sql);
    }

    // Lay danh sach san pham theo day chuyen va bom cua chung
    public function getItemAndBOMByLine($lineID, $extItemID = 0) {
        // Loc them theo item 
        $itemFilter = $extItemID ? sprintf(' and spcdc.Ref_MaSanPham = %1$d', $extItemID) : '';
        $sql = sprintf('select spcdc.MaSanPham as ItemCode, spcdc.TenSanPham as Item, spcdc.Ref_MaSanPham as RefItem
						, spcdc.SoLuongTrenGio as QtyPerHour, spcdc.DonViTinh as ItemUOM
						, ctsp.ThuocTinh as Attribute, ctsp.DonViTinh as UOM, ctsp.ThaoRoLapDat as Assembly 
						, ctsp.TenCauThanhSanPham as BOM, ctsp.IFID_M114 as BOMIFID, ctsp.SoLuong as BOMQty
						, ctsp.IOID as BOMIOID, ifnull(ctsp.Ref_ThuocTinh, 0) as RefAttribute
						, dc.Ref_LichLamViec as CalendarID, dc.IOID as LineID
						, ifnull(tpsp.IOID,0) as TPIOID, ifnull(tpsp.SoLuong, 0) as MemberQty
						, ifnull(cd.IOID,0) as CDIOID, ifnull(cd.SoGio, 0) as OperationTime
						, cd.Ten as Operation, cd.Ref_Ten as RefOperation
						, cddc.IOID as CDDCIOID, cddc.Ref_MaDonViThucHien as WorkCenterID, cddc.DonViThucHien as WorkCenter
						, case when ctsp.ThaoRoLapDat = 1 then ctsp.Ref_MaSanPham else tpsp.Ref_MaThanhPhan end as RefItemX
						, case when ctsp.ThaoRoLapDat = 1 then ifnull(ctsp.Ref_ThuocTinh, 0) else ifnull(tpsp.Ref_ThuocTinh, 0) end as RefAttributeX
						, spdr.IOID as SPDRIOID
						from ODayChuyen as dc
						inner join OSanPhamCuaDayChuyen as spcdc on dc.IFID_M702 = spcdc.IFID_M702
						inner join OCauThanhSanPham as ctsp on ctsp.Ref_MaSanPham = spcdc.Ref_MaSanPham
						left join OCongDoanBOM as cd on cd.IFID_M114 = ctsp.IFID_M114
						left join OThanhPhanSanPham as tpsp on ifnull(ctsp.ThaoRoLapDat,0) = 0 
							and tpsp.IFID_M114 = ctsp.IFID_M114
							and tpsp.Ref_CongDoan = cd.Ref_Ten
						left join OCongDoanDayChuyen as cddc on dc.IFID_M702 = cddc.IFID_M702
							and cddc.Ref_CongDoan = cd.Ref_Ten
						left join OSanPhamDauRa as spdr on ctsp.IFID_M114 = spdr.IFID_M114
						and cd.Ref_Ten = spdr.Ref_CongDoan
						where dc.IOID = %1$d %2$s 
						and ctsp.HoatDong = 1 and ifnull(cd.IOID, 0) != 0 and (ifnull(tpsp.IOID, 0) != 0
						or ifnull(spdr.IOID,0) != 0) 
						order by ctsp.Ref_MaSanPham, ctsp.IOID, cd.IOID', $lineID, $itemFilter);
        //echo $sql; die;
        return $this->_o_DB->fetchAll($sql);
    }

    // Lay san pham co the san xuat theo nguyen vat lieu trong bom va so luong ton kho cua chung
    public function getMaxQtyByBOMAndInventory($BOMID) { // IFID
        if (!is_array($BOMID)) {
            if ($BOMID == '') {
                return;
            }
            $temp = $BOMID;
            $BOMID = array();
            $BOMID[] = $temp;
        } else {
            if (!count($BOMID))
                return;
        }

        $tempBOMID = '';
        foreach ($BOMID as $id) {
            $tempBOMID .= $tempBOMID ? ' or ' : '';
            $tempBOMID .= sprintf(' ctsp.IFID_M114 = %1$d ', $id);
        }
        $tempBOMID = $tempBOMID ? sprintf(' (%1$s) ', $tempBOMID) : '';

        $sql = sprintf('select ctsp.IFID_M114 as BOMIFID, ifnull(ctsp.SoLuong, 0) as BOMQty
						, ifnull(ctsp.ThaoRoLapDat, 0) as Assembly, ifnull(ctsp.Ref_MaSanPham, 0) as RefItemX
						, ifnull(ctsp.Ref_ThuocTinh, 0) as RefAttributeX
						, ifnull(k.SoLuongHC, 0) as InventoryQty
						, ifnull(tpsp.SoLuong, 0) as MemberQty
						, case when ctsp.ThaoRoLapDat = 1 then ifnull(ctsp.Ref_MaSanPham,0) else ifnull(tpsp.Ref_MaThanhPhan, 0) end as RefItem
						, case when ctsp.ThaoRoLapDat = 1 then ctsp.MaSanPham else tpsp.MaThanhPhan end as ItemCode
						, case when ctsp.ThaoRoLapDat = 1 then ctsp.TenSanPham else tpsp.TenThanhPhan end as ItemName
						, case when ctsp.ThaoRoLapDat = 1 then ctsp.ThuocTinh else tpsp.ThuocTinh end as Attribute
						, case when ctsp.ThaoRoLapDat = 1 then ifnull(ctsp.Ref_ThuocTinh, 0) else ifnull(tpsp.Ref_ThuocTinh, 0) end as RefAttribute
						, case when ctsp.ThaoRoLapDat = 1 then ctsp.DonViTinh else tpsp.DonViTinh end as UOM
						, case when ctsp.ThaoRoLapDat = 1 then ifnull(ctsp.SoLuong,0) else ifnull(tpsp.SoLuong, 0) end as Qty
                                                , ifnull(tpsp.BanTP, 0) as SFG
						from OCauThanhSanPham as ctsp
						left join OThanhPhanSanPham as tpsp on ctsp.IFID_M114 = tpsp.IFID_M114 
							and ifnull(ctsp.ThaoRoLapDat,0) = 0
						left join OKho as k on
							(ctsp.ThaoRoLapDat = 1 and ctsp.Ref_MaSanPham = k.Ref_MaSP 
								and ifnull(ctsp.Ref_ThuocTinh,0) = ifnull(k.Ref_ThuocTinh,0) )
							or
							(ifnull(ctsp.ThaoRoLapDat,0) = 0 and tpsp.Ref_MaThanhPhan = k.Ref_MaSP   
								and ifnull(tpsp.Ref_ThuocTinh,0) = ifnull(k.Ref_ThuocTinh,0) )
						where %1$s
						and ctsp.HoatDong = 1 
						#and ifnull(tpsp.IOID, 0) != 0
						order by ctsp.IFID_M114, ctsp.MaSanPham, tpsp.MaThanhPhan', $tempBOMID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getPlanedOperationByLineShiftDate($line, $fromdate, $todate) {
        $sql = sprintf('select sum(ifnull(sx.SoLuong,0)) as OperationQty
						#, Ref_CongDoan as OperationID
						from OSanXuat as sx
						where 	
						sx.Ref_DayChuyen = %1$d
						and 
						(sx.TuNgay  >= %2$s
						and 
						sx.DenNgay <= %3$s
						)
						#group by Ref_CongDoan ', $line, $this->_o_DB->quote($fromdate)
                , $this->_o_DB->quote($todate));
        return $this->_o_DB->fetchAll($sql);
    }

    // filterArr 
    // 		date (Y-m-d), line, shift, operation, workcenter, assembly, item, attr (ID)
    public function getExistsProductionOrder($filterArr) {
        //$filter = (isset($filterArr['date']) && $filterArr['date'])?//date

        $sql = sprintf('select *
                                from OSanXuat
                                where
                                SanXuatSuaChua = 1
                                #and NgayYeuCau = 
                                and TuNgay = %1$s
                                and DenNgay = %6$s
                                and Ref_DayChuyen = %2$d
                                and ifnull(ThaoDo, 0) = %3$d
                                and Ref_MaSP = %4$d
                                and ifnull(Ref_ThuocTinh ,0) = %5$d
                                ', $this->_o_DB->quote($filterArr['start']), $filterArr['line']
                , $filterArr['assembly']
                , $filterArr['item'], $filterArr['attr']
                , $this->_o_DB->quote($filterArr['end']));
        //SanXuatSuaChua NgayYeuCau DayChuyen CaSX CongDoan DonViSanXuat ThaoDo MaSP ThuocTinh
        //echo $sql;die;
        return $this->_o_DB->fetchOne($sql);
    }

    // $date: Ngay thong ke
    // $type: Kieu 1 - San luong 2 - Nguyen vat lieu 
    // @todo: 3 - San luong + Nguyen vat lieu
    public function getProductionVolume($date, $type, $echo = false) {
        // date loc theo ngay
        $dateFilter = sprintf(' tksl.Ngay = %1$s ', $this->_o_DB->quote($date));
        $selectByType = 'null';

        if ($type == 1) { // type = 1 lay san luong 
            $typeFilter = sprintf(' #inner join OSanLuong as jobj on tksl.ThaoDo = 1 '
                                    . '#and tksl.IFID_M717 = jobj.IFID_M717  ');
            $selectByType = sprintf('
				, tksl.Ref_MaSP  as RefItem
				, tksl.MaSP  as ItemCode
				, tksl.TenSP  as ItemName
				, tksl.DonViTinh  as UOM
				, tksl.ThuocTinh  as Attribute
				, ifnull(tksl.Ref_ThuocTinh, 0)  as RefAttribute
				, tksl.SoLuong  as Qty
			');
                    $sql = sprintf('select lsx.DayChuyen as LineCode, lsx.Ref_DayChuyen as LineID
                            , tksl.Ca as ShiftCode, tksl.Ref_Ca as ShiftID %1$s
                        from OThongKeSanLuong as tksl 
                        inner join OSanXuat as lsx on tksl.Ref_MaLenhSX = lsx.IOID
                        %2$s
                        where %3$s', $selectByType, $typeFilter, $dateFilter);
            
        } elseif ($type == 2) {// type = 2 lay nguyen vat lieu
            $typeFilter = sprintf(' inner join ONVLDauVao as jobj on 
                                    # ifnull(tksl.ThaoDo, 0) = 0 and 
                                    tksl.IFID_M712 = jobj.IFID_M712');
            $selectByType = sprintf('
				, jobj.Ref_MaSP  as RefItem
				, jobj.MaSP  as ItemCode
				, jobj.TenSP  as ItemName
				, jobj.DonViTinh  as UOM
				, jobj.ThuocTinh  as Attribute
				, ifnull(jobj.Ref_ThuocTinh, 0)  as RefAttribute
				, jobj.SoLuong  as Qty
			');
            
                    $sql = sprintf('select lsx.DayChuyen as LineCode, lsx.Ref_DayChuyen as LineID
                            , tksl.Ca as ShiftCode, tksl.Ref_Ca as ShiftID %1$s
                        from OPhieuGiaoViec as tksl 
                        inner join OSanXuat as lsx on tksl.Ref_MaLenhSX = lsx.IOID
                        %2$s
                        where %3$s', $selectByType, $typeFilter, $dateFilter);
        }

        if ($echo) {
            echo $sql;
            die;
        } else {
            return $this->_o_DB->fetchAll($sql);
        }
    }

    // @Function: getProductionRequirementByLine(), lay dong chinh yeu cau cung ung theo day chuyen
    // @Parameter:
    //		- $filter: dc, ngay bd, ngay kt array($manuLineID=> id, start=> date, end=> date)
    // 		- Chi loc theo day chuyen
    //		- $count: true/false, true tra ve so luong ban ghi, false tra ve cac ban ghi
    //		- $pagination: phan trang voi $count = false, array('page'=>,'display'=>)
    // @Return: tra ve so luong ban ghi hoac cac ban ghi cua yeu cau san xuat theo day chuyen
    public function getProductionRequirementByLine($filter, $count = false, $pagination = array()) {
        if ($count) {
            $sql = sprintf('
							select count(1) as NumberRecord
							from
							(
							select 
							distinct yccu.IFID_M764
							from OChiTietYeuCau as ctyc
							inner join OYeuCauSanXuat as yccu on ctyc.IFID_M764 = yccu.IFID_M764
							left join OKeHoachCungUng as khcu on khcu.SoDonHang = yccu.SoYeuCau 
							inner join OSanPhamCuaDayChuyen as spcdc on ctyc.Ref_MaSP = spcdc.Ref_MaSanPham
							inner join ODayChuyen as dc on spcdc.IFID_M702 = dc.IFID_M702
							where dc.IOID = %1$d
							and ifnull(khcu.IOID, 0) = 0
							#and 
							#	((ctyc.NgayBatDau between %%2$s and %%3$s)
							#	or
							#	(ctyc.NgayKetThuc between %%2$s and %%3$s))
							group by yccu.IFID_M764
							) as t1', $filter['manuLineID']);
            //, $this->_o_DB->quote($filter['start'])
            //, $this->_o_DB->quote($filter['end']));
            $dataSql = $this->_o_DB->fetchOne($sql);
            return $dataSql ? $dataSql->NumberRecord : 0;
        } else {
            $startPosition = ($pagination['page'] - 1) * $pagination['display'];
            $sql = sprintf('select 
							yccu.IFID_M764 as MainIFID, yccu.SoYeuCau as ReqNo
							, yccu.TuNgay as ReqStartDate, yccu.DenNgay as ReqEndDate
							from OChiTietYeuCau as ctyc
							inner join OYeuCauSanXuat as yccu on ctyc.IFID_M764 = yccu.IFID_M764
							left join OKeHoachCungUng as khcu on khcu.SoDonHang = yccu.SoYeuCau 
							inner join OSanPhamCuaDayChuyen as spcdc on ctyc.Ref_MaSP = spcdc.Ref_MaSanPham
							inner join ODayChuyen as dc on spcdc.IFID_M702 = dc.IFID_M702
							where dc.IOID = %1$d
							and ifnull(khcu.IOID, 0) = 0
							#and 
							#	((ctyc.NgayBatDau between %%4$s and %%5$s)
							#	or
							#	(ctyc.NgayKetThuc between %%4$s and %%5$s))
							group by yccu.IFID_M764
							order by ctyc.NgayBatDau, ctyc.NgayKetThuc, yccu.IOID
							limit %2$d, %3$d', $filter['manuLineID'], $startPosition, $pagination['display']);
            //, $this->_o_DB->quote($filter['start'])
            //, $this->_o_DB->quote($filter['end']));
            return $this->_o_DB->fetchAll($sql);
        }
    }

    // End getProductionRequirementByLine()
    // @Function: getProductionRequirementDetail(), lay chi tiet yeu cau cung ung cua tung yeu cau
    // @Parameter: 
    // 		- $reqArr: ifid yeu cau cung ung.
    // @Return: tra ve chi tiet yeu cau cung ung theo ifid truyen vao
    public function getProductionRequirementDetail($reqArr) {
        $temp = '';
        foreach ($reqArr as $id) {
            $temp .= $temp ? ' or ' : '';
            $temp .= sprintf(' ctyc.IFID_M764 = %1$d ', $id);
        }
        $temp = $temp ? sprintf(' and (%1$s)  ', $temp) : ' and 1 = 0 ';

        $sql = sprintf('select 
						ctyc.IFID_M764 as MainIFID, ctyc.IOID as SubIOID, ctyc.MaSP as ItemCode
						, ctyc.TenSP as ItemName, ctyc.ThuocTinh as Attribute
						, ctyc.DonViTinh as ItemUOM, ctyc.SoLuong as ItemQty
						, ctyc.ThamChieu as Ref, ctyc.NgayBatDau as StartDate
						, ctyc.NgayKetThuc as EndDate, ctyc.IOID as IOID
						, ifnull(lsx.IOID, 0) as ToIOID, ctyc.Ref_MaSP as RefItem
						, ifnull(lsx.SoLuong, 0) as ProductionQty
						from OChiTietYeuCau as ctyc
						left join qsioidlink as link on ctyc.IOID = link.FromIOID AND ctyc.IFID_M764 = link.FromIFID
						left join OSanXuat as lsx on lsx.IOID = link.ToIOID AND lsx.IFID_M710 = link.ToIFID
						where 1=1 %1$s
						', $temp);
        return $this->_o_DB->fetchAll($sql);
    }

    // End getProductionRequirementDetail()
    // @general
    public function getBomByItems($refItemArr) {
        $temp = '';
        foreach ($refItemArr as $id) {
            $temp .= $temp ? ' or ' : '';
            $temp .= sprintf(' ctsp.Ref_MaSanPham = %1$d ', $id);
        }
        $temp = $temp ? sprintf(' and (%1$s)  ', $temp) : ' and 1 = 0 ';

        $sql = sprintf('select ctsp.TenCauThanhSanPham as BOMName
						, ctsp.IOID as BOMKey, ctsp.Ref_MaSanPham as RefItem
						, ifnull(ctsp.ThaoRoLapDat, 0) as Assembly
						from OCauThanhSanPham as ctsp
						where ctsp.HoatDong = 1 %1$s ', $temp);
        return $this->_o_DB->fetchAll($sql);
    }

    // @Function: getBOMByIdArr(), lay cac "Thiet ke san pham" theo ioid cua chung
    // @Parameter: 
    // 		- $bomIDArr: mang chi so IOID cua BOM.
    //		- $getType:
    //			+ 1 - Thanh phan san pham theo cong doan
    //			+ 2 - San pham dau ra theo cong doan
    // 			+ 3 - Cong doan cua BOM
    //			+ 4 - Lay cong cu dung cu
    // @Return: Chi tiet mot hoac nhieu "Thiet ke san pham".
    public function getBOMByIdArr($bomIDArr, $getType = 1, $echo = false) {
        $temp = '';
        foreach ($bomIDArr as $id) {
            $temp .= $temp ? ' or ' : '';
            $temp .= sprintf(' ctsp.IOID = %1$d ', $id);
        }
        $temp = $temp ? sprintf(' and (%1$s)  ', $temp) : ' and 1 = 0 ';

        $join = '';
        $select = '';
        switch ($getType) {
            case 1:
                $select .= sprintf('tpsp.Ref_MaThanhPhan as RefItem, tpsp.MaThanhPhan as ItemCode
									, tpsp.TenThanhPhan as ItemName
									, tpsp.ThuocTinh as Attribute, ifnull(tpsp.Ref_ThuocTinh, 0) as RefAttribute
									, tpsp.Ref_CongDoan as RefOperation
									, tpsp.DonViTinh as ItemUOM
									, tpsp.SoLuong as ItemQty
									, ctsp.IOID as MainIOID
									, ifnull(ctsp.ThaoRoLapDat,0) as Assembly');
                $join .= sprintf(' inner join OThanhPhanSanPham as tpsp on ctsp.IFID_M114 = tpsp.IFID_M114 
									and ifnull(tpsp.Ref_CongDoan,0) = cd.Ref_Ten');
                break;
            case 2:
                $select .= sprintf('spdr.Ref_MaSP as RefItem, spdr.MaSP as ItemCode, spdr.TenSP as ItemName
									, spdr.ThuocTinh as Attribute, ifnull(spdr.Ref_ThuocTinh, 0) as RefAttribute
									, spdr.Ref_CongDoan as RefOperation
									, spdr.DonViTinh as ItemUOM
									, spdr.SoLuong as ItemQty
									, spdr.BatDauTruoc as NextQty
									, ctsp.IOID as MainIOID
									, ifnull(ctsp.ThaoRoLapDat,0) as Assembly');
                $join .= sprintf(' inner join OSanPhamDauRa as spdr on ctsp.IFID_M114 = spdr.IFID_M114 
									and ifnull(spdr.Ref_CongDoan,0) = cd.Ref_Ten');
                break;
            case 3:
                $select .= ' 
							ctsp.DonViTinh as MainUOM, ctsp.SoLuong as MainQty
							, ifnull(ctsp.SoLuongToiThieu, 0) as MinQty
							, ctsp.IOID as MainIOID
							, ifnull(ctsp.ThaoRoLapDat,0) as Assembly
							, ctsp.Ref_MaSanPham as RefMainItem
							, ifnull(ctsp.Ref_ThuocTinh,0) as RefMainAttribute
							, ctsp.MaSanPham as MainItemCode
							, ctsp.TenSanPham as MainItemName
							, ifnull(ctsp.ThuocTinh, 0) as MainAttribute
							, cd.Ten as Operation, ifnull(cd.GiaCongNgoai, 0) as Outsource
							, ifnull(cd.ChiPhiGiaCong, 0) as Cost
							, cd.Ref_Ten as RefOperation, ifnull(cd.SoGio,0) as OperationTime';
                break;
            case 4:
                $select .= sprintf('ccdc.Ref_MaSP as RefItem, ccdc.MaSP as ItemCode, ccdc.TenSP as ItemName
									, ccdc.ThuocTinh as Attribute, ifnull(ccdc.Ref_ThuocTinh, 0) as RefAttribute
									, ccdc.Ref_CongDoan as RefOperation
									, ccdc.DonViTinh as ItemUOM
									, ccdc.SoLuong as ItemQty
									, ctsp.IOID as MainIOID
									, ifnull(ctsp.ThaoRoLapDat,0) as Assembly');
                $join .= sprintf(' inner join OCongCuCauThanh as ccdc on ctsp.IFID_M114 = ccdc.IFID_M114 
									and ifnull(ccdc.Ref_CongDoan,0) = cd.Ref_Ten');
                break;
        }

        $sql = sprintf('select %3$s
						from OCauThanhSanPham as ctsp
						left join OCongDoanBOM as cd on ctsp.IFID_M114 = cd.IFID_M114
						%2$s
						where ctsp.HoatDong = 1 %1$s
						order by ctsp.IOID, cd.STT ', $temp, $join, $select);
        if ($echo) {
            echo '<pre>';
            print_r($sql);
            die;
        } else {
            return $this->_o_DB->fetchAll($sql);
        }
    }

    // End getBomByIDArr()
    // Thoi gian da dat san xuat cua cac xu ly don hang khac
    public function getDaLay($startDate) {
        $sql = sprintf('select khsx.*
						, cddc.Ref_CongDoan as RefOperation
						, cddc.HieuSuat as Performance
						, cd.Ref_Ten as RefBomOperation
						, ifnull(cd.SoGio,0) as SoGio
						, ctsp.SoLuong as SoLuongBom
						from OSanXuat as khsx
						inner join ODayChuyen as dc on khsx.Ref_DayChuyen = dc.IOID
						inner join OCongDoanDayChuyen as cddc on dc.IFID_M702 = cddc.IFID_M702
						inner join OCauThanhSanPham as ctsp on khsx.Ref_ThietKe = ctsp.IOID
						left join OCongDoanBOM as cd on ctsp.IFID_M114 = cd.IFID_M114
							and cd.Ref_Ten = cddc.Ref_CongDoan
						where khsx.TuNgay >= %1$s
						', $this->_o_DB->quote($startDate));
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @param type description's
     * @return 
     */
    public function getProductionCosts($ifid) {
        $sql = sprintf('select 
                    thucte.Ref_MaSanPham as RealRefItem
                    , thucte.MaSanPham as RealItemCode
                    , thucte.TenSanPham as RealItemName
                    , thucte.Ref_ThuocTinh as RealRefAttribute
                    , thucte.ThuocTinh as RealAttribute
                    , thucte.Ref_CongDoan as RealRefOperator
                    , thucte.CongDoan as RealOperator
                    , thucte.DonViTinh as RealUOM
                    , thucte.SoLuong as RealQty
                    , thucte.CPNVL as RealConsumption
                    , thucte.CPNhanCong as RealEmployee
                    , thucte.CPMayMoc as RealMachine
                    , thucte.CPGianTiep as RealIndirect
                    , thucte.GiaThanhDonVi as RealUnit
                    , thucte.GiaThanh as RealTotal
                    , kehoach.MaSanPham as PlanItemCode
                    , kehoach.TenSanPham as PlanItemName
                    , kehoach.ThuocTinh as PlanAttribute
                    , kehoach.CongDoan as PlanOperator
                    , kehoach.DonViTinh as PlanUOM
                    , kehoach.SoLuong as PlanQty
                    , kehoach.CPNVL as PlanConsumption
                    , kehoach.CPNhanCong as PlanEmployee
                    , kehoach.CPMayMoc as PlanMachine
                    , kehoach.CPGianTiep as PlanIndirect
                    , kehoach.GiaThanhDonVi as PlanUnit
                    , kehoach.GiaThanh as PlanTotal
                    
                    from OGiaThanhSanXuat as thucte
                    inner join OTinhGiaThanh as tinhthucte
                    left join OTinhGiaThanh as tinhkehoach
                        on tinhkehoach.IFID_M761 is not null and tinhkehoach.Code = tinhthucte.Code
                    left join OGiaThanhSanXuat as kehoach
                        on tinhkehoach.IFID_M761 = kehoach.IFID_M761
                            and kehoach.Ref_MaSanPham = thucte.Ref_MaSanPham
                            and ifnull(kehoach.Ref_ThuocTinh,0) = ifnull(thucte.Ref_ThuocTinh, 0)
                            and kehoach.Ref_CongDoan = thucte.Ref_CongDoan 
                    where thucte.IFID_M713 = %1$d
                    and tinhthucte.IFID_M713 is not null
                    order by thucte.Ref_MaSanPham, thucte.Ref_CongDoan, thucte.IOID', $ifid);
        //echo $sql; die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @param int $RefItem IOID cua san pham
     * @param int $RefLine IOID cua day chuyen
     * @param date $date Ngay san xuat (Required)
     * @path /extra/production/createwo/po/search
     */
    public function getProductionOrdersForCreateWorkOrders($date, $RefLine = 0, $RefItem = 0)
    {
            $FilterLine = $RefLine?sprintf(' AND Ref_DayChuyen = %1$d ', $RefLine):'';
            $FilterItem = $RefItem?sprintf(' AND Ref_MaSP = %1$d ', $RefItem):'';
            $sql = sprintf('select * 
                                from OSanXuat
                                where TuNgay <= %1$s
                                and DenNgay >= %1$s
                                %2$s
                                %3$s'
                                , $this->_o_DB->quote($date), $FilterLine, $FilterItem);
            return $this->_o_DB->fetchAll($sql);
            
    }
    
   
   
    
   
    
    
}

?>