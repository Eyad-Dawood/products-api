<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\ProductSearchRequest;
use App\Http\Resources\ProductResource;

class ProductSearchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductSearchRequest $request)
    {
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

        $query->orderBy($sortBy,$sortDir);

        //pagination
        $perPage = $request->query('per_page',10);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }
    
}
