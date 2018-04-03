<?php
/**
 * 
 * For Viglacery Tien Son
 * @author huy.bui
 *
 */
class Qss_Bin_Process_Inventory extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		//Lay danh sách mat hang
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
						FROM  OKho');
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

		//Lấy tồn kho
		$db = Qss_Db::getAdapter('stock');
		$sql = sprintf('SELECT t.ma_kho,t.ma_vt,t.so_luong,dmvt.ten_vt,dmvt.dvt
							FROM dbo.ff_EdItemxs(%1$s, \'\', \'\')
							as t
							inner join dmvt on dmvt.ma_vt = t.ma_vt
							where loai_vt = 21
							order by dmvt.ma_vt',
							$this->_db->quote(date('Y-m-d')));
		$tonkho  = $db->fetchAll($sql);
		$updateSQL = '';
		foreach ($tonkho as $item)
		{
			$makho = $item->ma_kho;
			$masp = $item->ma_vt;
			$tensp = $item->ma_vt;
			$dactinh = '';
			$donvitinh = $item->dvt;
					
			$toithieu = 0;
			$toida = 0;
			$soluong = $item->so_luong;
					
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
			if(!in_array($masp, $arrSP))
			{
				//manual insert
				$arr = array('OSanPham'=>array(0=>array('MaSanPham'=>$masp,'TenSanPham'=>$tensp,'DonViTinh'=>$donvitinh,'DacTinhKyThuat'=>$dactinh?$dactinh:' ','SLToiThieu'=>$toithieu,'SLToiDa'=>$toida)),
							'ODonViTinhSP'=>array(0=>array('DonViTinh'=>$donvitinh,'HeSoQuyDoi'=>1,'MacDinh'=>1)));
				$service = $this->services->Form->Manual('M113',0,$arr);						
			}
			if(!isset($arrKho[$makho][$masp]))
			{
				//manual insert
				$arr = array('OKho'=>array(0=>array('Kho'=>$makho,'MaSP'=>$masp,'DonViTinh'=>$donvitinh,'SoLuongKhoiTao'=>$soluong)));
				$service = $this->services->Form->Manual('M602',0,$arr);
			}
			else 
			{
				$updateItem = $arrKho[$makho][$masp];
				$updateSQL = sprintf('(%1$d,%2$d)',$updateItem->IOID,$soluong);
			}

		}
		if($updateSQL != '')
		{
			$sql = sprintf('replace into OKho(IOID,SoLuongHienCo) 
						values(%1$s)',$updateSQL);
			$this->_db->execute($sql);
		}
			
	}
}
?>