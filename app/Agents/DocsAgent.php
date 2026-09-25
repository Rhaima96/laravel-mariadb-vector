<?php

namespace App\Agents;

use Illuminate\Support\Collection;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

final class DocsAgent implements Agent
{
    use Promptable;

    public function __construct(private Collection $context) {}

    public function instructions(): string
    {
        return "Answer the question using only the context below. Quote the source file name you relied on. If the context does not contain the answer, say so plainly instead of guessing.\n\n"
            .$this->context
                ->map(fn ($document) => "### {$document->source}\n{$document->content}")
                ->implode("\n\n");
    }
}
