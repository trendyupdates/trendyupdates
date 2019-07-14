<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/**
 * Marketplace block for fieldset of configurable product.
 */

namespace Cmsmart\Marketplace\Block\Catalog\Product\Steps;

class AttributeValues extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    public function getCaption()
    {
        return __('Attribute Values');
    }
}
