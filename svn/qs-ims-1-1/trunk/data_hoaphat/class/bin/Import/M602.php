<?php
/**
 * Description of OBangThuocTinh
 *
 * @author Thinh
 */
class Qss_Bin_Import_M602 extends Qss_Lib_Bin
{
    /**
     * 
     */
    public function __doExecute(File &$file)
    {
    	$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load($file->szFN);
		$ws = $objPHPExcel->getSheet(0);
		$file->total = $ws->getHighestRow()-1;
        $file->crow = $file->total + 2;
		//Lay danh sách mat hang
		$sql = sprintf('SELECT 
						convert(max(MaSanPham_106) using utf8) as MaSP,
						max(Ref_662) as ToiThieu,
						max(Ref_663) as ToiDa,
						max(Ref_1723) as DacTinh
						FROM  viewz_OSanPham_2
						group by ioid');//convert(max(DacTinhKyThuat_1723) using utf8) as DacTinh,
		$arrSP = array();
		$dataSQL = $this->_db->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$arrSP[$item->MaSP] = $item;
		}
		$sql = sprintf('SELECT 
						convert(max(Kho_733) using utf8) as MaKho,
						convert(max(MaSP_731) using utf8) as MaSP,
						max(Ref_737) as SoLuong
						FROM  viewz_OKho_2
						group by ioid');
   		$arrKho = array();
		$dataSQL = $this->_db->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$arrKho[$item->MaKho][$item->MaSP] = $item;
		}
    	$sql = sprintf('SELECT * FROM ODanhSachKho');
   		$arrDSKho = array();
		$dataSQL = $this->_db->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$arrDSKho[] = $item->MaKho;
		}
   	 	$sql = sprintf('SELECT * FROM  ODonViTinh');
   		$arrDVT = array();
		$dataSQL = $this->_db->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$arrDVT[] = $item->DonViTinh;
		}
				//ktra neu ko chua co trong kho thi manual insert vao danh muc san pham va kho
				//neu co thi cap nhat toi thieu, toi da
				//neu co dac tinh thi cap nhat dac tinh trong danh muc mat hang
		$arrtoithieu = array();
		$arrtoida = array();
		$arrdactinh = array();
		$arrsoluong = array();
				
		foreach ($ws->getRowIterator() as $row)
		{
			if ( $row->getRowIndex() != 1 )
			{
				$makho = ($ws->getCell("A" . $row->getRowIndex())->getValue());
				if($makho && preg_match('/^\S*\S*$/', $makho))
				{
					$masp = ($ws->getCell("B" . $row->getRowIndex())->getValue());
					$tensp = ($ws->getCell("C" . $row->getRowIndex())->getValue());
					$dactinh = ($ws->getCell("D" . $row->getRowIndex())->getValue());
					$donvitinh = ($ws->getCell("E" . $row->getRowIndex())->getValue());
					
					$toithieu = (int)($ws->getCell("F" . $row->getRowIndex())->getValue());
					$toida = (int)($ws->getCell("G" . $row->getRowIndex())->getValue());
					$soluong = ($ws->getCell("H" . $row->getRowIndex())->getValue());
					
					if(!in_array($donvitinh, $arrDVT))
					{
						//manual insert
						$arr = array('ODonViTinh'=>array(0=>array('DonViTinh'=>$donvitinh)));
						$service = $this->services->Form->Manual('M102',0,$arr);
					}
					if(!in_array($makho, $arrDSKho))
					{
						//manual insert
						$arr = array('ODanhSachKho'=>array(0=>array('MaKho'=>$makho,'TenKho'=>$makho)));
						$service = $this->services->Form->Manual('M601',0,$arr);
					}
					if(!isset($arrSP[$masp]))
					{
						//manual insert
						$arr = array('OSanPham'=>array(0=>array('MaSanPham'=>$masp,'TenSanPham'=>$tensp,'DonViTinh'=>$donvitinh,'DacTinhKyThuat'=>$dactinh?$dactinh:' ','SLToiThieu'=>$toithieu,'SLToiDa'=>$toida)),
									'ODonViTinhSP'=>array(0=>array('DonViTinh'=>$donvitinh,'HeSoQuyDoi'=>1,'MacDinh'=>1)));
						$service = $this->services->Form->Manual('M113',0,$arr);						
					}
					if(!isset($arrKho[$makho][$masp]))
					{
						//manual insert
						$arr = array('OKho'=>array(0=>array('Kho'=>$makho,'MaSP'=>$masp,'DonViTinh'=>$donvitinh,'SoLuongHC'=>$soluong)));
						$service = $this->services->Form->Manual('M602',0,$arr);
					}
					if($toithieu && isset($arrSP[$masp]))
					{
						$arrtoithieu[] = sprintf('(%1$d,%2$d)',$arrSP[$masp]->ToiThieu,$toithieu);
					}
					if($toida && isset($arrSP[$masp]))
					{
						$arrtoida[] = sprintf('(%1$d,%2$d)',$arrSP[$masp]->ToiDa,$toida);
					}
					if($dactinh && isset($arrSP[$masp]))
					{
						$arrdactinh[] = sprintf('(%1$d,%2$s)',$arrSP[$masp]->DacTinh,$this->_db->quote($dactinh));
					}
					if($soluong && isset($arrKho[$masp]))
					{
						$arrsoluong[] = sprintf('(%1$d,%2$d)',$arrKho[$masp]->SoLuong,$soluong);
					}
					$file->intImported++;
				}
			}
		}
		if(count($arrtoithieu))
		{
			$sql = sprintf('replace into datfloat(ID,Data) Values%1$s',implode(',', $arrtoithieu));
			$this->_db->execute($sql);
		}
    	if(count($arrtoida))
		{
			$sql = sprintf('replace into datfloat(ID,Data) Values%1$s',implode(',', $arrtoida));
			$this->_db->execute($sql);
		}
 	   if(count($arrdactinh))
		{
			$sql = sprintf('replace into datmediumdesc(ID,Data) Values%1$s',implode(',', $arrdactinh));
			$this->_db->execute($sql);
		}
   		if(count($arrsoluong))
		{
			$sql = sprintf('replace into datfloat(ID,Data) Values%1$s',implode(',', $arrsoluong));
			$this->_db->execute($sql);
		}
		
    }
    
}
