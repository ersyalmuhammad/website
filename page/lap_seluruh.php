<div class="row">
	<div class="col-md-12">
	    <div class="panel panel-info">
	        <div class="panel-heading"><h3 class="text-center">Laporan Rekomendasi Seluruh karyawan</h3></div>
	        <div class="panel-body">
				<form class="form-inline" action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
					<label for="tahun">Tahun :</label>
					<select class="form-control" name="tahun">
						<option>---</option>
						<option value="2022">2022</option>
					</select>
					<button type="submit" class="btn btn-primary">Tampilkan</button>
				</form>
	            <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
				<?php
				$q = $connection->query("SELECT b.kd_jabatan, b.nama, h.nilai, m.nama AS karyawan, m.nim, (SELECT MAX(nilai) FROM hasil WHERE nim=h.nim) AS nilai_max FROM karyawan m JOIN hasil h ON m.nim=h.nim JOIN jabatan b ON b.kd_jabatan=h.kd_jabatan WHERE m.tahun_mengajukan='$_POST[tahun]' order by h.nilai desc");
				$jabatan = []; $data = []; $d = [];
				while ($r = $q->fetch_assoc()) {
					$jabatan[$r["kd_jabatan"]] = $r["nama"];
					$s = $connection->query("SELECT b.nama, a.nilai FROM hasil a JOIN jabatan b USING(kd_jabatan) WHERE a.nim=$r[nim] AND a.tahun=$_POST[tahun]");
					while ($rr = $s->fetch_assoc()){
						$d[$rr['nama']] = $rr['nilai'];
					}
					$m = max($d);
					$k = array_search($m, $d);
					$data[$r["nim"]."-".$r["karyawan"]."-".$r["nilai_max"]."-".$k][$r["kd_jabatan"]] = $r["nilai"];
				}
				?>
				<hr>
				<table class="table table-condensed">
	                <thead>
	                    <tr>
							<th>NIM</th>
							<th>Nama</th>
							<!-- <?php foreach ($jabatan as $val): ?>
		                        <th><?=$val?></th>
							<?php endforeach; ?> -->
							<th>Nilai Maksimal</th>
							<th>Ranking</th>
							<th>Rekomendasi</th>
	                    </tr>
	                </thead>
					<tbody>
						<?php
							$rank = 1;
						?>
					<?php foreach($data as $key => $val): ?>
						<tr>
							<?php $x = explode("-", $key); ?>
							<td><?=$x[0]?></td>
							<td><?=$x[1]?></td>
							<!-- <?php foreach ($val as $v): ?>
								<td><?=number_format($v, 8)?></td>
							<?php endforeach; ?> -->
							<td><?=number_format($x[2], 8)?></td>
							<td><?=$rank++?></td>
							<td><?=$x[3]?></td>
						</tr>
					<?php endforeach ?>
					</tbody>
		            </table>
	            <?php endif; ?>
	        </div>
	    </div>
	</div>
</div>
