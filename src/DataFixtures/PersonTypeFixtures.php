<?php

namespace App\DataFixtures;

use App\Entity\Sheet\PersonType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonTypeFixtures extends Fixture
{
    public const COMPOSER_REF  = 'person-type-composer';
    public const ARRANGER_REF  = 'person-type-arranger';
    public const CONDUCTOR_REF = 'person-type-conductor';

    public const NAMES = [
        ['Composer',  self::COMPOSER_REF],
        ['Arranger',  self::ARRANGER_REF],
        ['Conductor', self::CONDUCTOR_REF],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::NAMES as [$name, $ref]) {
            $type = (new PersonType())->setName($name);
            $manager->persist($type);
            $this->addReference($ref, $type);
        }

        $manager->flush();
    }
}
