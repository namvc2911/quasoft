<?php

class Qss_Model_Hoaphat_Maintenance extends Qss_Model_Abstract
{
        public $_common;

        /**
         * Build constructor
         * '
         * @return void
         */
        public function __construct()
        {
                parent::__construct();
                $this->_common = new Qss_Model_Extra_Extra();
        }

    public function getEquipmentsList($equipGroup = 0, $loc = 0)
    {
       
        $where    = array();
        $whereSql = '';
        
        if($equipGroup)
        {
            $where[] = sprintf('  TB.Ref_NhomThietBi = %1$d', $equipGroup) ;
        }
        
        if($loc)
        {
            $locSql = sprintf(' select * from OKhuVuc where IOID = %1$d ', $loc);
            $locDat = $this->_o_DB->fetchOne($locSql);
            
            if($locDat)
            {
                $where[] = sprintf(' TB.Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)', $locDat->lft, $locDat->rgt) ;
            }
        }  

        $whereSql = $where?sprintf(' where %1$s ', implode(' and ', $where)):'';
        
        
        $sql = sprintf('
				SELECT 
                    TB.*
                    ,TB.NgayDuaVaoSuDung as Ngay
                    , KV.Ten AS TenKhuVuc
				FROM ODanhSachThietBi AS TB 
                LEFT JOIN OKhuVuc   AS KV ON ifnull(TB.Ref_MaKhuVuc, 0) = KV.IOID
				%1$s
                order by KV.lft
		', $whereSql);
        return $this->_o_DB->fetchAll($sql);
    }

        public function getPeriodWorkOrderHistory($start, $end, $equipGroup = 0)
        {
                $sql = sprintf('SELECT 
                                PBT.*, 
                                CDTB.Status, 
                                KV.Ten AS TenKhuVuc,
                                if(CDTB.Ngay is null,TB.NgayDuaVaoSuDung,CDTB.Ngay) as NgayCaiDat
                                FROM OPhieuBaoTri as PBT
                                LEFT JOIN ODanhSachThietBi as TB on PBT.Ref_MaThietBi = TB.IOID 
                                LEFT JOIN OKhuVuc AS KV On KV.IOID = ifnull(TB.Ref_MaKhuVuc, 0)
                                LEFT JOIN
                                    (select cd.IOID, cd.Ref_MaKVMoi, cd.MaKVMoi, cd.Ngay, cd.ThoiGian
                                    , pbt.IFID_M759 as IFID, pbt.Ref_MaThietBi , ifnull(qsiforms.Status, 0) as Status
                                    from OPhieuBaoTri as pbt
                                    
                                    inner join OCaiDatDiChuyen as cd on pbt.IFID_M759 = cd.IFID_M759
                                    inner join qsiforms on qsiforms.IFID = pbt.IFID_M759
                                    where ifnull(cd.HoanThanh,0) = 1
                                    and qsiforms.Status = 3
                                    and cd.Ngay <= %1$s 
                                    order by cd.Ngay desc,cd.ThoiGian desc 
                                    limit 1
                                ) as CDTB on CDTB.Ref_MaThietBi = TB.IOID
                                WHERE 
                                (PBT.Ngay BETWEEN %1$s AND %2$s)
                                 '
                        ,
                        $this->_o_DB->quote($start)
                        , $this->_o_DB->quote($end)
                );
                $sql .= ($equipGroup) ? ' AND TB.Ref_NhomThietBi = ' . $equipGroup : '';
                $sql .= ' ORDER BY PBT.MaThietBi, PBT.Ngay ';
                return $this->_o_DB->fetchAll($sql);
        }

        // @Remove
        public function getMaintenancePlans($maintType = 0, $eqGroup = 0) // include by period and not by period
        {

                $whereTemp = array();
                $where = '';

                // Add maintain type filter
                if ($maintType)
                {
                        $whereTemp[] = sprintf(' btdk.Ref_LoaiBaoTri = %1$d ',
                                $maintType);
                }

                // Add Eq group filter
                if ($eqGroup)
                {
                        $whereTemp[] = sprintf(' dstb.Ref_NhomThietBi = %1$d ',
                                $eqGroup);
                }
                $where = count($whereTemp) ? sprintf(' where %1$s ',
                                implode(' and ', $whereTemp)) : '';

                $sql = sprintf('SELECT btdk.*
                                , plbt.CanCu
                                , plbt.KyBaoDuong
                                , plbt.GiaTri
                                , plbt.ChiSo
                                , btdk.DieuChinhTheoPBT
                                , plbt.LapLai
                                , ifnull(btdk.BenNgoai,0) as BenNgoai
                                , t.GiaTri as GiaTriThu
                                , k.MaKy
                                , /*bttn.Ngay*/ NULL as NgayBTKDK
                                , kv1.Ten as TenKhuVucTheoDS
                            FROM OBaoTriDinhKy as btdk
                            left join OPhanLoaiBaoTri AS plbt ON plbt.IOID = btdk.Ref_LoaiBaoTri
                            left join OThu as t on t.IOID = btdk.Ref_Thu
                            left join OKy as k on k.IOID = plbt.Ref_KyBaoDuong
                            left join ODanhSachThietBi as dstb on dstb.IOID = btdk.Ref_MaThietBi
                            left join OKhuVuc AS kv1 ON kv1.IOID = ifnull(dstb.Ref_MaKhuVuc, 0)
                            /*left join OBaoTriTheoNgay as bttn on btdk.IFID_M724 = bttn.IFID_M724*/
                            %1$s
                            order by btdk.IOID
                            ', $where);
                return $this->_o_DB->fetchAll($sql);
        }

        public function getEquipMaterial($filter, $limit = 5000)
        {
                $sql = sprintf('select dspt.* , dstb.IFID_M705
                                        from ODanhSachThietBi as dstb 
                                        left join ODanhSachPhuTung as dspt on dstb.IFID_M705 = dspt.IFID_M705
                                        where dstb.IOID = %1$d
                                        order by dstb.IFID_M705
                                        limit %2$s', $filter['RefEq'], $limit);
                return $this->_o_DB->fetchAll($sql);
        }
}

?>