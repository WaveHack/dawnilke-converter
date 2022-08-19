#!/usr/bin/env php
<?php

$spriteSize = 16;

/** @var GdImage[] $images */
$images = [];

foreach (glob('DawnLike/**/*.png') as $filename) {
    echo "Processing {$filename}\n";

    $isSingle = !preg_match('/.+?[01]\.png$/', $filename);
    $isFirst = preg_match('/.+?0\.png$/', $filename);
    $isSecond = !$isFirst;

    preg_match('#DawnLike/(\w+)/(\w+?)[01]?\.png#', $filename, $matches);
    [, $category, $type] = $matches;

    if ($category === '' || $type === '') {
        continue;
    }

    $sourceImage = imagecreatefrompng($filename);
    imagealphablending($sourceImage, true);
    imagesavealpha($sourceImage, true);

    $imageWidth = imagesx($sourceImage);
    $imageHeight = imagesy($sourceImage);

    $numImagesX = $imageWidth / $spriteSize;
    $numImagesY = $imageHeight / $spriteSize;

    $i = 0;
    for ($x = 0; $x < $numImagesX; $x++) {
        for ($y = 0; $y < $numImagesY; $y++) {
            $arrayKey = "{$category}.{$type}.{$i}";
            $outputFilename = "output/{$category}/{$type}/{$type}_{$i}.png";

            @mkdir("output/{$category}/{$type}", 0777, true);

            $destinationImage = imagecreatetruecolor($spriteSize, $spriteSize);
            imagealphablending($destinationImage, false);
            imagesavealpha($destinationImage, true);

            imagecopy(
                $destinationImage,
                $sourceImage,
                0,
                0,
                $x * $spriteSize,
                $y * $spriteSize,
                $spriteSize,
                $spriteSize
            );

            $paletteImage = imagecreate($spriteSize, $spriteSize);
            imagecopy($paletteImage, $destinationImage, 0, 0, 0, 0, $spriteSize, $spriteSize);
            $colorsTotal = imagecolorstotal($paletteImage);
            imagedestroy($paletteImage);

            if ($colorsTotal === 1) {
                continue;
            }

            if ($isSingle) {
                imagepng($destinationImage, $outputFilename);

            } elseif ($isFirst) {
                $images[$arrayKey] = $destinationImage;

            } elseif ($isSecond) {
                $properImage = imagecreatetruecolor($spriteSize * 2, $spriteSize);
                imagealphablending($properImage, false);
                imagesavealpha($properImage, true);

                imagecopy(
                    $properImage,
                    $images[$arrayKey],
                    0,
                    0,
                    0,
                    0,
                    $spriteSize,
                    $spriteSize
                );

                imagecopy(
                    $properImage,
                    $destinationImage,
                    $spriteSize,
                    0,
                    0,
                    0,
                    $spriteSize,
                    $spriteSize
                );

                imagepng($properImage, $outputFilename);

                imagedestroy($properImage);
                imagedestroy($images[$arrayKey]);
                imagedestroy($destinationImage);
            }

            $i++;
        }
    }
}
