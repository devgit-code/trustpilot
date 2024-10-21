<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Str;
use Storage;
use Validator;

class ReviewController extends Controller
{
    public function fetch_by_company()
    {
        $validator = Validator::make(request()->all(), [
            "domain" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $domain = request()->domain ?? "";
        $domain = str_replace("www.", "", $domain);
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("https://", "", $domain);

        $company = DB::table("companies")
            ->where("domain", "=", $domain)
            ->first();

        if ($company == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Company not found."
            ]);
        }

        $reviews = DB::table("reviews")
            ->select("reviews.*", "companies.title AS company_title",
                "companies.domain AS companies_domain",
                "users.reviews AS user_reviews", "users.location AS user_location",
                "users.name AS user_name", "users.profile_image")
            ->join("companies", "companies.id", "=", "reviews.company_id")
            ->join("users", "users.id", "=", "reviews.user_id")
            ->where("reviews.company_id", "=", $company->id)
            ->orderBy("reviews.id", "desc")
            ->paginate(50);

        $reviews_arr = [];
        foreach ($reviews as $review)
        {
            $user_location = json_decode($review->user_location ?? "{}");
            if ($user_location)
            {
                $user_location = $user_location->country ?? "";
            }

            $review_obj = [
                "id" => $review->id,
                "ratings" => $review->ratings ?? 0,
                "title" => $review->title ?? "",
                "review" => $review->review ?? "",
                "proofs" => json_decode($review->proofs ?? "[]"),
                "company" => (object) [
                    "id" => $review->company_id,
                    "title" => $review->company_title ?? "",
                    "domain" => $review->companies_domain ?? ""
                ],
                "user" => (object) [
                    "id" => $review->user_id,
                    "name" => $review->user_name ?? "",
                    "reviews" => $review->user_reviews ?? 0,
                    "location" => $user_location ?? "",
                    "profile_image" => ($review->profile_image && Storage::exists("public/" . $review->profile_image)) ? url("/storage/" . $review->profile_image) : ""
                ],
                "replies" => [],
                "created_at" => date("d M, Y h:i:s a", strtotime($review->created_at . " UTC"))
            ];

            $proofs = [];
            foreach ($review_obj["proofs"] as $proof)
            {
                if ($proof && Storage::exists("public/" . $proof))
                {
                    array_push($proofs, url("/storage/" . $proof));
                }
            }
            $review_obj["proofs"] = $proofs;

            array_push($reviews_arr, (object) $review_obj);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "reviews" => $reviews_arr
        ]);
    }

    public function destroy()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $user = auth()->user();
        $id = request()->id ?? 0;

        $review = DB::table("reviews")
            ->where("id", "=", $id)
            ->where("user_id", "=", $user->id)
            ->first();

        if ($review == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Review not found."
            ]);
        }

        $proofs = json_decode($review->proofs ?? "[]");
        foreach ($proofs as $p)
        {
            if ($p && Storage::exists("public/" . $p))
            {
                Storage::delete("public/" . $p);
            }
        }

        DB::table("reviews")
            ->where("id", "=", $review->id)
            ->delete();

        DB::table("companies")
            ->where("id", "=", $review->company_id)
            ->decrement("reviews", 1);

        return response()->json([
            "status" => "success",
            "message" => "Review has been deleted."
        ]);
    }

    public function detail()
    {
        $id = request()->id ?? 0;

        $review = DB::table("reviews")
            ->select("reviews.*", "companies.title AS company_title",
                "companies.domain AS companies_domain",
                "users.reviews AS user_reviews", "users.location AS user_location",
                "users.name AS user_name", "users.profile_image")
            ->join("companies", "companies.id", "=", "reviews.company_id")
            ->join("users", "users.id", "=", "reviews.user_id")
            ->where("reviews.id", "=", $id)
            ->first();

        if ($review == null)
        {
            abort(404);
        }

        $user_location = json_decode($review->user_location ?? "{}");
        if ($user_location)
        {
            $user_location = $user_location->country ?? "";
        }

        $review_obj = [
            "id" => $review->id,
            "ratings" => $review->ratings ?? 0,
            "title" => $review->title ?? "",
            "review" => $review->review ?? "",
            "proofs" => json_decode($review->proofs ?? "[]"),
            "company" => (object) [
                "id" => $review->company_id,
                "title" => $review->company_title ?? "",
                "domain" => $review->companies_domain ?? ""
            ],
            "user" => (object) [
                "id" => $review->user_id,
                "name" => $review->user_name ?? "",
                "reviews" => $review->user_reviews ?? 0,
                "location" => $user_location ?? "",
                "profile_image" => ($review->profile_image && Storage::exists("public/" . $review->profile_image)) ? url("/storage/" . $review->profile_image) : ""
            ],
            "created_at" => date("d M, Y h:i:s a", strtotime($review->created_at . " UTC"))
        ];

        $proofs = [];
        foreach ($review_obj["proofs"] as $proof)
        {
            if ($proof && Storage::exists("public/" . $proof))
            {
                array_push($proofs, url("/storage/" . $proof));
            }
        }
        $review_obj["proofs"] = $proofs;

        return view("reviews/detail", [
            "id" => $id,
            "review" => (object) $review_obj
        ]);
    }

    public function review()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required",
            "ratings" => "required",
            "title" => "required",
            "review" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $user = auth()->user();

        $id = request()->id ?? 0;
        $ratings = request()->ratings ?? 0;
        $title = request()->title ?? "";
        $review = request()->review ?? "";
        $files = request()->file("files");

        if ($ratings < 1 || $ratings > 5) {
            return response()->json([
                "status" => "error",
                "message" => "Ratings must be in-between 1 and 5."
            ]);
        }

        // check company
        $company = DB::table("companies")
            ->where("id", "=", $id)
            ->first();

        if ($company == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Company not found."
            ]);
        }

        // save all files
        $proofs = [];
        if ($files)
        {
            foreach ($files as $file)
            {
                if ($file->isValid())
                {
                    $file_path = "reviews/" . $company->id . "/" . time() . "-" . $file->getClientOriginalName();
                    $file->storeAs("/public", $file_path);

                    array_push($proofs, $file_path);
                }
            }
        }

        // check if already reviewed
        $review_exists = DB::table("reviews")
            ->where("company_id", "=", $company->id)
            ->where("user_id", "=", $user->id)
            ->first();

        if ($review_exists != null)
        {
            return response()->json([
                "status" => "error",
                "message" => "You have already reviewed this company."
            ]);
        }

        // save in reviews
        $obj = [
            "company_id" => $company->id,
            "user_id" => $user->id,
            "ratings" => $ratings,
            "title" => $title,
            "review" => $review,
            "proofs" => json_encode($proofs),
            "created_at" => now()->utc(),
            "updated_at" => now()->utc()
        ];

        $obj["id"] = DB::table("reviews")
            ->insertGetId($obj);

        // increment in users table
        DB::table("users")
            ->where("id", "=", $user->id)
            ->increment("reviews", 1);

        // increment in companies table
        DB::table("companies")
            ->where("id", "=", $company->id)
            ->increment("reviews", 1);

        // calculate company rating and update

        return response()->json([
            "status" => "success",
            "message" => "Review has been posted.",
            "review_id" => $obj["id"]
        ]);
    }
}
