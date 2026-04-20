<?php

namespace App\Tests\Admin;

use App\Controller\Admin\DashboardController;
use App\Entity\Security\Member;
use App\Entity\Setlist\Setlist;
use App\Entity\Sheet\Person;
use App\Entity\Sheet\PersonType;
use App\Entity\Sheet\Sheet;
use App\Enum\Security\MemberRole;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestFormAsserts;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestIndexAsserts;

abstract class AbstractAdminTestCase extends AbstractCrudTestCase
{
    use CrudTestIndexAsserts;
    use CrudTestFormAsserts;

    protected function getDashboardFqcn(): string
    {
        return DashboardController::class;
    }

    protected function loginAs(MemberRole $role): void
    {
        $this->client->loginUser($this->getMember($role));
    }

    protected function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function getMember(MemberRole $role): Member
    {
        // Fixture members are loaded with @sheetmusic.test emails.
        $email  = "{$role->value}@sheetmusic.test";
        $member = $this->em()->getRepository(Member::class)->findOneBy(['email' => $email]);

        static::assertNotNull($member, "Fixture member '{$email}' not found");

        return $member;
    }

    protected function getPerson(string $name): Person
    {
        $person = $this->em()->getRepository(Person::class)->findOneBy(['name' => $name]);

        static::assertNotNull($person, "Fixture person '{$name}' not found");

        return $person;
    }

    protected function getPersonType(string $name): PersonType
    {
        $personType = $this->em()->getRepository(PersonType::class)->findOneBy(['name' => $name]);

        static::assertNotNull($personType, "Fixture person type '{$name}' not found");

        return $personType;
    }

    protected function getSheet(string $title): Sheet
    {
        $sheet = $this->em()->getRepository(Sheet::class)->findOneBy(['title' => $title]);

        static::assertNotNull($sheet, "Fixture sheet '{$title}' not found");

        return $sheet;
    }

    protected function getSetlist(MemberRole $owner): Setlist
    {
        $title   = $owner->name . ' Setlist';
        $setlist = $this->em()->getRepository(Setlist::class)->findOneBy(['title' => $title]);

        static::assertNotNull($setlist, "Fixture setlist '{$title}' not found");

        return $setlist;
    }
}
