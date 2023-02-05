<?php
$update = (isset($_GET['action']) AND $_GET['action'] == 'update') ? true : false;
if ($update) {
	$sql = $connection->query("SELECT * FROM nilai JOIN penilaian USING(kd_kriteria) WHERE kd_nilai='$_GET[key]'");
	$row = $sql->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" AND isset($_POST["save"])) {
	$validasi = false; $err = false;
	if ($update) {
		foreach ($_POST['nilai'] as $kd_kriteria_idx => $nilai_value) {
			$kriteria = $connection->query("SELECT * FROM kriteria WHERE kd_kriteria = '$kd_kriteria_idx'")->fetch_assoc();

			if ($kriteria['calculation_type']=='percent') {
				$nilai_value = round($nilai_value / $kriteria['max_value'], 2);
			}

			$currentNilai = $connection->query("SELECT kd_nilai FROM nilai WHERE nim='$_POST[nim]' AND kd_kriteria='$kd_kriteria_idx'")->fetch_assoc();
			$connection->query("UPDATE nilai SET nilai = '$nilai_value' WHERE kd_nilai='$currentNilai[kd_nilai]' ");
		}
	} else {
		$query = "INSERT INTO nilai VALUES ";
		foreach ($_POST["nilai"] as $kd_kriteria => $nilai) {
			$kriteria = $connection->query("SELECT * FROM kriteria WHERE kd_kriteria = '$kd_kriteria'")->fetch_assoc();

			if ($kriteria['calculation_type']=='percent') {
				$nilai = round($nilai / $kriteria['max_value'], 2);
			}

			$query .= "(NULL, '$_POST[kd_jabatan]', '$kd_kriteria', '$_POST[nim]', '$nilai'),";
		}
		$sql = rtrim($query, ',');
		$validasi = true;
	}

	if ($validasi) {
		foreach ($_POST["nilai"] as $kd_kriteria => $nilai) {

			$q = $connection->query("SELECT kd_nilai FROM nilai WHERE kd_jabatan=$_POST[kd_jabatan] AND kd_kriteria=$kd_kriteria AND nim=$_POST[nim] AND nilai LIKE '%$nilai%'");
			if ($q->num_rows) {
				echo alert("Nilai untuk ".$_POST["nim"]." sudah ada!", "?page=nilai");
				$err = true;
			}
		}
	}

	if (is_string($sql)) {
	  if (!$err AND $connection->query($sql)) {
			echo alert("Berhasil!", "?page=nilai");
		} else {
			echo alert("Gagal!", "?page=nilai");
		}
	}
}

