<?php

class Qss_Service_Process_Run extends Qss_Service_Abstract
{

	public function __doExecute ($form)
	{
		$process = new Qss_Model_Process();
		$user = Qss_Register::get('userinfo');
		$classname = 'Qss_Bin_Process_' . ucfirst($form->sz_Class);
		try
		{
			$log = array('FPLID'=>0,
						'IFID'=>$form->i_IFID,
						'UID'=>$user->user_id,
						'Date'=>date('Y-m-d'),
						'STime'=>date('H:i:s'),
						'Status'=>0,//treo
						'Note'=>'Manual:');
			$calid = $process->saveLog($log);
			$bash = new $classname($form);
			$bash->init();
			$bash->__doExecute();
			$log = array('FPLID'=>$calid,
						'ETime'=>date('H:i:s'),
						'Status'=>$bash->isError()?2:1,
						'Note'=>'Manual: ' . $bash->getMessage());//
			$calid = $process->saveLog($log);
			//echo $bash->getMessage();
			if($bash->isError())
			{
				$this->setError();
				$this->setMessage($bash->getMessage());
			}
		}
		catch(Exception $e)
		{
			$form->updateError(true, $e->getMessage());
		}
	}

}
?>