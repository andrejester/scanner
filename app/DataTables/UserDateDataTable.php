<?php

namespace App\DataTables;

use App\Models\System\UserDate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UserDateDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('date_end', function ($data) {
                $carbonDate = Carbon::parse($data->date_end);
                $formattedDate = $carbonDate->format('Y-m-d H:i:s');
                return $formattedDate;
            })
            ->addColumn('date_start', function ($data) {
                $carbonDate = Carbon::parse($data->date_start);
                $formattedDate = $carbonDate->format('Y-m-d H:i:s');
                return $formattedDate;
            })
            ->addColumn('user_id', function ($data) {
                return $data->user->name;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserDate $model): QueryBuilder
    {
        return $model->newQuery()->with(['user']);
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
            ->orderBy([1, 'asc'])
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

            Column::make('id'),

            Column::make('user_id')->title("Username"),
            Column::make('date')->title("Tanggal"),
            Column::make('description')->title("Keterangan"),
            Column::make('date_start')->title("Waktu Mulai"),
            Column::make('date_end')->title("Waktu Habis"),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'UserDate_' . date('YmdHis');
    }
}
