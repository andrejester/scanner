<?php

namespace App\DataTables;

use App\Models\Backend\AlumniTmp;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AlumniTmpDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.alumnitmp.alumnitmp_action', compact('data'));
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    AlumniTmp::STATUS_PENDING => '<span class="badge bg-warning">Belum Diproses</span>',
                    AlumniTmp::STATUS_APPROVED => '<span class="badge bg-success">Disetujui</span>',
                    AlumniTmp::STATUS_REJECTED => '<span class="badge bg-danger">Ditolak</span>',
                ];
                return $badges[$row->status] ?? '<span class="badge bg-secondary">Unknown</span>';
            })
            ->addColumn('foto_preview', function ($row) {
                if ($row->foto) {
                    return '<img src="' . asset('storage/files/2/' . $row->foto) . '" alt="Foto" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">';
                }
                return '<span class="text-muted">-</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d/m/Y H:i');
            })
            ->editColumn('jenjang', function ($row) {
                return strtoupper($row->jenjang);
            })
            ->rawColumns(['action', 'status_badge', 'foto_preview'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Backend\AlumniTmp $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AlumniTmp $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('alumnitmp-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy([0, 'desc'])
            ->parameters(['responsive' => true])
            ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
            Column::computed('foto_preview')->title('Foto')->width(80)->orderable(false)->searchable(false),
            Column::make('nama')->title('Nama')->width(200),
            Column::make('nim')->title('NIM')->width(120),
            Column::make('jenjang')->title('Jenjang')->width(80),
            Column::make('program_studi')->title('Program Studi')->width(200),
            Column::make('tahun_lulus')->title('Tahun Lulus')->width(100),
            Column::computed('status_badge')->title('Status')->width(120),
            Column::make('created_at')->title('Tanggal Daftar')->width(150),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'AlumniTmp_' . date('YmdHis');
    }
}
