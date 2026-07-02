<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;
use App\Models\Backend\Property;
use App\Models\Backend\Post_category;
use App\Models\Backend\Post_tag;


class PropertyDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))

            ->addColumn('action', function ($data) {
                return view('backend.property.property_action', compact('data'));
            })

            ->addColumn('cat_id', function ($row) {
                return $row->category ? $row->category->title : 'N/A';
            })
            ->addColumn('image', function ($row) {
                return asset($row->image);
            })
            ->addColumn('description', function ($row) {
                return Str::limit(strip_tags($row->description), 50, '...');
            })

            ->rawColumns(["action"])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Property $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('id', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('property-table')
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
            Column::make('name')->title("Title")->width(10),
            Column::make('cat_id')->title("Kategori")->width(10),
            Column::make('image')->title("Gambar"),
            Column::make('description')->title("Deskripsi")
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Property_' . date('YmdHis');
    }
}
