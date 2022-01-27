<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PatikeBoje;
use App\Models\PatikeVelicine;
use App\Models\BojaVelicine;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'pol'
    ];

    public function boje()
    {
        return $this->hasMany(PatikeBoje::class);
    }

    public function velicine()
    {
        return $this->hasMany(PatikeVelicine::class);
    }

    public function bojaVelicine()
    {
        return $this->hasMany(BojaVelicine::class);
    }
}
