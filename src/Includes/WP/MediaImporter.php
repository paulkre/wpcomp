<?php

namespace WPComp\Includes\WP;

class MediaImporter
{
  static function get_id(string $rel_path)
  {
    $path = \get_template_directory() . '/' . $rel_path;

    $key = 'theme-image__' . $rel_path;
    if ($id = \get_option($key))
      if (\get_post($id))
        return $id;

    $id = self::import_to_wp($path);

    \update_option($key, $id);

    return $id;
  }

  static function import_to_wp(string $path)
  {
    $filename = basename($path);

    $upload_path = \wp_upload_dir()['path'] . '/' . $filename;
    copy($path, $upload_path);

    $mime_type = \wp_check_filetype($filename)['type'];

    $id = \wp_insert_attachment([
      'guid'           => $upload_path,
      'post_mime_type' => $mime_type,
      'post_title'     => $filename,
      'post_content'   => '',
      'post_status'    => 'inherit'
    ], $upload_path);

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    \wp_update_attachment_metadata($id, \wp_generate_attachment_metadata($id, $upload_path));

    return $id;
  }
}
