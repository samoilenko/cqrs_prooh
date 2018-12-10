<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-08
 * Time: 22:53
 */

declare(strict_types=1);

namespace App\Basket\Model\Exception;

use App\Basket\Model\ERP\ProductId;

final class UnknownProduct extends \InvalidArgumentException
{
    public static function withProductId(ProductId $productId): self
    {
        return new self(sprintf(
            'Product with %s is unknown.',
            $productId->toString()
        ));
    }
}
