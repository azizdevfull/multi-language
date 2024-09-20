<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TranslationService
{
    /**
     * Create translations for a given model.
     *
     * @param Model $model
     * @param array $translations
     * @return bool
     */
    public function createTranslations(Model $model, array $translations): bool
    {
        DB::beginTransaction();

        try {
            $insertData = [];

            foreach ($translations as $translationData) {
                // Validate translation data
                if (empty($translationData['language_code']) || empty($translationData['key']) || empty($translationData['translation'])) {
                    throw new \InvalidArgumentException('Invalid translation data provided');
                }

                // Prepare data for bulk insert
                $insertData[] = [
                    'language_code' => $translationData['language_code'],
                    'key' => $translationData['key'],
                    'translation' => $translationData['translation'],
                    'translatable_id' => $model->id,
                    'translatable_type' => get_class($model),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Perform bulk insert
            Translation::insert($insertData);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            // Optionally log the error
            throw new \RuntimeException('Failed to create translations: ' . $e->getMessage());
        }
    }
}
