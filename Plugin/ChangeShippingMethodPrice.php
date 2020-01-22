<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingPricePerProduct\Plugin;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierInterface;

/**
 * Class ChangeShippingMethodPrice
 */
class ChangeShippingMethodPrice
{
    /**
     * Set individual shipping price per product to each shipping rate
     *
     * @param AbstractCarrierInterface $subject
     * @param $result
     * @param RateRequest $request
     * @return mixed
     */
    public function afterCollectRates(
        AbstractCarrierInterface $subject,
        $result,
        RateRequest $request
    ) {
        if (!$result instanceof \Magento\Shipping\Model\Rate\Result) {
            return $result;
        }

        $priceSurcharge = 0;
        $items = $request->getAllItems();
        foreach ($items as $item) {
            $product = $item->getProduct();
            if (!$product) {
                continue;
            }
            $priceSurcharge += (float)$product->getData('base_individual_shipping_price');
        }

        foreach ($result->getAllRates() as $rate) {
            if (!$rate->getData('individual_price_applied')) {
                $individualPrice = $rate->getPrice() + $priceSurcharge;
                $rate->setPrice($individualPrice);
                $rate->setData('individual_price_applied', true);
            }
        }

        return $result;
    }
}
