<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Korpa extends Model
{
    use HasFactory;

    protected $table = 'korpas';

    protected $fillable = [
        'user_id',
        'product_id',
        'velicina',
        'boja',
        'kolicina'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
