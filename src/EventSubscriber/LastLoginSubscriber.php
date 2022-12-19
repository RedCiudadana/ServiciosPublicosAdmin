<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LastLoginSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function onLoginSuccess(LoginSuccessEvent $loginSuccessEvent)
    {
        $user = $loginSuccessEvent->getAuthenticatedToken()->getUser();
        
        if (!($user instanceof User)) {
            return;
        }

        $user->setLastLogin(new \DateTime());

        $this->em->persist($user);
        $this->em->flush($user);
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess'
        ];
    }
}
