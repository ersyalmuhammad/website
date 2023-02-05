<?php

$results = $connection->query("
	SELECT 
		 subquery.*, 
		 (hasil_masa_kerja + hasil_tingkat_pendidikan + hasil_tingkat_absensi + hasil_kinerja_kerja) as total 
		 FROM (
		        SELECT 
		         subquery.*,
		         (n_masa_kerja * (SELECT bobot FROM model WHERE model.kd_kriteria = 23)) as hasil_masa_kerja,
		         (n_tingkat_pendidikan * (SELECT bobot FROM model WHERE model.kd_kriteria = 24)) as hasil_tingkat_pendidikan,
		         (n_tingkat_absensi * (SELECT bobot FROM model WHERE model.kd_kriteria = 25)) as hasil_tingkat_absensi,
		         (n_kinerja_kerja * (SELECT bobot FROM model WHERE model.kd_kriteria = 26)) as hasil_kinerja_kerja
		         FROM (
		            SELECT
		                subquery.*,
		                (masa_kerja / (SELECT bobot FROM penilaian WHERE penilaian.kd_kriteria = 23 AND is_max=1)) as n_masa_kerja,
		                (tingkat_pendidikan / (SELECT bobot FROM penilaian WHERE penilaian.kd_kriteria = 24 AND is_max=1)) as n_tingkat_pendidikan,
		                (tingkat_absensi / (SELECT bobot FROM penilaian WHERE penilaian.kd_kriteria = 25 AND is_max=1)) as n_tingkat_absensi,
		                (kinerja_kerja / (SELECT bobot FROM penilaian WHERE penilaian.kd_kriteria = 26 AND is_max=1)) as n_kinerja_kerja
		                FROM (
		                SELECT 
		                    karyawan.nim, 
		                    karyawan.nama,
		                    (select nilai.nilai FROM nilai WHERE nilai.nim = karyawan.nim AND nilai.kd_kriteria = 23 LIMIT 1) as masa_kerja,
		                    (select nilai.nilai FROM nilai WHERE nilai.nim = karyawan.nim AND nilai.kd_kriteria = 24 LIMIT 1) as tingkat_pendidikan,
		                    (select nilai.nilai FROM nilai WHERE nilai.nim = karyawan.nim AND nilai.kd_kriteria = 25 LIMIT 1) as tingkat_absensi,
		                    (select nilai.nilai FROM nilai WHERE nilai.nim = karyawan.nim AND nilai.kd_kriteria = 26 LIMIT 1) as kinerja_kerja
		                        FROM (SELECT karyawan.nim, karyawan.nama FROM (SELECT nilai.nim FROM nilai WHERE kd_jabatan = '$_GET[jabatan]' GROUP BY nim) nilai JOIN karyawan ON nilai.nim = karyawan.nim) karyawan
		                 ) subquery
		          ) subquery
		     ) subquery ORDER BY (hasil_masa_kerja + hasil_tingkat_pendidikan + hasil_tingkat_absensi + hasil_kinerja_kerja) DESC
")->fetch_all(); 

?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>Data Matrix</strong>
			</div>
			<div class="panel-body">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>NIM</th>
							<th>Nama</th>
							<th>Masa Kerja</th>
							<th>Pendidikan</th>
							<th>Tingkat Kehadiran</th>
							<th>Kinerja</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($results as $result) : ?>
							<tr>
								<td><?php echo $result[0]; ?></td>
								<td><?php echo $result[1]; ?></td>
								<td><?php echo $result[2]; ?></td>
								<td><?php echo $result[3]; ?></td>
								<td><?php echo $result[4]; ?></td>
								<td><?php echo $result[5]; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>Normalisasi</strong>
			</div>
			<div class="panel-body">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>NIM</th>
							<th>Nama</th>
							<th>Masa Kerja</th>
							<th>Pendidikan</th>
							<th>Tingkat Kehadiran</th>
							<th>Kinerja</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($results as $result) : ?>
							<tr>
								<td><?php echo $result[0]; ?></td>
								<td><?php echo $result[1]; ?></td>
								<td><?php echo $result[6]; ?></td>
								<td><?php echo $result[7]; ?></td>
								<td><?php echo round($result[8], 2); ?></td>
								<td><?php echo round($result[9], 2); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>Hasill Data Perhitungan SAW</strong>
			</div>
			<div class="panel-body">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>NIM</th>
							<th>Nama</th>
							<th>Masa Kerja</th>
							<th>Pendidikan</th>
							<th>Tingkat Kehadiran</th>
							<th>Kinerja</th>
							<th>Hasil</th>
							<th>Rank</th>
						</tr>
					</thead>
					<tbody>
						<?php $rank = 1?>
						<?php foreach ($results as $result) : ?>
							<tr>
								<td><?php echo $result[0]; ?></td>
								<td><?php echo $result[1]; ?></td>
								<td><?php echo $result[10]; ?></td>
								<td><?php echo $result[11]; ?></td>
								<td><?php echo round($result[12], 2); ?></td>
								<td><?php echo round($result[13], 2); ?></td>
								<td><?php echo round($result[14], 2); ?></td>
								<td><?=$rank++?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>