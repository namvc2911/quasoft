<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';

class Qss_Model_Inventory_ViwasupcoInventory extends Qss_Model_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}

	public function syncInventory($ws)
	{
		$sql = sprintf('SELECT * FROM OSanPham');
		$arrSP = array();
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$arrSP[] = $item->MaSanPham;
		}
		$sql = sprintf('SELECT * FROM  OKho');
		$arrKho = array();
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$arrKho[$item->Kho][$item->MaSP] = $item;
		}
		$sql = sprintf('SELECT * FROM ODanhSachKho');
		$arrDSKho = array();
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$arrDSKho[] = $item->MaKho;
		}
		$sql = sprintf('SELECT * FROM  ODonViTinh');
		$arrDVT = array();
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$arrDVT[] = $item->DonViTinh;
		}
		//ktra neu ko chua co trong kho thi manual insert vao danh muc san pham va kho
		//neu co thi cap nhat toi thieu, toi da
		//neu co dac tinh thi cap nhat dac tinh trong danh muc mat hang
		$arrsoluong = array();

		$updateSQL = '';
		$updateSQLSanPham = '';
		$dvt = new Qss_Model_Import_Form('M102');
		$kho = new Qss_Model_Import_Form('M601');
		$sanpham = new Qss_Model_Import_Form('M113');
		$tonkhom = new Qss_Model_Import_Form('M602');
		$count = 0;
		foreach ($ws->getRowIterator() as $row)
		{
			if ( $row->getRowIndex() != 1 )
			{

				$makho = $ws->getCell("A" . $row->getRowIndex())->getValue();
				$tenkho = $ws->getCell("B" . $row->getRowIndex())->getValue();
				$masp = $ws->getCell("C" . $row->getRowIndex())->getValue();
				$tensp = $ws->getCell("D" . $row->getRowIndex())->getValue();
				$donvitinh = $ws->getCell("F" . $row->getRowIndex())->getValue();
				$soluong = $ws->getCell("O" . $row->getRowIndex())->getValue();

				/*if(isset($arrKho[$makho][$masp]))
				{
					if(floatval($arrKho[$makho][$masp]->SoLuongHC) == floatval($soluong))
					{
						unset($arrKho[$makho][$masp]);
						continue;
					}
				}*/
				if(!in_array($donvitinh, $arrDVT))
				{
					//manual insert
					$arr = array('ODonViTinh'=>array(0=>array('DonViTinh'=>$donvitinh)));
					//$service = new Qss_Service();
					//$service->Form->Manual('M102',0,$arr);
					$dvt->setData($arr);
					$arrDVT[] = $donvitinh;
				}
				if(!in_array($makho, $arrDSKho))
				{
					//manual insert
					$arr = array('ODanhSachKho'=>array(0=>array('MaKho'=>$makho,'TenKho'=>$tenkho,'LoaiKho'=>Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL)));
					//$service = new Qss_Service();
					//$service->Form->Manual('M601',0,$arr);
					$kho->setData($arr);
					$arrDSKho[] = $makho;
				}
				if(!in_array($masp, $arrSP))
				{
					//manual insert
					$arr = array('OSanPham'=>array(0=>array('MaSanPham'=>$masp,'TenSanPham'=>$tensp,'DonViTinh'=>$donvitinh)),
								'ODonViTinhSP'=>array(0=>array('DonViTinh'=>$donvitinh,'HeSoQuyDoi'=>1,'MacDinh'=>1)));
					$sanpham->setData($arr);
					$arrSP[] = $masp;
				}
				if(!isset($arrKho[$makho][$masp]))
				{
					//manual insert
					$arr = array('OKho'=>array(0=>array('Kho'=>$makho,'MaSP'=>$masp,'TenSP'=>$tensp,'DonViTinh'=>$donvitinh,'SoLuongHC'=>$soluong)));
					$tonkhom->setData($arr);
				}
				else
				{
					$updateItem = $arrKho[$makho][$masp];
					unset($arrKho[$makho][$masp]);
					if($updateSQL != '')
					{
						$updateSQL .= ',';
					}
					$updateSQL .= sprintf('(%1$d,%2$f)',$updateItem->IOID,$soluong);
				}
				$count++;
			}
		}
		//can than
		/*foreach ($arrKho as $sanphams)
		 {
			foreach ($sanphams as $sp)
			{
			if(strval($sp->SoLuongHC) !== '0')
			{
			if($updateSQL != '')
			{
			$updateSQL .= ',';
			}
			$updateSQL .= sprintf('(%1$d,%2$d)',$sp->IOID,0);
			}
			}
			}*/
		$sql = sprintf('update OKho set SoLuongHC = 0,SoLuongKhoiTao = null');
        $this->_o_DB->execute($sql);
		$dvt->generateSQL();
		//file_put_contents('D:\aaa.txt',print_r($dvt->getErrorRows(),true));
		$dvt->cleanTemp();
		$kho->generateSQL();
		//file_put_contents('D:\bbb.txt',print_r($kho->getErrorRows(),true));
		$kho->cleanTemp();
		$sanpham->generateSQL();
		//file_put_contents('D:\ccc.txt',print_r($sanpham->getErrorRows(),true));
		$sanpham->cleanTemp();
		$tonkhom->generateSQL();
		//file_put_contents('D:\ddd.txt',print_r($tonkhom->getErrorRows(),true));
		//print_r($tonkhom->getErrorRows());
		$tonkhom->cleanTemp();
		if($updateSQL != '')
		{
			$sqltemp = sprintf('CREATE TEMPORARY TABLE tmp_kho
						(IOID int NOT NULL, 
	        			SoLuongHC DECIMAL(24,2) NOT NULL)
	        			ENGINE = MEMORY');
			$this->_o_DB->execute($sqltemp);

			$sql = sprintf('insert into tmp_kho(IOID,SoLuongHC)
							values%1$s',$updateSQL);
			$this->_o_DB->execute($sql);
			$sql = sprintf('update OKho
							inner join tmp_kho on tmp_kho.IOID = OKho.IOID
							set OKho.SoLuongHC = tmp_kho.SoLuongHC',
			$updateSQL);
			$this->_o_DB->execute($sql);
			
			$sql = sprintf('update qsiforms 
							set LastModify = unix_timestamp()
							where FormCode = "M602" and deleted = 0');
			$this->_o_DB->execute($sql);

			$sqltemp = sprintf('DROP TABLE IF EXISTS tmp_kho');
			$this->_o_DB->execute($sqltemp);
		}
	}
}