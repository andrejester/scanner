@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">FAQ /</span> Edit
    </h4>
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger" role="alert">
                <h6 class="alert-heading mb-1">Error</h6>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    @endif
    <div class="card mb-4">
        <form class="card-body" action="{{ route('masterfaq.update', $masterfaq->id) }}" method="POST">
            @csrf
            @method('put')
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="question">Question</label>
                    <input type="text" id="question" class="form-control" name="question"
                        value="{{ $masterfaq->question }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="answer">Answer</label>
                    <input type="text" id="answer" class="form-control" name="answer"
                        value="{{ $masterfaq->answer }}">
                </div>
                <div class="col mb-12">
                    <label class="form-check-label">Status</label>
                    <div class="col mt-2">
                        <select name="status" class="form-control" id="status">
                            <option value="active" {{ $masterfaq->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $masterfaq->status == 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-label-secondary"></button>
                </div>

        </form>
    </div>
@endsection
