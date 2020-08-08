<?php

namespace WPComp;

class Theme
{
  const ACTIVATE_ACTION = 'theme/activate';
  const STATIC_COMPONENT_CLASS_FILTER = 'theme/static-component-class';

  private static $initialized = false;

  static function init()
  {
    if (self::$initialized) return;
    self::$initialized = true;

    self::handle_activation();

    add_filter('acf/settings/show_admin', '__return_false');
  }

  static function register_post_type(string $name, array $props)
  {
    Includes\WP\PostType::register($name, $props);
    Includes\ACF\PostType::register($name, $props);
  }

  static function register_page_template(string $path, array $props)
  {
    Includes\WP\Page::register_template($path, $props);
    Includes\ACF\Page::register_template($path, $props);
  }

  static function register_options(string $name, array $props)
  {
    Includes\ACF\OptionsPage::register($name, $props);
  }

  static function register_activation_callback(callable $cb, $priority = 10)
  {
    \add_action(self::ACTIVATE_ACTION, $cb, $priority);
  }

  private static function handle_activation()
  {
    \add_action('init', function () {
      if (!empty($_GET['activated']) && is_admin())
        do_action(self::ACTIVATE_ACTION);
    });
  }

  static function render($content = null)
  {
    session_start();

    $static_component_class = \apply_filters(self::STATIC_COMPONENT_CLASS_FILTER, null);
    if ($static_component_class) $content = new $static_component_class;

    Includes\WPackio::enqueue_assets($content);

    $content->render();
  }

  static function set_static_component_class(string $component_class)
  {
    \add_filter(self::STATIC_COMPONENT_CLASS_FILTER, function () use ($component_class) {
      return $component_class;
    });
  }

  static function get_post_by_slug(string $slug, string $post_type = 'page')
  {
    $res = \get_posts([
      'name' => $slug,
      'post_type' => $post_type,
      'post_status' => 'publish',
    ]);
    return $res ? $res[0] : null;
  }

  static function create_page(string $slug, string $title, $template_path = null, $field_data = null)
  {
    $id = Includes\WP\Page::create($slug, $title, $template_path);
    if ($field_data) Includes\ACF\Page::initialize_fields($id, $field_data);
    return $id;
  }

  static function get_field($selector, $post_id = false, $format_value = true)
  {
    return \function_exists('get_field') ? \get_field($selector, $post_id, $format_value) : false;
  }

  static function update_field($selector, $value, $post_id = false)
  {
    return \function_exists('update_field') ? \update_field($selector, $value, $post_id) : false;
  }

  static function admin_print($msg, $type = null, $is_dismissable = false)
  {
    \add_action('admin_notices', function () use ($msg, $type, $is_dismissable) {
?>
      <div class="notice<?= $type ? " notice-$type" : null ?><?= $is_dismissable ? ' is-dismissable' : null ?>">
        <p><?= $msg ?></p>
      </div>
<?php
    });
  }
}
