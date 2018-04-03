<?php
class Qss_Bin_Notify_Validate_M602_Sync extends Qss_Lib_Notify_Validate
{
    const TITLE ='Đồng bộ tồn kho dữ liệu từ MS SQL.' ;

    const TYPE ='SUBSCRIBE';

    public function __doExecute()
    {
        try{
        	$msDB = Qss_Db::getAdapter('mssql');
        	$msDB->open();
        	$sqlTonKho  = '{call usp_TonTheoKho(?,?)}';
        	$params = array(array(date('Y-m-d'),SQLSRV_PARAM_IN),
					array('CC0', SQLSRV_PARAM_IN));
			$tonkho = $msDB->fetchAll($sqlTonKho,$params);
			$sql = sprintf('SELECT 
						*
						FROM  OSanPham');
			$arrSP = array();
			$dataSQL = $this->_db->fetchAll($sql);
			foreach ($dataSQL as $item)
			{
				$arrSP[] = $item->MaSanPham;
			}
			$sql = sprintf('SELECT *
							FROM  OKho where Kho like "CC0%%"');
	   		$arrKho = array();
			$dataSQL = $this->_db->fetchAll($sql);
			foreach ($dataSQL as $item)
			{
				$arrKho[$item->Kho][$item->MaSP] = $item;
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
	
			$updateSQL = '';
			$updateSQLSanPham = '';
			$dvt = new Qss_Model_Import_Form('M102');
			$kho = new Qss_Model_Import_Form('M601');
			$sanpham = new Qss_Model_Import_Form('M113');
			$tonkhom = new Qss_Model_Import_Form('M602');
			$count = 0;
			foreach ($tonkho as $item)
			{
				$makho = $item->Ma_Kho;
				$masp = $item->Ma_Vt;
				$tensp = $item->Ten_Vt;
				$dactinh = '';
				$donvitinh = $item->Dvt;
				$dongia = $item->Gia*1000;
				$tenkho = $item->Ten_Kho;
						
				$toithieu = 0;
				$toida = 0;
				$soluong = $item->So_Luong_Ton;
				if(isset($arrKho[$makho][$masp]))
				{
					if(((int)$arrKho[$makho][$masp]->SoLuongHC) == ((int)$soluong))
					{
						unset($arrKho[$makho][$masp]);
						continue;
					}
				}
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
					$arr = array('ODanhSachKho'=>array(0=>array('MaKho'=>$makho,'TenKho'=>$tenkho)));
					//$service = new Qss_Service();
					//$service->Form->Manual('M601',0,$arr);
					$kho->setData($arr);
					$arrDSKho[] = $makho;
				}
				if(!in_array($masp, $arrSP))
				{
					//manual insert
					$arr = array('OSanPham'=>array(0=>array('MaSanPham'=>$masp,'TenSanPham'=>$tensp,'DonViTinh'=>$donvitinh,'DacTinhKyThuat'=>$dactinh?$dactinh:' ','SLToiThieu'=>$toithieu,'SLToiDa'=>$toida)),
								'ODonViTinhSP'=>array(0=>array('DonViTinh'=>$donvitinh,'HeSoQuyDoi'=>1,'MacDinh'=>1)));
					if($dongia)
					{
						$arr['OSanPham'][0]['GiaMua'] = $dongia;
					}
					$sanpham->setData($arr);
					$arrSP[] = $masp;			
				}
				elseif($dongia)
				{
					if($updateSQLSanPham != '')
					{
						$updateSQLSanPham .= ',';
					}
					$updateSQLSanPham .= sprintf('(%1$s,%2$d)',$this->_db->quote($masp),$dongia);
				}
				if(!isset($arrKho[$makho][$masp]))
				{
					//manual insert
					$arr = array('OKho'=>array(0=>array('Kho'=>$makho,'MaSP'=>$masp,'TenSP'=>$tensp,'DonViTinh'=>$donvitinh,'SoLuongKhoiTao'=>$soluong,'SoLuongHC'=>$soluong)));
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
					$updateSQL .= sprintf('(%1$d,%2$d)',$updateItem->IOID,$soluong);
				}
				$count++;
			}
			foreach ($arrKho as $sanphams)
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
			}
			//giải quyết danh mục vật tư mới
			$sqlHanMoi = sprintf('select * from B20Item 
						where isActive = 1 AND ItemGroupCode = \'CCDC\'
						and (CONVERT(VARCHAR(10), CreatedAt ,103)  = \'%1$s\'
						or CONVERT(VARCHAR(10), ModifiedAt ,103)  = \'%1$s\')'
					,date('d/m/Y'));
			$hangmoi = $msDB->fetchAll($sqlHanMoi);
        	foreach ($hangmoi as $item)
			{
				$masp = $item->Code;
				$tensp = $item->Name;
				$donvitinh = $item->Unit;

				if(!in_array($donvitinh, $arrDVT))
				{
					//manual insert
					$arr = array('ODonViTinh'=>array(0=>array('DonViTinh'=>$donvitinh)));
					//$service = new Qss_Service();
					//$service->Form->Manual('M102',0,$arr);
					$dvt->setData($arr);
					$arrDVT[] = $donvitinh;
				}
				
				if(!in_array($masp, $arrSP))
				{
					//manual insert
					$arr = array('OSanPham'=>array(0=>array('MaSanPham'=>$masp,'TenSanPham'=>$tensp,'DonViTinh'=>$donvitinh,'DacTinhKyThuat'=>$dactinh?$dactinh:' ','SLToiThieu'=>$toithieu,'SLToiDa'=>$toida)),
								'ODonViTinhSP'=>array(0=>array('DonViTinh'=>$donvitinh,'HeSoQuyDoi'=>1,'MacDinh'=>1)));
					$sanpham->setData($arr);
					$arrSP[] = $masp;			
				}
				$count++;
			}
			
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
			$tonkhom->cleanTemp();
			if($updateSQL != '')
			{
				$sqltemp = sprintf('CREATE TEMPORARY TABLE tmp_kho
						(IOID int NOT NULL, 
	        			SoLuongHC DECIMAL NOT NULL)
	        			ENGINE = MEMORY');
        		$this->_db->execute($sqltemp);
				
        		$sql = sprintf('insert into tmp_kho(IOID,SoLuongHC) 
							values%1$s',$updateSQL);
				$this->_db->execute($sql);
				
				$sql = sprintf('update OKho
							inner join tmp_kho on tmp_kho.IOID = OKho.IOID
							set OKho.SoLuongHC = tmp_kho.SoLuongHC'
						,$updateSQL);
				$this->_db->execute($sql);
				
				$sqltemp = sprintf('DROP TABLE IF EXISTS tmp_kho');
        		$this->_db->execute($sqltemp);
			}
			if($updateSQLSanPham != '')
			{
				$sqltemp = sprintf('CREATE TEMPORARY TABLE tmp_sanpham
						(MaSanPham varchar(200), 
	        			DonGia DECIMAL NOT NULL)
	        			ENGINE = MEMORY');
        		$this->_db->execute($sqltemp);
				
        		$sql = sprintf('insert into tmp_sanpham(MaSanPham,DonGia) 
							values%1$s',$updateSQLSanPham);
				$this->_db->execute($sql);
				
				$sql = sprintf('update OSanPham
							inner join tmp_sanpham on tmp_sanpham.MaSanPham = OSanPham.MaSanPham
							set OSanPham.GiaMua = tmp_sanpham.DonGia'
						,$updateSQL);
				$this->_db->execute($sql);
				
				$sqltemp = sprintf('DROP TABLE IF EXISTS tmp_sanpham');
        		$this->_db->execute($sqltemp);
			}
			$msDB->close();
		}
		catch(Exception $e)
		{
			$this->setError();
			$this->setMessage($e->getMessage());
		}
		//echo $count;
    }
}
?>