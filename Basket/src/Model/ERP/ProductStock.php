<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-08
 * Time: 22:50
 */

declare(strict_types=1);

namespace App\Basket\Model\ERP;

final class ProductStock
{
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var int
     */
    private $version;

    public static function fromArray(array $data): self
    {
        return new self(
            ProductId::fromString($data['product_id'] ?? ''),
            $data['quantity'] ?? 0,
            $data['version'] ?? 0
        );
    }

    private function __construct(ProductId $productId, int $quantity, int $version)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->version = $version;
    }

    /**
     * @return ProductId
     */
    public function productId(): ProductId
    {
        return $this->productId;
    }

    /**
     * @return int
     */
    public function quantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function version(): int
    {
        return $this->version;
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId->toString(),
            'quantity' => $this->quantity,
            'version' => $this->version
        ];
    }

    public function equals($other): bool
    {
        if(!$other instanceof self) {
            return false;
        }

        return $this->toArray() === $other->toArray();
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }
}
