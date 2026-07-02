<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Utils\Field\Field;

class DefaultFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Field::fields();
    }
}
