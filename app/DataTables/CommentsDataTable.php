<?php

namespace App\DataTables;

use App\Models\Comments;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;

class CommentsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('backend.comments.comments_action', compact('data'));
            })
            ->addColumn('author', function ($row) {
                if ($row->user) {
                    return $row->user->name;
                }
                return $row->name ?? $row->email ?? 'Guest';
            })
            ->addColumn('content_type', function ($row) {
                if ($row->blog_id) return 'Blog';
                if ($row->video_id) return 'Video';
                return '-';
            })
            ->addColumn('content_title', function ($row) {
                if ($row->blog) return Str::limit($row->blog->title, 50);
                return '-';
            })
            ->addColumn('comment_short', function ($row) {
                return Str::limit(strip_tags($row->comment), 80, '...');
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'active'   => '<span class="badge bg-success">Active</span>',
                    'inactive' => '<span class="badge bg-secondary">Inactive</span>',
                    'pending'  => '<span class="badge bg-warning">Pending</span>',
                ];
                return $badges[$row->status] ?? $row->status;
            })
            ->rawColumns(['action', 'status_badge'])
            ->setRowId('id');
    }

    public function query(Comments $model): QueryBuilder
    {
        return $model->newQuery()->with(['user', 'blog'])->whereNull('parent_id')->orderBy('id', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('comments-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy([1, 'desc'])
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
            Column::make('id')->title('ID')->width(60),
            Column::make('author')->title('Author')->width(150),
            Column::make('content_type')->title('Type')->width(80),
            Column::make('content_title')->title('Content')->width(200),
            Column::make('comment_short')->title('Comment'),
            Column::make('status_badge')->title('Status')->width(100),
            Column::make('created_at')->title('Date')->width(150),
        ];
    }

    protected function filename(): string
    {
        return 'Comments_' . date('YmdHis');
    }
}
