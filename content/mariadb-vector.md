# Vector columns in MariaDB

MariaDB stores embeddings in a native `VECTOR` column. The type became generally
available in MariaDB 11.8 LTS, after a preview in 11.7. A column declares how many
dimensions it holds, and the server rejects any vector of a different length at
insert time.

```sql
CREATE TABLE documents (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    content TEXT NOT NULL,
    embedding VECTOR(1536) NOT NULL,
    VECTOR INDEX (embedding) M=6 DISTANCE=cosine
);
```

## Distance functions

Two functions compare vectors: `VEC_DISTANCE_COSINE` and `VEC_DISTANCE_EUCLIDEAN`.
Cosine distance ignores magnitude and only compares direction, which is what most
text embedding models are trained for. Euclidean distance takes magnitude into
account and suits embeddings that encode scale.

## Index rules

A vector index is an HNSW graph. Three constraints matter in practice.

The indexed column must be `NOT NULL`. A table may carry only one vector index.
And the index is only used when the query's distance function matches the index's
`DISTANCE` setting — an index built with `DISTANCE=cosine` does nothing for a
`VEC_DISTANCE_EUCLIDEAN` query. The results stay correct, but the server falls
back to scanning every row.

The `M` parameter is the graph degree, between 3 and 200, defaulting to 6. Higher
values improve recall and cost memory.
