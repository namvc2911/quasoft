<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';
class Qss_Model_Extra_Excel extends Qss_Model_Abstract
{
	protected $arrCol = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O');
	function __construct()
	{
		parent::__construct();
	}
	
	//-----------------------------------------------------------------------
	public function genEquipmentMatrix($location = 0)
	{
		//$objPHPExcel = new PHPExcel();
		$fn = QSS_DATA_BASE . '/equipmentmatrix.xls';
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load($fn);
		$ws = $objPHPExcel->getSheet(0);
		//Get data
		$where = '';
		if($location)
		{
			$sql = sprintf('select * from OKhuVuc where IOID = %1$d',$location);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if($dataSQL)
			{
				$where .= sprintf(' and Ref_MaKhuVuc in (select IOID from OKhuVuc where lft >= %1$d and rgt <= %2$d)',
						$dataSQL->lft,$dataSQL->rgt);
			}
		}
		$sql = sprintf('select ODanhSachThietBi.IOID as tbid, ODanhSachPhuTung.Ref_MaSP as spid,
				ODanhSachThietBi.MaThietBi,
				ODanhSachThietBi.LoaiThietBi
				from ODanhSachThietBi 
				left join ODanhSachPhuTung on ODanhSachPhuTung.IFID_M705 =  ODanhSachThietBi.IFID_M705
				where 1=1 %1$s
				order by ODanhSachThietBi.IOID',$where);
		//echo $sql;die;
		//left join OSanPham on ODanhSachPhuTung.Ref_MaSP = OSanPham.IOID 
				
		$dataSQL = $this->_o_DB->fetchAll($sql);
		$arrTB = array();
		$arrTBSP = array();
		$arrSP = array();
		$arrTenSP = array();
		$arrTenTB = array();
		foreach($dataSQL as $item)
		{
			$arrTB[$item->tbid] = $item->MaThietBi;
			$arrTenTB[$item->tbid] = $item->LoaiThietBi;
			if($item->spid)
			{
				$arrTBSP[$item->tbid][$item->spid] = 1;
			}
		}
		$sql = sprintf('select * from OSanPham
				order by IOID');
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach($dataSQL as $item)
		{
			$arrSP[$item->IOID] = $item->MaSanPham;
			$arrTenSP[$item->IOID] = $item->TenSanPham;
		}
		
		$i = 2;
		$thietbiioid = 0;
		foreach($arrTB as $key=>$value)
		{
			//Write cell
			$ws->getCell(PHPExcel_Cell::stringFromColumnIndex($i).'1')->setValue($value);
			$ws->getCell(PHPExcel_Cell::stringFromColumnIndex($i).'2')->setValue($arrTenTB[$key]);
			$ws->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
			$j = 3;
			foreach ($arrSP as $k=>$v)
			{
				$ws->getCell('A'.$j)->setValue($v);
				$ws->getCell('B'.$j)->setValue($arrTenSP[$k]);
				if(isset($arrTBSP[$key][$k]))
				{
					$ws->getCell(PHPExcel_Cell::stringFromColumnIndex($i).$j)->setValue($arrTBSP[$key][$k]);
				}
				$j++;
			}
			$i++;
		} 
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	//-----------------------------------------------------------------------
}
?>