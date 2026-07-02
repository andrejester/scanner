<?php

namespace App\DataTables;

use App\Models\Master\MasterVideo;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MasterVideoDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.mastervideo.mastervideo_action', compact('data'));
            })
            ->addColumn('category', function ($row) {
                return $row->category->title ?? '-';
            })
            ->addColumn('tanggal', function ($row) {
                return optional($row->tanggal)->format('d M Y');
            })
            ->addColumn('video', function ($row) {
                $sourceType = $row->source_type ?? 'banner';
                if ($sourceType === 'banner' && $row->video) {
                    return 'Banner: ' . basename($row->video);
                }
                if ($sourceType === 'youtube') {
                    return 'YouTube';
                }
                if ($sourceType === 'instagram') {
                    return 'Instagram';
                }
                if ($sourceType === 'tiktok') {
                    return 'TikTok';
                }
                if ($row->video) {
                    return basename($row->video);
                }
                if ($row->youtube) {
                    return 'Link Video';
                }
                return '-';
            })
            ->addColumn('status', function ($row) {
                $badgeClass = $row->status === 'active' ? 'bg-success' : 'bg-secondary';
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    public function query(MasterVideo $model): QueryBuilder
    {
        return $model->newQuery()->with('category')->orderBy('created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('mastervideo-table')
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
            Column::make('title')->title('Judul'),
            Column::computed('category')->title('Kategori'),
            Column::make('tanggal')->title('Tanggal'),
            Column::computed('video')->title('Video / YouTube'),
            Column::make('status')->title('Status'),
        ];
    }

    protected function filename(): string
    {
        return 'MasterVideo_' . date('YmdHis');
    }
}
