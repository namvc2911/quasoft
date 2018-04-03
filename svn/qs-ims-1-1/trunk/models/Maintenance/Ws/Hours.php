<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Maintenance_Ws_Hours extends Qss_Model_Abstract
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
	public function sendHours($uid,$pwd,$phanxuong,$code,$hours,$reset,$date,$time)
	{
		$userinfo = new Qss_Model_UserInfo();
		$userinfo->user_name = $uid;
		$userinfo->user_password = Qss_Util::hmac_md5($pwd, 'EP');
		$userinfo->user_dept_id = $phanxuong;
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
		else 
		{
			Qss_Register::set('userinfo', $userinfo);
			$service = new Qss_Service();
			$data = array('ONhatTrinhThietBi'=>array(0=>array('MaTB'=>$code
															,'Ngay'=>$date
															,'ThoiGian'=>$time
															,'SoCuoi'=>$hours
															,'Reset'=>$reset)));
			$retval = $service->Form->Manual('M765', 0, $data);
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
		
	}
}
?>