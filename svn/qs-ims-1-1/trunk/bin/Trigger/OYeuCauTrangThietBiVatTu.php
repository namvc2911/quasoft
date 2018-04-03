<?php
class Qss_Bin_Trigger_OYeuCauThietBi extends Qss_Lib_Trigger
{
//	public function onInserted($object)
//	{
//		//send email if breakdown
//		parent::init();
//		return;
//		$arrToDonVi     = array();
//		$arrCCDonVi     = array();
//
//		$sql    = sprintf('
//		            (
//					SELECT
//					dsnv1.Email AS EMail
//					, nv1.QuanLy
//					, dsnv1.TenNhanVien AS UserName
//					FROM
//					(
//                        SELECT *
//                        FROM ODonViSanXuat
//                        WHERE IOID = %1$d
//					) AS dvsx2
//					INNER JOIN ONhanVien AS nv1 ON dvsx2.IFID_M125 = nv1.IFID_M125
//					INNER JOIN ODanhSachNhanVien AS dsnv1 on dsnv1.IOID = nv1.Ref_MaNV
//					ORDER BY dvsx2.IFID_M125
//
//		            )
//
//		            UNION ALL
//
//		            (
//					SELECT
//					dsnv1.Email AS EMail
//					, nv1.QuanLy
//					, dsnv1.TenNhanVien AS UserName
//					FROM
//					(
//                        SELECT * FROM ODonViSanXuat WHERE ifnull(QuanLy, 0) = 1
//					) AS dvsx2
//					INNER JOIN ONhanVien AS nv1 ON dvsx2.IFID_M125 = nv1.IFID_M125
//					INNER JOIN ODanhSachNhanVien AS dsnv1 on dsnv1.IOID = nv1.Ref_MaNV
//					ORDER BY dvsx2.IFID_M125
//
//		            )
//					',$this->_params->Ref_DonViYeuCau);
//		$employees = $this->_db->fetchAll($sql);
//
//		if(count($employees))
//		{
//			foreach ($employees as $item)
//			{
//				if($item->QuanLy)
//				{
//					$arrCCDonVi[$item->EMail] = $item->UserName;
//				}
//				else
//				{
//					$arrToDonVi[$item->EMail] = $item->UserName;
//				}
//			}
//			if(count($arrToDonVi) || count($arrCCDonVi))
//			{
//				$subject = 'Cảnh báo yêu cầu điều động thiết bị ngày ' . $this->_params->Ngay;
//				$body = sprintf('<a href="http://%1$s/user/form/edit?ifid=%2$d&deptid=%3$d">Chi tiết điều động thiết bị!</a><br>',
//						$_SERVER['HTTP_HOST'],
//						 $this->_params->IFID_M751,
//						 1);
//				$body .= 'QS-IMS Mailer';
//				$this->_sendMail($subject, $arrToDonVi, $body,$arrCCDonVi);
//			}
//		}
//	}
}