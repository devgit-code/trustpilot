<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Str;
use Storage;
use Validator;

class CompanyController extends Controller
{
    public function admin_update()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required",
            "title" => "required",
            "description" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $this->auth_admin();
        $admin = auth()->user();
        $id = request()->id ?? 0;
        $title = request()->title ?? "";
        $description = request()->description ?? "";
        $categories = request()->selected_categories ?? "";
        $domain = request()->domain ?? "";
        $contact_phone = request()->contact_phone ?? "";
        $contact_email = request()->contact_email ?? "";
        $contact_city = request()->contact_city ?? "";
        $contact_country = request()->contact_country ?? "";
        $contact_address = request()->contact_address ?? "";
        $facebook = request()->facebook ?? "";
        $twitter = request()->twitter ?? "";
        $instagram = request()->instagram ?? "";
        $logo = request()->file("logo"); // screenshot

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

        $domain = str_replace("www.", "", $domain);
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("https://", "", $domain);

        if (!empty($domain))
        {
            $other_company = DB::table("companies")
                ->where("id", "!=", $company->id)
                ->where("domain", "=", $domain)
                ->first();

            if ($other_company != null)
            {
                return response()->json([
                    "status" => "error",
                    "message" => "Domain already exists."
                ]);
            }
        }

        $obj = [
            "domain" => $domain,
            "title" => $title,
            "description" => $description,
            "categories" => $categories,
            "contact_phone" => $contact_phone,
            "contact_email" => $contact_email,
            "contact_city" => $contact_city,
            "contact_country" => $contact_country,
            "contact_address" => $contact_address,
            "facebook" => $facebook,
            "twitter" => $twitter,
            "instagram" => $instagram,
            "updated_at" => now()->utc()
        ];

        if ($logo && $logo->isValid())
        {
            if ($company->screenshot && Storage::exists("public/" . $company->screenshot))
            {
                Storage::delete("public/" . $company->screenshot);
            }

            $file_path = "companies/" . $company->id . "/" . time() . "-" . $logo->getClientOriginalName();
            $logo->storeAs("public/", $file_path);

            $obj["screenshot"] = $file_path;
        }

        DB::table("companies")
            ->where("id", "=", $company->id)
            ->update($obj);

