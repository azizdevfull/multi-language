<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function __construct(protected TranslationService $translationService)
    {
    }
    public function index(Request $request)
    {
        $lang = $request->query('lang', 'en'); // Default to 'en' if not specified

        $posts = Post::with([
            'translations' => function ($query) use ($lang) {
                $query->where('language_code', $lang);
            }
        ])->get();

        return PostResource::collection($posts);
    }


    public function store(StorePostRequest $request)
    {
        DB::beginTransaction();

        try {
            $post = new Post();
            $post->save();
            $this->translationService->createTranslations($post, $request->translations);

            DB::commit();
            return response()->json(['post' => $post->load('translations')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create post or translations'], 500);
        }
    }



    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Validate the request
            $validated = $request->validate([
                'translations' => 'required|array',
                'translations.*.language_code' => 'required|string|exists:languages,code',
                'translations.*.title' => 'required|string|max:255',
                'translations.*.content' => 'required|string',
            ]);

            // Find the post
            $post = Post::findOrFail($id);

            // Update the translations
            foreach ($validated['translations'] as $translation) {
                Translation::updateOrCreate(
                    ['key' => 'post.title.' . $post->id, 'language_code' => $translation['language_code']],
                    ['translation' => $translation['title']]
                );

                Translation::updateOrCreate(
                    ['key' => 'post.content.' . $post->id, 'language_code' => $translation['language_code']],
                    ['translation' => $translation['content']]
                );
            }

            DB::commit();
            return response()->json(['post' => $post]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update post'], 500);
        }
    }
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Find the post
            $post = Post::findOrFail($id);

            // Delete translations
            Translation::where('key', 'LIKE', 'post.title.' . $post->id)->delete();
            Translation::where('key', 'LIKE', 'post.content.' . $post->id)->delete();

            // Delete the post
            $post->delete();

            DB::commit();
            return response()->json(['message' => 'Post deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete post'], 500);
        }
    }


}
