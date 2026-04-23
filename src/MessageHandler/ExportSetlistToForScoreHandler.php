<?php

namespace App\MessageHandler;

use App\Message\ExportSetlistToForScore;
use App\Service\ForScore\ForScore4SSExporter;

final readonly class ExportSetlistToForScoreHandler
{
    public function __construct(
        private ForScore4SSExporter $exporter,
    ) {}

    public function __invoke(ExportSetlistToForScore $action): string
    {
        return $this->exporter->export($action->setlist);
    }
}
