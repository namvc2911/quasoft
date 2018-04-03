<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Lib_WValidation extends Qss_Lib_Bin
{
	public function onNext(){
		if($this->_form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE){
			$requireddocs = $this->_form->getRequiredDocuments();	
			if (count((array)$requireddocs)){
				$this->setError();
				foreach ($requireddocs as $item){
					$this->setMessage(sprintf('Hồ sơ yêu cầu: %1$s - %2$s',$item->Code,$item->Type));
				}
			}
			$requiredactivities = $this->_form->getRequiredActivities();	
			if (count((array)$requiredactivities)){
				$this->setError();
				foreach ($requiredactivities as $item){
					$this->setMessage(sprintf('Công việc yêu cầu: %1$s',$item->TypeName));
				}
			}
		}
	}
	public function onBack(){}
	public function onAlert()
	{
		//if($this->_form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
		{
			$requireddocs = $this->_form->getRequiredDocuments();	
			if (count((array)$requireddocs)){
				$this->setError();
				foreach ($requireddocs as $item)
				{
					$this->setMessage(sprintf('Hồ sơ yêu cầu: %1$s - %2$s',$item->Code,$item->Type));
				}
			}
			$requiredactivities = $this->_form->getRequiredActivities();	
			if (count((array)$requiredactivities))
			{
				$this->setError();
				foreach ($requiredactivities as $item)
				{
					$this->setMessage(sprintf('Công việc yêu cầu: %1$s',$item->TypeName));
				}
			}
		}
	}
	public function next(){}
	public function back(){}
	public function move(){}
	public function onMove(){}
}
?>