<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-07
 * Time: 23:45
 */
declare(strict_types=1);

namespace App\BasketTest\Model;

use App\Basket\Model\Basket;
use App\Basket\Model\ERP\ERP;
use App\Basket\Model\ERP\ProductId;
use App\Basket\Model\ERP\ProductStock;
use App\Basket\Model\Event\ProductAddedToBasket;
use App\Basket\Model\Event\ShoppingSessionStarted;
use App\Basket\Model\Basket\BasketId;
use App\Basket\Model\Basket\ShoppingSession;
use App\Basket\Model\Exception\UnknownProduct;
use App\BasketTest\TestCase;
use Prooph\EventSourcing\AggregateChanged;
use Ramsey\Uuid\Uuid;

class BasketTest extends TestCase
{
    /**
     * @var ShoppingSession
     */
    private $shoppingSession;

    /**
     * @var BasketId
     */
    private $basketId;

    /**
     * @var ProductId
     */
    private $product1;

    protected function setUp()
    {
        $this->shoppingSession = ShoppingSession::fromString('123');
        $this->basketId = BasketId::fromString(Uuid::uuid4()->toString());
        $this->product1 = ProductId::fromString('A1');
    }

    /**
     * @test
     */
    public function it_starts_a_shopping_session()
    {
        $basket = Basket::startShoppingSession($this->shoppingSession, $this->basketId);

        /** @var AggregateChanged[] $events */
        $events = $this->popRecordedEvents($basket);

        $this->assertCount(1, $events);

        /** @var ShoppingSessionStarted $event */
        $event = $events[0];

        $this->assertSame(ShoppingSessionStarted::class, $event->messageName());
        $this->assertTrue($this->basketId->equals($event->basketId()));
        $this->assertTrue($this->shoppingSession->equals($event->shoppingSession()));
    }

    /**
     * @test
     */
    public function it_adds_a_product_if_stock_quantity_is_greater_than_zero()
    {
        $basket = $this->reconstituteBasketFromHistory(
            $this->shoppingSessionStarted()
        );

        $basket->addProduct($this->product1, $this->product1ERP());

        /** @var AggregateChanged[] $events */
        $events = $this->popRecordedEvents($basket);

        $this->assertCount(1, $events);

        /** @var ProductAddedToBasket $event */
        $event = $events[0];

        $this->assertSame(ProductAddedToBasket::class, $event->messageName());
        $this->assertTrue($this->basketId->equals($event->basketId()));
        $this->assertTrue($this->product1->equals($event->productId()));
        $this->assertSame(5, $event->payload()['stock_quantity']);
        $this->assertSame(1, $event->payload()['stock_version']);
        $this->assertSame(1, $event->payload()['quantity']);
    }

    /**
     * @test
     * @expectedException \App\Basket\Model\Exception\ProductAddedTwice
     */
    public function it_throws_an_error_on_product_duplicate() {
        $basket = $this->reconstituteBasketFromHistory(
            $this->shoppingSessionStarted(),
            $this->product1Added()
        );
        $basket->addProduct($this->product1, $this->product1ERP());
    }

    /**
     * @test
     * @expectedException \App\Basket\Model\Exception\UnknownProduct
     */
    public function it_stops_operation_if_product_is_unknown()
    {
        $basket = $this->reconstituteBasketFromHistory(
            $this->shoppingSessionStarted()
        );

        $ERP = $this->prophesize(ERP::class);

        //This ERP mock knows no product
        $ERP->getProductStock($this->product1)->willThrow(UnknownProduct::withProductId($this->product1));

        $basket->addProduct($this->product1, $ERP->reveal());
    }

    /**
     * @test
     */
    public function it_adds_product_without_stock_info_if_ERP_is_unavailable()
    {
        $basket = $this->reconstituteBasketFromHistory(
            $this->shoppingSessionStarted()
        );

        $ERP = $this->prophesize(ERP::class);

        //This ERP is unavailable
        $ERP->getProductStock($this->product1)->willReturn(null);

        $basket->addProduct($this->product1, $ERP->reveal());

        /** @var AggregateChanged[] $events */
        $events = $this->popRecordedEvents($basket);

        $this->assertCount(1, $events);

        /** @var ProductAddedToBasket $event */
        $event = $events[0];

        $this->assertSame(ProductAddedToBasket::class, $event->messageName());
        $this->assertTrue($this->basketId->equals($event->basketId()));
        $this->assertTrue($this->product1->equals($event->productId()));
        $this->assertSame(1, $event->payload()['quantity']);
        //No stock info present
        $this->assertSame(null, $event->payload()['stock_quantity']);
        $this->assertSame(null, $event->payload()['stock_version']);
    }

    /**
     * @test
     * @expectedException \App\Basket\Model\Exception\ProductOutOfStock
     */
    public function it_does_not_add_product_if_product_is_out_of_stock()
    {
        $basket = $this->reconstituteBasketFromHistory(
            $this->shoppingSessionStarted()
        );

        //Set stock quantity to zero in the ERP mock
        $basket->addProduct($this->product1, $this->product1ERP(0));
    }

    /**
     * Test helper to create a ShoppingSessionStarted event
     *
     * If we need to change signature of the event later, we have a central place in the test case
     * where we can align the creation.
     *
     * @return ShoppingSessionStarted
     */
    private function shoppingSessionStarted(): ShoppingSessionStarted
    {
        return ShoppingSessionStarted::occur($this->basketId->toString(), [
            'shopping_session' => $this->shoppingSession->toString()
        ]);
    }

    /**
     * Test helper to create a ProductAddedToBasket event
     *
     * If we need to change signature of the event later, we have a central place in the test case
     * where we can align the creation.
     *
     * @return ProductAddedToBasket
     */
    private function product1Added(): ProductAddedToBasket
    {
        return ProductAddedToBasket::occur($this->basketId->toString(), [
            'product_id' => $this->product1->toString(),
            'stock_quantity' => 5,
            'stock_version' => 1,
            'quantity' => 1,
        ]);
    }

    /**
     * Helper method to reconstitute a Basket from history
     *
     * With this helper we get better type hinting in the test methods
     * because type hint for reconstituteAggregateFromHistory() is only AggregateRoot
     *
     * @param AggregateChanged[] ...$events
     * @return Basket
     */
    private function reconstituteBasketFromHistory(AggregateChanged ...$events): Basket
    {
        return $this->reconstituteAggregateFromHistory(
            Basket::class,
            $events
        );
    }

    private function product1ERP(int $stockQuantity = 5): ERP
    {
        //Create a Mock of the ERP interface
        $ERP = $this->prophesize(ERP::class);

        $ERP->getProductStock($this->product1)->willReturn(ProductStock::fromArray(
            [
                'product_id' => $this->product1->toString(),
                'quantity' => $stockQuantity,
                'version' => 1
            ]
        ));

        return $ERP->reveal();
    }
}
