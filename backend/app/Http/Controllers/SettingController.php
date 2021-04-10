<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth:api');

        $this->middleware(function ($request, $next) {
            $this->login_user = Auth::user();

            return $next($request);
        });

    }

    public function getCategories()
    {
        $categories = Category::all();

        foreach($categories as $key => $category) {
            $categories[$key]['header'] = $this->integerToRomanNumeral($category->order) . ". " . mb_strtoupper($category->name);
        }

        return response()->json([
            'categories' => $categories
        ], 200);
    }
}
