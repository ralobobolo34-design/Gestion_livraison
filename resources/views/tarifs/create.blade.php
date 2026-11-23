@extends('layouts.app')

@section('titre', 'Nouveau tarif')

@section('contenu')
<div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form action="{{ route('tarifs.store') }}" method="POST">
        @csrf
        @include('tarifs._form')
    </form>
</div>
@endsection