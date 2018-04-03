<?php
class Qss_Bin_Calculate_OYeuCauBaoTri_TinhTrangXuLy extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$sql = sprintf('select qsworkflowsteps.Name from OPhieuBaoTri
					inner join qsiforms on qsiforms.IFID = OPhieuBaoTri.IFID_M759
					inner join qsworkflows on qsworkflows.FormCode = qsiforms.FormCode
					inner join qsworkflowsteps on qsworkflowsteps.WFID = qsworkflows.WFID and qsiforms.Status = qsworkflowsteps.StepNo
					where Ref_PhieuYeuCau = %1$d'
				, $this->_ioid);
		$data = $this->_db->fetchOne($sql);
		if($data)
		{
			return $data->Name;
		}
		return 'N/A';
	}
}
?>