<?php

namespace App\DataTables;

use App\Models\Backend\MasterKategoriDownload;
use App\Models\Master\MasterDownloadCategory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MasterKategoriDownloadDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.masterkategoridownload.masterkategoridownload_action', compact('data'));
            })
            ->addColumn('jumlah_download', function ($row) {
                return $row->download_count;
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(MasterDownloadCategory $model): QueryBuilder
    {
        return $model->newQuery()->withCount('download')->orderBy('id', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('masterkategoridownload-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy([1, 'asc'])
            ->parameters(['responsive' => true])
            ->selectStyleSingle();
    }

    public function getColumns(): array
    {
        return [
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
            Column::make('nama')->title('Nama Kategori')->width(220),
            Column::make('jumlah_download')->title('Jumlah Download')->width(120),
            Column::make('is_active')->title('Status')->width(80),
        ];
    }

    protected function filename(): string
    {
        return 'MasterKategoriDownload_' . date('YmdHis');
    }
}
