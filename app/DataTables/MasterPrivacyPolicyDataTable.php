<?php

namespace App\DataTables;

use App\Models\Backend\MasterPrivacyPolicy;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class MasterPrivacyPolicyDataTable extends DataTable
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
                return view('backend.masterprivacypolicy.masterprivacypolicy_action', compact('data'));
            })
            ->rawColumns(["action"])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(MasterPrivacyPolicy $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('id', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('masterprivacypolicy-table')
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
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
            Column::make('title')->title("Keterangan"),
            Column::make('slug')->title("Slug"),
            Column::make('is_active')->title("Status"),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'masterprivacypolicy' . date('YmdHis');
    }
}
