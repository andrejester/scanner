<?php

namespace App\DataTables;

use App\Models\Master\MasterPortofolio;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MasterGaleriDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.mastergaleri.mastergaleri_action', compact('data'));
            })
            ->addColumn('photo', function ($row) {
                if ($row->photo) {
                    return '<img src="' . asset('storage/files/2/' . $row->photo) . '" width="60" height="45" style="object-fit:cover;" class="rounded">';
                }
                return '-';
            })
            ->addColumn('aktif', function ($row) {
                return $row->aktif === 'Y'
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>';
            })
            ->addColumn('category', function ($row) {
                return $row->category?->title ?? '-';
            })
            ->rawColumns(['action', 'photo', 'aktif'])
            ->setRowId('id');
    }

    public function query(MasterPortofolio $model): QueryBuilder
    {
        return $model->newQuery()->with('category')->orderBy('id', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('mastergaleri-table')
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
            Column::make('photo')->title('Foto')->width(80),
            Column::make('title')->title('Judul'),
            Column::make('category')->title('Kategori')->width(160),
            Column::make('aktif')->title('Status')->width(100),
        ];
    }

    protected function filename(): string
    {
        return 'MasterGaleri_' . date('YmdHis');
    }
}
