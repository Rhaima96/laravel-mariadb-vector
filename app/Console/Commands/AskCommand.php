<?php

namespace App\Console\Commands;

use App\Agents\DocsAgent;
use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AskCommand extends Command
{
    protected $signature = 'ask {question} {--matches=4} {--similarity=0.25}';

    protected $description = 'Answer a question from the ingested documents using MariaDB vector search';

    public function handle(): int
    {
        $question = $this->argument('question');

        // Embed the question once, then reuse the vector for both the distance
        // column and the filter. Passing the string directly would work too --
        // the query builder embeds it through laravel/ai -- but that bills twice.
        $vector = Str::of($question)->toEmbeddings();

        $matches = Document::query()
            ->select('id', 'source', 'content')
            ->selectVectorDistance('embedding', $vector, as: 'distance')
            ->whereVectorSimilarTo('embedding', $vector, minSimilarity: (float) $this->option('similarity'))
            ->limit((int) $this->option('matches'))
            ->get();

        if ($matches->isEmpty()) {
            $this->warn('Nothing similar enough. Lower --similarity or ingest more content.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('<fg=gray>Matched:</>');

        foreach ($matches as $match) {
            $this->line(sprintf('  %-28s distance %.4f', $match->source, $match->distance));
        }

        $this->newLine();
        $this->line((new DocsAgent($matches))->prompt($question)->text);
        $this->newLine();

        return self::SUCCESS;
    }
}
