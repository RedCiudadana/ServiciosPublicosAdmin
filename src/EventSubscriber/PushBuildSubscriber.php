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
        $this->logger = $logger;
    }

    public function onKernelFinishRequest(FinishRequestEvent $event)
    {
        $notifications = $this->pushBuildHandler->getBuildNotifications();

        if (count($notifications) > 0) {
            $message = 'Build triggered by: ';

            foreach ($notifications as $title) {
                $message = $message . sprintf('\n %s %s', $message, $title);
            }

            $netlifyHook = $this->parameterBag->get('app_netlify_build_hook');

            if ($netlifyHook) {
                try {
                    $this->httpClient->request('POST', $netlifyHook);
                } catch (\Throwable $th) {
                    $this->looger->error(sprintf('Fail the push build notification to Netlify %s', $netlifyHook));
                }
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
