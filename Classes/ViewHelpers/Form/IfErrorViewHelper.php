<?php
namespace CDSRC\CdsrcTemplateBuilder\ViewHelpers\Form;

/* **********************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 *  All rights reserved
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ******************************************************************** */

use Closure;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * View helper which render content if form property has error
 */
class IfErrorViewHelper extends AbstractConditionViewHelper {


	/**
	 * @return void
	 */
	public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('property', 'string', 'Property to be checked.', false);
        $this->registerArgument('properties', 'array', 'Properties to be checked.', false);
	}

	/**
	 * Render the "Base" tag by outputting $request->getBaseUri()
	 *
	 * @return string
	 */
	public function render() {
        self::setArgumentCondition($this->arguments, $this->renderingContext);
        return parent::render();
	}

    /**
     * Default implementation for CompilableInterface. See CompilableInterface
     * for a detailed description of this method.
     *
     * @param array $arguments
     * @param Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    static public function renderStatic(array $arguments, Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
        self::setArgumentCondition($arguments, $renderingContext);
        return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }


    /**
     * @param $arguments
     * @param RenderingContextInterface $renderingContext
     */
    static protected function setArgumentCondition(&$arguments, RenderingContextInterface $renderingContext){
        if(!isset($arguments['property']) && !isset($arguments['properties'])){
            return;
        }
        if(isset($arguments['property']) && trim($arguments['property']) !== ''){
            $arguments['properties'] = array($arguments['property']);
        }
        $originalRequestMappingResults = $renderingContext->getControllerContext()->getRequest()->getOriginalRequestMappingResults();
        $arguments['condition'] = false;
        foreach($arguments['properties'] as $property) {
            if($originalRequestMappingResults->forProperty($property)->hasErrors()){
                $arguments['condition'] = true;
            }
        }
    }
}
