<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-07
 * Time: 22:35
 */
declare(strict_types=1);

namespace App\Basket\Model\Event;

use Prooph\EventSourcing\AggregateChanged;
use App\Basket\Model\Basket\BasketId;
use App\Basket\Model\Basket\ShoppingSession;

final class ShoppingSessionStarted extends AggregateChanged
{
    public function basketId(): BasketId
    {
        //Note: Internally, we work with scalar types, but the getter returns the value object
        return BasketId::fromString($this->aggregateId());
    }

    public function shoppingSession(): ShoppingSession
    {
        //Same here, return domain specific value object
        return ShoppingSession::fromString($this->payload['shopping_session']);
    }
}
