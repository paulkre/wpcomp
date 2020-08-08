<?php

namespace WPComp\Includes\ACF;

use WPComp\Theme;

class Page
{
  static function register_template(string $path, $props)
  {
    if (!function_exists('acf_add_local_field_group')) return;

    @$groups = $props['acf_groups'];
    if (!$groups) return;

    $i = 0;
    foreach ($groups as $grp_name => &$grp)
      self::register_group($path, $grp_name, $grp, $i++);
  }

  static function initialize_fields(int $pid, $field_data)
  {
    if (!is_array($field_data)) return;

    foreach ($field_data as $key => $value) {
      if (Theme::get_field($key, $pid)) continue;
      Theme::update_field($key, $value);
    }
  }

  private static function register_group(string $template_path, string $grp_name, $props, $position = 0)
  {
    @[
      'title' => $title,
      'fields' => $fields,
    ] = $props;

    \acf_add_local_field_group([
      'key' => $grp_name,
      'title' => $title,
      'fields' => $fields,
      'menu_order' => $position,
      'location' => [[[
        'param' => 'page_template',
        'operator' => '==',
        'value' => $template_path
      ]]]
    ]);
  }
}
