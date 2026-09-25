# Embeddings with the Laravel AI SDK

The `laravel/ai` package turns text into vectors. The framework stores and
searches them. Nothing else is needed to connect the two.

```php
use Illuminate\Support\Str;

$embedding = Str::of('MariaDB stores vectors natively.')->toEmbeddings();
```

The default provider is OpenAI and the default model is `text-embedding-3-small`,
which returns 1536 dimensions. That number is a contract with the schema: the
column has to be declared with exactly the same size.

## Searching

```php
Document::query()
    ->whereVectorSimilarTo('embedding', 'how do vector indexes work?')
    ->limit(10)
    ->get();
```

Passing a string rather than an array of floats is what ties the two halves
together: the query builder calls `toEmbeddings()` on it. `whereVectorSimilarTo`
filters on cosine similarity between 0.0 and 1.0 and orders by it, which the
grammar compiles to `vec_distance_cosine(...) <= 1 - $minSimilarity`.

## Changing model

Distances between vectors produced by different models are meaningless. Changing
the embedding model means re-embedding the whole table, and if the new model has a
different dimension count, altering the column too.

## Vector stores are something else

`Laravel\Ai\Stores` refers to vector stores hosted by the AI provider. They are
not a driver layer over your own database, and no configuration points them at
MariaDB. To keep the data in your own database, use a vector column.
