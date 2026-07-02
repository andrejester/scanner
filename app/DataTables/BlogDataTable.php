<?php

namespace App\DataTables;

use App\Models\Master\MasterPost;
use App\Models\Master\MasterPostTag;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BlogDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.blog.blog_action', compact('data'));
            })

            ->editColumn('summary', function ($row) {
                return Str::words(strip_tags($row->summary), 20, '...');
            })

            ->addColumn('kategori', function ($row) {
                return $row->category ? $row->category->title : '—';
            })

            ->addColumn('thumbnail', function ($row) {
                if (!$row->photo) return '—';
                return '<img src="' . asset($row->photo) . '" width="100">';
            })

            ->addColumn('tags', function ($row) {
                if (!$row->tags) return '—';
                $tagIds = array_filter(explode(',', $row->tags));
                $titles = MasterPostTag::whereIn('id', $tagIds)->pluck('title')->toArray();
                return $titles ? implode(', ', $titles) : '—';
            })

            ->rawColumns(['action', 'thumbnail'])
            ->setRowId('id');
    }

    /**
     * Query source of dataTable
     */
    public function query(MasterPost $model): QueryBuilder
    {
        return $model->newQuery()->with(['category'])->orderBy('updated_at', 'desc');
    }

    /**
     * Optional HTML builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('blog-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy([1, 'asc'])
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'buttons' => ['pdf'],
            ]);
    }

    /**
     * DataTable Columns
     */
    public function getColumns(): array
    {
        return [
            Column::computed('action')
                ->width(60)
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),

            Column::make('title')->title("Judul")->width(300),
            Column::make('tags')->title("Tag")->width(200),

            Column::make('kategori')->title("Kategori")->data("kategori"),

            Column::make('thumbnail')->title("Foto")->exportable(false)->printable(false),

            Column::make('status')->title("Status"),
        ];
    }

    protected function filename(): string
    {
        return 'MasterPost_' . date('YmdHis');
    }
}
