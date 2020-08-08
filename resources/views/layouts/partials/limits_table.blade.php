@php
	$table_class = isset($table_class) ? $table_class : ' ';
@endphp

@if(isset($hasUser) && $hasUser)
	@include('layouts.partials.users_table', [
		'load_scripts'  =>false,
		'data'          =>[$user],
		'actions'       =>false,
		'autoHide'      =>false,
		'table_class'   =>' table-dark'
	])
	
	{{--
<div class="card text-white bg-dark mb-3" style="max-width: 18rem;">
  <div class="card-header">Header</div>
	
	<div class="card-body">
		<h5 class="card-title">Dark card title</h5>
		<p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
	</div>
</div>--}}
@endif


{{--table limits--}}
<table class="table text-center table-sm table-hover table-responsive-lg main-table has-auto-hide table-striped table-bordered {{$table_class}}">
	<thead>
		<tr>
			<th class="allow-2-hide" scope="col">#</th>
			<th class="allow-2-hide2" scope="col">Resource</th>
			<th scope="col">Limit</th>
			<th scope="col">Remaining</th>
			<th scope="col">Reset In</th>
			<th class="allow-2-hide" scope="col">
				<img class="clickable refreshAll" onclick="refreshAll.call(this);" src="{{asset('img/refresh_icon.gif')}}" width="16" height="16">
			</th>
		</tr>
	</thead>
	
	<tbody>
		@if(isset($data) && is_array($data))
			@foreach($data as $_id=>$col)
				@php
					$trClass = intval($col['remaining'])===0 ? ' suspended' : '';
				@endphp
				<tr rtype="{{$col['method']}}" class="{{$trClass}}">
					<th class="allow-2-hide" scope="row">{{$_id+1}}</th>
					<td tfield="familyKey" class="allow-2-hide2">{{$col['familyKey']}}</td>
					<td tfield="limit">{!! $col['limit'] !!}</td>
					<td tfield="remaining">{{$col['remaining']}}</td>
					<td tfield="reset" tformat="[reset] <b>|</b> [reset_s] sec.">{{$col['reset']}} <b>|</b> {{$col['reset_s']}} sec.</td>
					
					<td class="allow-2-hide">
						<img class="clickable refreshRow" onclick="refreshRow.call(this);" src="{{asset('img/refresh_icon.gif')}}" width="16" height="16" alt="{{$col['limit']}}/{{$col['remaining']}}" title="{{$col['limit']}}/{{$col['remaining']}}">
					</td>
				</tr>
			@endforeach
		@endif
	</tbody>
</table>

@if(!isset($load_scripts) || $load_scripts !== false)
	@include('layouts.partials.limits_table_scripts')
@endif