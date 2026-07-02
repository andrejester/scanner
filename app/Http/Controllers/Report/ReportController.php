<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Transaksi\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function BannerReport()
    {

        abort_if(Gate::denies('banner_read'), 403);
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#banner-report').addClass('active');
        ";
        return view('report.banner', $data);
    }

    public function asetReport()
    {
        abort_if(Gate::denies('reports_read'), 403);
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#aset-report').addClass('active');
        $('#report').addClass('open active');
        ";
        return view('report.aset', $data);
    }

    public function journalReport()
    {
        abort_if(Gate::denies('journal_read'), 403);
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#journal-report').addClass('active');
        $('#akuntansi-report').addClass('open active');
        ";
        return view('report.journal', $data);
    }

    public function memberReport()
    {
        abort_if(Gate::denies('member_read'), 403);
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#member-report').addClass('active');
        ";
        return view('report.member', $data);
    }

    public function balancesheetReport()
    {
        abort_if(Gate::denies('balancesheet_read'), 403);
        $data = Template::get();
        $data['jsTambahan'] = "
        $('#balancesheet-report').addClass('active');

        $('#akuntansi-report').addClass('open active');
        ";
        return view('report.balancesheet', $data);
    }
    public function profitlossReport()
    {
        abort_if(Gate::denies('profitloss_read'), 403);
        $data = Template::get();
        $data['jsTambahan'] = "
        $('#profitloss-report').addClass('active');

        $('#akuntansi-report').addClass('open active');
        ";
        return view('report.profitloss', $data);
    }

    public function loanReport()
    {

        abort_if(Gate::denies('loan_read'), 403);
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#loan-report-bil').addClass('active');

        $('#loan-report').addClass('open active');
        ";
        return view('report.loan', $data);
    }
}
