<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductController extends ApiController
{
    public function upload(Request $request)
    {
        if ($request->has('image')) {
            $file = $request->file('image');

            $filename = 'P' . time() . '.' . $file->getClientOriginalExtension();

            $path = 'products/';

            Storage::putFileAs($path, $file, $filename);

            return $path . $filename;
        }
    }

    public function getAllProductsForCategory($categoryId)
    {
        $products = Product::where('category_id', $categoryId)
            ->orWhereHas('category', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId)
                    ->orWhereHas('parent', function ($query) use ($categoryId) {
                        $query->where('category_id', $categoryId);
                    });
            })->get();
        return $products->toArray();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAll(Request $request): JsonResponse
    {
        try {
            $products = Product::query();

            $perPage = $request->get('perPage', 20);
            $search = $request->get('search', '');

            if ($search && $search !== '') {
                $products = $products->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%');
                });
            }

            $categoryId = $request->get('category');

            if ($categoryId) {
                $products = $products->where('category_id', $categoryId);
            }

            $status = $request->get('status');

            if ($status) {
                $products = $products->where('status', $status);
            }

            $products = $products->paginate($perPage);

            $results = [
                'data' => $products->items(),
                'currentPage' => $products->currentPage(),
                'perPage' => $products->perPage(),
                'total' => $products->total(),
                'hasMorePages' => $products->hasMorePages()
            ];

            return $this->sendResponse($results);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function get($id): JsonResponse
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return $this->sendError('Product not found!', [], Response::HTTP_NOT_FOUND);
            }

            return $this->sendResponse($product->toArray());
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:100|unique:products,name',
                'category_id' => 'required|exists:categories,id',
                'description' => 'required|unique:products,description',
                'quantity' => 'required|numeric',
                'price' => 'required|numeric',
                'image' => 'nullable',
                'status' => 'nullable'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $product = new Product();
            $product->name = $request->get('name');
            $product->category_id = $request->get('category_id');
            $product->description = $request->get('description');
            $product->quantity = $request->get('quantity');
            $product->price = $request->get('price');
            $product->image = $request->get('image');
            $product->status = $request->get('status');
            $product->save();

            return $this->sendResponse([], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return $this->sendError('Product not found', [], Response::HTTP_NOT_FOUND);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|max:100|unique:products,name',
                'category_id' => 'required|exists:categories,id',
                'description' => 'required|unique:products,description',
                'quantity' => 'required|numeric',
                'price' => 'required|numeric',
                'image' => 'nullable',
                'status' => 'nullable'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $product = new Product();
            $product->name = $request->get('name');
            $product->category_id = $request->get('category_id');
            $product->description = $request->get('description');
            $product->quantity = $request->get('quantity');
            $product->price = $request->get('price');
            $product->image = $request->get('image');
            $product->status = $request->get('status');
            $product->save();

            return $this->sendResponse($product->toArray());
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return $this->sendError('Product not found', [], Response::HTTP_NOT_FOUND);
            }

            DB::beginTransaction();

            $product->delete();

            DB::commit();

            return $this->sendResponse([], Response::HTTP_NO_CONTENT);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
