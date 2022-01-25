<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Filter
{
    public static function request(Request $request, array $merge = [], array $excludeKeys = [])
    {
        $except = array_keys($merge);
        $array  = Arr::except($request->all(), $except);
        return Arr::except(array_merge($array, $merge), $excludeKeys);
    }
}
