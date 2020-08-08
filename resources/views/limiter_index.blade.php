@extends('layouts.app')

@include('layouts.partials.auto_assigner')
{{--@section('title', 'Limit')--}}
{{--@section('extra_title', ($user && $user['t_name'] ? " {$user['t_name']}" : ''))--}}

@section('content')
	
	@include('layouts.partials.limits_table', [ 'load_scripts'=>true, 'data'=>$data ])
	
@endsection
