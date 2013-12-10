<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace Driver\Core\Formatter;

class KOstache
{
    protected $data;

    public function setup($data)
    {
        $this->data = $data;
    }

    public function format($template)
    {
        $view_name = 'View_'.$template;
        $view = new $view_name;
        $view->data = $this->data;

        $renderer = \Kostache_Layout::factory();
        return $renderer->render($view);
    }
}
