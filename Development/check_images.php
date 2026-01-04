<?php
// check_images.php
echo "<h2>Server File Check</h2>";
echo "Current Folder: " . getcwd() . "<br><br>";

if (is_dir('img')) {
    echo "✅ 'img' folder FOUND.<br>";
    echo "<h3>Files inside 'img':</h3>";
    $files = scandir('img');
    foreach($files as $file) {
        if($file != '.' && $file != '..') {
            echo "📄 " . $file . "<br>";
        }
    }
} else {
    echo "❌ 'img' folder NOT FOUND. Please create it.";
}
?>