<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);
            $search = $request->input('q');

            $query = Product::query();

            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
                $query->orWhere('description', 'like', '%' . $search . '%');;
            }

            $total = $query->count(); // total semua data

            $offset = ($page - 1) * $limit;

            $data = $query
                ->offset($offset)
                ->limit($limit)
                ->get();

            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'Get Data Success!',
                'pages' => [
                    'current_page' => (int) $page,
                    'per_page' => (int) $limit,
                    'total' => $total,
                    'last_page' => ceil($total / $limit),
                    'starting_number' => (int)($offset + 1),
                    'created_url' => linksPage((int) $page, (int) ceil($total / $limit))
                    // 'next_page_url' => $page < ceil($total / $limit) ? url()->current() . '?page=' . ($page + 1) . '&limit=' . $limit : null,
                    // 'prev_page_url' => $page > 1 ? url()->current() . '?page=' . ($page - 1) . '&limit=' . $limit : null
                ],
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        try {
            // $imageName = null;

            // // Kalau ada gambar, simpan ke storage
            // if ($request->hasFile('image')) {
            //     $imageName = time() . '.' . $request->file('image')->extension();
            //     $request->file('image')->storeAs('public/images', $imageName);
            // }

            $data = Product::create([
                'id' => get_uuid(),
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'product_category' => $validated['product_category'],
                'description' => $request->description,
                'price' => $validated['price'],
                // 'image' => $imageName // pastikan kolom ini ada di table
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'data' => $data,
                'message' => 'Create Data Success!',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Create Failed: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $product = Product::findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $product,
                'message' => 'Product retrieved successfully.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProductRequest $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            // Validasi (boleh kamu ganti ke FormRequest juga)
            $validated = $request->validated();


            // Handle image baru
            // if ($request->hasFile('image')) {
            //     // Hapus gambar lama (jika ada)
            //     if ($product->image && \Storage::exists('public/images/' . $product->image)) {
            //         \Storage::delete('public/images/' . $product->image);
            //     }

            //     // Simpan gambar baru
            //     $imageName = time() . '.' . $request->file('image')->extension();
            //     $request->file('image')->storeAs('public/images', $imageName);

            //     $validated['image'] = $imageName;
            // }

            // Update data
            $product->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully!',
                'data' => $product
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($id);

            // Hapus gambar jika ada
            if ($product->image && Storage::exists('public/images/' . $product->image)) {
                Storage::delete('public/images/' . $product->image);
            }

            // Hapus data terkait (jika ada, contoh relasi manual)
            // DB::table('product_stocks')->where('product_id', $id)->delete();

            // Hapus produk
            $product->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully!',
                'id' => $id
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'string|exists:product,id'
        ]);

        try {
            DB::beginTransaction();

            $products = Product::whereIn('id', $request->ids)->get();

            foreach ($products as $product) {
                // Hapus gambar jika ada
                if ($product->image && Storage::exists('public/images/' . $product->image)) {
                    Storage::delete('public/images/' . $product->image);
                }

                // Hapus produk
                $product->delete();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Products deleted successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
