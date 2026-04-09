<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    // 查詢（含搜尋 + 分頁）
    public function index(Request $request)
    {
        $products = $this->productService->getProducts($request->user(), $request);
        return ApiResponse::success(ProductResource::collection($products));
    }

    // 查單筆
    public function show(Request $request, $id)
    {
        $product = $this->productService->getProduct($request->user(), $id);
        return ApiResponse::success(new ProductResource($product));
    }

    // 新增
    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->createProduct($request->user(), $request->validated());
        return ApiResponse::success(new ProductResource($product), null, 201);
    }

    // 更新
    public function update(UpdateProductRequest $request, $id)
    {
        $product = $this->productService->updateProduct($request->user(), $id, $request->validated());
        return ApiResponse::success(new ProductResource($product), 'Product updated');
    }

    // 刪除
    public function destroy(Request $request, $id)
    {
        $this->productService->deleteProduct($request->user(), $id);
        return ApiResponse::success(null, 'Product deleted');
    }
}
