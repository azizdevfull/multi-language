<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\ProductResource;
use App\Models\Post;
use App\Models\Product;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(protected TranslationService $translationService)
    {
    }
    public function index(Request $request)
    {
        $lang = $request->query('lang', 'en'); // Default to 'en' if not specified

        $products = Product::with([
            'translations' => function ($query) use ($lang) {
                $query->where('language_code', $lang);
            }
        ])->get();

        return ProductResource::collection($products);
    }


    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $product = new Product();
            $product->save();
            $this->translationService->createTranslations($product, $request->translations);

            DB::commit();
            return response()->json(['product' => $product->load('translations')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create product or translations'], 500);
        }
    }

}
