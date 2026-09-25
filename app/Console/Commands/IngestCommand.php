<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class IngestCommand extends Command
{
    protected $signature = 'ingest {path=content : Folder of markdown files, relative to the project root}';

    protected $description = 'Split markdown files into chunks, embed them, and store them in MariaDB';

    public function handle(): int
    {
        $files = glob(base_path($this->argument('path')).'/*.md');

        if (empty($files)) {
            $this->error('No markdown files found in '.$this->argument('path'));

            return self::FAILURE;
        }

        Document::truncate();

        foreach ($files as $file) {
            foreach ($this->chunk(file_get_contents($file)) as $chunk) {
                Document::create([
                    'source' => basename($file),
                    'content' => $chunk,
                    'embedding' => Str::of($chunk)->toEmbeddings(),
                ]);

                $this->line('  <fg=gray>'.basename($file).'</> '.Str::limit(str_replace("\n", ' ', $chunk), 60));
            }
        }

        $this->newLine();
        $this->info(Document::count().' chunks stored.');

        return self::SUCCESS;
    }

    /**
     * Split text into chunks on paragraph boundaries.
     *
     * Note: splits on blank lines and packs up to $max characters. Good enough for
     * markdown prose; swap in a token-aware splitter if chunks start cutting mid-sentence.
     *
     * @return array<int, string>
     */
    private function chunk(string $text, int $max = 600): array
    {
        $chunks = [];
        $current = '';

        foreach (preg_split("/\n\s*\n/", $text) as $paragraph) {
            if ($current !== '' && strlen($current) + strlen($paragraph) > $max) {
                $chunks[] = trim($current);
                $current = '';
            }

            $current .= $paragraph."\n\n";
        }

        return array_values(array_filter(array_merge($chunks, [trim($current)])));
    }
}
