<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-07
 * Time: 23:16
 */

declare(strict_types=1);

namespace App\Basket\Model\Basket;

final class ShoppingSession
{
    private $shoppingSession;

    public static function fromString(string $shoppingSession): self
    {
        return new self($shoppingSession);
    }

    private function __construct(string $shoppingSession)
    {
        if($shoppingSession === '') {
            throw new \InvalidArgumentException("Shopping session must not be an empty string");
        }

        $this->shoppingSession = $shoppingSession;
    }

    public function toString(): string
    {
        return $this->shoppingSession;
    }

    public function equals($other): bool
    {
        if(!$other instanceof self) {
            return false;
        }

        return $this->shoppingSession === $other->shoppingSession;
    }

    public function __toString(): string
    {
        return $this->shoppingSession;
    }
}
