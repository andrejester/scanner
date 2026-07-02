<?php

namespace App\DataTables;

use App\Models\Backend\Banner;
use App\Models\Master\MasterBanner;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;

class BannerDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.banner.banner_action', compact('data'));
            })
            ->addColumn('description', function ($row) {
                return Str::limit(strip_tags($row->description), 80, '...');
            })
            ->addColumn('photo', function ($row) {
                if ($row->photo) {
                    // Check if photo is external URL or local storage path
                    $photoUrl = $row->photo;
                    if (!filter_var($photoUrl, FILTER_VALIDATE_URL)) {
                        $photoUrl = asset('storage/files/2/' . $photoUrl);
                    }
                    return '<img src="' . $photoUrl . '" width="100" class="rounded">';
                }
                return '-';
            })
            ->addColumn('status', function ($row) {
                $badgeClass = $row->status == 'active' ? 'bg-success' : 'bg-secondary';
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
            })
            ->rawColumns(['action', 'photo', 'status'])
            ->setRowId('id');
    }

    public function query(MasterBanner $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('banner-table')
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
            Column::make('photo')->title('Foto')->width(120),
            Column::make('title')->title('Judul')->width(250),
            Column::make('description')->title('Deskripsi')->width(300),
            Column::make('status')->title('Status')->width(100),
        ];
    }

    protected function filename(): string
    {
        return 'Banner_' . date('YmdHis');
    }
}
