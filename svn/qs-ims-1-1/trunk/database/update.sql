-- Them vat tu theo loai
INSERT INTO `qsobjects` (`ObjectCode`, `ObjectName`, `ObjectName_en`, `OrderField`, `OrderType`) VALUES
('OLoaiThietBiSuDungTheoVatTu', 'Loáº¡i thiáº¿t bá»‹', 'Equip type', '0', ''),
('OVatTuThayTheTheoLoai', 'Danh sÃ¡ch váº­t tÆ°', 'Items', '0', '');


INSERT INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Mobile`) VALUES
(10, 'OVatTuThayTheTheoLoai', 'M113', 'OSanPham', 'MaSanPham', 'TenSanPham', 'MaSanPham', 'MÃ£', 1, '', NULL, 1, NULL, 0, 4, 200, 0, '', '', 1, 1, '', '', '', '', 'Code', 1, 0, 1),
(20, 'OVatTuThayTheTheoLoai', 'M113', 'OSanPham', 'TenSanPham', '0', 'TenSanPham', 'TÃªn', 2, '', NULL, 1, NULL, 0, 3, 200, 0, '', '', 1, 0, '', '', '', 'auto', 'Name', 0, 1, 1);


INSERT INTO `qsfobjects` (`FormCode`, `ObjectCode`, `ParentObjectCode`, `Main`, `Public`, `Editing`, `Track`, `Insert`, `Deleting`, `ObjNo`) VALUES
('M113', 'OLoaiThietBiSuDungTheoVatTu', '0', 0, 0, 1, 0, 1, 1, 50),
('M770', 'OVatTuThayTheTheoLoai', '0', 0, 0, 1, 0, 1, 1, 30);


-- Chuyen tai san len truoc phieu ban giao
UPDATE qsfields SET FieldNo = 42 WHERE ObjectCode = 'OChiTietThuHoiTaiSan' AND FieldCode = 'MaTaiSan';
UPDATE qsfields SET FieldNo = 43 WHERE ObjectCode = 'OChiTietThuHoiTaiSan' AND FieldCode = 'TenTaiSan';

-- Hiện đơn giá trong phiếu bàn giao trên grid
UPDATE qsfields SET Grid = 1 WHERE ObjectCode = 'OChiTietBanGiaoTaiSan' AND FieldCode = 'DonGia';


-- Chỉnh sửa hiện mobile M759

update qsfields set Mobile = 0 where ObjectCode = 'OPhieuBaoTri';
update qsfields set Mobile = 1 where ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'SoPhieu';
update qsfields set Mobile = 1 where ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'MaKhuVuc';
update qsfields set Mobile = 1 where ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'MaThietBi';
update qsfields set Mobile = 1 where ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'TenThietBi';
update qsfields set Mobile = 1 where ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'BoPhan';
update qsfields set Mobile = 1 where ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'MoTa';
update qsfields set Mobile = 1 where ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'LoaiBaoTri';
update qsfields set Mobile = 1 where ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'TenDVBT';
update qsfields set Mobile = 1 where ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'NgayBatDau';
update qsfields set Mobile = 1 where ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'Ngay';

update qsfields set Mobile = 0 where ObjectCode = 'OCongViecBTPBT';
update qsfields set Mobile = 1 where ObjectCode = 'OCongViecBTPBT' AND FieldCode = 'MoTa';
update qsfields set Mobile = 1 where ObjectCode = 'OCongViecBTPBT' AND FieldCode = 'Ten';
update qsfields set Mobile = 1 where ObjectCode = 'OCongViecBTPBT' AND FieldCode = 'BoPhan';
update qsfields set Mobile = 1 where ObjectCode = 'OCongViecBTPBT' AND FieldCode = 'NguoiThucHien';
update qsfields set Mobile = 1 where ObjectCode = 'OCongViecBTPBT' AND FieldCode = 'Ngay';

update qsfields set Mobile = 0 where ObjectCode = 'OVatTuPBT';
update qsfields set Mobile = 1 where ObjectCode = 'OVatTuPBT' AND FieldCode = 'MaVatTu';
update qsfields set Mobile = 1 where ObjectCode = 'OVatTuPBT' AND FieldCode = 'TenVatTu';
update qsfields set Mobile = 1 where ObjectCode = 'OVatTuPBT' AND FieldCode = 'DonViTinh';
update qsfields set Mobile = 1 where ObjectCode = 'OVatTuPBT' AND FieldCode = 'SoLuong';
update qsfields set Mobile = 1 where ObjectCode = 'OVatTuPBT' AND FieldCode = 'Ngay';


