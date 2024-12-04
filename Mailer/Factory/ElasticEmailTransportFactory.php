<?php

namespace MauticPlugin\ElasticEmailMailerBundle\Mailer\Factory;

use MauticPlugin\ElasticEmailMailerBundle\Mailer\Transport\ElasticEmailApiTransport;
use MauticPlugin\ElasticEmailMailerBundle\Mailer\Transport\ElasticEmailSmtpTransport;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

class ElasticEmailTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();
        $user = $this->getUser($dsn);
        $host = 'default' === $dsn->getHost() ? 'api.elasticemail.com' : $dsn->getHost();

        if ('elasticemail+api' === $scheme || 'elasticemail' === $scheme) {
            return (new ElasticEmailApiTransport($user, $this->client, $this->dispatcher, $this->logger))
                ->setHost($host);
        }

        if ('elasticemail+smtp' === $scheme) {
            $password = $this->getPassword($dsn);

            return new ElasticEmailSmtpTransport($user, $password, $this->dispatcher, $this->logger);
        }

        throw new UnsupportedSchemeException($dsn, 'elasticemail', $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['elasticemail', 'elasticemail+api','elasticemail+smtp'];
    }
}
