<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemsController extends Controller
{
    /**
     * Display a listing of items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Fetch all items from the database
        $items = DB::table('item')->get();
        
        // Return the view with items data
        return view('pabrik.items', compact('items'));
    }
}