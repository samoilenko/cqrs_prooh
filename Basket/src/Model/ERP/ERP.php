<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-08
 * Time: 22:40
 */

declare(strict_types=1);

namespace App\Basket\Model\ERP;

use App\Basket\Model\Exception\UnknownProduct;

interface ERP
{
    /**
     * Get stock information for given product
     *
     * If stock information cannot be fetched from the ERP system
     * this method returns null.
     *
     * If product is not known by the ERP system this method must throw an UnknownProduct exception
     *
     * @param ProductId $productId
     * @return ProductStock|null
     * @throws UnknownProduct
     */
    public function getProductStock(ProductId $productId): ?ProductStock;
}
