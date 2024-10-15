<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Lcobucci\JWT\Validation\Constraint\ValidAt;

class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return $this->rst(true, 200, null, null, ["categories" => $categories]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to fetch categories", [["message" => "database error occurres"]]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->only(["name", "description"]), [
            "name" => "required|string|unique:categories,name",
            "description" => "nullable|string",
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "error validating data", [["messages" => $validator->errors()]]);

        try {
            Category::create($validator->getData());
            return $this->rst(true, 201, "category created");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to create category", [["message" => "database error occurres"]]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $category = Category::findOrFail($id);
            return $this->rst(true, 200, null, null, ["category" => $category]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to fetch category", [["message" => "database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "Failed to fetch category", [["message" => "category not found"]]);
        }
    }

    public function update(string $id, Request $request)
    {
        $validator = Validator::make($request->only(["name", "description"]), [
            "name" => "required|string|exists:categories,name",
            "description" => "nullable|string",
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "error validating data", [["messages" => $validator->errors()]]);

        try {
            $category = Category::findOrFail($id);
            $category->description = $validator->getData()["description"];
            $category->save();
            return $this->rst(true, 200, "category updated");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "failed updating category", [["message" => "database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "failed updating category", [["message" => "category not found"]]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
            return $this->rst(true, 200, "category deleted");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "failed deleting category", [["message" => "database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "failed deleting category", [["message" => "category not found"]]);
        }
    }
}
