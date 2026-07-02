<?php

namespace App\DataTables;


use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

use Yajra\DataTables\Html\ButtonGroup;

class RolePermissionDataTable extends DataTable
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
                return view('user.permission_action', compact('data'));
            })
            ->addColumn('permissions', function ($data) {
                return view('user.permission_data', [
                    'data' => $data
                ]);
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Role $model): QueryBuilder
    {
        return $model->newQuery()->with(['permissions' => function ($query) {
            $query->select('name')->take(10)->get();
        }])->where('name', '!=', 'Admin');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {

        return $this->builder()
            ->setTableId('formdatatable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom("<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4'f>> .
                                    'tr' .
                                    <'row'<'col-md-5'i><'col-md-7 mt-2'p>>")
            ->buttons([
                Button::make('copy')
                    ->addClass("btn btn-sm btn-primary mt-2 mr-2")
                    ->text('<i class="bx bx-copy"></i> Copy'),
                Button::make('excel')
                    ->addClass("btn btn-sm btn-primary mt-2")
                    ->text('<i class="bx bx-copy"></i> Excel'),
            ])


            // ->buttons([

            //     [
            //         'extend' => 'copy',
            //         'className' => 'btn btn-sm btn-primary mt-1' // Add custom class for export button
            //     ],
            // ])
            // ->initComplete('function(settings, json) {
            //     // Remove default classes
            //     $(".copy").removeClass("buttons-create");
            // }')
            ->orderBy(2);
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
                ->addClass('text-center')
                ->addClass('align-middle'),

            Column::make('name')
                ->addClass('text-center')
                ->addClass('align-middle'),

            Column::computed('permissions')
                ->addClass('text-center')
                ->addClass('align-middle'),

        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'RolePermission_' . date('YmdHis');
    }
}
