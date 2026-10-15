<?php

namespace App\Http\Controllers\Api\Product;

use App\Models\Product;
use App\Http\Requests\ProductSearchRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class ProductSearchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductSearchRequest $request)
    {
        $data = Cache::remember($request->cacheKey(), 600, function () use ($request) {
        
        $query = Product::query()->select([
            'id',
            'name',
            'description',
            'price',
            'category_id',
            'is_available',
            'created_at'
        ])->with('category:id,name');

        // name / description search
        if($request->filled('q'))
            {
                $searchTerm = $request->query('q');
                
                $query->whereFullText(['name', 'description'], $searchTerm, ['mode' => 'boolean']);
            }

        //price range filter 
        if($request->filled('min_price'))
            {
                $query->where('price','>=',$request->query('min_price'));
            }

        if($request->filled('max_price'))
            {
                $query->where('price','<=',$request->query('max_price'));
            }

        //category filter
        if($request->filled('category_id'))
            {
                $query->where('category_id',$request->query('category_id'));
            }

        // availability filter
        if($request->filled('is_available'))
            {
                $query->where('is_available',$request->query('is_available'));
            }

        //sorting
        $sortBy = $request->query('sort_by','created_at');
        $sortDir = $request->query('sort_dir','desc');

        $query->
        orderBy($sortBy,$sortDir)
        ->orderBy('id','desc'); // fall back when values are equal


        //pagination
        $perPage = (int) $request->query('per_page',10);

        $paginator = $query->simplePaginate($perPage);

        return ProductResource::collection($paginator)->response()->getData(true);
        
        });

       return response()->json($data);
    }
    
}
