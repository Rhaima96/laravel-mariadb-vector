# Connecting Laravel to MariaDB

Laravel has shipped a dedicated `mariadb` database driver since Laravel 11. It is
not the same as the `mysql` driver: it has its own connection, connector, query
grammar and schema grammar.

```dotenv
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
```

## Why the mysql driver is not good enough

Pointing the `mysql` driver at a MariaDB server appears to work. Basic queries and
most migrations run fine, and then specific things break in ways that are hard to
trace back to the driver.

Vector distance queries throw `Vector distance queries are only supported by
Postgres and MariaDB.` Creating a vector index throws `The database driver in use
does not support vector indexes.` A `uuid()` column is created as `char(36)`
instead of MariaDB's native `uuid` type. A `geometry()` column with an SRID emits
`srid`, which MariaDB rejects — it expects `ref_system_id`. JSON paths compile to
`json_unquote(json_extract(...))` instead of MariaDB's `json_value(...)`.

You can check which driver a connection actually uses:

```php
DB::connection()->getDriverName();     // "mariadb"
DB::connection()->getServerVersion();  // "12.3.0-MariaDB"
```

## Versions

Laravel's own continuous integration runs its database test suite against MariaDB
10.11, 11.8 LTS and 12.3 LTS. Vector columns and vector distance queries require
11.7 or later.
