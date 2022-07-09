<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

/**
 *
 */
class CategoryController extends ApiController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAll(Request $request){
        try {
            return $this->sendResponse(Category::all()->toArray());
        } catch (\Exception $exception) {
            Log::error($exception);
            return $this->sendError('Something went wrong, please contact administrator!');
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:50',
                'parent_id' => 'nullable|exists:categories,id'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $name = $request->get('name');
            $parentId = $request->get('parent_id');

            if ($parentId) {
                $parent = Category::find($parentId);

                if ($parent->parent?->parent) {
                    return $this->sendError('You can\'t add a 3rd level subcategory!');
                }
            }

            $category = new Category();
            $category->name = $name;
            $category->parent_id = $parentId;
            $category->save();

            return $this->sendResponse($category->toArray());
        } catch (\Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!');
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function get($id){
        try{
            return $this->sendResponse(Category::findOrFail($id)->toArray());
        } catch (\Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!');
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request!', $validator->messages()->toArray());
        }

        $category = Category::findOrFail($id);
        $category->name = $request->get('name');
        $category->parent_id = $request->get('parent_id');
        $category->save();

        return $this->sendResponse($category->toArray());
    }

    /**
     * @param $id
     * @return void|JsonResponse
     */
    public function delete($id){
        try {
            $category = Category::findOrFail($id);

             foreach ($category->childs as $child){
                $child->delete();
            }
            $category->delete();
            
            return $this->sendResponse([
                'deleted_category&subcategories' => $category
            ]);

        } catch (\Exception $exception) {
            Log::error($exception);
            return $this->sendError('Something went wrong, please contact administrator!');
        }
    }
}
