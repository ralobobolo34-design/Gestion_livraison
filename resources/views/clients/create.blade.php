@extends('layouts.app')

@section('titre', 'Nouveau client')

@section('contenu')
<div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form action="{{ route('clients.store') }}" method="POST">
        @csrf
        @include('clients._form')
    </form>
</div>
@endsection