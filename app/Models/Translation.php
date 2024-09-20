<?php

namespace App\Models;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = ['translatable_id', 'translatable_type', 'language_code', 'key', 'translation'];

    // Define the polymorphic relationship
    public function translatable()
    {
        return $this->morphTo();
    }
}
