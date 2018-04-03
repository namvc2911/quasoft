<?php
class Qss_Model_Extra_Document extends Qss_Model_Abstract
{
	protected $_object;
	protected $_prefix = '';
	protected $_field = '';
	protected $_lenth = 0;
	protected $_view = '';
    protected $_docField = '';

    /**
     * Qss_Model_Extra_Document constructor.
     * @param Qss_Model_Object $object
     * - Thiết lập cấu hình chứng từ bằng các phương thức set (Ứu tiên số 1)
     * - Mặc định khởi tạo một số chứng từ lấy số chứng từ từ cấu hình chứng từ (Ứu tiên số 2)
     * - Mặc định tiếp theo lấy số chứng từ từ custom (Ưu tiên số 3)
     * - Nếu set thì sẽ tự động bỏ qua hai trường hợp trên
     */
	public function __construct (Qss_Model_Object $object)
	{
		parent::__construct();
		$this->_object = $object;
		$this->_view   = '' . $this->_object->ObjectCode;
		$customDocNo   = 'Qss_Bin_Custom_'.$this->_object->FormCode.'_Document';

		// Lấy cấu hình số phiếu từ trong custom/module/document.php
        if(class_exists($customDocNo)) {
            $docNoCustomClass = new $customDocNo($this->_object);
            $this->_prefix    = $docNoCustomClass->_prefix;
            $this->_prefix    = $this->changeDateKeyToDate();
            $this->_lenth     = $docNoCustomClass->_lenth;
            $this->_docField  = $docNoCustomClass->_docField;
        }

        // Lấy cấu hình từ cấu hình chứng từ
		/*$configDocNoSql = sprintf('
            SELECT * 
            FROM ONhomChungTu as NhomChungTu
			INNER JOIN OLoaiChungTu AS LoaiChungTu ON NhomChungTu.IOID = LoaiChungTu.Ref_NhomChungTu
			WHERE MaModule = %1$s', $this->_o_DB->quote($this->_object->FormCode));
        $configDocNo = $this->_o_DB->fetchOne($configDocNoSql);

		if($configDocNo) {
			$this->_prefix   = $configDocNo->KyTuDauMa;
			$this->_lenth    = $configDocNo->DoDaiMa;
			$this->_docField = $configDocNo->TruongSoChungTu;
		}*/

		// Chuyển kiểu d, m, y sang ngày tháng hiện tại
        //$this->_prefix    = $this->changeDateKeyToDate();
	}

	public function changeDateKeyToDate() {
        $arrSearch       = array('{d}','{m}','{y}','{Y}');
        $arrReplace      = array(date('d'),date('m'),date('y'),date('Y'));
        return str_replace($arrSearch, $arrReplace, $this->_prefix);
    }

	public function setPrefix($set)
	{
        $arrSearch  = array('{d}','{m}','{y}','{Y}');
        $arrReplace = array(date('d'),date('m'),date('y'),date('Y'));

		$this->_prefix = str_replace($arrSearch, $arrReplace, $set);
	}
	
	public function getPrefix()
	{
		return $this->_prefix;
	}

    public function setLenth($set)
    {
        $this->_lenth = $set;
    }

    public function getLenth()
    {
        return $this->_lenth;
    }

    public function setDocField($set)
    {
        $this->_docField = $set;
    }

    public function getDocField()
    {
        return $this->_docField;
    }

	public function getDocumentNoByIFID()
	{
		$sql = sprintf('SELECT %1$s as DocNo 
					FROM  %2$s 
					WHERE IFID_%3$s = %4$d
					LIMIT 1',
			$this->_docField,
			$this->_view,
			$this->_object->FormCode,
			$this->_object->i_IFID);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->DocNo:0;
	}
	public function getDocumentNo($condition = '')
	{
		$retval = '';
        // Trả về số phiếu vừa nhập trong trường hợp thay đổi số phiếu đã được lưu lại trước đó
		if($this->_object->i_IOID)
		{
			return $this->_object->getFieldByCode($this->_docField)->getValue();
		}

		// Tính toán số phiếu với trường hợp không thay đổi số phiếu
        // hoặc tạo ra một bản ghi mới
        // Nếu không thay đổi gì số phiếu đã lưu thì trả về giá trị số phiếu đã lưu theo database đã lưu trước đó
        // Với trường hợp bản ghi mới, số phiếu sẽ được tính toán theo dữ liệu tính toán số phiếu truyền vào
		if($this->_docField)
		{
            // Nếu không thay đổi gì số phiếu đã lưu thì trả về giá trị số phiếu đã lưu theo database đã lưu trước đó
			if($this->_object->i_IFID)
			{
				$retval = $this->getDocumentNoByIFID($this->_object->i_IFID);
			}

            // Với trường hợp bản ghi mới, số phiếu sẽ được tính toán theo dữ liệu tính toán số phiếu truyền vào
			if(!$retval)
			{
				$iLastWorkOrderNo = $this->getLast($condition);
				$iLastWorkOrderNo++;
				$retval = $this->writeDocumentNo($iLastWorkOrderNo);
			}
		}
		return $retval;
	}

    public function writeDocumentNo($iLastWorkOrderNo)
    {
        return $this->_prefix.str_pad($iLastWorkOrderNo, $this->_lenth, '0', STR_PAD_LEFT);
    }

    public function getLast($condition = '')
    {
        $sql = sprintf('SELECT %1$s as Last
							FROM  %2$s
							WHERE (%1$s	REGEXP  \'^%3$s[0-9]+\')
							%4$s
							ORDER BY cast(substr(%1$s,length(\'%3$s\')+1) as signed) DESC
							LIMIT 1',
            $this->_docField,
            $this->_view,
            $this->_prefix,
            $condition);
        $dataSQL = $this->_o_DB->fetchOne($sql);

        $lastWorkOrderNo = $dataSQL?$dataSQL->Last:0;;

        // @2015-04-07: Sua loi danh ma voi dau / trong project pos
        // @todo: Can viet mot ham xu ly ky tu dac biet cho phan nay
        $tempPrefix = str_replace(array('/'), array('\/'), $this->_prefix);

        return (int)preg_replace('/'.$tempPrefix.'/', '', $lastWorkOrderNo);
    }
}