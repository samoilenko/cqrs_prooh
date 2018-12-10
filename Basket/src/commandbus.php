<?php
require (__DIR__."/../../vendor/prooph/service-bus/examples/quick-start.php");

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Example\Command\EchoText;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;

$commandBus = new CommandBus();

$router = new CommandRouter();

//Register a callback as CommandHandler for the EchoText command
$router->route('Prooph\ServiceBus\Example\Command\EchoText')
    ->to(function (EchoText $aCommand): void {
        echo $aCommand->getText();
    });

//Expand command bus with the router plugin
$router->attachToMessageBus($commandBus);

//We create a new Command
$echoText = new EchoText('It works');

//... and dispatch it
$commandBus->dispatch($echoText);

//Output should be: It works
