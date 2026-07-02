<?php

namespace App\DataTables;

use App\Models\Master\ProductAset;
use App\Models\Master\ProductLoan;
use App\Models\Master\ProductSaving;
use App\Models\Transaksi\Loan;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class   UserDateDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('transaksi.loan_action', compact('data'));
            })
            ->addColumn('member_code', function ($data) {
                return $data->member_code;
                return $data->member->name;
            })
            ->addColumn('loan_amount', function ($data) {
                return format_currency($data->loan_amount);
            })

            ->addColumn('date_open', function ($data) {
                return tanggalIndonesia($data->date_open);
            })

            ->rawColumns(["name", "action"])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Loan $model): QueryBuilder
    {
        return $model->newQuery()->with(['member']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('formdatatable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy([1, 'asc'])
            ->parameters([
                'responsive' => true,

            ])
            ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [

            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->addClass('align-middle'),
            Column::make('rekening')->title("rekening"),
            Column::make('date_open')->title("Tanggal"),
            Column::make('member_code')->title("Nama"),
            Column::make('loan_amount')->title("Plafond"),
            Column::make('loan_term')->title("Lama"),

        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'User_' . date('YmdHis');
    }
}
