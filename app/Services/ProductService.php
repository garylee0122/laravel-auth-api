<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;

class ProductService
{
    public function getProducts(User $user, $request)
    {

        $query = $user->products(); // 🔥 只查自己的

        $query->when($request->filled('keyword'), function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->keyword . '%');
        });

        return $query->paginate(5);
    }

    public function createProduct(User $user, array $data)
    {
        return $user->products()->create($data); // 🔥 綁 user
    }

    public function updateProduct(User $user, $id, array $data)
    {
        $product = $user->products()->findOrFail($id); // 🔥 只能更新自己的
        $product->update($data);

        return $product->refresh();
    }

    public function deleteProduct(User $user, $id)
    {
        $product = $user->products()->findOrFail($id); // 🔥 只能刪除自己的
        $product->delete();
    }

    public function getProduct(User $user, $id)
    {
        return $user->products()->findOrFail($id); // 🔥 只能查自己的
    }
}
