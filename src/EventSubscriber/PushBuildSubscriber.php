<?php

namespace App\EventSubscriber;

use App\Handler\PushBuildHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelInterface;
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

    /**
     * {@inheritdoc}
     */
    private $kernel;

    public function __construct(PushBuildHandler $pushBuildHandler, ParameterBagInterface $parameterBag, HttpClientInterface $httpClient, LoggerInterface $logger, KernelInterface $kernel) {
        $this->pushBuildHandler = $pushBuildHandler;
        $this->parameterBag = $parameterBag;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->kernel = $kernel;
    }

    public function onKernelFinishRequest(FinishRequestEvent $event)
    {
        $notifications = $this->pushBuildHandler->getBuildNotifications();

        if (count($notifications) > 0) {

            $fs = new Filesystem();

            /**
             * Use command `app:update-netlify` to trigger build
             */
            $fs->dumpFile($this->kernel->getCacheDir() .'need_udpate.txt', '1');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.finish_request' => 'onKernelFinishRequest',
        ];
    }
}
