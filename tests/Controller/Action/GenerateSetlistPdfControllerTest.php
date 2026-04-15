<?php

namespace App\Tests\Controller\Action;

use App\Entity\Security\Member;
use App\Entity\Setlist\Setlist;
use App\Enum\Security\MemberRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GenerateSetlistPdfControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    private function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function loginAs(MemberRole $role): void
    {
        $email  = "{$role->value}@sheetmusic.test";
        $member = $this->em()->getRepository(Member::class)->findOneBy(['email' => $email]);
        static::assertNotNull($member, "Fixture member '{$email}' not found");
        $this->client->loginUser($member);
    }

    private function getSetlist(MemberRole $owner): Setlist
    {
        $title   = $owner->name . ' Setlist';
        $setlist = $this->em()->getRepository(Setlist::class)->findOneBy(['title' => $title]);
        static::assertNotNull($setlist, "Fixture setlist '{$title}' not found");
        return $setlist;
    }

    private function actionUrl(Setlist $setlist): string
    {
        return static::getContainer()->get('router')->generate('admin_generate_setlist_pdf', ['id' => $setlist->getId()]);
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function testAnonymousRedirectsToLogin(): void
    {
        $setlist = $this->getSetlist(MemberRole::Member);

        $this->client->request('GET', $this->actionUrl($setlist));

        static::assertResponseRedirects('/login');
    }

    // -------------------------------------------------------------------------
    // Happy path (Gotenberg replaced by FakeGotenbergPdf in test env)
    // -------------------------------------------------------------------------

    public function testItReturnsAPdfAttachment(): void
    {
        $this->loginAs(MemberRole::Librarian);
        $setlist = $this->getSetlist(MemberRole::Member);

        $this->client->request('GET', $this->actionUrl($setlist));

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame(
            'Content-Disposition',
            sprintf('attachment; filename="setlist-%s"', $setlist->getId()),
        );
    }
}
