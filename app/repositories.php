<?php

declare(strict_types=1);

use App\Domain\Directory\EventQueueRepository;
use App\Infrastructure\Persistence\Directory\PdoEventQueueRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        EventQueueRepository::class => \DI\autowire(PdoEventQueueRepository::class),
    ]);
};
