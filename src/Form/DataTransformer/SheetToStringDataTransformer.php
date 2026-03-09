<?php

namespace App\Form\DataTransformer;

use App\Entity\Sheet;
use App\Repository\SheetRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<Sheet[], string>
 */
readonly class SheetToStringDataTransformer implements DataTransformerInterface
{
    public function __construct(private SheetRepository $sheetRepository)
    {
    }

    /**
     * @param Sheet[] $value
     */
    public function transform(mixed $value): string
    {
        $data = json_encode(array_map(fn (Sheet $sheet) => $sheet->getId(), $value));
        if($data) {
            return $data;
        } else {
            return '{}';
        }
    }

    /**
     * @param string $value
     * @return Sheet[]
     */
    public function reverseTransform(mixed $value): array
    {
        /** @var string[] $array */
        $array = json_decode($value, true);
        /** @var Sheet[] $sheets */
        $sheets = [];

        foreach ($array as $id) {
            $sheet = $this->sheetRepository->find($id);
            if ($sheet) {
                $sheets[] = $sheet;
            }
        }
        return $sheets;
    }
}
