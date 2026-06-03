<?php
echo "<h2>Diagnostik Folder Build Laravel</h2>";
echo "<b>Path dokumen saat ini (seharusnya folder public):</b> " . __DIR__ . "<br><br>";

$expectedPath = __DIR__ . '/build/manifest.json';
echo "<b>Mencari manifest.json di:</b> " . $expectedPath . "<br>";

if (file_exists($expectedPath)) {
    echo "<h3 style='color:green;'>✅ BERHASIL! File manifest.json ditemukan di lokasi yang benar!</h3>";
} else {
    echo "<h3 style='color:red;'>❌ GAGAL! File manifest.json TIDAK ADA di alamat tersebut.</h3>";
    
    echo "<b>Isi dari folder saat ini (/public) :</b><br>";
    echo "<pre>"; print_r(scandir(__DIR__)); echo "</pre>";
    
    echo "<b>Isi dari folder /build/ :</b><br>";
    if (is_dir(__DIR__ . '/build')) {
        echo "<pre>"; print_r(scandir(__DIR__ . '/build')); echo "</pre>";
    } else {
        echo "<span style='color:red'>Folder /build tidak ditemukan di dalam folder ini!</span><br>";
    }
}
?>
