@extends('layouts.app')

@section('titre', 'Modifier le client')

@section('contenu')
<div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form action="{{ route('clients.update', $client) }}" method="POST">
        @csrf
        @method('PUT')
        @include('clients._form')
    </form>
</div>
@endsection