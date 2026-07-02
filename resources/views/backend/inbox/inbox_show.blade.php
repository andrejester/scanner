@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Inbox /</span> Show
    </h4>

    <div class="card mb-4">
        <form class="card-body" method="POST">
            @csrf
            @method('put')
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="Subject">Subject</label>
                    <input type="text" id="subject" class="form-control" name="subject" value="{{ $inbox->subject }}"
                        readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="Name">Name</label>
                    <input type="text" id="name" class="form-control" name="name" value="{{ $inbox->name }}"
                        readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="Email">Email</label>
                    <input type="text" id="emil" class="form-control" name="email" value="{{ $inbox->email }}"
                        readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="Phone">Phone</label>
                    <input type="text" id="phone" class="form-control" name="phone" value="{{ $inbox->phone }}"
                        readonly>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="message">message</label>
                    <textarea class="form-control" id="message" name="message" readonly>{{ $inbox->message }}</textarea>
                </div>

                <div class="pt-4">
                    <button type="button" class="btn btn-label-secondary" onclick="window.history.back();">
                        Back
                    </button>
                </div>

        </form>
    </div>
@endsection

@push('custom_js')
@endpush
