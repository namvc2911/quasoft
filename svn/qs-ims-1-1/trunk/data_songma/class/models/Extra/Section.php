<?php

class Qss_Model_Extra_Section extends Qss_Model_Abstract
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

        // *****************************************************************
        // === getAllSections(),Lay tat ca section trong bang matcat bang cach 
        // === group by theo Ma
        // *****************************************************************
        public function getAllSections()
        {
                $sql = sprintf('select * 
                     from DanhSachMatCat as mc
                     ');
                return $this->_o_DB->fetchAll($sql);
        }

        // *****************************************************************
        // === getSectionByCode($section), lay tu bang mat cat cac ban ghi theo 
        // === mot section truyen vao, order by theo ngay
        // *****************************************************************
        public function getSectionDataByCode($section)
        {
                $field = 'Ma';
                if (is_numeric($section))
                {
                        $field = 'IOID';
                }
                $sql = sprintf('select  mc.Ma as Section
                     , mc.KhoangCachLe as Distance
                     , mc.CaoDoZ as ZIndex
                     , mc.Ngay as SDate
                     ,ifnull(mc.KhoangCachCongDon,0) as XDistance
                     from MatCat as mc
                     where mc.%2$s = %1$s
                     order by mc.Ngay, ifnull(mc.KhoangCachCongDon,0) 
                     ', $this->_o_DB->quote($section), $field);
                return $this->_o_DB->fetchAll($sql);
        }
		
		public function getSectionDataByCodeGroupByYear($section)
        {
                $field = 'Ma';
                if (is_numeric($section))
                {
                        $field = 'IOID';
                }
                $sql = sprintf('select  
					 mc.IOID	
					 , mc.Ma as Section
                     , mc.KhoangCachLe as Distance
                     , mc.CaoDoZ as ZIndex
                     , mc.Ngay as SDate
                     , mc.KhoangCachCongDon as XDistance
					 , year(mc.Ngay) AS `Year`
                     from MatCat as mc
                     where mc.%2$s = %1$s
                     group by year(mc.Ngay)
                     ', $this->_o_DB->quote($section), $field);
                return $this->_o_DB->fetchAll($sql);
        }

        public function getSectionByID($id)
        {
				$where = $id ? sprintf(' mc.IOID = %1$d ', $id) : '';
                $where = $where ? sprintf(' where %1$s', $where) : ' where 1 = 0 ';

                $sql = sprintf(' select mc.*, lbd.Style, lbd.Icon, lbd.File
                              from DanhSachMatCat as mc 
                              left join LopBanDo as lbd on mc.Ref_LopBanDo = lbd.IOID
                    %1$s', $where);
                return $this->_o_DB->fetchOne($sql);
        }
}
