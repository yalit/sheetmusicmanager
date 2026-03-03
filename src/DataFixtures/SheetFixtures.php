<?php

namespace App\DataFixtures;

use App\Entity\Sheet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SheetFixtures extends Fixture
{
    public const SHEET_1_REF = 'sheet-1';
    public const SHEET_2_REF = 'sheet-2';

    public const SHEETS = [
        ['Toccata and Fugue in D Minor', ['BWV565'], ['organ', 'baroque'], self::SHEET_1_REF],
        ['Eine Kleine Nachtmusik',       ['K525'],   ['orchestra', 'classical'], self::SHEET_2_REF],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::SHEETS as [$title, $refs, $tags, $ref]) {
            $sheet = (new Sheet())
                ->setTitle($title)
                ->setRefs($refs)
                ->setTags($tags)
                ->setFiles(['test.pdf'])
            ;

            $manager->persist($sheet);
            $this->addReference($ref, $sheet);
        }

        $manager->flush();
    }
}
