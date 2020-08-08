<?php

namespace WPComp\Includes\WP;

use WPComp\Theme;

class Page
{
  static function register_template(string $path, $props)
  {
    if (@$props['disable_editor'])
      \add_action('admin_init', function () use ($path) {
        @$id = $_GET['post'] ?? $_GET['from_post'];
        if (!$id) return;
        $post = \get_post($id);
        if (!$post || \get_page_template_slug($post) !== $path) return;

        self::disable_permalink_editing();
        \remove_post_type_support('page', 'editor');
      });
  }

  static function create(string $name, string $title, $template_path = null)
  {
    $res = \get_posts([
      'name' => $name,
      'post_type' => 'page',
      'post_status' => \get_post_stati()
    ]);

    if ($res) return $res[0]->ID;

    $id = \wp_insert_post([
      'post_name' => $name,
      'post_title' => $title,
      'post_type' => 'page',
      'post_status' => 'publish',
      'page_template' => $template_path,
    ]);

    Theme::admin_print(
      __('New') .  ' ' . __('page') . ': "' . $title . '"'
        . ' <a href="/wp-admin/post.php?post=' . $id . '&action=edit">' . __('Edit') . '</a>',
      'success',
      true
    );

    return $id;
  }

  private static function disable_permalink_editing()
  {
    add_action('edit_form_before_permalink', function () {
?>
      <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
          var res = document.getElementsByClassName("edit-slug");
          if (res.length > 0) res[0].disabled = true;
        })
      </script>
<?php
    });
  }
}
