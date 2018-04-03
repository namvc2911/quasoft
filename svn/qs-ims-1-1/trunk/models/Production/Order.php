<?php

/**
 * @author: ThinhTuan
 * @component: Production
 */
class Qss_Model_Production_Order extends Qss_Model_Abstract {

    public function __construct() {
        parent::__construct();
    }
    
    public function getOrderByIOID($ioids)
    {
        $sql = sprintf('select *
							from OSanXuat 
                            where IOID in (%1$s)            
                            order by MaLenhSX'
        		,$ioids);
        return $this->_o_DB->fetchAll($sql);
    }
     public function getOrderInfor($start,$end,$po,$display,$page,$lang = 'vn', $count = false)
    {
    	$date = '';
    	$porders = '';
    	$select = '';
    	$stepName = ($lang == 'vn') ?' wfs.Name':sprintf(' wfs.Name_%1$s ', $lang);
    	
    	if($start && $end)
    	{
    		$date = sprintf(' and (sx.TuNgay >= %1$s and sx.DenNgay <= %2$s)'
    		, $this->_o_DB->quote($start), $this->_o_DB->quote($end)); 
    	}
    	elseif($start)
    	{
    		$date = sprintf(' and  sx.TuNgay >= %1$s'
    		, $this->_o_DB->quote($start));     		
    	}
    	elseif($end)
    	{
    		$date = sprintf(' and  sx.DenNgay <= %1$s'
    		, $this->_o_DB->quote($end));     		
    	}
    	
    	if($po)
    	{
    		$porders = sprintf(' and sx.IOID in (%1$s) ', $po);
    	}
    	
    	// Co dem so luong ban ghi hay khong
    	if($count)
    	{
    		$select = 'count(1) as Total';
    	}
    	else 
    	{
    		$select = 'pgv.*, ifnull(tksl.SoLuong, 0) as SoLuongThongKe, ifnull(tksl.SoLuongLoi, 0) as SoLuongLoi'
    		.', ifnull(pgv.SoLuong, 0) as SoLuongHoanThanh, ifnull(pgv.SoLuongThucHien, 0) as SoLuongYeuCau, '.
    		$stepName .' as Step, ifnull(wfs.Color, \'\') as Class, wfs.StepNo'
    		. ', pgv.IFID_M712';
    	}
    	
    	$sql = sprintf(' SELECT %3$s
    					FROM OSanXuat as sx
    					INNER JOIN OPhieuGiaoViec as pgv ON sx.MaLenhSX = pgv.MaLenhSX
    					INNER JOIN qsiforms as ifo ON pgv.IFID_M712 = ifo.IFID
    					INNER JOIN qsworkflows as wf ON ifo.FormCode = wf.FormCode
    					INNER JOIN qsworkflowsteps as wfs ON wf.WFID = wfs.WFID 
    						AND ifnull(ifo.Status, 0) = ifnull(wfs.StepNo, 0)
    					LEFT JOIN OThongKeSanLuong as tksl ON
    						tksl.MaLenhSX = pgv.MaLenhSX
    						and tksl.Ngay = pgv.Ngay
    						and tksl.Ref_Ca = pgv.Ref_Ca
    						and tksl.Ref_CongDoan = pgv.Ref_CongDoan
    						and tksl.Ref_MaSP = pgv.Ref_MaSP
    						and ifnull(tksl.Ref_DonViTinh, 0) = ifnull(pgv.Ref_DonViTinh, 0)
    						and ifnull(tksl.Ref_ThuocTinh, 0) = ifnull(pgv.Ref_ThuocTinh, 0)
    					WHERE 1=1 
    					%1$s %2$s
    					ORDER BY sx.Ref_DayChuyen', $date, $porders, $select);
    	//echo '<pre>';echo $sql;die;
    	if(!$count)
    	{
    		$startRecord = ceil(($page - 1) * $display);
    		$sql .= sprintf(' LIMIT %1$d, %2$d', $startRecord, $display);
    		return $this->_o_DB->fetchAll($sql);
    	}
    	else 
    	{
    		$dataSql = $this->_o_DB->fetchOne($sql);
    		return $dataSql?$dataSql->Total:0;
    	}
    }
 	public function getOrderComments($start = '', $end = '', $po = array())
    {
    	$date = '';
    	$porders = '';
       	if($start && $end)
    	{
    		$date = sprintf(' and (sx.TuNgay >= %1$s and sx.DenNgay <= %2$s)'
    		, $this->_o_DB->quote($start), $this->_o_DB->quote($end)); 
    	}
    	elseif($start)
    	{
    		$date = sprintf(' and  sx.TuNgay >= %1$s'
    		, $this->_o_DB->quote($start));     		
    	}
    	elseif($end)
    	{
    		$date = sprintf(' and  sx.DenNgay <= %1$s'
    		, $this->_o_DB->quote($end));     		
    	}
    	
    	if($po)
    	{
    		$porders = sprintf(' and sx.IOID in (%1$s) ', implode(',',$po));
    	}
    	
		$sql = sprintf(' SELECT count(pgv.IFID_M712) as Total, pgv.IFID_M712 as IFID
    					FROM OSanXuat as sx
    					INNER JOIN OPhieuGiaoViec as pgv ON sx.MaLenhSX = pgv.MaLenhSX
    					INNER JOIN qsfcomment as qsfc ON pgv.IFID_M712 = qsfc.IFID
    					INNER JOIN qsiforms as qsif ON pgv.IFID_M712 = qsif.IFID
    					WHERE 1=1 
    					%1$s %2$s
    					GROUP BY pgv.IFID_M712
    					ORDER BY sx.Ref_DayChuyen', $date, $porders);
		return $this->_o_DB->fetchAll($sql);
    }
     /**
     * 
     * Lay lenh san xuat theo mot khoang thoi gian nhom theo day chuyen
     * @param date $start Ngay bat dau
     * @param date $end Ngay ket thuc
     * @path /extra/production/info/workorder
     */
    public function getOrderByRange($start = '', $end = '', $lang = '')
    {
    	$date = '';
    	$stepName = '';
    	
    	if($start && $end)
    	{
    		$date = sprintf(' where TuNgay >= %1$s and DenNgay <= %2$s'
    		, $this->_o_DB->quote($start), $this->_o_DB->quote($end)); 
    	}
    	elseif($start)
    	{
    		$date = sprintf(' where TuNgay >= %1$s'
    		, $this->_o_DB->quote($start));     		
    	}
    	elseif($end)
    	{
    		$date = sprintf(' where DenNgay <= %1$s'
    		, $this->_o_DB->quote($end));     		
    	}
    	$stepName = ($lang == 'vn') ?' wfs.Name':sprintf(' wfs.Name_%1$s ', $lang);
    	
    	$sql = sprintf(' SELECT sx.*, %2$s as Step, ifnull(wfs.Color, \'\') as Class
    					FROM OSanXuat as sx
    					INNER JOIN qsiforms as ifo ON sx.IFID_M710 = ifo.IFID
    					INNER JOIN qsworkflows as wf ON ifo.FormCode = wf.FormCode
    					INNER JOIN qsworkflowsteps as wfs ON wf.WFID = wfs.WFID 
    						AND ifnull(ifo.Status, 0) = ifnull(wfs.StepNo, 0)
    					%1$s
    					ORDER BY sx.Ref_DayChuyen', $date, $stepName);
    	return $this->_o_DB->fetchAll($sql);
    }
       
}

?>