if (isset($_GET['action']) AND $_GET['action'] == 'delete') {
  $connection->query("DELETE FROM nilai WHERE kd_nilai='$_GET[key]'");
	echo alert("Berhasil!", "?page=nilai");
}
?>
<div class="row">
	<div class="col-md-4">
	    <div class="panel panel-<?= ($update) ? "warning" : "info" ?>">
	        <div class="panel-heading"><h3 class="text-center"><?= ($update) ? "EDIT" : "TAMBAH" ?></h3></div>
	        <div class="panel-body">
	            <form action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
									<div class="form-group">
										<label for="nim">Karyawan</label>
										<?php if ($_POST): ?>
											<input type="hidden" name="nim" value="<?=$_POST['nim']?>">
											<?php $karyawan = $connection->query("SELECT nama,nim FROM karyawan WHERE nim=$_POST[nim]")->fetch_assoc(); ?>
											<input type="text" class="form-control" readonly="on" value="<?=$karyawan['nim'].' - '.$karyawan['nama']?>" />
										<?php else: ?>
											<select class="form-control" name="nim">
												<option>---</option>
												<?php $sql = $connection->query("SELECT * FROM karyawan"); while ($data = $sql->fetch_assoc()): ?>
													<option value="<?=$data["nim"]?>" <?= (!$update) ? "" : (($row["nim"] != $data["nim"]) ? "" : 'selected="selected"') ?>><?=$data["nim"]?> | <?=$data["nama"]?></option>
												<?php endwhile; ?>
											</select>
										<?php endif; ?>
									</div>
									<div class="form-group">
	                  <label for="kd_jabatan">Jabatan</label>
										<?php if ($_POST): ?>
											<?php $q = $connection->query("SELECT nama FROM jabatan WHERE kd_jabatan=$_POST[kd_jabatan]"); ?>
											<input type="text"value="<?=$q->fetch_assoc()["nama"]?>" class="form-control" readonly="on">
											<input type="hidden" name="kd_jabatan" value="<?=$_POST["kd_jabatan"]?>">
										<?php else: ?>
											<select class="form-control" name="kd_jabatan" id="jabatan">
												<option>---</option>
												<?php $sql = $connection->query("SELECT * FROM jabatan"); while ($data = $sql->fetch_assoc()): ?>
													<option value="<?=$data["kd_jabatan"]?>"<?= (!$update) ? "" : (($row["kd_jabatan"] != $data["kd_jabatan"]) ? "" : 'selected="selected"') ?>><?=$data["nama"]?></option>
												<?php endwhile; ?>
											</select>
										<?php endif; ?>
									</div>
									<?php if ($_POST): ?>
										<?php $q = $connection->query("SELECT * FROM kriteria"); while ($r = $q->fetch_assoc()): ?>
				                <div class="form-group">
					                  <label for="nilai"><?=ucfirst($r["nama"])?></label>
														<?php if ($r['input_type'] == 'select') :?>
															<select class="form-control" name="nilai[<?=$r["kd_kriteria"]?>]" id="nilai">
															<option>---</option>
															<?php $sql = $connection->query("SELECT * FROM penilaian WHERE kd_kriteria=$r[kd_kriteria]"); while ($data = $sql->fetch_assoc()): ?>
																<option value="<?=$data["bobot"]?>" class="<?=$data["kd_kriteria"]?>"<?= (!$update) ? "" : (($row["kd_penilaian"] != $data["kd_penilaian"]) ? "" : ' selected="selected"') ?>><?=$data["keterangan"]?></option>
															<?php endwhile; ?>
														</select>
														<?php else: ?>
															<input type="text" name="nilai[<?=$r['kd_kriteria']?>]" class="form-control" >
														<?php endif; ?>
				                </div>
										<?php endwhile; ?>
										<input type="hidden" name="save" value="true">
									<?php endif; ?>
	                <button type="submit" id="simpan" class="btn btn-<?= ($update) ? "warning" : "info" ?> btn-block"><?=($_POST) ? "Simpan" : "Tampilkan"?></button>
	                <?php if ($update): ?>
										<a href="?page=nilai" class="btn btn-info btn-block">Batal</a>
									<?php endif; ?>
	            </form>
	        </div>
	    </div>
	</div>
	<div class="col-md-8">
	    <div class="panel panel-info">
	        <div class="panel-heading"><h3 class="text-center">DAFTAR</h3></div>
	        <div class="panel-body">
	            <table class="table table-condensed">
	                <thead>
	                    <tr>
	                        <th>No</th>
													<th>NIK</th>
													<th>Nama</th>
	                        <th>Jabatan</th>
	                        <th>Kriteria</th>
	                        <th>Nilai</th>
	                        <th></th>
	                    </tr>
	                </thead>
	                <tbody>
	                    <?php $no = 1; ?>
	                    <?php if ($query = $connection->query("SELECT a.kd_nilai, c.nama AS nama_jabatan, b.nama AS nama_kriteria, d.nim, d.nama AS nama_karyawan, a.nilai FROM nilai a JOIN kriteria b ON a.kd_kriteria=b.kd_kriteria JOIN jabatan c ON a.kd_jabatan=c.kd_jabatan JOIN karyawan d ON d.nim=a.nim ORDER BY a.kd_nilai DESC, d.nama")): ?>
	                        <?php while($row = $query->fetch_assoc()): ?>
	                        <tr>
	                            <td><?=$no++?></td>
															<td><?=$row['nim']?></td>
															<td><?=$row['nama_karyawan']?></td>
	                            <td><?=$row['nama_jabatan']?></td>
	                            <td><?=$row['nama_kriteria']?></td>
	                            <td><?=$row['nilai']?></td>
	                            <td>
	                                <div class="btn-group">
	                                    <a href="?page=nilai&action=update&key=<?=$row['kd_nilai']?>" class="btn btn-warning btn-xs">Edit</a>
	                                    <a href="?page=nilai&action=delete&key=<?=$row['kd_nilai']?>" class="btn btn-danger btn-xs">Hapus</a>
	                                </div>
	                            </td>
	                        </tr>
	                        <?php endwhile ?>
	                    <?php endif ?>
	                </tbody>
	            </table>
	        </div>
	    </div>
	</div>
</div>
<script type="text/javascript">
$("#kriteria").chained("#jabatan");
$("#nilai").chained("#kriteria");
</script>
