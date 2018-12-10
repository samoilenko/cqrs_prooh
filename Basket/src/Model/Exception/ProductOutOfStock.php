<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-08
 * Time: 23:02
 */

declare(strict_types=1);

namespace App\Basket\Model\Exception;

use App\Basket\Model\ERP\ProductId;

final class ProductOutOfStock extends \RuntimeException
{
    public static function withProductId(ProductId $productId): self
    {
        return new self(sprintf(
            'Product with %s is out of stock.',
            $productId->toString()
        ));
    }
}
