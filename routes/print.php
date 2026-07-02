<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('journal-pdf', function (Request $request) {

    $journal = $request->session()->get('journal');
    // return view('print.journal', ["data" => $journal]);
    $pdf = Pdf::loadView('print.journal', [
        'data' => $journal,
    ])->setPaper('a4', 'landscape');
    return $pdf->stream();
})->name('journal-pdf.index');


Route::get('member-pdf', function (Request $request) {

    $data = $request->session()->get('member');
    // return view('print.member', ["data" => $data]);
    $pdf = Pdf::loadView('print.member', [
        'data' => $data,
    ])->setPaper('a4', 'landscape');
    return $pdf->stream('member.pdf', ['Content-Disposition' => 'inline']);
})->name('member-pdf.index');

Route::get('loan-pdf', function (Request $request) {

    $data = $request->session()->get('loan');
    // return view('print.loan', ["data" => $data]);
    $pdf = Pdf::loadView('print.loan', [
        'data' => $data,
    ])->setPaper('a4', 'landscape');
    return $pdf->stream('loan.pdf', ['Content-Disposition' => 'inline']);
})->name('loan-pdf.index');


Route::get('balancesheet-pdf', function (Request $request) {

    $data = $request->session()->get('balancesheet');
    $pdf = Pdf::loadView('print.balancesheet', [
        'data' => $data,
    ])->setPaper('a4');
    return $pdf->stream('balancesheet.pdf', ['Content-Disposition' => 'inline']);
})->name('balancesheet-pdf.index');


Route::get('profitloss-pdf', function (Request $request) {

    $data = $request->session()->get('profitloss');
    $pdf = Pdf::loadView('print.profitloss', [
        'data' => $data,
    ])->setPaper('a4');
    return $pdf->stream('profitloss.pdf', ['Content-Disposition' => 'inline']);
})->name('profitloss-pdf.index');

Route::get('aset-pdf', function (Request $request) {

    $data = $request->session()->get('aset');
    // return view('print.aset', ["data" => $data]);
    $pdf = Pdf::loadView('print.aset', [
        'data' => $data,
    ])->setPaper('a4', 'landscape');
    return $pdf->stream('aset.pdf', ['Content-Disposition' => 'inline']);
})->name('aset-pdf.index');
