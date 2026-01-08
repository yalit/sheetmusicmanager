<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class SheetReference
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $referenceCode = '';

    #[Assert\NotBlank]
    public string $referenceType = '';

    public function __construct(string $referenceCode = '', string $referenceType = '')
    {
        $this->referenceCode = $referenceCode;
        $this->referenceType = $referenceType;
    }

    /**
    * @return Array<string, string>
    **/
    public function toArray(): array
    {
        return [
            'reference_code' => $this->referenceCode,
            'reference_type' => $this->referenceType,
        ];
    }

    /**
    * @param $data Array<string, string>
    **/
    public static function fromArray(array $data): self
    {
        return new self(
            $data['reference_code'] ?? '',
            $data['reference_type'] ?? ''
        );
    }
}
