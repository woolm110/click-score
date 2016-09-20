<?php

/**
 * Class MyImageStyle
 */
class ImageStyle
{
  public $width;
  public $height;
  public $backgroundColor;
  public $textSize;
  public $textAngle;
  public $fontFile;
  public $textColor;

  public function __construct($width, $height, $backgroundColor, $textSize, $textAngle, $fontFile, $textColor)
  {
    $this->width = $width;
    $this->height = $height;
    $this->backgroundColor = $this->hexToRGB($backgroundColor);
    $this->textSize = $textSize;
    $this->textAngle = $textAngle;
    $this->fontFile = __DIR__ . '/fonts/' . $fontFile;
    $this->textColor = $this->hexToRGB($textColor);
  }

  public static function hexToRGB($hex)
  {
    return array_combine(array('r', 'g', 'b'), sscanf($hex, "#%02x%02x%02x"));
  }
}
