<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FieldTest;

class WebFieldTestController extends Controller
{
    /**
     * Display a listing of the field tests.
     */
    public function index()
    {
        // Get all testing data descending, with user relation
        $tests = FieldTest::with('user')->orderBy('created_at', 'desc')->paginate(20);

        return view('pengujian.index', compact('tests'));
    }
}
