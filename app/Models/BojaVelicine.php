<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BojaVelicine extends Model
{
    use HasFactory;

    protected $table = 'boja_velicines';

    protected $fillable = [
        'product_id',
        'boja',
        'velicina',
        'stanje'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
