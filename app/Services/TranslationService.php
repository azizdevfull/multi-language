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

    /**
     * Update translations for a given model.
     *
     * @param Model $model
     * @param array $translations
     * @return bool
     */
    public function updateTranslations(Model $model, array $translations): bool
    {
        DB::beginTransaction();

        try {
            // Validate translation data
            foreach ($translations as $translationData) {
                if (empty($translationData['language_code']) || empty($translationData['key']) || empty($translationData['translation'])) {
                    throw new \InvalidArgumentException('Invalid translation data provided');
                }
            }

            // Retrieve existing translations
            $existingTranslations = Translation::where('translatable_id', $model->id)
                ->where('translatable_type', get_class($model))
                ->get()
                ->keyBy(function ($item) {
                    return $item->language_code . '-' . $item->key; // Unique key for each translation
                });

            $insertData = [];
            $updateData = [];

            foreach ($translations as $translationData) {
                $uniqueKey = $translationData['language_code'] . '-' . $translationData['key'];

                if (isset($existingTranslations[$uniqueKey])) {
                    // Update existing translation
                    $updateData[] = [
                        'id' => $existingTranslations[$uniqueKey]->id,
                        'translation' => $translationData['translation'],
                        'updated_at' => now(),
                    ];
                } else {
                    // Prepare new translation data for bulk insert
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
            }

            // Bulk update existing translations
            if (!empty($updateData)) {
                foreach ($updateData as $data) {
                    Translation::where('id', $data['id'])->update($data);
                }
            }

            // Bulk insert new translations
            if (!empty($insertData)) {
                Translation::insert($insertData);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            // Optionally log the error
            throw new \RuntimeException('Failed to update translations: ' . $e->getMessage());
        }
    }
}
