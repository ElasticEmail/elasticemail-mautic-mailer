<?php

namespace MauticPlugin\ElasticEmailMailerBundle\Mailer\Transport;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class ElasticEmailSmtpTransport extends EsmtpTransport
{
    private const DEFAULT_HOST = 'smtp.elasticemail.com';

    public function __construct(
        string $username,
        string $password,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct('smtp.elasticemail.com', 2525, false, $dispatcher, $logger);

        $this->setUsername($username);
        $this->setPassword($password);
    }
}
