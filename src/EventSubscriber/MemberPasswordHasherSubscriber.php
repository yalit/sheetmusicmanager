<?php

namespace App\EventSubscriber;

use App\Entity\Security\Member;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class MemberPasswordHasherSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'hashPassword',
            BeforeEntityUpdatedEvent::class => 'hashPassword',
        ];
    }

    public function hashPassword(BeforeEntityPersistedEvent|BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!$entity instanceof Member || !$entity->getPlainPassword()) {
            return;
        }

        $entity->setPassword(
            $this->passwordHasher->hashPassword($entity, $entity->getPlainPassword())
        );
    }
}
