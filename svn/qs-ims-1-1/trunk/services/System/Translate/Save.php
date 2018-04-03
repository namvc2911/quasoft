<?php

class Qss_Service_System_Translate_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$model = new Qss_Model_System_Language();
		$languages = $model->getAll(1);
		$arrUpdate = array();
		$error = false;
		$id = $params['id'];
		if($id != $params['ID'])
		{
			if($model->isDuplicate($params['ID']))
			{
				$this->setMessage($this->_translate(170));
				$error = true;
			}
		}
		foreach ($languages as $language)
		{
			$arrData = array();
			$arrData['ID'] = $params['ID'];
			$arrData['Language'] = $language->Code;
			$arrData['Text'] = @$params['Text_'.$language->Code];
			if(!@$params['Text_'.$language->Code])
			{
				$this->setMessage($language->Name . $this->_translate(185));
				$error = true;
			}
			$arrUpdate[] = $arrData;
		}
		if($error)
		{
			$this->setError();
		}
		else
		{
			foreach ($arrUpdate as $item)
			{
				$model->saveTranslation($item);
			}
		}
	}

}
?>