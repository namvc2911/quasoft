update OCauTrucThietBi ct
inner join ODanhSachPhuTung_Old pt on pt.IFID_M705 = ct.IFID_M705
and pt.Ref_ViTri = ct.IOID
set ct.MaSP = pt.MaSP,
ct.Ref_MaSP = pt.Ref_MaSP,
ct.TenSP = pt.TenSP,
ct.Ref_TenSP = pt.Ref_TenSP,
ct.DonViTinh = pt.DonViTinh,
ct.Ref_DonViTinh = pt.Ref_DonViTinh,
ct.SoLuongChuan = pt.SoLuongChuan,
ct.SoLuongHC = pt.SoLuongHC,
ct.SoNgayCanhBao = pt.SoNgayCanhBao;

update OCauTrucThietBi set PhuTung = 1 where ifnull(Ref_MaSP,0) != 0;