<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ProductOptionWidget\Form\DataProvider;

use ArrayObject;
use Generated\Shared\Transfer\ProductAbstractOptionStorageTransfer;
use Generated\Shared\Transfer\ProductOptionTransfer;
use Generated\Shared\Transfer\ProductOptionValueStorageTransfer;
use Generated\Shared\Transfer\ShoppingListItemTransfer;
use Generated\Shared\Transfer\ShoppingListTransfer;
use SprykerShop\Yves\ProductOptionWidget\Dependency\Client\ProductOptionWidgetToProductOptionStorageClientInterface;

class ShoppingListItemProductOptionFormDataProvider implements ShoppingListItemProductOptionFormDataProviderInterface
{
    protected const SHOPPING_LIST_UPDATE_FORM_NAME = 'shopping_list_update_form';
    protected const PRODUCT_OPTIONS_FIELD_NAME = 'productOptions';

    /**
     * @var \SprykerShop\Yves\ProductOptionWidget\Dependency\Client\ProductOptionWidgetToProductOptionStorageClientInterface
     */
    protected $productOptionStorageClient;

    /**
     * @param \SprykerShop\Yves\ProductOptionWidget\Dependency\Client\ProductOptionWidgetToProductOptionStorageClientInterface $productOptionStorageClient
     */
    public function __construct(ProductOptionWidgetToProductOptionStorageClientInterface $productOptionStorageClient)
    {
        $this->productOptionStorageClient = $productOptionStorageClient;
    }

    /**
     * @param \Generated\Shared\Transfer\ShoppingListTransfer $shoppingListTransfer
     * @param array $params
     *
     * @return \Generated\Shared\Transfer\ShoppingListTransfer
     */
    public function expandData(ShoppingListTransfer $shoppingListTransfer, array $params): ShoppingListTransfer
    {
        if (!isset($params[static::SHOPPING_LIST_UPDATE_FORM_NAME])) {
            return $shoppingListTransfer;
        }

        $requestFormData = $params[static::SHOPPING_LIST_UPDATE_FORM_NAME];
        $shoppingListTransfer = $this->setUpProductOptions($shoppingListTransfer, $requestFormData);

        return $shoppingListTransfer;
    }

    /**
     * @param \Symfony\Component\Form\ChoiceList\View\ChoiceView[] $productOptionGroups
     *
     * @return \ArrayObject
     */
    public function mapProductOptionGroups(array $productOptionGroups): ArrayObject
    {
        $mappedProductOptionGroups = [];
        foreach ($productOptionGroups as $productOptionGroup) {
            $mappedProductOptionGroups[] = $productOptionGroup->data;
        }

        return new ArrayObject($mappedProductOptionGroups);
    }

    /**
     * @param \Generated\Shared\Transfer\ShoppingListTransfer $shoppingListTransfer
     * @param array $requestFormData
     *
     * @return \Generated\Shared\Transfer\ShoppingListTransfer
     */
    protected function setUpProductOptions(ShoppingListTransfer $shoppingListTransfer, array $requestFormData): ShoppingListTransfer
    {
        $shoppingListItems = [];

        foreach ($shoppingListTransfer->getItems() as $itemKey => $shoppingListItemTransfer) {
            if (!$requestFormData[ShoppingListTransfer::ITEMS] || !$requestFormData[ShoppingListTransfer::ITEMS][$itemKey]) {
                continue;
            }
            $idsProductOptionValue = $this->getIdsProductOptionValue($requestFormData, $itemKey);
            $shoppingListItems[] = $this->setUpProductOptionsPerShoppingListItemTransfer($shoppingListItemTransfer, $idsProductOptionValue);
        }

        return $shoppingListTransfer->setItems(new ArrayObject($shoppingListItems));
    }

    /**
     * @param array $requestFormData
     * @param string $itemKey
     *
     * @return int[]
     */
    protected function getIdsProductOptionValue(array $requestFormData, string $itemKey): array
    {
        return array_filter($requestFormData[ShoppingListTransfer::ITEMS][$itemKey][static::PRODUCT_OPTIONS_FIELD_NAME]);
    }

    /**
     * @param \Generated\Shared\Transfer\ShoppingListItemTransfer $shoppingListItemTransfer
     * @param int[] $idsProductOptionValue
     *
     * @return \Generated\Shared\Transfer\ShoppingListItemTransfer
     */
    protected function setUpProductOptionsPerShoppingListItemTransfer(ShoppingListItemTransfer $shoppingListItemTransfer, array $idsProductOptionValue): ShoppingListItemTransfer
    {
        $productOptionTransfers = $this->createProductOptionTransfers($idsProductOptionValue);
        $shoppingListItemTransfer->setProductOptions($productOptionTransfers);

        return $shoppingListItemTransfer;
    }

