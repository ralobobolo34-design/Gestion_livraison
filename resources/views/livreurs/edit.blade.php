@extends('layouts.app')

@section('titre', 'Modifier le livreur')

@section('contenu')
<div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form action="{{ route('livreurs.update', $livreur) }}" method="POST">
        @csrf
        @method('PUT')
        @include('livreurs._form')
    </form>
</div>
@endsection