        return response()->json([
            "status" => "success",
            "message" => "Company has been updated."
        ]);
    }
    
    public function admin_edit()
    {
        $id = request()->id ?? 0;

        $company = DB::table("companies")
            ->where("id", "=", $id)
            ->first();

        if ($company == null)
        {
            abort(404);
        }

        if ($company->screenshot && Storage::exists("public/" . $company->screenshot))
        {
            $company->screenshot = url("/storage/" . $company->screenshot);
        }

        if ($company->categories)
        {
            $company->categories = json_decode($company->categories ?? "[]");
        }
        else
        {
            $company->categories = [];
        }

        $categories = DB::table("categories")
            ->orderBy("id", "desc")
            ->get();

        return view("admin/companies/edit", [
            "company" => $company,
            "categories" => $categories
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

        $admin = $this->auth_admin();
        $id = request()->id ?? 0;

        // check if company exists
        $company = DB::table("companies")
            ->where("id", "=", $id)
            ->first();

        if ($company == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Company does not exists."
            ]);
        }

        // get all its reviews and delete their proof images
        $reviews = DB::table("reviews")
            ->where("company_id", "=", $company->id)
            ->get();

        foreach ($reviews as $review)
        {
            $proofs = $review->proofs ?? "[]";
            $proofs = json_decode($proofs);

            foreach ($proofs as $proof)
            {
                if ($proof && Storage::exists("public/" . $proof))
                {
                    Storage::delete("public/" . $proof);
                }
            }
        }

        DB::table("reviews")
            ->where("company_id", "=", $company->id)
            ->delete();

        // delete company screenshot
        if ($company->screenshot && Storage::exists("public/" . $company->screenshot))
        {
            Storage::delete("public/" . $company->screenshot);
        }

        if (Storage::exists("public/companies/" . $company->id))
        {
            Storage::deleteDirectory("public/companies/" . $company->id);
        }

        // delete company
        DB::table("companies")
            ->where("id", "=", $company->id)
            ->delete();

        return response()->json([
            "status" => "success",
            "message" => "Company has been deleted."
        ]);
    }

    public function store()
    {
        $validator = Validator::make(request()->all(), [
            "title" => "required",
            "description" => "required",
            "logo" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $admin = $this->auth_admin();
        $title = request()->title ?? "";
        $description = request()->description ?? "";
        $categories = request()->selected_categories ?? "";
        $domain = request()->domain ?? "";
        $contact_phone = request()->contact_phone ?? "";
        $contact_email = request()->contact_email ?? "";
        $contact_city = request()->contact_city ?? "";
        $contact_country = request()->contact_country ?? "";
        $contact_address = request()->contact_address ?? "";
        $facebook = request()->facebook ?? "";
        $twitter = request()->twitter ?? "";
        $instagram = request()->instagram ?? "";
        $logo = request()->file("logo"); // screenshot

        $domain = str_replace("www.", "", $domain);
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("https://", "", $domain);

        $company = DB::table("companies")
            ->where("domain", "=", $domain)
            ->first();

        if ($company != null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Domain already exists."
            ]);
        }

        $obj = [
            "domain" => $domain,
            "title" => $title,
            "description" => $description,
            "categories" => $categories,
            "contact_phone" => $contact_phone,
            "contact_email" => $contact_email,
            "contact_city" => $contact_city,
            "contact_country" => $contact_country,
            "contact_address" => $contact_address,
            "facebook" => $facebook,
            "twitter" => $twitter,
            "instagram" => $instagram,
            "created_at" => now()->utc(),
            "updated_at" => now()->utc()
        ];

        $obj["id"] = DB::table("companies")
            ->insertGetId($obj);

        if ($logo->isValid())
        {
            $file_path = "companies/" . $obj["id"] . "/" . time() . "-" . $logo->getClientOriginalName();
            $logo->storeAs("public/", $file_path);

            DB::table("companies")
                ->where("id", "=", $obj["id"])
                ->update([
                    "screenshot" => $file_path
                ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Company has been added."
        ]);
    }

    public function add()
    {
        $categories = DB::table("categories")
            ->orderBy("id", "desc")
            ->get();

        return view("admin/companies/add", [
            "categories" => $categories
        ]);
    }

    public function fetch()
    {
        $companies = DB::table("companies")
            ->orderBy("id", "desc")
            ->paginate(50);

        $companies_arr = [];
        foreach ($companies as $company)
        {
            $obj = [
                "id" => $company->id,
                "domain" => $company->domain ?? "",
                "title" => $company->title ?? "",
                "description" => $company->description ?? "",
                "keywords" => $company->keywords ?? "",
                "screenshot" => $company->screenshot ?? "",
                "ratings" => $company->ratings ?? 0,
                "reviews" => $company->reviews ?? 0
            ];

            if ($obj["screenshot"] && Storage::exists("public/" . $obj["screenshot"]))
            {
                $obj["screenshot"] = url("/storage/" . $obj["screenshot"]);
            }

            array_push($companies_arr, (object) $obj);
        }

        return view("admin/companies/index", [
            "companies" => $companies_arr
        ]);
    }

    public function fetch_by_category()
    {
        $validator = Validator::make(request()->all(), [
            "category" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $category = request()->category ?? "";

        $companies = DB::table("companies")
            ->where("categories", "LIKE", "%" . $category . "%")
            ->orderBy("id", "desc")
            ->paginate(50);

        $companies_arr = [];
        foreach ($companies as $company)
        {
            $obj = [
                "id" => $company->id,
                "domain" => $company->domain ?? "",
                "title" => $company->title ?? "",
                "description" => $company->description ?? "",
                "keywords" => $company->keywords ?? "",
                "screenshot" => $company->screenshot ?? "",
                "ratings" => $company->ratings ?? 0,
                "reviews" => $company->reviews ?? 0
            ];

            if ($obj["screenshot"] && Storage::exists("public/" . $obj["screenshot"]))
            {
                $obj["screenshot"] = url("/storage/" . $obj["screenshot"]);
            }

            array_push($companies_arr, (object) $obj);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "companies" => $companies_arr
        ]);
    }

    public function update()
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
        $about_us = request()->about_us ?? "";
        $description = request()->description ?? "";
        $contact_us = request()->contact_us ?? "";
        $screenshot = request()->file("screenshot");

        // check company
        $company = DB::table("companies")
            ->where("id", "=", $id)
            ->where("is_claimed", "=", 1)
            ->first();

        if ($company == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Company not found."
            ]);
        }

        // check if company is claimed
        $claim = DB::table("claims")
            ->where("company_id", "=", $company->id)
            ->where("user_id", "=", $user->id)
            ->where("is_claimed", "=", 1)
            ->first();

        if ($claim == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Claim not found."
            ]);
        }

        $obj = [
            "about_us" => $about_us,
            "description" => $description,
            "contact_us" => $contact_us,
            "updated_at" => now()->utc()
        ];

        if ($screenshot && $screenshot->isValid())
        {
            $file_path = "companies/" . $company->id . "/" . time() . "-" . $screenshot->getClientOriginalName();
            $screenshot->storeAs("/public", $file_path);

            $obj["screenshot"] = $file_path;
        }

        DB::table("companies")
            ->where("id", "=", $company->id)
            ->update($obj);

        return response()->json([
            "status" => "success",
            "message" => "Company has been updated."
        ]);
    }

    public function edit()
    {
        $id = request()->id ?? 0;

        return view("company/edit", [
            "id" => $id
        ]);
    }

    public function verify_claim()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required",
            "code" => "required"
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
        $code = request()->code ?? "";

        // check company
        $company = DB::table("companies")
            ->where("id", "=", $id)
            ->where("is_claimed", "=", 0)
            ->first();

        if ($company == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Company not found."
            ]);
        }

        // check if company is already claimed
        $claim = DB::table("claims")
            ->where("company_id", "=", $company->id)
            ->where("user_id", "=", $user->id)
            ->where("code", "=", $code)
            ->where("is_claimed", "=", 0)
            ->first();

        if ($claim == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Claim not found."
            ]);
        }

        // set claim from claimed
        DB::table("claims")
            ->where("id", "=", $claim->id)
            ->update([
                "is_claimed" => 1,
                "updated_at" => now()->utc()
            ]);

        // set company as claimed
        DB::table("companies")
            ->where("id", "=", $company->id)
            ->update([
                "is_claimed" => 1,
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Congratulations ! Company has been claimed."
        ]);
    }

    public function claim_detail()
    {
        $id = request()->id ?? 0;

        return view("company/claim", [
            "id" => $id
        ]);
    }

    public function claim()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required",
            "email" => "required"
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
        $email = request()->email ?? "";

        // get company
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

        // check if already claimed
        $claim = DB::table("claims")
            ->where("company_id", "=", $company->id)
            ->where("user_id", "=", $user->id)
            ->first();

        if ($claim != null)
        {
            return response()->json([
                "status" => "error",
                "message" => "You have already claimed for this company."
            ]);
        }

        $code = Str::random(6);
        // send email
        $email_id = $this->send_mail($email, $email, "Claim Website", "Your verification code to claim the website '" . $company->domain . "' is: <b style='font-size: 30px;'>" . $code . "</b>");
        // $email_id = "";

        // save in claims table
        $obj = [
            "company_id" => $company->id,
            "user_id" => $user->id,
            "email" => $email,
            "code" => $code,
            "email_id" => $email_id,
            "created_at" => now()->utc(),
            "updated_at" => now()->utc()
        ];

        $obj["id"] = DB::table("claims")
            ->insertGetId($obj);

        return response()->json([
            "status" => "success",
            "message" => "Verification code has been emailed at '" . $email . "'."
        ]);
    }

    public function search()
    {
        $validator = Validator::make(request()->all(), [
            "q" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $per_page = 50;
        $query = request()->q ?? "";

        $companies = DB::table("companies")
            ->where("domain", "LIKE", "%" . $query . "%")
            ->orWhere("title", "LIKE", "%" . $query . "%")
            ->orWhere("description", "LIKE", "%" . $query . "%")
            ->orWhere("keywords", "LIKE", "%" . $query . "%")
            ->orWhere("categories", "LIKE", "%" . $query . "%")
            ->paginate($per_page);

        $companies_arr = [];
        foreach ($companies as $company)
        {
            $obj = [
                "id" => $company->id,
                "domain" => $company->domain ?? "",
                "title" => $company->title ?? "",
                "description" => $company->description ?? "",
                "keywords" => $company->keywords ?? "",
                "screenshot" => $company->screenshot ?? "",
                "ratings" => $company->ratings ?? 0,
                "reviews" => $company->reviews ?? 0
            ];

            if ($obj["screenshot"] && Storage::exists("public/" . $obj["screenshot"]))
            {
                $obj["screenshot"] = url("/storage/" . $obj["screenshot"]);
            }

            array_push($companies_arr, (object) $obj);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "companies" => $companies_arr
        ]);
    }

    public function evaluate()
    {
        $id = request()->id ?? 0;
        $stars = request()->stars ?? 0;

        return view("company/evaluate", [
            "id" => $id,
            "stars" => $stars
        ]);
    }

    private function add_company($domain)
    {
        $domain = str_replace("www.", "", $domain);
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("https://", "", $domain);
        // $url = "https://" . $domain;
        $url = $this->ensure_http_prefix($domain);

        $title = "";
        $description = "";
        $keywords = "";
        $author = "";
        $favicons = "";

        try
        {
            $html = file_get_contents($url);

            // Use a regular expression to extract the title
            if (preg_match("/<title>(.*?)<\/title>/i", $html, $matches))
                $title = $matches[1];

            // Extract meta tags using regular expressions
            if (preg_match('/<meta\s+name="description"\s+content="(.*?)"/i', $html, $matches))
                $description = $matches[1];

            if (preg_match('/<meta\s+name="keywords"\s+content="(.*?)"/i', $html, $matches))
                $keywords = $matches[1];

            if (preg_match('/<meta\s+name="author"\s+content="(.*?)"/i', $html, $matches))
                $author = $matches[1];

            if (preg_match('/<link\s+[^>]*rel=["\'](?:shortcut\s+icon|icon)["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches))
                $favicons = $matches[1];
        }
        catch (\Exception $exp)
        {
            // 
        }

        // get company logo too

        $obj = [
            "domain" => $domain,
            "title" => $title,
            "description" => $description,
            "keywords" => $keywords,
            "author" => $author,
            "favicons" => $favicons,
            "screenshot" => null,
            "created_at" => now()->utc(),
            "updated_at" => now()->utc()
        ];

        $obj["id"] = DB::table("companies")
            ->insertGetId($obj);

        return (object) $obj;
    }

    public function find()
    {
        $domain = request()->domain ?? "";
        $user = auth("sanctum")->user();

        $company = DB::table("companies")
            ->where("id", "=", $domain)
            ->orWhere("domain", "=", $domain)
            ->first();

        if ($company == null)
        {
            $company = $this->add_company($domain);
        }

        $obj = [
            "id" => $company->id,
            "domain" => $company->domain ?? "",
            "domain_with_server" => $this->ensure_http_prefix($company->domain ?? ""),
            "title" => $company->title ?? "",
            "description" => $company->description ?? "",
            "about_us" => $company->about_us ?? "",
            "contact_us" => $company->contact_us ?? "",
            "screenshot" => ($company->screenshot && Storage::exists("public/" . $company->screenshot)) ? url("/storage/" . $company->screenshot) : "",
            "is_claimed" => $company->is_claimed ?? 0,
        ];

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

        $is_my_claimed = false;
        if ($user != null)
        {
            $is_my_claimed = DB::table("claims")
                ->where("company_id", "=", $company->id)
                ->where("user_id", "=", $user->id)
                ->exists();
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "company" => (object) $obj,
            "reviews" => $reviews_arr,
            "has_reviewed" => false,
            "is_my_claimed" => $is_my_claimed
        ]);
    }

    public function detail()
    {
        $domain = request()->domain ?? "";
        $domain = str_replace("www.", "", $domain);
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("https://", "", $domain);

        return view("company/detail", [
            "domain" => $domain
        ]);
    }
}
