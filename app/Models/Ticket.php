<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    //
}


namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasUlids;

    protected $fillable = [
        'subject',
        'body',
        'status',
        'category',
        'category_source',
        'explanation',
        'confidence',
        'internal_note',
    ];

    protected $casts = [
        'confidence' => 'float',
    ];
}
