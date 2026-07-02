<?php

namespace App\DataTables;

use App\Models\Backend\Video;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VideoDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))

            ->addColumn('kategori.title', function ($row) {
                return $row->kategori ? $row->kategori->title : 'N/A';
            })

            ->addColumn('action', function ($data) {
                return view('backend.video.video_action', compact('data'));
            })

            ->rawColumns(["action"])
            ->setRowId('id');
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Video $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('id', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('video-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy([1, 'asc'])
            ->parameters([
                'responsive' => true,
                'buttons' => ['pdf'],
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
            Column::make('title')->title("Judul"),

            Column::make('kategori.title')
                ->title("Kategori")
                ->data('kategori.title'),

            Column::make('tanggal')->title("tanggal"),
            Column::make('status')->title("Status"),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Video_' . date('YmdHis');
    }
}
