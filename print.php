<?php 
    @ob_start();
    session_start();
    if(!empty($_SESSION['admin'])){ }else{
        echo '<script>window.location="login.php";</script>';
        exit;
    }
    require 'config.php';
    include $view;
    $lihat = new view($config);
    $toko = $lihat -> toko();
    $hsl = $lihat -> penjualan();
?>
<html>
<head>
    <title>Nota</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .nota-container {
            width: 900px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 10px;
        }
        .nota-header, .nota-footer {
            text-align: center;
        }
        .nota-table {
            width: 100%;
            border-collapse: collapse;
        }
        .nota-table th, .nota-table td {
            border-bottom: 1px dotted #000;
            padding: 5px;
            text-align: left;
        }
        .nota-summary {
            text-align: right;
            margin-top: 10px;
        }
        .nota-footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <script>window.print();</script>
    <div class="nota-container">
        <div class="nota-header">
            <p><strong><?php echo $toko['nama_toko']; ?></strong></p>
            <p><?php echo $toko['alamat_toko']; ?></p>
            <p>Tanggal: <?php echo date("j F Y, G:i"); ?></p>
            <p>Kasir: Kelompok2<?php echo htmlentities($_GET['//(perlu diganti)']); ?></p>
        </div>

        <table class="nota-table">
            <tr>
                <th>No.</th>
                <th>Barang</th>
                <th>Jumlah</th>
                <th>Total</th>
            </tr>
            <?php $no = 1; foreach ($hsl as $isi) { ?>
            <tr>
                <td><?php echo $no; ?></td>
                <td><?php echo $isi['nama_barang']; ?></td>
                <td><?php echo $isi['jumlah']; ?></td>
                <td>Rp. <?php echo number_format($isi['total']); ?></td>
            </tr>
            <?php $no++; } ?>
        </table>

        <div class="nota-summary">
            <?php $hasil = $lihat->jumlah(); ?>
            <p>Total: Rp. <?php echo number_format($hasil['bayar']); ?></p>
            <p>Bayar: Rp. <?php echo number_format(htmlentities($_GET['bayar'])); ?></p>
            <p>Kembali: Rp. <?php echo number_format(htmlentities($_GET['kembali'])); ?></p>
        </div>

        <div class="nota-footer">
            <p>Terima Kasih Telah Berbelanja di Toko Kami!</p>
        </div>
    </div>
</body>
</html>
