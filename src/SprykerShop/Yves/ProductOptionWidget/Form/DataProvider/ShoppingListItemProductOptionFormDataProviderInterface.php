<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ProductOptionWidget\Form\DataProvider;

use ArrayObject;
use Generated\Shared\Transfer\ShoppingListItemTransfer;
use Generated\Shared\Transfer\ShoppingListTransfer;

interface ShoppingListItemProductOptionFormDataProviderInterface
{
    /**
     * @param \Generated\Shared\Transfer\ShoppingListTransfer $shoppingListTransfer
     * @param array $params
     *
     * @return \Generated\Shared\Transfer\ShoppingListTransfer
     */
    public function expandData(ShoppingListTransfer $shoppingListTransfer, array $params): ShoppingListTransfer;

    /**
     * @param \Generated\Shared\Transfer\ShoppingListItemTransfer $shoppingListItemTransfer
     *
     * @return \ArrayObject|\Generated\Shared\Transfer\ProductOptionGroupStorageTransfer[]|null
     */
    public function getProductOptionGroups(ShoppingListItemTransfer $shoppingListItemTransfer);

    /**
     * @param \Symfony\Component\Form\ChoiceList\View\ChoiceView[] $productOptionGroups
     *
     * @return \ArrayObject
     */
    public function mapProductOptionGroups(array $productOptionGroups): ArrayObject;
}
