# Laravel + MariaDB Vector

A small reference application: markdown files in, semantic search out, with the
embeddings and the application data living in the same MariaDB database.

Everything here uses what Laravel 13 and `laravel/ai` already provide. There is no
custom SQL, no glue package, and no vector service to run alongside the database.

## What it does

`php artisan ingest` splits the markdown files in `content/` into chunks, turns
each chunk into a 1536-dimension embedding through the Laravel AI SDK, and stores
it in a MariaDB `VECTOR` column with an HNSW index.

`php artisan ask "..."` embeds the question, finds the closest chunks with
`whereVectorSimilarTo`, prints the cosine distance of each match, and hands them to
an agent to answer from.

## Requirements

- PHP 8.3+, MariaDB 11.7 or later (11.8 LTS or 12.3 LTS recommended)
- An OpenAI API key, or any other embedding provider the AI SDK supports

## Setup

```bash
composer install
cp .env.example .env && php artisan key:generate

docker compose up -d          # MariaDB 12.3 on port 3308
php artisan migrate
php artisan ingest
php artisan ask "why should I not use the mysql driver with MariaDB?"
```

The compose file publishes MariaDB on **3308**, not 3306, so a MySQL you already
run locally keeps working.

Set your key in `.env`:

```dotenv
OPENAI_API_KEY=sk-...
```

## The database side

```php
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->text('content');
    $table->vector('embedding', 1536);
    $table->vectorIndex('embedding');
});
```

`vectorIndex()` emits `vector index ... M=6 DISTANCE=cosine`. Three rules follow
from that, and all three are easy to trip over:

- the indexed column must be `NOT NULL` — do not add `->nullable()`
- a table may have only one vector index
- the index is only used when the query's distance function matches its `DISTANCE`

On the model, the `AsVector` cast does the encoding both ways:

```php
protected function casts(): array
{
    return ['embedding' => AsVector::class];
}
```

## Things worth knowing

**Use the `mariadb` driver, never `mysql`.** Against a MariaDB server, the `mysql`
driver silently produces `char(36)` for uuid columns, refuses to create vector
indexes, and disables vector distance queries entirely.

**Do not call `Schema::ensureVectorExtensionExists()`.** It appears in Laravel's
own AI SDK documentation, but it is a `pgvector` helper and throws
`Extensions are only supported by Postgres.` on MariaDB. MariaDB needs no
extension.

**Cosine is the only metric the framework emits.** For `VEC_DISTANCE_EUCLIDEAN`,
use `whereRaw()` with a matching `DISTANCE=euclidean` index, or a package that adds
the metric.

**Tune `--similarity` before blaming the search.** `ask` defaults to `0.25`, which
is deliberately permissive for a three-file corpus. Raise it as the corpus grows.

## License

MIT
