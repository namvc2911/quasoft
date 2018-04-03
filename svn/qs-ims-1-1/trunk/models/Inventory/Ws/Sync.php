<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Inventory_Ws_Sync extends Qss_Model_Abstract
{


	public function __construct ()
	{
		parent::__construct();

	}
	public function login($uid,$pwd,$phanxuong)
	{
		$userinfo = new Qss_Model_UserInfo();
		$userinfo->user_name = $uid;
		$userinfo->user_password = Qss_Util::hmac_md5($pwd, 'EP');
		$userinfo->user_dept_id = $phanxuong;
		return $userinfo->checkLogin(31,0);
	}
	public function update($uid,$pwd,$data)
	{
		$userinfo = new Qss_Model_UserInfo();
		$userinfo->user_name = $uid;
		$userinfo->user_password = Qss_Util::hmac_md5($pwd, 'EP');
		$userinfo->user_dept_id = 1;
		$loginret = $userinfo->checkLogin(31,0);
		/*$sql = sprintf('select qsusergroups.*,qsusers.*,qsdepartments.Name from qsusers
				inner join qsusergroups on qsusergroups.UID=qsusers.UID
				inner join qsdepartments on qsdepartments.DepartmentID=qsusergroups.DepartmentID
				where LCASE(UserID)=LCASE(%1$s) and PassWord=%2$s and qsusergroups.DepartmentID=%3$d and isActive = 1', 
			$this->_o_DB->quote($uid), 
			$this->_o_DB->quote(Qss_Util::hmac_md5($pwd, 'EP')), 
			$phanxuong);*.
		$dataSQL = $this->_o_DB->fetchOne($sql);*/
		if(!$loginret)
		{
			return "Tên, mật khẩu chưa đúng!";
		}
		//Lấy tồn kho
		/*$sql = sprintf('SELECT t.ma_kho,t.ma_vt,t.so_luong,dmvt.ten_vt,dmvt.dvt
							FROM dbo.ff_EdItemxs(%1$s, \'\', \'\')
							as t
							inner join dmvt on dmvt.ma_vt = t.ma_vt
							where loai_vt = 21
							order by dmvt.ma_vt',
							$this->_o_DB->quote(date('Y-m-d')));*/
		//$tonkho  = $db->fetchAll($sql);
		
		//return ($tonkho[0][1]); 
		//Lay danh sách mat hang
		try{
			$tonkho = Qss_Json::decode($data);
			Qss_Register::set('userinfo', $userinfo);
			$sql = sprintf('SELECT 
						*
						FROM  OSanPham');
			$arrSP = array();
			$dataSQL = $this->_o_DB->fetchAll($sql);
			foreach ($dataSQL as $item)
			{
				$arrSP[] = $item->MaSanPham;
			}
			$sql = sprintf('SELECT *
							FROM  OKho');
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
			foreach ($tonkho as $item)
			{
				$makho = $item[0];
				$masp = $item[1];
				$tensp = $item[3];;
				$dactinh = '';
				$donvitinh = $item[4];
				$dongia = $item[5]*1000;
				$tenkho = $item[6];
						
				$toithieu = 0;
				$toida = 0;
				$soluong = (double) $item[2];
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
					$updateSQLSanPham .= sprintf('(%1$s,%2$d)',$this->_o_DB->quote($masp),$dongia);
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
					if($updateSQL != '')
					{
						$updateSQL .= ',';
					}
					$updateSQL .= sprintf('(%1$d,%2$d)',$updateItem->IOID,$soluong);
				}
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
        		$this->_o_DB->execute($sqltemp);
				
        		$sql = sprintf('insert into tmp_kho(IOID,SoLuongHC) 
							values%1$s',$updateSQL);
				$this->_o_DB->execute($sql);
				
				$sql = sprintf('update OKho
							inner join tmp_kho on tmp_kho.IOID = OKho.IOID
							set OKho.SoLuongHC = tmp_kho.SoLuongHC'
						,$updateSQL);
				$this->_o_DB->execute($sql);
				
				$sqltemp = sprintf('DROP TABLE IF EXISTS tmp_kho');
        		$this->_o_DB->execute($sqltemp);
			}
			if($updateSQLSanPham != '')
			{
				$sqltemp = sprintf('CREATE TEMPORARY TABLE tmp_sanpham
						(MaSanPham varchar(200), 
	        			DonGia DECIMAL NOT NULL)
	        			ENGINE = MEMORY');
        		$this->_o_DB->execute($sqltemp);
				
        		$sql = sprintf('insert into tmp_sanpham(MaSanPham,DonGia) 
							values%1$s',$updateSQLSanPham);
				$this->_o_DB->execute($sql);
				
				$sql = sprintf('update OSanPham
							inner join tmp_sanpham on tmp_sanpham.MaSanPham = OSanPham.MaSanPham
							set OSanPham.GiaMua = tmp_sanpham.DonGia'
						,$updateSQL);
				$this->_o_DB->execute($sql);
				
				$sqltemp = sprintf('DROP TABLE IF EXISTS tmp_sanpham');
        		$this->_o_DB->execute($sqltemp);
			}
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
		return 'OK '. date('d-m-Y h:i:s');
	}
	public function receipt($uid,$pwd,$data)
	{
		$userinfo = new Qss_Model_UserInfo();
		$userinfo->user_name = $uid;
		$userinfo->user_password = Qss_Util::hmac_md5($pwd, 'EP');
		$userinfo->user_dept_id = 1;
		$loginret = $userinfo->checkLogin(31,0);
		if(!$loginret)
		{
			return "Tên, mật khẩu chưa đúng!";
		}
		try{
			Qss_Register::set('userinfo', $userinfo);
			$nhapkho = Qss_Json::decode($data);
			$import = new Qss_Model_Import_Form('M402');
			$phieu = '';
			$arrDanhSach = array();
			foreach ($nhapkho as $item)
			{
				$phieunhap = $item[0];
				$kho = $item[1];
				$ngayct = $item[2];
				$ngaynhap = $item[2];
				$masp = $item[3];
				$tensp = $item[4];;
				$donvitinh = $item[5];
				$soluong = $item[6];
				$dongia = $item[7]*1000;
				$thanhtien = $item[8]*1000;
				$soyccc = $item[9];
				if($phieu != '' && $phieu != $phieunhap)
				{
					//insert 
					$arrInsert = array('ONhapKho'=>array(0=>array(
											'SoChungTu'=>$phieunhap
											,'Kho'=>$kho
											,'NgayChungTu'=>$ngayct
											,'NgayChuyenHang'=>$ngaynhap)),
										'ODanhSachNhapKho'=>$arrDanhSach);
					$import->setData($arrInsert);
					//set arrayDetail
					$arrDanhSach = array();
				}
				$arrDanhSach[] = array('SoYeuCau'=>$soyccc
								,'MaSanPham'=>$masp
								,'TenSanPham'=>$tensp
								,'DonViTinh'=>$donvitinh
								,'SoLuong'=>$soluong
								,'DonGia'=>$dongia
								,'ThanhTien'=>$thanhtien
							);
			}
			if(count($arrDanhSach))
			{
				$arrInsert = array('ONhapKho'=>array(0=>array(
											'SoChungTu'=>$phieunhap
											,'Kho'=>$kho
											,'NgayChungTu'=>$ngayct
											,'NgayChuyenHang'=>$ngaynhap)),
										'ODanhSachNhapKho'=>$arrDanhSach);
				$import->setData($arrInsert);
			}
			$import->generateSQL();			
			$import->cleanTemp();
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
		return 'GR: '. date('d-m-Y h:i:s');
	}
}
?>