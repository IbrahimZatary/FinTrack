<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalyticsController extends Controller
{


    public function spendingByCategory()
{
    $data = auth()->user()->expenses()
        ->selectRaw('category_id, SUM(amount) as total')
        ->whereMonth('date', now()->month)
        ->groupBy('category_id')
        ->with('category')
        ->get()
        ->map(function ($item) {
            return [
                'category' => $item->category->name,
                'amount' => $item->total,
                'color' => $item->category->color
            ];
        });
    
    return response()->json($data);
} 
    // For charts Json
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
