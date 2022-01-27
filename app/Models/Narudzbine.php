<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Narudzbine extends Model
{
    use HasFactory;

    protected $table = 'narudzbines';

    protected $fillable = [
        'user_id',
        'ime',
        'prezime',
        'email',
        'telefon',
        'grad',
        'postanski_broj',
        'adresa',
        'placanje',
        'ukupan_iznos',
    ];
}
