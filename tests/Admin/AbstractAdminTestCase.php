<?php

namespace App\Tests\Admin;

use App\Controller\Admin\DashboardController;
use App\Entity\Member;
use App\Entity\Person;
use App\Entity\PersonType;
use App\Entity\Setlist;
use App\Entity\Sheet;
use App\Enum\MemberRole;
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
        $member = (new Member())
            ->setEmail("test.{$role->value}@test.com")
            ->setName($role->name)
            ->setPassword('test')
            ->setRole($role);

        $this->client->loginUser($member);
    }

    protected function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function createSheet(): Sheet
    {
        $sheet = (new Sheet())->setTitle('Test Sheet');
        $this->em()->persist($sheet);
        $this->em()->flush();
        return $sheet;
    }

    protected function createSetlistOwnedBy(string $ownerEmail): Setlist
    {
        $setlist = (new Setlist())->setTitle('Test Setlist');
        $this->em()->persist($setlist);
        $this->em()->flush();

        // Set createdBy directly — Gedmo would override if set before flush
        $this->em()->getConnection()->executeStatement(
            'UPDATE setlist SET created_by = ? WHERE id = ?',
            [$ownerEmail, $setlist->getId()]
        );
        $this->em()->clear();

        return $this->em()->find(Setlist::class, $setlist->getId());
    }

    protected function createPerson(): Person
    {
        $person = (new Person())->setName('Test Person');
        $this->em()->persist($person);
        $this->em()->flush();
        return $person;
    }

    protected function createPersonType(): PersonType
    {
        $personType = (new PersonType())->setName('Test Type');
        $this->em()->persist($personType);
        $this->em()->flush();
        return $personType;
    }

    protected function createMember(): Member
    {
        $member = (new Member())
            ->setEmail('existing.member@test.com')
            ->setName('Existing Member')
            ->setPassword('$2y$10$testpassword')
            ->setRole(MemberRole::Member);
        $this->em()->persist($member);
        $this->em()->flush();
        return $member;
    }

    protected function tearDown(): void
    {
        $conn = $this->em()->getConnection();
        $conn->executeStatement('PRAGMA foreign_keys = OFF');
        $conn->executeStatement('DELETE FROM set_list_item');
        $conn->executeStatement('DELETE FROM setlist');
        $conn->executeStatement('DELETE FROM credited_person');
        $conn->executeStatement('DELETE FROM sheet');
        $conn->executeStatement('DELETE FROM person');
        $conn->executeStatement('DELETE FROM person_type');
        $conn->executeStatement("DELETE FROM member WHERE email LIKE 'test.%' OR email = 'existing.member@test.com'");
        $conn->executeStatement('PRAGMA foreign_keys = ON');
        parent::tearDown();
    }
}
