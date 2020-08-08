<?php

namespace WPComp;

class Component
{
  public $props;
  public $children;
  public $do_not_enqueue_children;
  public $do_not_enqueue;

  function __construct($props = null, $children = null, $do_not_enqueue_children = false, $do_not_enqueue = false)
  {
    $this->props = $props;
    $this->children = $children;
    $this->do_not_enqueue_children = $do_not_enqueue_children;
    $this->do_not_enqueue = $do_not_enqueue;
  }

  protected function render_children()
  {
    if ($this->children === null) return;

    if (!self::is_valid_component($this->children))
      throw new \Exception('Child component is invalid (' . self::class . ')');

    self::render_component($this->children);
  }

  protected static function render_component($component)
  {
    if (is_array($component))
      foreach ($component as &$comp)
        self::render_component($comp);
    else if ($component instanceof Component)
      $component->render();
    else if (is_string($component))
      echo $component;
    else if (is_callable($component))
      $component();
  }

  static function is_valid_component($value)
  {
    return is_array($value) || $value instanceof Component || is_string($value) || is_callable($value);
  }

  function render()
  {
    $this->render_children();
  }
}
