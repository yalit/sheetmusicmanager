<?php

namespace App\Tests\Doubles;

use Sensiolabs\GotenbergBundle\Builder\BuilderInterface;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Test double for GotenbergPdfInterface.
 *
 * Returns a fluent fake builder that handles any chained method call
 * and streams a minimal HTTP 200 response when stream() is called.
 * Avoids needing a live Gotenberg sidecar in CI.
 */
final class FakeGotenbergPdf implements GotenbergPdfInterface
{
    public function get(string $builder): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }

    public function html(): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }

    public function url(): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }

    public function markdown(): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }

    public function office(): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }

    public function merge(): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }

    public function convert(): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }

    public function split(): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }

    public function flatten(): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }

    public function encrypt(): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }

    public function embed(): BuilderInterface
    {
        return new FakeGotenbergBuilder();
    }
}

/**
 * Fluent builder stub: any method call returns $this, except stream() which
 * returns a minimal StreamedResponse so controllers can return it directly.
 */
final class FakeGotenbergBuilder implements BuilderInterface
{
    private string $filename = 'file.pdf';
    private string $disposition = 'attachment';

    public function __call(string $name, array $args): static
    {
        if ($name === 'fileName' && isset($args[0])) {
            $this->filename    = $args[0];
            $this->disposition = $args[1] ?? 'attachment';
        }

        return $this;
    }

    public function stream(): StreamedResponse
    {
        return new StreamedResponse(
            static fn() => print('%PDF-1.4 fake merged pdf'),
            200,
            ['Content-Disposition' => sprintf('%s; filename="%s"', $this->disposition, $this->filename)],
        );
    }
}
