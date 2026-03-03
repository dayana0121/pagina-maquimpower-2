<?php
require_once 'includes/db.php';

echo "<h1>DEBUG TIKTOK VIDEOS</h1>";

// Query
$stmtTikTok = $pdo->query("SELECT * FROM tiktok_videos WHERE activo = 1 ORDER BY id DESC LIMIT 6");
$tiktokVideos = $stmtTikTok->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
echo "Total de videos: " . count($tiktokVideos) . "\n\n";
print_r($tiktokVideos);
echo "</pre>";

// Verificar que los archivos existan
echo "<h2>Verificar archivos:</h2>";
foreach($tiktokVideos as $video) {
    $file = __DIR__ . $video['video_archivo'];
    $thumb = __DIR__ . $video['thumbnail'];
    echo "<p>";
    echo "Video: " . htmlspecialchars($video['video_archivo']) . " - ";
    echo file_exists($file) ? "✅ EXISTS" : "❌ NOT FOUND";
    echo "<br>";
    echo "Thumb: " . htmlspecialchars($video['thumbnail']) . " - ";
    echo file_exists($thumb) ? "✅ EXISTS" : "❌ NOT FOUND";
    echo "</p>";
}
?>