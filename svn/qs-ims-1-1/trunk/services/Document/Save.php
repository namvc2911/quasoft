<?php

class Qss_Service_Document_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$filename = QSS_DATA_DIR . '/tmp/' . $params['doc'];
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		if(!$params['id'] && !file_exists($filename))
		{
			$this->setError();
			$this->setMessage('Tài liệu tải lên yêu cầu bắt buộc');
			return;
		}
		$document = new Qss_Model_Document();
		$params['ext'] = $ext;
		$params['size'] = (int) @filesize($filename);
		$id = $document->save($params);
		if(is_file($filename))
		{
			if(!is_dir(QSS_DATA_DIR . "/documents/"))
			{
				mkdir(QSS_DATA_DIR . "/documents/");
			}
			$destfile = QSS_DATA_DIR . "/documents/" . $id . "." . $ext;
			$ret = copy($filename, $destfile);
			unlink($filename);
		}
		if(isset($params['members']) && is_array($params['members']))
		{
			$document->emptyMember($id);
			foreach ($params['members'] as $uid)
			{
				$document->saveMember($id,$uid);
			}
		}
	}

}
?>