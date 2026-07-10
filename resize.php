<?php
$img = imagecreatefrompng('public/loops-icon.png');
$out = imagecreatetruecolor(512, 512);
imagealphablending($out, false);
imagesavealpha($out, true);
$transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
imagefilledrectangle($out, 0, 0, 512, 512, $transparent);
imagecopyresampled($out, $img, 0, 0, 0, 0, 512, 512, imagesx($img), imagesy($img));
imagepng($out, 'public/loops-icon-512.png');
echo "Created loops-icon-512.png\n";
