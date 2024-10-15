<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $companies = Company::all();
            return $this->rst(true, 200, null, null, ["companies" => $companies]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to fetch companies", [["message" => "database error occurres"]]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->only(["name", "description"]), [
            "name" => "required|string|unique:companies,name",
            "description" => "nullable|string",
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "error validating data", [["messages" => $validator->errors()]]);

        try {
            Company::create($validator->getData());
            return $this->rst(true, 201, "company created");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to create company", [["message" => "database error occurres"]]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $company = Company::findOrFail($id);
            return $this->rst(true, 200, null, null, ["company" => $company]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to fetch company", [["message" => "database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "Failed to fetch company", [["message" => "company not found"]]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->only(["name", "description"]), [
            "name" => "required|string|exists:companies,name",
            "description" => "nullable|string",
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "error validating data", [["messages" => $validator->errors()]]);

        try {
            $company = Company::findOrFail($id);
            $company->description = $validator->getData()["description"];
            $company->save();
            return $this->rst(true, 200, "company updated");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "failed updating company", [["message" => "database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "failed updating company", [["message" => "company not found"]]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->delete();
            return $this->rst(true, 200, "company deleted");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "failed deleting company", [["message" => "database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "failed deleting company", [["message" => "company not found"]]);
        }
    }
}
