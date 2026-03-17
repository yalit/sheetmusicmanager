<?php

namespace App\DataFixtures;

use App\Entity\Setlist;
use App\Entity\SetListItem;
use App\Entity\Sheet;
use App\Enum\MemberRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;

class SetlistFixtures extends Fixture implements DependentFixtureInterface
{
    public const MEMBER_SETLIST_REF      = 'setlist-member';
    public const CONTRIBUTOR_SETLIST_REF = 'setlist-contributor';
    public const LIBRARIAN_SETLIST_REF   = 'setlist-librarian';
    public const ADMIN_SETLIST_REF       = 'setlist-admin';
    public const EMPTY_SETLIST_REF       = 'setlist-empty';
    public const MISSING_FILES_REF       = 'missing-files';

    public function getDependencies(): array
    {
        return [MemberFixtures::class, SheetFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $setlists = [
            [
                'title'      => 'Member Setlist',
                'date'       => new \DateTime('2025-03-15'),
                'owner'      => MemberRole::Member->value.'@sheetmusic.test',
                'ref'        => self::MEMBER_SETLIST_REF,
                'items'      => [
                    [SheetFixtures::SHEET_1_REF,  1, 'Processional'],
                    [SheetFixtures::SHEET_3_REF,  2, 'Offertory'],
                    [SheetFixtures::SHEET_7_REF,  3, 'Closing Hymn'],
                ],
            ],
            [
                'title'      => 'Contributor Setlist',
                'date'       => new \DateTime('2025-06-21'),
                'owner'      => MemberRole::Contributor->value.'@sheetmusic.test',
                'ref'        => self::CONTRIBUTOR_SETLIST_REF,
                'items'      => [
                    [SheetFixtures::SHEET_6_REF,  1, 'Overture'],
                    [SheetFixtures::SHEET_2_REF,  2, 'Serenade'],
                    [SheetFixtures::SHEET_4_REF,  3, 'Allegro'],
                    [SheetFixtures::SHEET_11_REF, 4, 'Finale'],
                ],
            ],
            [
                'title'      => 'Librarian Setlist',
                'date'       => new \DateTime('2025-09-14'),
                'owner'      => MemberRole::Librarian->value.'@sheetmusic.test',
                'ref'        => self::LIBRARIAN_SETLIST_REF,
                'items'      => [
                    [SheetFixtures::SHEET_8_REF,  1, 'Prélude'],
                    [SheetFixtures::SHEET_9_REF,  2, 'Intermezzo'],
                    [SheetFixtures::SHEET_12_REF, 3, 'Nocturne'],
                    [SheetFixtures::SHEET_13_REF, 4, 'Rêverie'],
                    [SheetFixtures::SHEET_14_REF, 5, 'Grand finale'],
                ],
            ],
            [
                'title'      => 'Empty Setlist',
                'date'       => new \DateTime('2025-01-01'),
                'owner'      => MemberRole::Member->value.'@sheetmusic.test',
                'ref'        => self::EMPTY_SETLIST_REF,
                'items'      => [],
            ],
            [
                'title'      => 'Missing files Setlist',
                'date'       => new \DateTime('2025-01-01'),
                'owner'      => MemberRole::Member->value.'@sheetmusic.test',
                'ref'        => self::MISSING_FILES_REF,
                'items'      => [
                    [SheetFixtures::MISSING_FILE_REF,  1, 'Entrée'],
                ],
            ],
            [
                'title'      => 'Admin Setlist',
                'date'       => new \DateTime('2025-12-24'),
                'owner'      => MemberRole::Admin->value.'@sheetmusic.test',
                'ref'        => self::ADMIN_SETLIST_REF,
                'items'      => [
                    [SheetFixtures::SHEET_7_REF,  1, 'Entrée'],
                    [SheetFixtures::SHEET_5_REF,  2, 'Requiem'],
                    [SheetFixtures::SHEET_10_REF, 3, 'Cantique'],
                    [SheetFixtures::SHEET_3_REF,  4, 'Sortie'],
                ],
            ],
        ];

        foreach ($setlists as $data) {
            $setlist = (new Setlist())
                ->setTitle($data['title'])
                ->setDate($data['date'])
            ;
            $manager->persist($setlist);
            $manager->flush();

            $setlist->setCreatedBy($data['owner']);
            $setlist->setUpdatedBy($data['owner']);
            $manager->persist($setlist);
            $manager->flush();

            foreach ($data['items'] as [$sheetRef, $position, $name]) {
                /** @var Sheet $sheet */
                $sheet = $this->getReference($sheetRef, Sheet::class);
                $item = (new SetListItem())
                    ->setSetlist($setlist)
                    ->setSheet($sheet)
                    ->setPosition($position)
                    ->setName($name)
                ;
                $manager->persist($item);
            }
            $manager->flush();

            $this->addReference($data['ref'], $setlist);
        }

        $manager->clear();
    }
}
