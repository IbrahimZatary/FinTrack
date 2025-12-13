<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    //
    public function index() {}    // GET /expenses
public function create() {}   // GET /expenses/create  
public function store() {}    // POST /expenses
public function edit() {}     // GET /expenses/{id}/edit
public function update() {}   // PUT /expenses/{id}
public function destroy() {}  // DELETE /expenses/{id}
}
