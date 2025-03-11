<?php

namespace MauticPlugin\ElasticEmailMailerBundle\EventSubscriber;

use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\TransportWebhookEvent;
use Mautic\EmailBundle\Model\TransportCallback;
use Mautic\LeadBundle\Entity\DoNotContact;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class CallbackSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TransportCallback
     */
    private $transportCallback;

    public function __construct(LoggerInterface $logger, TransportCallback $transportCallback)
    {
        $this->logger = $logger;
        $this->transportCallback = $transportCallback;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::ON_TRANSPORT_WEBHOOK => 'processCallbackRequest',
        ];
    }

    public function processCallbackRequest(TransportWebhookEvent $event): void
    {
        try {
            $request = $event->getRequest();

            // Check whether this callback is coming from Elastic Email
            $provider = $request->get('provider');
            if (null === $provider || 'elasticemail' !== strtolower($provider)) {
                return;
            }

            $this->logger->info('ElasticEmail callback received');

            // First try to get data from query parameters since Elastic Email uses GET
            $payload = $request->query->all();

            // Remove provider from payload to avoid confusing it with actual data
            if (isset($payload['provider'])) {
                unset($payload['provider']);
            }

            // If empty, try to get POST data or JSON body (for completeness)
            if (empty($payload)) {
                $payload = $request->request->all();
                
                if (empty($payload)) {
                    $content = $request->getContent();
                    if (!empty($content)) {
                        $decodedPayload = json_decode($content, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPayload)) {
                            $payload = $decodedPayload;
                        }
                    }
                }
            }

            $this->logger->info('ElasticEmail callback payload', ['payload' => $payload]);

            if (empty($payload)) {
                $this->logger->warning('ElasticEmail callback: Empty payload received');
                return;
            }
            
            $status = $payload['status'] ?? '';
            $category = $payload['category'] ?? '';
            $email = $payload['to'] ?? '';
            $messageid = $payload['messageid'] ?? '';
            
            $this->logger->info('ElasticEmail processing', [
                'status' => $status, 
                'category' => $category,
                'email' => $email,
                'messageid' => $messageid
            ]);
            
            // Process based on status and category
            $this->processElasticEmailWebhook($messageid, $email, $status, $category, $payload);
            
            // Always return a 200 OK response after processing
            $event->setResponse(new Response('OK', Response::HTTP_OK));
        } catch (\Exception $e) {
            $this->logger->error('ElasticEmail callback exception: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return;
        }
    }
    
    /**
     * Process the webhook based on status and category
     */
    private function processElasticEmailWebhook(string $messageid, string $email, string $status, string $category, array $payload): void
    {
        // First try to process by hash ID if available
        if (!empty($messageid)) {
            $this->processElasticEmailWebhookByHash($messageid, $status, $category, $payload);
            return;
        }
        
        // If no hash, try by email address
        if (!empty($email)) {
            $this->processElasticEmailWebhookByAddress($email, $status, $category, $payload);
        }
    }
    
    /**
     * Process the webhook using the message ID (hash)
     */
    private function processElasticEmailWebhookByHash(string $messageid, string $status, string $category, array $payload): void
    {
        // Handle bounces and errors
        if ($this->isBounce($status, $category)) {
            $reason = $this->getBounceReason($status, $category, $payload);
            $this->transportCallback->addFailureByHashId($messageid, $reason, DoNotContact::BOUNCED);
            $this->logger->info('ElasticEmail: Processed as bounce', [
                'messageid' => $messageid,
                'reason' => $reason
            ]);
            return;
        }
        
        // Handle spam complaints
        if ($this->isComplaint($status, $category)) {
            $reason = $payload['reason'] ?? 'Spam complaint via Elastic Email';
            $this->transportCallback->addFailureByHashId($messageid, $reason, DoNotContact::MANUAL);
            $this->logger->info('ElasticEmail: Processed as complaint', [
                'messageid' => $messageid,
                'reason' => $reason
            ]);
            return;
        }
        
        // Handle unsubscribes
        if ($this->isUnsubscribe($status, $category)) {
            $reason = $payload['reason'] ?? 'Unsubscribed via Elastic Email';
            $this->transportCallback->addFailureByHashId($messageid, $reason, DoNotContact::UNSUBSCRIBED);
            $this->logger->info('ElasticEmail: Processed as unsubscribe', [
                'messageid' => $messageid,
                'reason' => $reason
            ]);
            return;
        }
        
        // Log successful events but don't add to DNC
        if ($status === 'Sent' || $status === 'Opened' || $status === 'Clicked') {
            $this->logger->info('ElasticEmail: Processed as success event', [
                'messageid' => $messageid,
                'status' => $status
            ]);
        }
    }
    
    /**
     * Process the webhook using the email address
     */
    private function processElasticEmailWebhookByAddress(string $email, string $status, string $category, array $payload): void
    {
        // Handle bounces and errors
        if ($this->isBounce($status, $category)) {
            $reason = $this->getBounceReason($status, $category, $payload);
            $this->transportCallback->addFailureByAddress($email, $reason, DoNotContact::BOUNCED);
            $this->logger->info('ElasticEmail: Processed as bounce by address', [
                'email' => $email,
                'reason' => $reason
            ]);
            return;
        }
        
        // Handle spam complaints
        if ($this->isComplaint($status, $category)) {
            $reason = $payload['reason'] ?? 'Spam complaint via Elastic Email';
            $this->transportCallback->addFailureByAddress($email, $reason, DoNotContact::MANUAL);
            $this->logger->info('ElasticEmail: Processed as complaint by address', [
                'email' => $email,
                'reason' => $reason
            ]);
            return;
        }
        
        // Handle unsubscribes
        if ($this->isUnsubscribe($status, $category)) {
            $reason = $payload['reason'] ?? 'Unsubscribed via Elastic Email';
            $this->transportCallback->addFailureByAddress($email, $reason, DoNotContact::UNSUBSCRIBED);
            $this->logger->info('ElasticEmail: Processed as unsubscribe by address', [
                'email' => $email,
                'reason' => $reason
            ]);
            return;
        }
    }
    
    /**
     * Determine if the event is a bounce
     */
    private function isBounce(string $status, string $category): bool
    {
        if ($status === 'Error' || $status === 'Bounced') {
            return true;
        }
        
        // Check bounce-related categories
        $bounceCategories = [
            'NotDelivered',
            'NoMailbox',
            'GreyListed',
            'Throttled',
            'Timeout',
            'ConnectionProblem',
            'SPFProblem',
            'AccountProblem',
            'DNSProblem',
            'WhitelistingProblem',
            'CodeError',
            'ManualCancel',
            'ConnectionTerminated',
            'ContentFilter'
        ];
        
        return in_array($category, $bounceCategories);
    }
    
    /**
     * Determine if the event is a spam complaint
     */
    private function isComplaint(string $status, string $category): bool
    {
        return $status === 'AbuseReport' || $category === 'Spam' || $category === 'BlackListed';
    }
    
    /**
     * Determine if the event is an unsubscribe
     */
    private function isUnsubscribe(string $status, string $category): bool
    {
        return $status === 'Unsubscribed' || $category === 'Unsubscribed';
    }
    
    /**
     * Get a descriptive bounce reason
     */
    private function getBounceReason(string $status, string $category, array $payload): string
    {
        if (!empty($payload['reason'])) {
            return $payload['reason'];
        }
        
        // If category provides more detail, use that
        if (!empty($category)) {
            return $this->getBounceReasonFromCategory($category);
        }
        
        // Default reason based on status
        return 'Email delivery failed: ' . $status;
    }
    
    /**
     * Gets a human-readable bounce reason from category
     */
    private function getBounceReasonFromCategory(string $category): string
    {
        switch ($category) {
            case 'Spam':
                return 'Message was marked as spam';
            case 'BlackListed':
                return 'Sender is blacklisted';
            case 'NoMailbox':
                return 'Mailbox does not exist';
            case 'GreyListed':
                return 'Greylisted by recipient server';
            case 'Throttled':
                return 'Message throttled by recipient server';
            case 'Timeout':
                return 'Connection timed out';
            case 'ConnectionProblem':
                return 'Connection problem';
            case 'SPFProblem':
                return 'SPF validation failed';
            case 'AccountProblem':
                return 'Account problem';
            case 'DNSProblem':
                return 'DNS lookup failed';
            case 'WhitelistingProblem':
                return 'Whitelisting problem';
            case 'CodeError':
                return 'Code error';
            case 'ManualCancel':
                return 'Manually cancelled';
            case 'ConnectionTerminated':
                return 'Connection terminated';
            case 'ContentFilter':
                return 'Content filtered by recipient server';
            case 'NotDelivered':
                return 'Message could not be delivered';
            case 'Unknown':
                return 'Unknown delivery error';
            default:
                return 'Delivery error: ' . $category;
        }
    }
} 