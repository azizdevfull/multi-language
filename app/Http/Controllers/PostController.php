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
    public function index()
    {
        $posts = Post::paginate(10); // Har bir sahifada 10 ta postni ko'rsatadi
        return PostResource::collection($posts);
    }


    public function store(Request $request)
    {
        $posts = [
            ['title' => 'menin postim', 'content' => 'menin postim'],
            ['title' => 'menin postim 2', 'content' => 'menin postim 2'],
        ];
        Post::insert($posts);
        // yoki tog'ridan tog'ri 
        Post::insert([
            ['title' => 'menin postim', 'content' => 'menin postim'],
            ['title' => 'menin postim 2', 'content' => 'menin postim 2'],
        ]);

        return 'Post muvaffaqiyatli yaratildi';
    }





    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        DB::beginTransaction();

        try {
            // Validate the request
            $validated = $request->validate([
                'translations' => 'required|array',
                'translations.*.language_code' => 'required|string|exists:languages,code',
                'translations.*.key' => 'required|string|max:255',
                'translations.*.translation' => 'required|string',
            ]);

            // Use TranslationService to update translations
            $this->translationService->updateTranslations($post, $validated['translations']);

            DB::commit();
            return response()->json(['post' => $post->load('translations')], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update post or translations: ' . $e->getMessage()], 500);
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
