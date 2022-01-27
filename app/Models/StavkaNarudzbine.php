<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StavkaNarudzbine extends Model
{
    use HasFactory;

    protected $table = 'stavka_narudzbines';

    protected $fillable = [
        'narudzbina_id',
        'product_id',
        'boja',
        'velicina',
        'kolicina',
        'cena',
        'iznos',
    ];
}
