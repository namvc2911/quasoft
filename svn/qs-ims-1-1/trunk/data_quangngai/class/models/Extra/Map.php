<?php

class Qss_Model_Extra_Map extends Qss_Model_Abstract
{

        //-----------------------------------------------------------------------
        /**
         * Build constructor
         * '
         * @return void
         */
        public function __construct()
        {
                parent::__construct();
        }
        
        public function getBaseMap($lop = array(), $ifid = 0)
        {
                $where = array();
                if (count($lop))
                {
                        $where[] = sprintf('  map.Ref_LopBanDo in (%1$s) ',
                                implode(',', $lop));
                }
                
                if($ifid)
                {
                        $where[] = sprintf(' map.IFID_BanDoNen = %1$d ', $ifid);
                }

                $sql = ' SELECT map.BanDo as Icon, map.IOID
						FROM BanDo as map';

                if (!count($where))
                {
                	return array();
                }	
                $where = sprintf(' where %1$s ', implode(' and ', $where));
                $sql = ' SELECT map.BanDo as Icon, map.IOID
						FROM BanDo as map ' . $where;
                //echo $sql; die;
                return $this->_o_DB->fetchAll($sql);
        }

        public function getWorkstation($lop = array())
        {
                if (!count($lop))
                {
                	return array();
                }
				$where = sprintf(' WHERE mc.Ref_LopBanDo in (%1$s) ',
                                implode(',', $lop));
                $sql = sprintf(' select mc.*, lbd.Icon,lbd.Style from Tram as mc 
                                      left join LopBanDo as lbd on ifnull(mc.Ref_LopBanDo,0) = lbd.IOID
                        %1$s', $where);
                return $this->_o_DB->fetchAll($sql);
        }
        
 		public function getAnhVeTinhMap($lop = array())
        {
                if (!count($lop))
                {
                	return array();
                }
				$where = sprintf(' WHERE lop.IOID in (%1$s) ',
                                implode(',', $lop));
                $sql = sprintf(' select lop.* from OLopAnhVeTinh as lop 
                                      inner join OAnhVeTinh as anh on lop.IFID_AnhVeTinh = anh.IFID_AnhVeTinh
                        %1$s', $where);
                return $this->_o_DB->fetchAll($sql);
        }

        public function getMapLayers($refMapLayer = 0)
        {
                $where = $refMapLayer?sprintf(' where IOID = %1$d ', $refMapLayer):'';
                $sql = sprintf(' select * from LopBanDo %1$s order by Ten', $where);
                return $this->_o_DB->fetchAll($sql);
        }
 		public function getAnhVeTinh()
        {
                $sql = sprintf(' select t.*,t1.IOID as ID,t1.Ten as TenLop from OAnhVeTinh as t
                			inner join OLopAnhVeTinh as t1 on t.IFID_AnhVeTinh = t1.IFID_AnhVeTinh
                			order by t.IOID, t1.IOID 
                			');
                return $this->_o_DB->fetchAll($sql);
        }

        public function getSection($lop)
        {
                $where = ' WHERE 1=0 ';
                if (count($lop))
                {
                        $where = sprintf(' WHERE mc.Ref_LopBanDo in (%1$s) ',
                                implode(',', $lop));
                }

                $sql = sprintf(' select mc.*, lbd.Icon, lbd.Style
                              from DanhSachMatCat as mc 
                              left join LopBanDo as lbd on ifnull(mc.Ref_LopBanDo,0) = lbd.IOID
                              %1$s', $where);
                return $this->_o_DB->fetchAll($sql);
        }
        
        public function getHoKhoan($lop)
        {
                $where = '';
                if (count($lop))
                {
                        $where .= sprintf(' WHERE mc.Ref_LopBanDo in (%1$s) ',
                                implode(',', $lop));
                }

                $sql = sprintf(' select mc.*, lbd.Icon, lbd.Style
                              from HoKhoan as mc 
                              left join LopBanDo as lbd on ifnull(mc.Ref_LopBanDo,0) = lbd.IOID
                              %1$s', $where);
                return $this->_o_DB->fetchAll($sql);
        }
        
        public function getHoChua($lop)
        {
                $where = '';
                if (count($lop))
                {
                        $where .= sprintf(' WHERE mc.Ref_LopBanDo in (%1$s) ',
                                implode(',', $lop));
                }

                $sql = sprintf(' select mc.*, lbd.Icon, lbd.Style
                              from HoChua as mc 
                              left join LopBanDo as lbd on ifnull(mc.Ref_LopBanDo,0) = lbd.IOID
                              %1$s', $where);
                return $this->_o_DB->fetchAll($sql);
        }
        
        public function getXoiLoBoiTu($lop)
        {
                $where = '';
                if (count($lop))
                {
                        $where .= sprintf(' WHERE mc.Ref_LopBanDo in (%1$s) ',
                                implode(',', $lop));
                }

                $sql = sprintf(' select mc.*, lbd.Icon, lbd.Style
                              from XoiLoBoiTu as mc 
                              left join LopBanDo as lbd on ifnull(mc.Ref_LopBanDo,0) = lbd.IOID
                              %1$s', $where);
                return $this->_o_DB->fetchAll($sql);
        }        
}
