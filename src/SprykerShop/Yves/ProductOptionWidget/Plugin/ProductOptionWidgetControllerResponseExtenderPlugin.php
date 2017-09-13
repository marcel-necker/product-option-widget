<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ProductOptionWidget\Plugin;

use Spryker\Yves\Kernel\AbstractPlugin;
use Spryker\Yves\Kernel\Dependency\Plugin\ControllerResponseExpanderPluginInterface;
use SprykerShop\Yves\ProductDetailPage\Controller\ProductController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerShop\Yves\ProductOptionWidget\ProductOptionWidgetFactory getFactory()
 */
class ProductOptionWidgetControllerResponseExtenderPlugin extends AbstractPlugin implements ControllerResponseExpanderPluginInterface
{

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function getResult(Request $request)
    {
        $storageProductTransfer = $this->getStorageProductTransfer($request);
        $productOptionGroupsTransfer = $this
            ->getFactory()
            ->getProductOptionClient()
            ->getProductOptions($storageProductTransfer->getIdProductAbstract(), $this->getLocale());

        $storageProductTransfer = [
            'productOptionGroups' => $productOptionGroupsTransfer,
        ];

        return $storageProductTransfer;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Generated\Shared\Transfer\StorageProductTransfer
     */
    protected function getStorageProductTransfer(Request $request)
    {
        return $request->attributes->get(ProductController::ATTRIBUTE_STORAGE_PRODUCT_TRANSFER);
    }

    /**
     * @return string
     */
    protected function getLocale()
    {
        return $this->getFactory()->getApplication()['locale'];
    }

}
