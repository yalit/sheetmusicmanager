<?php

namespace App\Message\Factory;

use App\Message\AddSheetsToSetlist;
use App\Repository\SheetRepository;

readonly class AddSheetsToSetListFactory
{
    public function __construct(private SheetRepository $sheetRepository)
    {
    }

    /**
     * @param string[] $sheetIDs
     */
    public function create(array $sheetIDs): AddSheetsToSetlist
    {
        $addToSetlist = new AddSheetsToSetlist();
        foreach ($sheetIDs as $sheetID) {
            $sheet = $this->sheetRepository->find($sheetID);

            if ($sheet) {
                $addToSetlist->sheets[] = $sheet;
            }
        }
        return $addToSetlist;
    }
}
