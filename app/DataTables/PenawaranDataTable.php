<?php

namespace App\DataTables;

use App\Models\Backend\Penawaran;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PenawaranDataTable extends DataTable
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
                return view('backend.penawaran.penawaran_action', compact('data'));
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at : '<strong> Unread </strong>';
            })

            ->rawColumns(['action', 'created_at'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Penawaran $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('id', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('penawaran-table')
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
                ->width(60)
                ->addClass('text-center'),
            Column::make('nama_lengkap')->title("Nama"),
            Column::make('kota_domisili')->title("Kota"),
            Column::make('no_whatsapp')->title("Whatsapp"),
            Column::make('created_at')->title("Tanggal"),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'penawaran_' . date('YmdHis');
    }
}
