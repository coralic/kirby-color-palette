<?php

namespace coralic;

use ColorThief\ColorThief;
use InvalidArgumentException;
use RuntimeException;

class ColorPalette
{
  /**
   * @param array $color
   * @param bool $prependHash = true
   *
   * @return string
   */
  public static function fromRGBToHex($color, $prependHash = true)
  {
    return ($prependHash ? '#' : '') . sprintf("%02x%02x%02x", ...$color);
  }

  /**
   * @param Kirby\Cms\File $image
   * @param int $limit = 10
   * @param int $size = 400
   *
   * @return array
   * @throws InvalidArgumentException
   * @throws RuntimeException
   */
  public static function extractColor($image, $limit = 10, $size = 400)
  {
    $thumb = $image->resize($size)->save();
    $root = $thumb->root();
    $palette = ColorThief::getPalette($root, $limit);

    $toHex = function ($value) {
      return ColorPalette::fromRGBToHex($value);
    };

    $colors = array_map($toHex, $palette);
    return $colors;
  }
}
