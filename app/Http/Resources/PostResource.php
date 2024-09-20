<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // Get the language from the request
        $lang = $request->query('lang', 'en');

        // Get translations for the specified language
        $translations = $this->translations->filter(function ($translation) use ($lang) {
            return $translation->language_code === $lang;
        });

        return [
            'id' => $this->id,
            'translations' => TranslationResource::collection($translations), // Use TranslationResource
        ];
    }
}
