<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-08
 * Time: 00:00
 */

declare(strict_types=1);

namespace App\Basket\Model\Event;

use App\Basket\Model\Basket\BasketId;
use App\Basket\Model\ERP\ProductId;
use Prooph\EventSourcing\AggregateChanged;

final class ProductAddedToBasket extends AggregateChanged
{
    public function basketId(): BasketId
    {
        return BasketId::fromString($this->aggregateId());
    }

    public function productId(): ProductId
    {
        return ProductId::fromString($this->payload()['product_id']);
    }

    public function stockQuantity(): ?int
    {
        return $this->payload()['stock_quantity'];
    }

    public function stockVersion(): ?int
    {
        return $this->payload()['stock_version'];
    }

    public function quantity(): int
    {
        return $this->payload()['quantity'];
    }
}
