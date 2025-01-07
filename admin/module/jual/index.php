<?php 
$id = $_SESSION['admin']['id_member'];
$hasil = $lihat->member_edit($id);

// Koneksi ke database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=db_toko", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}


// Proses pencarian
$keyword = '';
$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cari'])) {
    $keyword = trim($_POST['cari']);
    if (!empty($keyword)) {
        $sql = "SELECT * FROM barang WHERE id_barang LIKE :keyword OR nama_barang LIKE :keyword";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':keyword' => "%$keyword%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_ke_keranjang'])) {
	$id_barang = $_POST['id_barang'];
	$nama_barang = $_POST['nama_barang'];
	$stok = $_POST['stok'];
	$harga_jual = $_POST['harga_jual'];
	$jumlah = 1; // Default jumlah 1
	$total = $harga_jual * $jumlah;

	// Tambahkan data ke tabel penjualan
	$sql = "INSERT INTO penjualan (id_barang, id_member, jumlah, total, tanggal_input) VALUES (?, ?, ?, ?, ?)";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$id_barang, $id, $jumlah, $total, date('Y-m-d H:i:s')]);

	// Redirect untuk menghindari pengiriman ulang data
	header("Location: index.php?page=jual");
	exit;
}


?>
<h4>Keranjang Penjualan</h4>
<br>
<?php if(isset($_GET['success'])){?>
<div class="alert alert-success">
    <p>Edit Data Berhasil!</p>
</div>
<?php }?>
<?php if(isset($_GET['remove'])){?>
<div class="alert alert-danger">
    <p>Hapus Data Berhasil!</p>
</div>
<?php }?>
<div class="row">
    <div class="col-sm-4">
        <div class="card card-primary mb-3">
            <div class="card-header bg-primary text-white">
                <h5><i class="fa fa-search"></i> Cari Barang</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="text" id="cari" class="form-control" name="cari" placeholder="Masukan: Kode / Nama Barang" value="<?= htmlspecialchars($keyword) ?>">
                    <button type="submit" class="btn btn-primary mt-2">Cari</button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
    <div class="table-responsive">
        <?php if (!empty($results)) : ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID Barang</th>
                        <th>Nama Barang</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Tanggal Input</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id_barang']) ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td>Rp. <?= number_format($row['harga_beli'], 2, ',', '.') ?></td>
                            <td>Rp. <?= number_format($row['harga_jual'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['stok']) ?></td>
                            <td><?= htmlspecialchars($row['tgl_input']) ?></td>
                            <td>
								<form method="POST" action="">
									<input type="hidden" name="id_barang" value="<?= htmlspecialchars($row['id_barang']) ?>">
									<input type="hidden" name="nama_barang" value="<?= htmlspecialchars($row['nama_barang']) ?>">
									<input type="hidden" name="stok" value="<?= htmlspecialchars($row['stok']) ?>">
									<input type="hidden" name="harga_jual" value="<?= htmlspecialchars($row['harga_jual']) ?>">
									<button type="submit" name="tambah_ke_keranjang" class="btn btn-success">Tambah</button>
								</form>
							</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="text-center">Data tidak ditemukan</p>
        <?php endif; ?>
    </div>
</div>

    <!-- Bagian KASIR tetap tidak diubah -->
    <div class="col-sm-12">
			<div class="card card-primary">
				<div class="card-header bg-primary text-white">
					<h5><i class="fa fa-shopping-cart"></i> KASIR
					<a class="btn btn-danger float-right" 
						onclick="return confirm('Apakah anda ingin reset keranjang ?');" href="fungsi/hapus/hapus.php?penjualan=jual">
						<b>RESET KERANJANG</b></a>
					</h5>
				</div>
				<div class="card-body">
					<div id="keranjang" class="table-responsive">
						<table class="table table-bordered">
							<tr>
								<td><b>Tanggal</b></td>
								<td><input type="text" readonly="readonly" class="form-control" value="<?php echo date("j F Y, G:i");?>" name="tgl"></td>
							</tr>
						</table>
						<table class="table table-bordered w-100" id="example1">
							<thead>
								<tr>
									<td> No</td>
									<td> Nama Barang</td>
									<td style="width:10%;"> Jumlah</td>
									<td style="width:20%;"> Total</td>
									<td> Kasir</td>
									<td> Aksi</td>
								</tr>
							</thead>
							<tbody>
								<?php $total_bayar=0; $no=1; $hasil_penjualan = $lihat -> penjualan();?>
								<?php foreach($hasil_penjualan  as $isi){?>
								<tr>
									<td><?php echo $no;?></td>
									<td><?php echo $isi['nama_barang'];?></td>
									<td>
										<!-- aksi ke table penjualan -->
										<form method="POST" action="fungsi/edit/edit.php?jual=jual">
												<input type="number" name="jumlah" value="<?php echo $isi['jumlah'];?>" class="form-control">
												<input type="hidden" name="id" value="<?php echo $isi['id_penjualan'];?>" class="form-control">
												<input type="hidden" name="id_barang" value="<?php echo $isi['id_barang'];?>" class="form-control">
											</td>
											<td>Rp.<?php echo number_format($isi['total']);?>,-</td>
											<td><?php echo $isi['nm_member'];?></td>
											<td>
												<button type="submit" class="btn btn-warning">Update</button>
										</form>
										<!-- aksi ke table penjualan -->
										<a href="fungsi/hapus/hapus.php?jual=jual&id=<?php echo $isi['id_penjualan'];?>&brg=<?php echo $isi['id_barang'];?>
											&jml=<?php echo $isi['jumlah']; ?>"  class="btn btn-danger">hapus<i class="fa fa-times"></i>
										</a>
									</td>
								</tr>
								<?php $no++; $total_bayar += $isi['total'];}?>
							</tbody>
					</table>
					<br/>
					<?php $hasil = $lihat -> jumlah(); ?>
					<div id="kasirnya">
						<table class="table table-stripped">
							<?php
							// proses bayar dan ke nota
							if(!empty($_GET['nota'] == 'yes')) {
								$total = $_POST['total'];
								$bayar = $_POST['bayar'];
								if(!empty($bayar))
								{
									$hitung = $bayar - $total;
									if($bayar >= $total)
									{
										$id_barang = $_POST['id_barang'];
										$id_member = $_POST['id_member'];
										$jumlah = $_POST['jumlah'];
										$total = $_POST['total1'];
										$tgl_input = $_POST['tgl_input'];
										$periode = $_POST['periode'];
										$jumlah_dipilih = count($id_barang);
										
										for($x=0;$x<$jumlah_dipilih;$x++){

											$d = array($id_barang[$x],$id_member[$x],$jumlah[$x],$total[$x],$tgl_input[$x],$periode[$x]);
											$sql = "INSERT INTO nota (id_barang,id_member,jumlah,total,tanggal_input,periode) VALUES(?,?,?,?,?,?)";
											$row = $config->prepare($sql);
											$row->execute($d);

											// ubah stok barang
											$sql_barang = "SELECT * FROM barang WHERE id_barang = ?";
											$row_barang = $config->prepare($sql_barang);
											$row_barang->execute(array($id_barang[$x]));
											$hsl = $row_barang->fetch();
											
											$stok = $hsl['stok'];
											$idb  = $hsl['id_barang'];

											$total_stok = $stok - $jumlah[$x];
											// echo $total_stok;
											$sql_stok = "UPDATE barang SET stok = ? WHERE id_barang = ?";
											$row_stok = $config->prepare($sql_stok);
											$row_stok->execute(array($total_stok, $idb));
										}
										echo '<script>alert("Belanjaan Berhasil Di Bayar !");</script>';
									}else{
										echo '<script>alert("Uang Kurang ! Rp.'.$hitung.'");</script>';
									}
								}
							}
							?>
							<!-- aksi ke table nota -->
							<form method="POST" action="index.php?page=jual&nota=yes#kasirnya">
								<?php foreach($hasil_penjualan as $isi){;?>
									<input type="hidden" name="id_barang[]" value="<?php echo $isi['id_barang'];?>">
									<input type="hidden" name="id_member[]" value="<?php echo $isi['id_member'];?>">
									<input type="hidden" name="jumlah[]" value="<?php echo $isi['jumlah'];?>">
									<input type="hidden" name="total1[]" value="<?php echo $isi['total'];?>">
									<input type="hidden" name="tgl_input[]" value="<?php echo $isi['tanggal_input'];?>">
									<input type="hidden" name="periode[]" value="<?php echo date('m-Y');?>">
								<?php $no++; }?>
								<tr>
									<td>Total Semua  </td>
									<td><input type="text" class="form-control" name="total" value="<?php echo $total_bayar;?>"></td>
								
									<td>Bayar  </td>
									<td><input type="text" class="form-control" name="bayar" value="<?php echo $bayar;?>"></td>
									<td><button class="btn btn-success"><i class="fa fa-shopping-cart"></i> Bayar</button>
									<?php  if(!empty($_GET['nota'] == 'yes')) {?>
										<a class="btn btn-danger" href="fungsi/hapus/hapus.php?penjualan=jual">
										<b>RESET</b></a></td><?php }?></td>
								</tr>
							</form>
							<!-- aksi ke table nota -->
							<tr>
								<td>Kembali</td>
								<td><input type="text" class="form-control" value="<?php echo $hitung;?>"></td>
								<td></td>
								<td>
									<a href="print.php?nm_member=<?php echo $_SESSION['admin']['nm_member'];?>
									&bayar=<?php echo $bayar;?>&kembali=<?php echo $hitung;?>" target="_blank">
									<button class="btn btn-secondary">
										<i class="fa fa-print"></i> Print Untuk Bukti Pembayaran
									</button></a>
								</td>
							</tr>
						</table>
						<br/>
						<br/>
					</div>
				</div>
			</div>
		</div>
	</div>
                <!-- Konten kasir tetap sama -->
            </div>
        </div>
    </div>
</div>
