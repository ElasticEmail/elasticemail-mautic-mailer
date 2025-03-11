<?php

declare(strict_types=1);

use MauticPlugin\ElasticEmailMailerBundle\Mailer\Factory\ElasticEmailTransportFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->load('MauticPlugin\\ElasticEmailMailerBundle\\', '../')
        ->exclude('../{Config,Mailer/Transport/ElasticEmailApiTransport.php,Mailer/Transport/ElasticEmailSmtpTransport.php}');

    $services->get(ElasticEmailTransportFactory::class)->tag('mailer.transport_factory');
};
