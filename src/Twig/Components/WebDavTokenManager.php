<?php

namespace App\Twig\Components;

use App\Entity\Security\Member;
use App\Service\WebDAV\WebDAVTokenHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class WebDavTokenManager
{
    use DefaultActionTrait;

    #[LiveProp]
    public Member $member;

    #[LiveProp(writable: true)]
    public int $ttlDays = 90;

    #[LiveProp(writable: false)]
    public ?string $plainToken = null;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WebDAVTokenHandler     $webDAVTokenHandler,
        private readonly UrlGeneratorInterface  $urlGenerator,
    ) {}

    public function getDavUrl(): string
    {
        return rtrim(
            $this->urlGenerator->generate('webdav', ['path' => ''], UrlGeneratorInterface::ABSOLUTE_URL),
            '/'
        );
    }

    #[LiveAction]
    public function generate(): void
    {
        $this->plainToken = $this->webDAVTokenHandler->new($this->member, max(1, $this->ttlDays));
    }

    #[LiveAction]
    public function renew(): void
    {
        $this->plainToken = $this->webDAVTokenHandler->renew($this->member, max(1, $this->ttlDays));
    }

    #[LiveAction]
    public function revoke(): void
    {
        $this->webDAVTokenHandler->revoke($this->member);
        $this->dismiss();
    }

    #[LiveAction]
    public function dismiss(): void
    {
        $this->plainToken = null;
    }
}
