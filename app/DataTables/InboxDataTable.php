<?php

namespace App\DataTables;

use App\Models\Backend\Inbox;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;

class InboxDataTable extends DataTable
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
                return view('backend.inbox.inbox_action', compact('data'));
            })

            ->editColumn('read_at', function ($row) {
                return $row->read_at ? $row->read_at : '<strong> Unread </strong>';
            })

            ->rawColumns(['action', 'read_at'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Inbox $model): QueryBuilder
    {
        return $model->newQuery();
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
            //->dom('Bfrtip')
            ->orderBy([0, 'desc'])
            ->parameters([
                'responsive' => true,

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
            Column::make('name')->title("Name"),
            Column::make('subject')->title("Subject"),
            Column::make('email')->title("email"),
            Column::make('message')->title("Message")
                ->format(function ($value) {
                    return Str::limit($value, 20, '...');
                }),
            Column::make('read_at')->title("Read")
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Inbox_' . date('YmdHis');
    }
}
