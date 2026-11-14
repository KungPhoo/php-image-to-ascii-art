<?php
function imageToAscii(
    string $file,
    int $width = 120,
    int $height = 60,
    string $linebreak = "\n",


    string|array $chars = "@W#O*+:. "
): string {
    $chars = is_array($chars)
        ? array_values($chars)
        : preg_split('//u', $chars, -1, PREG_SPLIT_NO_EMPTY);

    if (!$chars || $width < 1 || $height < 1) {
        throw new InvalidArgumentException(
            'Invalid dimensions or character scale.'
        );
    }

    $src = imagecreatefromstring(file_get_contents($file));

    if (!$src) {
        throw new RuntimeException('Cannot load image: ' . $file);
    }

    $sw = imagesx($src);
    $sh = imagesy($src);

    // Preserve aspect ratio (character ratio 1:2).
    $aspect = ($sw / $sh) * 2.0;

    $w = max(1, min($width,
        (int) round($height * $aspect)));

    $h = max(1, min($height,
        (int) round($w / $aspect)));

    // Resize image.
    $dst = imagecreatetruecolor($w, $h);
    imagealphablending($dst, true);
    imagefill($dst, 0, 0, 0xFFFFFF);

    imagecopyresampled(
        $dst, $src,
        0, 0, 0, 0,
        $w, $h, $sw, $sh
    );

    unset($src);

    // PASS 1: Calculate brightness and find min/max.
    // Store grayscale values to avoid reading pixels twice.
    $pixels = [];
    $min = 255;
    $max = 0;

    for ($y = 0; $y < $h; ++$y) {
        for ($x = 0; $x < $w; ++$x) {

            $rgb = imagecolorat($dst, $x, $y);

            $gray = (
                  77 * (($rgb >> 16) & 255)
                + 150 * (($rgb >> 8) & 255)
                +  29 * ($rgb & 255)
            ) >> 8;

            $pixels[] = $gray;

            if ($gray < $min) $min = $gray;
            if ($gray > $max) $max = $gray;
        }
    }

    unset($dst);

    // Build contrast-adjusted lookup table.
    $last = count($chars) - 1;
    $range = $max - $min;
    $lut = [];

    if ($range > 0) {

        $half = $range >> 1;

        for ($i = $min; $i <= $max; ++$i) {

            $index = intdiv(
                ($i - $min) * $last + $half,
                $range
            );

            $lut[$i] = $chars[$index];
        }
    } else {
    // Uniform image: preserve original brightness.
        $lut[$min] = $chars[
            intdiv($min * $last + 127, 255)
        ];
    }
    // PASS 2: Generate ASCII using normalized brightness.
    $lines = [];
    $pos = 0;

    for ($y = 0; $y < $h; ++$y) {
        $line = '';
        for ($x = 0; $x < $w; ++$x) {
            $line .= $lut[$pixels[$pos++]];
        }
        $lines[] = $line;
    }
    return implode($linebreak, $lines);
}
?>