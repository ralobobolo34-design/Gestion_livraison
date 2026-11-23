@extends('layouts.app')

@section('titre', 'Nouvelle zone')

@section('contenu')
<div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form action="{{ route('zones.store') }}" method="POST">
        @csrf
        @include('zones._form')
    </form>
</div>
@endsection