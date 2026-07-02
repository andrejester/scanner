<?php

namespace App\DataTables;

use App\Models\Backend\BannerAds;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BannerAdsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.banner-ads.banner_ads_action', compact('data'));
            })

            ->addColumn('image', function ($row) {

                if ($row->image) {
                    //dd($row->image);
                    return '<img src="' . asset('storage/files/2/' . $row->image) . '" width="50" class="rounded-circle">';
                }
                return '-';
            })

            // ->addColumn('image', function ($row) {
            //     if ($row->image) {
            //         $imageUrl = $row->image;
            //         if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            //             $imageUrl = asset('storage/files/2/' . $imageUrl);
            //         }
            //         return '<img src="' . $imageUrl . '" width="150" class="rounded">';
            //     }
            //     return '-';
            // })
            ->addColumn('position', function ($row) {
                $badgeMap = [
                    'left'       => 'bg-primary',
                    'right'      => 'bg-info',
                    'top'        => 'bg-warning',
                    'above_logo' => 'bg-success',
                ];
                $labelMap = [
                    'left'       => 'Kiri',
                    'right'      => 'Kanan',
                    'top'        => 'Atas',
                    'above_logo' => 'Atas Logo',
                ];
                $badgeClass = $badgeMap[$row->position] ?? 'bg-secondary';
                $label = $labelMap[$row->position] ?? ucfirst($row->position);
                return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
            })
            ->addColumn('is_active', function ($row) {
                $badgeClass = $row->is_active ? 'bg-success' : 'bg-secondary';
                $status = $row->is_active ? 'Aktif' : 'Tidak Aktif';
                return '<span class="badge ' . $badgeClass . '">' . $status . '</span>';
            })
            ->addColumn('link', function ($row) {
                if ($row->link) {
                    return '<a href="' . $row->link . '" target="_blank" class="text-primary"><i class="fas fa-external-link-alt"></i> Link</a>';
                }
                return '-';
            })
            ->rawColumns(['action', 'image', 'position', 'is_active', 'link'])
            ->setRowId('id');
    }

    public function query(BannerAds $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('position', 'asc')->orderBy('order', 'asc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('banner-ads-table')
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
            Column::make('image')->title('Gambar')->width(180),
            Column::make('title')->title('Judul')->width(200),
            Column::make('position')->title('Posisi')->width(100),
            Column::make('link')->title('Link')->width(100),
            Column::make('order')->title('Urutan')->width(80),
            Column::make('is_active')->title('Status')->width(100),
        ];
    }

    protected function filename(): string
    {
        return 'BannerAds_' . date('YmdHis');
    }
}
