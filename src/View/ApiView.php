<?php

namespace RestApi\View;

use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\View\View;

/**
 * Api View
 *
 * Default view class for rendering API response
 */
class ApiView extends View
{

    /**
     * Layout
     *
     * @var string
     */
    protected $_responseLayout = 'response';

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        if ('xml' === Configure::read('ApiRequest.responseType')) {
            $this->response->withType('xml');
            $this->_responseLayout = 'xml_response';
        } else {
            $this->response->withType('json');
        }
    }

    /**
     * Renders view for given template file and layout.
     *
     * Render triggers helper callbacks, which are fired before and after the template are rendered,
     * as well as before and after the layout. The helper callbacks are called:
     *
     * - `beforeRender`
     * - `afterRender`
     * - `beforeLayout`
     * - `afterLayout`
     *
     * If View::$autoLayout is set to `false`, the template will be returned bare.
     *
     * Template and layout names can point to plugin templates or layouts. Using the `Plugin.template` syntax
     * a plugin template/layout/ can be used instead of the app ones. If the chosen plugin is not found
     * the template will be located along the regular view path cascade.
     *
     * @param string|null $template Name of template file to use
     * @param string|false|null $layout Layout to use. False to disable.
     * @return string Rendered content.
     * @throws \Cake\Core\Exception\CakeException If there is an error in the view.
     * @triggers View.beforeRender $this, [$templateFileName]
     * @triggers View.afterRender $this, [$templateFileName]
     */
    public function render(?string $template = null, $layout = null): string
    {
        if (isset($this->hasRendered) && $this->hasRendered) {
            return null;
        }

        $this->layout = "RestApi.{$this->_responseLayout}";

        $this->Blocks->set('content', $this->renderLayout('', $this->layout));

        $this->hasRendered = true;

        return $this->Blocks->get('content');
    }
}
