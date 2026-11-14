<!DOCTYPE HTML>
<html>
<head><meta charset="UTF-8"></head>
<body>

<?php
require_once __DIR__ . '/image_to_ascii.php';
function prt($text, $img){
    echo('<h2>'.htmlspecialchars($text).'</h2><code>'.$img.'</code>');
}

$url = 'test.png';

prt("original", "");
echo("<img src='".$url."' alt='test image'/>");

prt( "120x60", imageToAscii($url,  120,  60, '</br>'));
prt(  "80x25", imageToAscii($url, 80, 25, '</br>'));
prt(  "40x25", imageToAscii($url, 40, 25, '</br>'));
prt("braille", imageToAscii($url, 80, 25, '</br>', '⣿⣷⣵⣕⢕⢑⢁⠁ ')); // 
prt("unicode", imageToAscii($url, 80, 25, '</br>', '█▓▒░ ')); // 

?>
</body>
</html>