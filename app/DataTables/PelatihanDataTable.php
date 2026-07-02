<?php

namespace App\DataTables;

use App\Models\Backend\Pelatihan;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PelatihanDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))

            ->addColumn('action', function ($data) {
                return view('backend.pelatihan.pelatihan_action', compact('data'));
            })

            ->editColumn('deskripsi_singkat', function ($row) {
                return Str::words(strip_tags($row->deskripsi_singkat), 20, '...');
            })

            ->addColumn('kategori', function ($row) {
                return $row->kategori ?? '—';
            })

            ->addColumn('thumbnail', function ($row) {
                if (!$row->thumbnail) return '—';
                return '<img src="' . asset($row->thumbnail) . '" width="100">';
            })

            ->rawColumns(['action', 'thumbnail'])
            ->setRowId('id');
    }


    /**
     * Query source of dataTable
     */
    public function query(Pelatihan $model): QueryBuilder
    {
        return $model->newQuery()
            ->orderBy('updated_at', 'desc');
    }


    /**
     * Optional HTML builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('pelatihan-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy([1, 'asc'])
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'buttons' => ['pdf'],
            ]);
    }

    /**
     * DataTable Columns
     */
    public function getColumns(): array
    {
        return [
            Column::computed('action')
                ->width(60)
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),

            Column::make('nama_pelatihan')->title("Nama Pelatihan")->width(300),

            Column::make('deskripsi_singkat')->title("Deskripsi Singkat")->width(300),

            Column::make('kategori')->title("Kategori")->data("kategori"),

            Column::make('thumbnail')->title("Thumbnail")
                ->exportable(false)
                ->printable(false),

            Column::make('status')->title("Status"),

            Column::make('tanggal_mulai')->title("Mulai"),

            Column::make('tanggal_selesai')->title("Selesai"),
        ];
    }


    protected function filename(): string
    {
        return 'Pelatihan_' . date('YmdHis');
    }
}
