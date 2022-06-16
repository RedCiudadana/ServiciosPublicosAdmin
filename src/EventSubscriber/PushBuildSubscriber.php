<?php

namespace App\EventSubscriber;

use App\Handler\PushBuildHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PushBuildSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    private $pushBuildHandler;

    /**
     * {@inheritdoc}
     */
    private $parameterBag;

    /**
     * {@inheritdoc}
     */
    private $httpClient;

    /**
     * {@inheritdoc}
     */
    private $logger;

    public function __construct(PushBuildHandler $pushBuildHandler, ParameterBagInterface $parameterBag, HttpClientInterface $httpClient, LoggerInterface $logger) {
        $this->pushBuildHandler = $pushBuildHandler;
        $this->parameterBag = $parameterBag;
        $this->httpClient = $httpClient;
    }

    public function onKernelFinishRequest(FinishRequestEvent $event)
    {
        $notifications = $this->pushBuildHandler->getBuildNotifications();

        if (count($notifications) < 1) {
            $message = 'Build trigger by the followed: ';

            foreach ($notifications as $title) {
                $message += sprintf('%s %s', $message, $title);
            }

            $netlifyHook = $this->parameterBag->get('app_netlify_build_hook');

            if ($netlifyHook) {
                $this->httpClient->request('POST', $netlifyHook);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.finish_request' => 'onKernelFinishRequest',
        ];
    }
}
