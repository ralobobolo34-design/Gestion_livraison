@extends('layouts.app')

@section('titre', 'Modifier le tarif')

@section('contenu')
<div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form action="{{ route('tarifs.update', $tarif) }}" method="POST">
        @csrf
        @method('PUT')
        @include('tarifs._form')
    </form>
</div>
@endsection