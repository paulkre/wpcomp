<?php

namespace WPComp\Includes\ACF;

class PostType
{
  static function register(string $post_type_name, $props)
  {
    if (!function_exists('acf_add_local_field_group')) return;

    @$groups = $props['acf_groups'];
    if (!$groups) return;

    \add_action('init', function () use ($post_type_name, $groups) {
      foreach ($groups as $name => &$props)
        self::register_group($post_type_name, $name, $props);
    });

    \add_action('admin_init', function () use ($groups) {
      foreach ($groups as $name => &$props)
        self::manage_admin_columns($name, $props['fields'] ?? null);
    });
  }

  private static function register_group(string $post_type_name, string $name, $props)
  {
    @[
      'title' => $title,
      'fields' => $fields,
    ] = $props;
    if (!$title || !$fields) return;

    \acf_add_local_field_group([
      'key' => $name,
      'title' => $title,
      'fields' => $fields,
      'location' => [[[
        'param' => 'post_type',
        'operator' => '==',
        'value' => $post_type_name
      ]]]
    ]);
  }

  private static function manage_admin_columns(string $name, $fields)
  {
    if (!$fields) return;

    $column_fields = [];
    foreach ($fields as &$field)
      if (isset($field['admin_column']) && $field['admin_column'])
        $column_fields[$field['name']] = $field;

    if (empty($column_fields)) return;

    \add_filter("manage_{$name}_posts_columns", function ($columns) use ($column_fields) {
      $new_columns = [
        'cb' => $columns['cb'],
        'title' => $columns['title']
      ];

      foreach ($column_fields as $key => &$field)
        $new_columns[$key] = $field['label'];

      $new_columns['date'] = $columns['date'];

      return $new_columns;
    });

    \add_action("manage_{$name}_posts_custom_column", function ($column, $post_id) use ($column_fields) {
      if (!key_exists($column, $column_fields)) return;

      $field = $column_fields[$column];
      $value = \WPComp\Theme::get_field($column);

      if (!$value) return;

      if ($field['type'] == 'text')
        echo $value;

      if ($field['type'] == 'date_picker')
        echo \DateTime::createFromFormat('d/m/Y', $value)->format('d.m.Y');

      if ($field['type'] == 'post_object')
        echo '<a href="' . \admin_url() . 'post.php?post=' . $value->ID . '&action=edit">' . $value->post_title . '</a>';
    }, 10, 2);

    \add_filter("manage_edit-{$name}_sortable_columns", function ($columns) use ($column_fields) {
      foreach ($column_fields as &$field)
        if (self::is_sortable($field['type']))
          $columns[$field['name']] = $field['name'];

      return $columns;
    });

    \add_action('pre_get_posts', function ($query) use ($column_fields) {
      if (!\is_admin() || !$query->is_main_query()) return;

      $key = $query->get('orderby');
      if (!key_exists($key, $column_fields)) return;

      $query->set('orderby', 'meta_value');
      $query->set('meta_key', $key);
      $query->set('meta_type', self::get_meta_type($column_fields[$key]['type']));
    });
  }

  private static function is_sortable($field_type)
  {
    switch ($field_type) {
      case 'text':
        return true;
      case 'date_picker':
        return true;
      default:
        return false;
    }
  }

  private static function get_meta_type($field_type)
  {
    switch ($field_type) {
      case 'date_picker':
        return 'date';
      default:
        return false;
    }
  }
}
