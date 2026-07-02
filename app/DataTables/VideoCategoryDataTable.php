<?php

namespace App\DataTables;

use App\Models\Backend\Video_category;
use App\Models\VideoCategory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class VideoCategoryDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $query->orderBy('id', 'desc');
        return (new EloquentDataTable($query))

            ->addColumn('action', function ($data) {
                return view('backend.videoCategory.videoCategory_action', compact('data'));
            })
            ->rawColumns(["action"])
            ->setRowId('id');
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Video_category $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('videocategory-table')
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
            Column::make('photo')->title("Foto"),
            Column::make('status')->title("Status"),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'videoCategory_' . date('YmdHis');
    }
}
