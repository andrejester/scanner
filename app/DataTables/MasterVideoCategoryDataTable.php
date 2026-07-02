<?php

namespace App\DataTables;

use App\Models\Master\MasterVideoCategory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MasterVideoCategoryDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.mastervideocategory.mastervideocategory_action', compact('data'));
            })
            ->addColumn('summary', function ($row) {
                return Str::limit(strip_tags($row->summary), 80, '...');
            })
            ->addColumn('photo', function ($row) {
                if ($row->photo) {
                    $photoUrl = $row->photo;
                    if (! filter_var($photoUrl, FILTER_VALIDATE_URL)) {
                        $photoUrl = asset('storage/files/2/' . $photoUrl);
                    }
                    return '<img src="' . $photoUrl . '" width="80" class="rounded">';
                }
                return '-';
            })
            ->addColumn('status', function ($row) {
                $badgeClass = $row->status === 'active' ? 'bg-success' : 'bg-secondary';
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
            })
            ->rawColumns(['action', 'photo', 'status'])
            ->setRowId('id');
    }

    public function query(MasterVideoCategory $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('mastervideocategory-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy([1, 'desc'])
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
            Column::make('title')->title('Nama Kategori'),
            Column::make('type')->title('Tipe'),
            Column::computed('summary')->title('Ringkasan'),
            Column::make('status')->title('Status'),
        ];
    }

    protected function filename(): string
    {
        return 'MasterVideoCategory_' . date('YmdHis');
    }
}
