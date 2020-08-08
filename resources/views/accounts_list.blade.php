@extends('layouts.app')

@include('layouts.partials.auto_assigner')
{{--@section('title', $title)--}}

@section('content')

@include('layouts.partials.users_table', [ 'load_scripts'=>true, 'data'=>$data ])

@endsection
