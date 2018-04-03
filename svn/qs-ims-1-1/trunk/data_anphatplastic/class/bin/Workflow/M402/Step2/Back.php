<?php
class Qss_Bin_Workflow_M402_Step2_Back extends Qss_Lib_Bin
{

	public function __doExecute()
	{
		$sql = sprintf('select 1 from ODanhSachKho where IOID = %1$d and IFNULL(LoaiKho, "") = "TAM"',$this->_params->Ref_Kho);
		$dataSQL = $this->_db->fetchOne($sql);

		if($dataSQL)
		{
			$sql = sprintf('
                select * 
                from qsworkflowsteps 
                where WFID=%1$d and StepNo in (%2$s) 
                order by OrderNo'
                , $this->_form->i_WorkFlowID,'1');
			return $this->_db->fetchAll($sql);
		}
	}
    
}