<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsVector;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['source', 'content', 'embedding'];

    protected function casts(): array
    {
        // Reads MariaDB's little-endian float32 bytes as an array of floats,
        // writes an array back through vec_fromtext().
        return ['embedding' => AsVector::class];
    }
}
