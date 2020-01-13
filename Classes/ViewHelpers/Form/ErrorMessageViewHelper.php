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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which render content if form property has error
 */
class ErrorMessageViewHelper extends AbstractViewHelper {


	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('property', 'string', 'Property to be checked.', true);
	}

	/**
	 * Render the "Base" tag by outputting $request->getBaseUri()
	 *
	 * @return string
	 */
	public function render() {
		return self::renderStatic(array('property' => $this->arguments['property']), $this->renderChildrenClosure, $this->renderingContext);
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
    static public function renderStatic(array $arguments, Closure $renderChildrenClosure = null, RenderingContextInterface $renderingContext = null) {
        $result = $renderingContext
				->getControllerContext()
				->getRequest()
				->getOriginalRequestMappingResults()
				->forProperty($arguments['property']);
		if($result->hasErrors()){
			return $result->getFirstError()->getMessage();
		}
		return '';
    }

}
