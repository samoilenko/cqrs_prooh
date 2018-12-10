<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-07
 * Time: 23:18
 */

declare(strict_types=1);

namespace App\Basket\Model\Basket;

use Ramsey\Uuid\Uuid;

final class BasketId
{
    private $basketId;

    public static function fromString(string $basketId): self
    {
        return new self($basketId);
    }

    private function __construct(string $basketId)
    {
        if(!Uuid::isValid($basketId)) {
            throw new \InvalidArgumentException("Given basket id is not a valid UUID. Got " . $basketId);
        }

        $this->basketId = $basketId;
    }

    public function toString(): string
    {
        return $this->basketId;
    }

    public function equals($other): bool
    {
        if(!$other instanceof self) {
            return false;
        }

        return $this->basketId === $other->basketId;
    }

    public function __toString(): string
    {
        return $this->basketId;
    }
}
