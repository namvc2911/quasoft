delete from qsiforms where FormCode = 'M113' 
or FormCode = 'M602'
or FormCode = 'M102'
or FormCode = 'M601'
or FormCode = 'M705'
or FormCode = 'M724';
delete from OSanPham;
delete from ODonViTinhSP;
delete from OKho;
delete from ODanhSachKho;
delete from ODonViTinh;
delete from ODanhSachThietBi;
delete from OCauTrucThietBi;
delete from OBaoTriDinhKy;
delete from OChuKyBaoTri;
delete from OCongViecBT;
delete from OVatTu;

truncate table eccbk.qsforms;
insert into eccbk.qsforms select * from pm3.qsforms;

truncate table eccbk.qsobjects;
insert into eccbk.qsobjects select * from pm3.qsobjects;

truncate table eccbk.qsfobjects;
insert into eccbk.qsfobjects select * from pm3.qsfobjects;

truncate table eccbk.qsfields;
insert into eccbk.qsfields select * from pm3.qsfields;

truncate table eccbk.qsmenu;
insert into eccbk.qsmenu select * from pm3.qsmenu;

truncate table eccbk.qsmenulink;
insert into eccbk.qsmenulink select * from pm3.qsmenulink;

truncate table eccbk.qsuiboxfields;
insert into eccbk.qsuiboxfields select * from pm3.qsuiboxfields;

truncate table eccbk.qsuiboxs;
insert into eccbk.qsuiboxs select * from pm3.qsuiboxs;

truncate table eccbk.qsuigroups;
insert into eccbk.qsuigroups select * from pm3.qsuigroups;

truncate table eccbk.qsbash;
insert into eccbk.qsbash select * from pm3.qsbash;

insert into ODonViTinhSP(IFID_M113,DeptID,DonViTinh,Ref_DonViTinh,HeSoQUyDoi,MacDinh)
select IFID_M113,DeptID,DonViTinh,Ref_DonViTinh,1,1 from OSanPham where IFID_M113 not in (select IFID_M113 from ODonViTinhSP )

update OPhieuBaoTri set DeptID = 2;# 72 row(s) affected.
# MySQL returned an empty result set (i.e. zero rows).

update qsiforms
inner join OPhieuBaoTri on OPhieuBaoTri.IFID_M759 = qsiforms.IFID
set DepartmentID = DeptID;# 72 row(s) affected.
# 9 row(s) affected.
# MySQL returned an empty result set (i.e. zero rows).
