<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-08
 * Time: 12:54
 */

declare(strict_types=1);

namespace App\Basket\Model\Exception;

use App\Basket\Model\Basket\BasketId;
use App\Basket\Model\ERP\ProductId;

final class ProductAddedTwice extends \RuntimeException
{
    public static function toBasket(BasketId $basketId, ProductId $productId): self
    {
        return new self(sprintf(
            'Product %s added twice to basket %s',
            $productId->toString(),
            $basketId->toString()
        ));
    }
}
