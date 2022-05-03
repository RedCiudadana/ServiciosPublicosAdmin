<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateSuperAdminUserCommand extends Command
{
    protected static $defaultName = 'app:create-super-admin-user';
    protected static $defaultDescription = 'Create a user with ROLE_SUPER_ADMIN role';

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager) {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email required to login');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        if ($email) {
            $io->note(sprintf('Creating user: %s', $email));
        }

        $helper = $this->getHelper('question');

        $question = new Question('Please set your password:');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $password = $helper->ask($input, $output, $question);

        $user = new User();
        $user->setEmail($email);
        $user->addRole('ROLE_SUPER_ADMIN');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('User %s created succesfully', $email));

        return Command::SUCCESS;
    }
}
