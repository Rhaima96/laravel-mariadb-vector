<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsVector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Document extends Model
{
    protected $fillable = ['source', 'content', 'embedding'];

    protected function casts(): array
    {
        // Reads MariaDB's little-endian float32 bytes as an array of floats,
        // writes an array back through vec_fromtext().
        return ['embedding' => AsVector::class];
    }

    /**
     * The chunks closest to a question, nearest first, each with a `distance` attribute.
     *
     * @return Collection<int, static>
     */
    public static function search(string $question, int $limit = 4, float $minSimilarity = 0.25): Collection
    {
        // Embed the question once, then reuse the vector for both the distance
        // column and the filter. Passing the string directly would work too --
        // the query builder embeds it through laravel/ai -- but that bills twice.
        $vector = Str::of($question)->toEmbeddings();

        return static::query()
            ->select('id', 'source', 'content')
            ->selectVectorDistance('embedding', $vector, as: 'distance')
            ->whereVectorSimilarTo('embedding', $vector, minSimilarity: $minSimilarity)
            ->limit($limit)
            ->get();
    }
}
