<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Storage;
use Validator;

class CategoryController extends Controller
{
    public function list()
    {
        $category = request()->category ?? "";

        return view("category", [
            "category" => $category
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

        $category_obj = DB::table("categories")
            ->where("id", "=", $id)
            ->first();

        if ($category_obj == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Category does not exists."
            ]);
        }

        DB::table("categories")
            ->where("id", "=", $category_obj->id)
            ->delete();

        return response()->json([
            "status" => "success",
            "message" => "Category has been deleted."
        ]);
    }

    public function store()
    {
        $validator = Validator::make(request()->all(), [
            "category" => "required",
            "description" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $admin = $this->auth_admin();
        $category = request()->category ?? "";
        $description = request()->description ?? "";

        $category_obj = DB::table("categories")
            ->where("category", "=", $category)
            ->first();

        if ($category_obj != null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Category already exists."
            ]);
        }

        DB::table("categories")
            ->insertGetId([
                "category" => $category,
                "description" => $description,
                "created_at" => now()->utc(),
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Category has been added."
        ]);
    }

    public function add()
    {
        return view("admin/categories/add");
    }
    
    public function fetch()
    {
        $user_id = request()->session()->get($this->session_key, 0);

        $user = DB::table("users")
            ->where("id", "=", $user_id)
            ->where("type", "=", "super_admin")
            ->first();

        if ($user == null)
        {
            abort(401);
        }

        $categories = DB::table("categories")
            ->orderBy("id", "desc")
            ->paginate(50);

        return view("admin/categories/index", [
            "categories" => $categories
        ]);
    }
}
