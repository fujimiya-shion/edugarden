@extends('layout.clients.main')

@section('content')
    <div id="staticPage">
        <div class="container py-5">
            <img src="{{ Storage::url($page->image) }}" alt="{{ $page->title }}" style="aspect-ratio: 21/9;">
            <h2 class="text-color fw-600">{{ $page->title }}</h2>
            <div class="mt-3">
                {!! $page->content !!}
            </div>
        </div>
    </div>
@endsection