<?php
include "../sysbase.php";
$db     = Qss_Db::getAdapter('main');
$object = new Qss_Model_System_Object();
$temp   = 'temp_'.uniqid();

$db->execute('SET NAMES utf8;');

// Chuyen field tu chu ky sang danh sach diem do
$db->execute(sprintf(' CREATE TEMPORARY TABLE %1$s select * from qsfields where ObjectCode = "OChuKyKiemTraDiemDo"; ', $temp));
$db->execute(sprintf(' UPDATE %1$s SET ObjectCode = "ODanhSachDiemDo" WHERE ObjectCode = "OChuKyKiemTraDiemDo"; ', $temp));
$db->execute(sprintf(' INSERT INTO qsfields SELECT * FROM %1$s WHERE ObjectCode = "ODanhSachDiemDo"; ', $temp));

// Sap xep lai
$db->execute('UPDATE qsfields SET FieldNo = 10  WHERE FieldCode ="Ma" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 20  WHERE FieldCode ="MaThietBi" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields
SET FieldNo = 30
  , RefFormCode = "M705"
  , RefObjectCode = "OCauTrucThietBi"
  , RefFieldCode = "BoPhan"
  , RefDisplayCode = "ViTri"
  , Regx = "parent"
WHERE FieldCode ="BoPhan" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 40  WHERE FieldCode ="ChiSo" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 50  WHERE FieldCode ="GioiHanDuoi" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 60  WHERE FieldCode ="GioiHanTren" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 70, Grid = 0  WHERE FieldCode ="ThuCong" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 80, Grid = 0  WHERE FieldCode ="Ky" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 90, Grid = 0  WHERE FieldCode ="Thu" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 100, Grid = 0  WHERE FieldCode ="Ngay" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 110, Grid = 0  WHERE FieldCode ="Thang" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 120, Grid = 0, Effect = 1  WHERE FieldCode ="LapLai" AND ObjectCode = "ODanhSachDiemDo";');
$db->execute('UPDATE qsfields SET FieldNo = 130, Grid = 0  WHERE FieldCode ="GhiChu" AND ObjectCode = "ODanhSachDiemDo";');

// Create lại view lần 1
if($object->v_fInit('ODanhSachDiemDo'))
{
    if($object->createView())
    {
        echo 'End 1!';
    }
    else
    {
        echo 'Fail in 1!';
        die;
    }
}

// Chuyen du lieu tu chu ky len danh sach diem do
$db->execute(sprintf('
UPDATE ODanhSachDiemDo AS A
INNER JOIN
(
    SELECT *
    FROM
    (SELECT * FROM OChuKyKiemTraDiemDo ORDER BY IOID DESC) AS C
	GROUP BY IFID_M754
) AS B ON A.IFID_M754 = B.IFID_M754
SET
	A.Ky = B.Ky
	, A.Ref_Ky = B.Ref_Ky
	, A.LapLai = B.LapLai
	, A.Thu = B.Thu
	, A.Ref_Thu = B.Ref_Thu
	, A.Ngay = B.Ngay
	, A.Ref_Ngay = B.Ref_Ngay
	, A.Thang = B.Thang
	, A.Ref_Thang = B.Ref_Thang
	, A.GhiChu = B.GhiChu
WHERE A.IFID_M754 = B.IFID_M754;
'));


// Create lại view lần 2
if($object->v_fInit('ODanhSachDiemDo'))
{
    if($object->createView())
    {
        echo 'End 2!';
    }
    else
    {
        echo 'Fail in 2!';
        die;
    }
}

// Chuyen view diem do sang M705 va an view thong so

$db->execute(sprintf('DELETE FROM qsfobjects WHERE ObjectCode = "ODanhSachDiemDo" AND FormCode = "M754";'));

$db->execute(sprintf('UPDATE qsfobjects SET Public = 1 WHERE ObjectCode = "OThongSoKyThuatTB" AND FormCode = "M705";'));

$db->execute(sprintf('INSERT INTO qsfobjects (FormCode,
ObjectCode,
ParentObjectCode,
Main,
Public,
Editing,
Track,
`Insert`,
Deleting,
ObjNo)
VALUES ("M705", "ODanhSachDiemDo", "OCauTrucThietBi", 0, 0, 1, 0, 1, 1, 50);'));


// Create lại view lần 3
if($object->v_fInit('ODanhSachDiemDo'))
{
    if($object->createView())
    {
        echo 'End 3!';
    }
    else
    {
        echo 'Fail in 3!';
        die;
    }
}

// Ghep danh sach diem do vao thiet bi
$db->execute(sprintf('UPDATE ODanhSachDiemDo AS A
INNER JOIN ODanhSachThietBi AS B ON A.Ref_MaThietBi = B.IOID
SET A.IFID_M705 = B.IFID_M705
WHERE A.Ref_MaThietBi = B.IOID;'));

// Sau khi ghep duoc thiet bi loai bo truong thiet bi
$db->execute(sprintf('UPDATE qsfields SET Effect = 0 WHERE FieldCode ="MaThietBi" AND ObjectCode = "ODanhSachDiemDo";'));


// Create lại view lần 4
if($object->v_fInit('ODanhSachDiemDo'))
{
    if($object->createView())
    {
        echo 'End 4!';
    }
    else
    {
        echo 'Fail in 4!';
        die;
    }
}

// Don cac phan khong can thiet
$db->execute(sprintf('DELETE FROM qsforms WHERE FormCode = "M754";'));
$db->execute(sprintf('DELETE FROM qsiforms WHERE FormCode = "M754";'));
$db->execute(sprintf('DELETE FROM qsfobjects WHERE FormCode = "M754";'));
$db->execute(sprintf('DELETE FROM qsobjects WHERE ObjectCode = "OChuKyKiemTraDiemDo";'));
$db->execute(sprintf('DELETE FROM OChuKyKiemTraDiemDo;'));
$db->execute(sprintf('DELETE FROM qsfobjects WHERE ObjectCode = "OThongSoKyThuatTB" AND FormCode = "M705";'));
$db->execute(sprintf('DELETE FROM qsobjects WHERE ObjectCode = "OThongSoKyThuatTB";'));
//$db->execute(sprintf('DELETE FROM OThongSoKyThuatTB;'));


// Create lại view lần 5
if($object->v_fInit('ODanhSachDiemDo'))
{
    if($object->createView())
    {
        echo 'End 5!!';
    }
    else
    {
        echo 'Fail in 5!';
        die;
    }
}

// Thay doi nhat trinh thiet bi
$db->execute('UPDATE qsfields SET RefFormCode = "M705"  WHERE FieldCode ="DiemDo" AND ObjectCode = "ONhatTrinhThietBi";');


// Create lại view lần 7
if($object->v_fInit('ONhatTrinhThietBi'))
{
    if($object->createView())
    {
        echo 'End 6!!';
    }
    else
    {
        echo 'Fail in 6!';
        die;
    }
}

// Thay doi nhat trinh thiet bi
$db->execute('UPDATE qsfields SET RefFormCode = "M705"  WHERE FieldCode ="DiemDo" AND ObjectCode = "ONhapChiSoTuDong";');

// Create lại view lần 7
if($object->v_fInit('ONhatTrinhThietBi'))
{
    if($object->createView())
    {
        echo 'OK!!';
    }
    else
    {
        echo 'NOK!';
        die;
    }
}

