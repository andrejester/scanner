<?php

namespace App\Http\Controllers\System;

use App\DataTables\UserDateDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\System\UserDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserDateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserDateDataTable $dataTable)
    {
        abort_if(Gate::denies('access_userdate'), 403);
        log_custom("Buka menu user tanggal");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        
        $('#user-date').addClass('active');
        ";
        return $dataTable->render('user.userdate', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('access_userdate'), 403);
        $request->validate([
            'username' => 'required',
            'description' => 'required',
            'old' => 'gt:0'
        ]);
        $data = $request->all();
        $data['user_id'] = $data['username'];
        $data['date_start'] = Date("Y-m-d H:i:s");
        $lama = $data['old'];
        $tgl = strtotime("+$lama minutes", strtotime(date("Y-m-d H:i"))); // Tambahkan jam saat ini agar tau batas max 
        $data['date_end'] = Date("Y-m-d H:i:s", $tgl);
        unset($data['username'], $data['old']);
        UserDate::create($data);
        log_custom("Merubah tanggal user ", $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
