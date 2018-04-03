<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Inventory_Ws_Receipt extends Qss_Model_Abstract
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
		return 'OK '. date('d-m-Y h:i:s');
	}
}
?>