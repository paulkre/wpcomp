<?php

namespace WPComp\Includes\WP;

class PostType
{
  static function register(string $name, array $props)
  {
    @[
      'wp' => $wp_props
    ] = $props;

    if (!$wp_props) return;

    $wp_props['public'] = true;
    $wp_props['show_ui'] = true;

    \add_action('init', function () use ($name, $wp_props) {
      \register_post_type($name, $wp_props);
    });

    \add_action('admin_init', function () use ($name, $wp_props) {
      self::add_admin_user_table_column($name, @$wp_props['labels']['name']);
    });
  }

  private static function add_admin_user_table_column(string $name, $label = null)
  {
    if (!$label) $label = $name;

    \add_filter('manage_users_custom_column', function ($value, $column_name, $id) use ($name) {
      if ($column_name == $name) {
        global $wpdb;
        $count = (int) $wpdb->get_var($wpdb->prepare(
          "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = '$name' AND post_status = 'publish' AND post_author = %d",
          $id
        ));
        $value = ($count > 0) ? '<a href="' . \admin_url() . 'edit.php?post_type=' . $name . '&author=' . $id . '">' . $count . '</a>' : $count;
      }
      return $value;
    }, 10, 3);

    \add_filter('manage_users_columns', function ($cols) use ($name, $label) {
      $cols[$name] = $label;
      return $cols;
    });
  }
}
