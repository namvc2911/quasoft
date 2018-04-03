<?php
return;
include "../sysbase.php";
include "../syslogin.php";
$db    = Qss_Db::getAdapter('main');

$db->execute('update OCauTrucThietBi ct
					inner join (select * from ODanhSachPhuTung_Old group by Ref_BoPhan
					having count(*) = 1) as pt on pt.IFID_M705 = ct.IFID_M705
					and pt.Ref_ViTri = ct.IOID
					set ct.MaSP = pt.MaSP,
					ct.Ref_MaSP = pt.Ref_MaSP,
					ct.TenSP = pt.TenSP,
					ct.Ref_TenSP = pt.Ref_TenSP,
					ct.DonViTinh = pt.DonViTinh,
					ct.Ref_DonViTinh = pt.Ref_DonViTinh,
					ct.SoLuongChuan = pt.SoLuongChuan,
					ct.SoLuongHC = pt.SoLuongHC,
					ct.SoNgayCanhBao = pt.SoNgayCanhBao');
$sql = sprintf('select t.*, (select count(*) from OCauTrucThietBi where Ref_TrucThuoc = t.Ref_ViTri) as tongcon 
					from ODanhSachPhuTung_Old as t
					where ifnull(t.Ref_BoPhan,0) = 0 or t.Ref_BoPhan 
					in (select Ref_BoPhan from ODanhSachPhuTung_Old group by Ref_BoPhan having count(*)>1)
					order by IFID_M705,Ref_BoPhan');
$duplicates = $db->fetchAll($sql);
$bp = null;
$tb = null;
$danhsachphutung = array();
$i = null;
$model = new Qss_Model_Import_Form('M705');
foreach ($duplicates as $item)
{
	/*if($i === null)
	{
		$i = $item->tongcon + 1;
	}*/
	$thietbi = $item->IFID_M705;
	$bophan = $item->Ref_BoPhan;
	if($tb !== null && $tb !== $thietbi && count($danhsachphutung))//update phụ tùng cho ông tb
	{
		$sqlSub = sprintf('select * from OCauTrucThietBi where IFID_M705 = %1$d',$tb);
		$dataSub = $db->fetchAll($sqlSub);
		foreach ($dataSub as $it)
		{
			$danhsachphutung[] = array('ViTri'=>$it->ViTri
						,'BoPhan'=>$it->BoPhan
						,'MaSP'=>$it->MaSP
						,'TenSP'=>$it->TenSP
						,'DonViTinh'=> $it->DonViTinh
						,'SoLuongChuan'=>$it->SoLuongChuan
						,'SoLuongHC'=>$it->SoLuongHC
						,'SoNgayCanhBao'=>$it->SoNgayCanhBao
						,'TrucThuoc'=>$it->TrucThuoc
						,'ifid'=>$it->IFID_M705);
		}
		$data = array('ODanhSachThietBi'=>array(0=>array('ifid'=>$tb)),
					'OCauTrucThietBi'=>$danhsachphutung);
		$model->setData($data);
		$danhsachphutung = array();
	}
	
	if($bp !== $bophan)//
	{
		$i = $item->tongcon + 1;
	}
	$danhsachphutung[] = array('ViTri'=>($item->ViTri?$item->ViTri:'R').'.'.$i
						,'BoPhan'=>$item->TenSP
						,'MaSP'=>$item->MaSP
						,'TenSP'=>$item->TenSP
						,'Ref_DonViTinh'=>(int) $item->Ref_DonViTinh
						,'SoLuongChuan'=>$item->SoLuongChuan
						,'SoLuongHC'=>$item->SoLuongHC
						,'SoNgayCanhBao'=>$item->SoNgayCanhBao
						,'TrucThuoc'=>$item->ViTri
						,'ifid'=>$item->IFID_M705);

	$i++;
	$tb = $thietbi;
	$bp = $bophan;
}
if($tb !== null && count($danhsachphutung))//update phụ tùng cho ông tb
{
	$sqlSub = sprintf('select * from OCauTrucThietBi where IFID_M705 = %1$d',$tb);
		$dataSub = $db->fetchAll($sqlSub);
		foreach ($dataSub as $it)
		{
			$danhsachphutung[] = array('ViTri'=>$it->ViTri
						,'BoPhan'=>$it->BoPhan
						,'MaSP'=>$it->MaSP
						,'TenSP'=>$it->TenSP
						,'DonViTinh'=> $it->DonViTinh
						,'SoLuongChuan'=>$it->SoLuongChuan
						,'SoLuongHC'=>$it->SoLuongHC
						,'SoNgayCanhBao'=>$it->SoNgayCanhBao
						,'TrucThuoc'=>$it->TrucThuoc
						,'ifid'=>$it->IFID_M705);
		}
	$data = array('ODanhSachThietBi'=>array(0=>array('ifid'=>$tb)),
					'OCauTrucThietBi'=>$danhsachphutung);
	$model->setData($data);
	$danhsachphutung = array();
}
//print_r($model->_data)
$model->generateSQL();
print_r($model->getErrorRows());

$db->execute(sprintf("update OCauTrucThietBi set PhuTung = 1 where ifnull(Ref_MaSP,0) != 0;"));

include "../syslogout.php";