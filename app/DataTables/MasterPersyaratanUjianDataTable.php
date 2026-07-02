<?php

namespace App\DataTables;

use App\Models\Backend\MasterPersyaratanUjian;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;

class MasterPersyaratanUjianDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.masterpersyaratanujian.masterpersyaratanujian_action', compact('data'));
            })
            ->addColumn('deskripsi', function ($row) {
                return Str::limit(strip_tags($row->deskripsi), 80, '...');
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(MasterPersyaratanUjian $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('urutan')->orderBy('id', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('masterpersyaratanujian-table')
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
            Column::make('urutan')->title('No. Urut')->width(80),
            Column::make('judul')->title('Judul')->width(220),
            Column::make('jenis_ujian')->title('Jenis Ujian')->width(130),
            Column::make('jenjang')->title('Jenjang')->width(90),
            Column::make('deskripsi')->title('Deskripsi'),
            Column::make('status')->title('Status')->width(80),
        ];
    }

    protected function filename(): string
    {
        return 'MasterPersyaratanUjian_' . date('YmdHis');
    }
}