    /**
     * @param int[] $idsProductOptionValue
     *
     * @return \ArrayObject|\Generated\Shared\Transfer\ProductOptionTransfer[]
     */
    protected function createProductOptionTransfers(array $idsProductOptionValue): ArrayObject
    {
        $productOptionTransfers = [];

        foreach ($idsProductOptionValue as $idProductOptionValue) {
            $productOptionTransfers[] = $this->createProductOptionTransfer($idProductOptionValue);
        }

        return new ArrayObject($productOptionTransfers);
    }

    /**
     * @param int $idProductOptionValue
     *
     * @return \Generated\Shared\Transfer\ProductOptionTransfer
     */
    protected function createProductOptionTransfer(int $idProductOptionValue): ProductOptionTransfer
    {
        return (new ProductOptionTransfer())->setIdProductOptionValue($idProductOptionValue);
    }

    /**
     * @param \Generated\Shared\Transfer\ShoppingListItemTransfer $shoppingListItemTransfer
     *
     * @return \ArrayObject|\Generated\Shared\Transfer\ProductOptionGroupStorageTransfer[]|null
     */
    public function getProductOptionGroups(ShoppingListItemTransfer $shoppingListItemTransfer)
    {
        $storageProductOptionGroupCollectionTransfer = $this->getStorageProductOptionGroupCollectionTransfer($shoppingListItemTransfer);

        if (!$storageProductOptionGroupCollectionTransfer) {
            return new ArrayObject();
        }

        $storageProductOptionGroupCollectionTransfer = $this->hydrateStorageProductOptionGroupCollectionTransfer($storageProductOptionGroupCollectionTransfer, $shoppingListItemTransfer);

        return $storageProductOptionGroupCollectionTransfer->getProductOptionGroups();
    }

    /**
     * @param \Generated\Shared\Transfer\ShoppingListItemTransfer $shoppingListItemTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractOptionStorageTransfer|null
     */
    protected function getStorageProductOptionGroupCollectionTransfer(ShoppingListItemTransfer $shoppingListItemTransfer)
    {
        return $this->productOptionStorageClient
            ->getProductOptionsForCurrentStore($shoppingListItemTransfer->getIdProductAbstract());
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractOptionStorageTransfer $storageProductOptionGroupCollectionTransfer
     * @param \Generated\Shared\Transfer\ShoppingListItemTransfer $shoppingListItemTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractOptionStorageTransfer
     */
    protected function hydrateStorageProductOptionGroupCollectionTransfer(
        ProductAbstractOptionStorageTransfer $storageProductOptionGroupCollectionTransfer,
        ShoppingListItemTransfer $shoppingListItemTransfer
    ): ProductAbstractOptionStorageTransfer {
        $storageProductOptionGroupCollectionTransfer = $this->hydrateProductOptionValuesIsSelected($storageProductOptionGroupCollectionTransfer, $shoppingListItemTransfer);

        return $storageProductOptionGroupCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractOptionStorageTransfer $storageProductOptionGroupCollectionTransfer
     * @param \Generated\Shared\Transfer\ShoppingListItemTransfer $shoppingListItemTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractOptionStorageTransfer
     */
    protected function hydrateProductOptionValuesIsSelected(
        ProductAbstractOptionStorageTransfer $storageProductOptionGroupCollectionTransfer,
        ShoppingListItemTransfer $shoppingListItemTransfer
    ): ProductAbstractOptionStorageTransfer {
        $selectedProductOptionIds = $this->getSelectedProductOptionIds($shoppingListItemTransfer);

        foreach ($storageProductOptionGroupCollectionTransfer->getProductOptionGroups() as $productOptionGroup) {
            foreach ($productOptionGroup->getProductOptionValues() as $productOptionValue) {
                $this->hydrateProductOptionValueIsSelected($productOptionValue, $selectedProductOptionIds);
            }
        }

        return $storageProductOptionGroupCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ShoppingListItemTransfer $shoppingListItemTransfer
     *
     * @return int[]
     */
    protected function getSelectedProductOptionIds(ShoppingListItemTransfer $shoppingListItemTransfer): array
    {
        $selectedProductOptionIds = [];
        foreach ($shoppingListItemTransfer->getProductOptions() as $productOptionTransfer) {
            $selectedProductOptionIds[] = $productOptionTransfer->getIdProductOptionValue();
        }

        return $selectedProductOptionIds;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOptionValueStorageTransfer $productOptionValue
     * @param int[] $selectedProductOptionIds
     *
     * @return \Generated\Shared\Transfer\ProductOptionValueStorageTransfer
     */
    protected function hydrateProductOptionValueIsSelected(
        ProductOptionValueStorageTransfer $productOptionValue,
        array $selectedProductOptionIds
    ): ProductOptionValueStorageTransfer {
        if (in_array($productOptionValue->getIdProductOptionValue(), $selectedProductOptionIds)) {
            $productOptionValue->setIsSelected(true);
        }

        return $productOptionValue;
    }
}
