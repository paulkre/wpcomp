<?php

namespace WPComp\Includes;

class WPackio
{
  const APP_NAME = 'hiveLab';
  const OUTPUT_PATH = 'dist';
  const ENTRY_GROUP = 'app';

  const ENTRY_NAMESPACE = 'WPComp\\\Components';

  static function enqueue_assets(\WPComp\Component $content)
  {
    $entries = [];
    self::collect_entries($entries, $content);
    if (!$entries) return;

    \add_action('wp_enqueue_scripts', function () use ($entries) {
      $wpackio = self::create_enqueue_manager();

      foreach ($entries as &$entry)
        $wpackio->enqueue(self::ENTRY_GROUP, $entry, []);
    });
  }

  static function enqueue(string $entry)
  {
    $wpackio = self::create_enqueue_manager();
    $wpackio->enqueue(self::ENTRY_GROUP, $entry, []);
  }

  private static function create_enqueue_manager()
  {
    return new \WPackio\Enqueue(self::APP_NAME, self::OUTPUT_PATH, null, 'theme');
  }

  private static function collect_entries(array &$entries, &$data)
  {
    if (!$data) return;

    if (is_array($data))
      foreach ($data as &$item)
        self::collect_entries($entries, $item);

    if ($data instanceof \WPComp\Component) {
      if ($data->do_not_enqueue) return;

      preg_match('/^' . self::ENTRY_NAMESPACE . '\\\(\w+)\\\/', get_class($data), $matches);
      if (@$entry = $matches[1])
        if (!in_array($entry, $entries))
          $entries[] = $entry;

      if (!$data->do_not_enqueue_children)
        self::collect_entries($entries, $data->children);
    }
  }
}
