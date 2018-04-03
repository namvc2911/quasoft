set @i := 0;
update OPhieuBaoTri as v2
Inner join
(
select
IFID_M759,
CONCAT('#', LPAD(@i := @i + 1, 3, '0')) AS updateA
from OPhieuBaoTri
Order By NgayBatDauDuKien ASC, SoPhieu ASC
) AS v1 ON v2.IFID_M759 = v1.IFID_M759
set v2.SoPhieu = v1.updateA