<?php

namespace App\DataFixtures;

use App\Entity\Sheet\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonFixtures extends Fixture
{
    public const BACH_REF       = 'person-bach';
    public const MOZART_REF     = 'person-mozart';
    public const BRAHMS_REF     = 'person-brahms';
    public const HANDEL_REF     = 'person-handel';
    public const VIVALDI_REF    = 'person-vivaldi';
    public const SCHUBERT_REF   = 'person-schubert';
    public const BEETHOVEN_REF  = 'person-beethoven';
    public const CHOPIN_REF     = 'person-chopin';
    public const LISZT_REF      = 'person-liszt';
    public const DEBUSSY_REF    = 'person-debussy';
    public const RAVEL_REF      = 'person-ravel';
    public const FAURE_REF      = 'person-faure';
    public const SATIE_REF      = 'person-satie';
    public const POULENC_REF    = 'person-poulenc';

    public const NAMES = [
        ['Johann Sebastian Bach',     self::BACH_REF],
        ['Wolfgang Amadeus Mozart',   self::MOZART_REF],
        ['Johannes Brahms',           self::BRAHMS_REF],
        ['George Frideric Handel',    self::HANDEL_REF],
        ['Antonio Vivaldi',           self::VIVALDI_REF],
        ['Franz Schubert',            self::SCHUBERT_REF],
        ['Ludwig van Beethoven',      self::BEETHOVEN_REF],
        ['Frédéric Chopin',           self::CHOPIN_REF],
        ['Franz Liszt',               self::LISZT_REF],
        ['Claude Debussy',            self::DEBUSSY_REF],
        ['Maurice Ravel',             self::RAVEL_REF],
        ['Gabriel Fauré',             self::FAURE_REF],
        ['Erik Satie',                self::SATIE_REF],
        ['Francis Poulenc',           self::POULENC_REF],
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
