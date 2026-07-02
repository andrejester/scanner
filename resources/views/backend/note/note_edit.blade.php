@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Note /</span> Edit
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
        <form class="card-body" action="{{ route('notes.update', $note->id) }}" method="POST">
            @csrf
            @method('PUT') <!-- Use PUT for update -->
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="message">Deskripsi</label>
                    <textarea class="form-control" id="message" name="message" rows="3">{{ $note->message }}</textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-label-secondary"></button>
                </div>

        </form>
    </div>
@endsection
