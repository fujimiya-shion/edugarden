@extends('layout.clients.main')

@section('content')
    <div id="staticPage">
        <div class="container py-5">
            @if ($page->image)
                <img src="{{ Storage::url($page->image) }}" alt="{{ $page->title }}" width="100%" style="aspect-ratio: 21/9;" class="rounded rounded-3">
            @endif
            <h2 class="text-color fw-600 @if ($page->image) mt-3 @endif">{{ $page->title }}</h2>
            <div class="mt-3">
                {!! $page->content !!}
            </div>
        </div>
    </div>
@endsection