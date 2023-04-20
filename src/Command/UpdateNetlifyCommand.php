<?php

namespace App\Command;

use App\Entity\User;
use App\Handler\PushBuildHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateNetlifyCommand extends Command
{
    protected static $defaultName = 'app:update-netlify';
    protected static $defaultDescription = 'Trigger netlify build hook';

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

    public function __construct(PushBuildHandler $pushBuildHandler, ParameterBagInterface $parameterBag, HttpClientInterface $httpClient, LoggerInterface $logger, KernelInterface $kernel)
    {
        $this->pushBuildHandler = $pushBuildHandler;
        $this->parameterBag = $parameterBag;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->kernel = $kernel;

        parent::__construct();
    }

    protected function configure(): void
    {
        // $this
        //     ->addArgument('email', InputArgument::REQUIRED, 'Email required to login');
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fs = new Filesystem();

        if ($fs->exists($this->kernel->getCacheDir() . 'need_udpate.txt')) {
            $netlifyHook = $this->parameterBag->get('app_netlify_build_hook');

            if ($netlifyHook) {
                try {
                    $this->httpClient->request('POST', $netlifyHook);
                } catch (\Throwable $th) {
                    $this->logger->error(sprintf('Fail the push build notification to Netlify %s. %s', $netlifyHook, $th->getMessage()));
                }
            }

            $fs->remove($this->kernel->getCacheDir() . 'need_udpate.txt');

            $io->success('Netlify build triggered!');

            return Command::SUCCESS;
        }

        $io->success('Nothing to update');

        return Command::SUCCESS;
    }
}
