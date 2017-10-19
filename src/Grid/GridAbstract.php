<?php

namespace View\Grid;

use Core\Render\View;
use Core\Blue\Model;
use Loader;

abstract class GridAbstract extends View\ViewAbstract
{
    const DEFAULT_RENDERER = 'Core\Render\View\Grid\Row';

    /**
     * string used to join many content for one marker
     *
     * @var string
     */
    protected $_contentGlue = "\n";

    /**
     * @var null|Row
     */
    protected $_customRowRenderer = null;

    /**
     * initialize grid renderer
     * 
     * @param array $options
     */
    public function __construct(array $options)
    {
        Loader::callEvent('initialize_grid_object_before', $this);
        $this->initializeBlock($options);

        $this->_options = array_merge($this->_options, $options);

        $this->afterInitializeBlock();
        Loader::callEvent('initialize_grid_object_after', $this);
    }

    /**
     * render grid content
     * 
     * @return string
     */
    public function render()
    {
        Loader::callEvent('render_grid_object_before', $this);

        $data = $this->_options['data'];
        if (empty($data)) {
            return '';
        }

        $renderedContent = [];
        foreach ($data as $row) {
            $this->changeRowData($row);

            if ($this->_customRowRenderer) {
                $renderer = $this->_customRowRenderer;
            } else {
                $renderer = self::DEFAULT_RENDERER;
            }

            /** @var Row $renderer */
            $renderer = Loader::getClass(
                $renderer,
                [
                    'template'      => $this->_options['template'],
                    'module'        => $this->_options['module'],
                    'data'          => $this->_getDataFromFow($row),
                    'cache'         => false,
                    'cache_data'    => false,
                ]
            );

            $renderer->setClearMarkers($this->getClearMarkers());
            $renderedContent[] = $renderer->render();
        }

        Loader::callEvent('render_grid_object_after', $this);
        return implode($this->_contentGlue, $renderedContent);
    }

    /**
     * set custom renderer to render row
     * give full class name with path
     * 
     * @param string $renderer
     * @return GridAbstract
     */
    public function serRowRenderer($renderer)
    {
        $this->_customRowRenderer = $renderer;
        return $this;
    }

    /**
     * return custom renderer class name
     * 
     * @return string
     */
    public function getRowRenderer()
    {
        return $this->_customRowRenderer;
    }

    /**
     * return data for row
     * 
     * @param array|Model\Object $row
     * @return array
     */
    protected function _getDataFromFow($row)
    {
        if ($row instanceof Model\Object) {
            return $row->getData();
        }

        return $row;
    }

    /**
     * allow to change row data before rendering
     * 
     * @param array $row
     */
    public function changeRowData($row){}
}
