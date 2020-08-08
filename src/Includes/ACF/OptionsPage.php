<?php

namespace WPComp\Includes\ACF;

class OptionsPage
{
  private static $registered_count = 0;

  static function register(string $name, $props)
  {
    if (!function_exists('acf_add_options_page')) return;

    @[
      'title' => $title,
      'icon_url' => $icon_url,
      'use_tabs' => $use_tabs,
      'acf_groups' => $acf_groups,
    ] = $props;

    if (!$acf_groups) return;

    \acf_add_options_page([
      'page_title'   => $title,
      'menu_title'  => $title,
      'menu_slug'   => $name,
      'capability'  => 'manage_options',
      'redirect'    => false,
      'icon_url' => $icon_url,
      'position' => 100 + self::$registered_count
    ]);
    self::$registered_count++;

    if (!$use_tabs) {
      foreach ($acf_groups as $grp_name => &$grp_props)
        self::register_group($name, $grp_name, $grp_props);
    } else self::register_groups_with_tabs($name, $acf_groups);
  }

  private static function register_group(string $name, string $grp_name, $props)
  {
    @[
      'title' => $title,
      'fields' => $fields,
    ] = $props;

    \acf_add_local_field_group([
      'key' => $grp_name,
      'title' => $title,
      'fields' => $fields,
      'location' => [[[
        'param' => 'options_page',
        'operator' => '==',
        'value' => $name
      ]]]
    ]);
  }

  private static function register_groups_with_tabs(string $name, $groups)
  {
    $fields = [];

    foreach ($groups as $grp_name => &$grp) {
      @[
        'title' => $grp_title,
        'fields' => $grp_fields,
      ] = $grp;

      $fields[] = [
        'name' => $grp_name,
        'key' => $grp_name,
        'label' => $grp_title,
        'type' => 'tab'
      ];

      foreach ($grp_fields as &$field)
        $fields[] = $field;
    }

    \acf_add_local_field_group([
      'key' => $name,
      'title' => __('Settings'),
      'fields' => $fields,
      'location' => [[[
        'param' => 'options_page',
        'operator' => '==',
        'value' => $name
      ]]],
      'style' => 'seamless'
    ]);
  }
}
