<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class PatikeBoje extends Model
{
    use HasFactory;

    protected $table = 'patike_bojes';

    protected $fillable = [
        'product_id',
        'boja',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function velicinaPoBoji()
    {
        return $this->belongsTo(VelicinaBoja::class);
    }
}
