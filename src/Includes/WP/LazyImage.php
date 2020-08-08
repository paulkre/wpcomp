<?php

namespace WPComp\Includes\WP;

class LazyImage
{
  const PREFIX = 'theme__';

  private static $sizes = [
    48,
    512,
    1024,
    1536,
    2048,
    2560
  ];

  private static $size_names;

  static function init()
  {
    self::$size_names = [];
    foreach (self::$sizes as $size) {
      $name = self::PREFIX . $size;
      \add_image_size($name, $size, $size, false);
      self::$size_names[$size] = $name;
    }
  }

  static function get_attachment_sources(int $id)
  {
    $sources = [];

    foreach (self::$sizes as &$size)
      $sources[] = \wp_get_attachment_image_src($id, self::$size_names[$size]);

    return $sources;
  }
}
