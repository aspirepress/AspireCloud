<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiResultsResponse;

class ThemeController extends Controller
{
     // Method for v1.0 (PHP serialized data)
    public function infoV1()
    {
        $themes = \DB::table('themes')->get()->toArray();
        $response = new ApiResultsResponse('themes', $themes, 1, 1);
        return response(serialize($response->toStdClass()), 200);
    }

     // Method for v1.1 (JSON response)
    public function infoV1_1()
    {
        return response()->json(['theme' => 'example', 'version' => '1.1']);
    }

    public function infoV1_2(Request $request)
    {
        $requestData = $request->query('request');

        $page = $requestData['page'];
        $perPage = $requestData['per_page'];
        $skip = ($page - 1) * $perPage;
        $themes = \DB::table('themes')->skip($skip)->take($perPage)->get()->toArray();
        $total = \DB::table('themes')->count();
        $response = new ApiResultsResponse('themes', $themes, $page, $perPage, $total);
        return response()->json($response);
    }
}
