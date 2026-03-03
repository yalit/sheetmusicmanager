<?php

namespace App\DataFixtures;

use App\Entity\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonFixtures extends Fixture
{
    public const BACH_REF   = 'person-bach';
    public const MOZART_REF = 'person-mozart';
    public const BRAHMS_REF = 'person-brahms';

    public const NAMES = [
            ['Johann Sebastian Bach',     self::BACH_REF],
            ['Wolfgang Amadeus Mozart',   self::MOZART_REF],
            ['Johannes Brahms',           self::BRAHMS_REF],
        ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::NAMES as [$name, $ref]) {
            $person = (new Person())->setName($name);
            $manager->persist($person);
            $this->addReference($ref, $person);
        }

        $manager->flush();
    }
}
