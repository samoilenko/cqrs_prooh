<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-07
 * Time: 23:00
 */
declare(strict_types=1);

namespace App\Basket\Model;

use App\Basket\Model\ERP\ERP;
use App\Basket\Model\Event\ShoppingSessionStarted;
use App\Basket\Model\Exception\ProductOutOfStock;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;
use App\Basket\Model\Basket\ShoppingSession;
use App\Basket\Model\Basket\BasketId;
use App\Basket\Model\ERP\ProductId;
use App\Basket\Model\Exception\ProductAddedTwice;
use App\Basket\Model\Event\ProductAddedToBasket;

final class Basket extends AggregateRoot
{
    /**
     * @var BasketId
     */
    private $basketId;

    /**
     * @var ShoppingSession
     */
    private $shoppingSession;

    /**
     * @var ProductId[]
     */
    private $products = [];

    public static function startShoppingSession(
        ShoppingSession $shoppingSession,
        BasketId $basketId)
    {
        //Start new aggregate lifecycle by creating an "empty" instance
        $self = new self();

        //Record the very first domain event of the new aggregate
        //Note: we don't pass the value objects directly to the event but use their
        //primitive counterparts. This makes it much easier to work with the events later
        //and we don't need complex serializers when storing events.
        $self->recordThat(ShoppingSessionStarted::occur($basketId->toString(), [
            'shopping_session' => $shoppingSession->toString()
        ]));

        //Return the new aggregate
        return $self;
    }

    public function addProduct(ProductId $productId, ERP $ERP): void
    {
        if(array_key_exists($productId->toString(), $this->products)) {
            throw ProductAddedTwice::toBasket($this->basketId, $productId);
        }

        //If the ERP system does not know the product an exception will be thrown here
        //which will stop the operation. The aggregate can not deal with that situation
        //as this is one of these "this should never happen" situations
        //If we want an unbreakable domain model we would need to talk to the business
        //and work out a failover plan triggered by a UnknownProductAddedToBasket event.
        $productStock = $ERP->getProductStock($productId);

        if(!$productStock) {
            $this->recordThat(ProductAddedToBasket::occur($this->basketId->toString(), [
                'product_id' => $productId->toString(),
                //If we did not get a response, we add the product and check stock later again
                //the shopping session should not be blocked by a temporarily unavailable ERP system
                'stock_version' => null,
                'stock_quantity'=> null,
                'quantity' => 1,
            ]));
            return;
        }

        if($productStock->quantity() === 0) {
            throw ProductOutOfStock::withProductId($productId);
        }

        $this->recordThat(ProductAddedToBasket::occur($this->basketId->toString(), [
            'product_id' => $productId->toString(),
            'stock_version' => $productStock->version(),
            'stock_quantity' => $productStock->quantity(),
            'quantity' => 1,
        ]));
    }

    protected function aggregateId(): string
    {
        //Return string representation of the globally unique identifier of the aggregate
        return $this->basketId->toString();
    }

    /**
     * Apply given event
     */
    protected function apply(AggregateChanged $event): void
    {
        //A simple switch by event name is the fastest way,
        //but you're free to split things up here and have, for example, methods like
        //private function whenShoppingSessionStarted()
        //To delegate work to them and keep the apply method lean
        switch ($event->messageName()) {
            case ShoppingSessionStarted::class:
                /** @var $event ShoppingSessionStarted */

                $this->basketId = $event->basketId();
                $this->shoppingSession = $event->shoppingSession();
                break;
            case ProductAddedToBasket::class:
                /** @var $event ProductAddedToBasket */

                //Use ProductId as index to avoid adding a product twice
                $this->products[$event->productId()->toString()] = [
                    'stock_quantity' => $event->stockQuantity(),
                    'stock_version' => $event->stockVersion(),
                    'quantity' => $event->quantity()
                ];
                break;
        }
    }
}
