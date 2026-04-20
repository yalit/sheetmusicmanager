<?php

namespace App\DataFixtures;

use App\Entity\Sheet\CreditedPerson;
use App\Entity\Sheet\Person;
use App\Entity\Sheet\PersonType;
use App\Entity\Sheet\Sheet;
use App\Entity\Sheet\ValueObject\StoredFile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SheetFixtures extends Fixture implements DependentFixtureInterface
{
    public const SHEET_1_REF  = 'sheet-1';
    public const SHEET_2_REF  = 'sheet-2';
    public const SHEET_3_REF  = 'sheet-3';
    public const SHEET_4_REF  = 'sheet-4';
    public const SHEET_5_REF  = 'sheet-5';
    public const SHEET_6_REF  = 'sheet-6';
    public const SHEET_7_REF  = 'sheet-7';
    public const SHEET_8_REF  = 'sheet-8';
    public const SHEET_9_REF  = 'sheet-9';
    public const SHEET_10_REF = 'sheet-10';
    public const SHEET_11_REF = 'sheet-11';
    public const SHEET_12_REF = 'sheet-12';
    public const SHEET_13_REF = 'sheet-13';
    public const SHEET_14_REF = 'sheet-14';
    public const MISSING_FILE_REF = 'missing_file';
    public const UNTAGGED_FILE_REF  = 'untagged_file';

    // Existing entries must stay at indexes 0 and 1 — tests depend on SHEETS[0][0] and SHEETS[1][0]
    public const SHEETS = [
        ['Toccata and Fugue in D Minor', ['BWV565'],  ['organ', 'baroque'],              self::SHEET_1_REF],
        ['Eine Kleine Nachtmusik',       ['K525'],    ['orchestra', 'classical'],        self::SHEET_2_REF],
        ['Jesu, Joy of Man\'s Desiring', ['BWV147'],  ['organ', 'baroque', 'sacred'],    self::SHEET_3_REF],
        ['Symphony No. 5 in C Minor',    ['Op67'],    ['orchestra', 'classical'],        self::SHEET_4_REF],
        ['Requiem in D Minor',           ['K626'],    ['choir', 'classical', 'sacred'],  self::SHEET_5_REF],
        ['The Four Seasons — Spring',    ['RV269'],   ['strings', 'baroque'],            self::SHEET_6_REF],
        ['Messiah — Hallelujah Chorus',  ['HWV56'],   ['choir', 'baroque', 'sacred'],    self::SHEET_7_REF],
        ['Clair de Lune',                ['L75'],     ['piano', 'impressionist'],        self::SHEET_8_REF],
        ['Gymnopédie No. 1',             [],          ['piano', 'contemporary'],         self::SHEET_9_REF],
        ['Cantique de Jean Racine',      ['Op11'],    ['choir', 'romantic', 'sacred'],   self::SHEET_10_REF],
        ['Pavane',                       ['Op50'],    ['orchestra', 'romantic'],         self::SHEET_11_REF],
        ['Nocturne in E flat Major',     ['Op9-2'],   ['piano', 'romantic'],             self::SHEET_12_REF],
        ['Liebestraum No. 3',            ['K543', 'J345'],          ['piano', 'romantic'],             self::SHEET_13_REF],
        ['Boléro',                       [],          ['orchestra', 'modern'],           self::SHEET_14_REF],
        ['No file on the drive',         [],          ['piano', 'unique_tag'],           self::MISSING_FILE_REF],
        ['Untagged',         [],          [],           self::UNTAGGED_FILE_REF],
    ];

    // [sheetRef, composerPersonRef, arrangerPersonRef|null, notes]
    private const CREDITS = [
        self::SHEET_1_REF  => [PersonFixtures::BACH_REF,       null,                       null],
        self::SHEET_2_REF  => [PersonFixtures::MOZART_REF,     null,                       null],
        self::SHEET_3_REF  => [PersonFixtures::BACH_REF,       null,                       null],
        self::SHEET_4_REF  => [PersonFixtures::BEETHOVEN_REF,  null,                       null],
        self::SHEET_5_REF  => [PersonFixtures::MOZART_REF,     null,                       'Unfinished at death, completed by Franz Xaver Süssmayr'],
        self::SHEET_6_REF  => [PersonFixtures::VIVALDI_REF,    null,                       null],
        self::SHEET_7_REF  => [PersonFixtures::HANDEL_REF,     null,                       null],
        self::SHEET_8_REF  => [PersonFixtures::DEBUSSY_REF,    null,                       null],
        self::SHEET_9_REF  => [PersonFixtures::SATIE_REF,      null,                       null],
        self::SHEET_10_REF => [PersonFixtures::FAURE_REF,      PersonFixtures::BRAHMS_REF, null],
        self::SHEET_11_REF => [PersonFixtures::FAURE_REF,      null,                       null],
        self::SHEET_12_REF => [PersonFixtures::CHOPIN_REF,     null,                       null],
        self::SHEET_13_REF => [PersonFixtures::LISZT_REF,      null,                       null],
        self::SHEET_14_REF => [PersonFixtures::RAVEL_REF,      null,                       null],
        self::MISSING_FILE_REF => [null, null, null],
        self::UNTAGGED_FILE_REF => [null, null, null],
    ];

    public function getDependencies(): array
    {
        return [PersonFixtures::class, PersonTypeFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var PersonType $composerType */
        $composerType = $this->getReference(PersonTypeFixtures::COMPOSER_REF, PersonType::class);
        /** @var PersonType $arrangerType */
        $arrangerType = $this->getReference(PersonTypeFixtures::ARRANGER_REF, PersonType::class);

        foreach (self::SHEETS as [$title, $refs, $tags, $ref]) {
            $sheet = (new Sheet())
                ->setTitle($title)
                ->setRefs($refs)
                ->setTags($tags)
                ->setFiles([StoredFile::fromArray(['name' => $ref, 'filename' => $ref.'.pdf'])])
            ;

            [$composerRef, $arrangerRef, $notes] = self::CREDITS[$ref];

            if ($notes !== null) {
                $sheet->setNotes($notes);
            }

            $manager->persist($sheet);

            if ($composerRef !== null) {
                /** @var Person $composer */
                $composer = $this->getReference($composerRef, Person::class);
                $credit = (new CreditedPerson())->setSheet($sheet)->setPerson($composer)->setPersonType($composerType);
                $manager->persist($credit);
            }

            if ($arrangerRef !== null) {
                /** @var Person $arranger */
                $arranger = $this->getReference($arrangerRef, Person::class);
                $credit = (new CreditedPerson())->setSheet($sheet)->setPerson($arranger)->setPersonType($arrangerType);
                $manager->persist($credit);
            }

            $this->addReference($ref, $sheet);
        }

        $manager->flush();
    }
}
