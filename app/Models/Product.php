<?php

namespace App\Models;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
}
