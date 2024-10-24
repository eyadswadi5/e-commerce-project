<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends BaseController
{
    public function store(string $product_id, Request $request) {
        $validator = Validator::make($request->only(["rating", "comment"]), [
            "rating" => "required|integer|max:5",
            "comment" => "required|string",
        ]);
        if ($validator->fails())    
            return $this->rst(false, 422, "Failed to add review", [["message" => "error validating data", "error" => $validator->errors()]]);
        try {
            $user = JWTAuth::user();
            $product = Product::findOrFail($product_id);
            $review = Review::create([
                "user_id" => $user->id,
                "product_id" => $product->id,
                "rating" => $request->rating,
                "comment" => $request->comment,
            ]);
            return $this->rst(true, 200, "Thanks for your review");
        } catch (JWTException $e) {
            return $this->rst(false, 401, "Failed to add review", [["message" => "something wrong with authenticating the user", "error" => $e]]);
        } catch (UserNotDefinedException $e) {
            return $this->rst(false, 401, "Failed to add review", [["message" => "user not found", "error" => $e]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to add review", [["message" => "database error occurres", "error" => $e]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 500, "Failed to add review", [["message" => "product not found"]]);
        }
    }

    public function index(string $product_id) {
        try {
            $product = Product::findOrFail($product_id);
            $reviews = $product->reviews();
            return $this->rst(true, 200, null, null, ["reviews" => $reviews]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to fetch reviews", [["message" => "database error", "error" => $e]]);
        }  catch (ModelNotFoundException $e) {
            return $this->rst(false, 500, "Failed to delete review", [["message" => "product not found"]]);
        }
    }

    public function approve(string $review_id) {
        try {
            $review = Review::findOrFail($review_id);
            $review->approved = true;
            $review->save();
            return $this->rst(true, 200, "review approved");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "failed to approve review", [["message" => "database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 500, "Failed to delete review", [["message" => "review not found"]]);
        }
    }

    public function delete(string $review_id) {
        try {
            $review = Review::findOrFail($review_id);
            $review->delete();
            return $this->rst(true, 200, "review deleted");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to delete review", [["message" => "database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 500, "Failed to delete review", [["message" => "review not found"]]);
        }
    }
}
