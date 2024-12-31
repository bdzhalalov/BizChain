<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'price'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function batches()
    {
        return $this->belongsToMany(Batch::class)->withPivot(['quantity', 'purchase_price']);
    }

    public function storages()
    {
        return $this->belongsToMany(Storage::class)->withPivot(['quantity']);
    }
}
