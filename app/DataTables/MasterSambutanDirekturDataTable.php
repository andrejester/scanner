<?php

namespace App\DataTables;

use App\Models\Master\MasterSambutanDirektur;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;

class MasterSambutanDirekturDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.mastersambutandirektur.mastersambutandirektur_action', compact('data'));
            })
            ->addColumn('sambutan', function ($row) {
                return Str::limit(strip_tags($row->sambutan), 80, '...');
            })
            ->addColumn('foto', function ($row) {
                if ($row->foto) {
                    return '<img src="' . asset('storage/files/2/' . $row->foto) . '" width="60" class="rounded">';
                }
                return '-';
            })
            ->rawColumns(['action', 'foto'])
            ->setRowId('id');
    }

    public function query(MasterSambutanDirektur $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('id', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('mastersambutandirektur-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy([1, 'asc'])
            ->parameters([
                'responsive' => true,
            ])
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
            Column::make('nama_direktur')->title('Nama Direktur')->width(200),
            Column::make('jabatan')->title('Jabatan')->width(180),
            Column::make('sambutan')->title('Sambutan'),
            Column::make('foto')->title('Foto')->width(80),
            Column::make('is_active')->title('Status')->width(80),
        ];
    }

    protected function filename(): string
    {
        return 'MasterSambutanDirektur_' . date('YmdHis');
    }
}
