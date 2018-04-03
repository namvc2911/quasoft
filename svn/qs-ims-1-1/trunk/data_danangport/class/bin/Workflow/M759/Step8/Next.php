<?php
class Qss_Bin_Workflow_M759_Step8_Next extends Qss_Lib_Bin
{

	public function __doExecute()
	{
		$sql = sprintf('select 1 from OPhanLoaiBaoTri where IOID = %1$d and LoaiBaoTri = "B"',$this->_params->Ref_LoaiBaoTri);
		$dataSQL = $this->_db->fetchOne($sql);
		if($dataSQL)
		{
			$sql = sprintf('select * from qsworkflowsteps 
						where WFID=%1$d and StepNo in (%2$s) 
						order by OrderNo', 
					$this->_form->i_WorkFlowID, 
					'7');
			return $this->_db->fetchAll($sql);
		}
		else
		{
			$sql = sprintf('select * from qsworkflowsteps 
						where WFID=%1$d and StepNo in (%2$s) 
						order by OrderNo', 
					$this->_form->i_WorkFlowID, 
					'6');
			return $this->_db->fetchAll($sql);
		}
	}
    
}