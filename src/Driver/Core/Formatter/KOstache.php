<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace Driver\Core\Formatter;

class KOstache
{
    protected $data;
    protected $layout = NULL;

    public function setup(array $data)
    {
        $this->data = $data;
    }

    public function format($template)
    {
        $view_name = 'View_'.$template;
        $view = new $view_name;
        $view->data = $this->data;

        $renderer = \Kostache_Layout::factory($this->layout);
        return $renderer->render($view);
    }
}
