<?php
@ob_start();
session_start();
if (!empty($_SESSION['admin'])) {
} else {
    header('Location: login.php');
    exit;
}

require_once('tcpdf/tcpdf.php'); // Pastikan Anda memiliki pustaka TCPDF
require 'config.php';
include $view;
$lihat = new view($config);

$bulan_tes = array(
    '01' => "Januari",
    '02' => "Februari",
    '03' => "Maret",
    '04' => "April",
    '05' => "Mei",
    '06' => "Juni",
    '07' => "Juli",
    '08' => "Agustus",
    '09' => "September",
    '10' => "Oktober",
    '11' => "November",
    '12' => "Desember"
);

// Inisialisasi TCPDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Data Laporan Penjualan');
$pdf->SetHeaderData('', '', 'Data Laporan Penjualan', date('Y-m-d'));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(15, 27, 15);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->AddPage();

// Konten HTML
$html = '<h3 style="text-align:center;">';
if (!empty(htmlentities($_GET['cari']))) {
    $html .= 'Data Laporan Penjualan ' . $bulan_tes[htmlentities($_GET['bln'])] . ' ' . htmlentities($_GET['thn']);
} elseif (!empty(htmlentities($_GET['hari']))) {
    $html .= 'Data Laporan Penjualan ' . htmlentities($_GET['tgl']);
} else {
    $html .= 'Data Laporan Penjualan ' . $bulan_tes[date('m')] . ' ' . date('Y');
}
$html .= '</h3>';

$html .= '<table border="1" cellpadding="4">
    <thead>
        <tr bgcolor="yellow">
            <th>No</th>
            <th>ID Barang</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>Modal</th>
            <th>Total</th>
            <th>Kasir</th>
            <th>Tanggal Input</th>
        </tr>
    </thead>
    <tbody>';

$no = 1;
$bayar = $modal = $jumlah = 0;
if (!empty(htmlentities($_GET['cari']))) {
    $periode = htmlentities($_GET['bln']) . '-' . htmlentities($_GET['thn']);
    $hasil = $lihat->periode_jual($periode);
} elseif (!empty(htmlentities($_GET['hari']))) {
    $hari = htmlentities($_GET['tgl']);
    $hasil = $lihat->hari_jual($hari);
} else {
    $hasil = $lihat->jual();
}

foreach ($hasil as $isi) {
    $bayar += $isi['total'];
    $modal += $isi['harga_beli'] * $isi['jumlah'];
    $jumlah += $isi['jumlah'];
    $html .= '<tr>
        <td>' . $no . '</td>
        <td>' . $isi['id_barang'] . '</td>
        <td>' . $isi['nama_barang'] . '</td>
        <td>' . $isi['jumlah'] . '</td>
        <td>Rp.' . number_format($isi['harga_beli'] * $isi['jumlah']) . ',-</td>
        <td>Rp.' . number_format($isi['total']) . ',-</td>
        <td>' . $isi['nm_member'] . '</td>
        <td>' . $isi['tanggal_input'] . '</td>
    </tr>';
    $no++;
}

$html .= '<tr>
    <td>-</td>
    <td>-</td>
    <td><b>Total Terjual</b></td>
    <td><b>' . $jumlah . '</b></td>
    <td><b>Rp.' . number_format($modal) . ',-</b></td>
    <td><b>Rp.' . number_format($bayar) . ',-</b></td>
    <td><b>Keuntungan</b></td>
    <td><b>Rp.' . number_format($bayar - $modal) . ',-</b></td>
</tr>';

$html .= '</tbody></table>';

// Output PDF
ob_end_clean(); // Bersihkan buffer sebelum menghasilkan PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('data-laporan-' . date('Y-m-d') . '.pdf', 'I');
?>