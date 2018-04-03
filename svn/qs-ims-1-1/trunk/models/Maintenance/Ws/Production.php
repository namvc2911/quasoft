<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Maintenance_Ws_Production extends Qss_Model_Abstract
{


	public function __construct ()
	{
		parent::__construct();

	}
	
	public function sendProduction($uid,$pwd,$phanxuong,$date,$thankeomo,$thanboclen,$thanvaosang,$thansach,$thantieuthu,$ghichu)
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
		else 
		{
			Qss_Register::set('userinfo', $userinfo);
			$service = new Qss_Service();
			$sql = sprintf('select * from qsdepartments where DeptCode = %1$s',$this->_o_DB->quote($phanxuong));
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if($dataSQL)
			{
				$phanxuongid = (int) $dataSQL->DepartmentID;
				$data = array('OHoatDongSanXuat'=>array(0=>array('Ngay'=>$date)),
					'OChiTietHoatDongSanXuat'=>array(0=>array('DonVi'=>$phanxuongid
														,'ThanKeoMo'=>$thankeomo
														,'ThanBocLen'=>$thanboclen
														,'ThanVaoSang'=>$thanvaosang
														,'ThanSach'=>$thansach
														,'ThanTieuThu'=>$thantieuthu
														,'GhiChu'=>$ghichu)));
				$retval = $service->Form->Manual('M149', 0, $data);
				if(!$retval->isError())
				{
					return sprintf('Cập nhật lần cuối: %1$s'
							,date('d-m-Y h:i'));
				}
				else
				{
					return $retval->getMessage(Qss_Service_Abstract::TYPE_HTML);
				}
			}
			else
			{
				return 'Kiểm tra lại mã phân xưởng ';
			}
		}
		
	}
}
?>