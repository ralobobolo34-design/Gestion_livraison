@extends('layouts.app')

@section('titre', 'Modifier la zone')

@section('contenu')
<div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form action="{{ route('zones.update', $zone) }}" method="POST">
        @csrf
        @method('PUT')
        @include('zones._form')
    </form>
</div>
@endsection