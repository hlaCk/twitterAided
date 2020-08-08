@php
	$hasAutoHideClass = (isset($autoHide) && $autoHide === false) ? '' : 'has-auto-hide';
	$autoHide = (isset($autoHide) && $autoHide === false) ? 'ignore-hide' : '';
	$table_class = isset($table_class) ? $table_class : ' ';
@endphp

<table class="table table-sm text-center table-hover table-responsive-lg account-table table-bordered table-striped {{$hasAutoHideClass}} {{$table_class}}">
	<thead>
		<tr>
			<th class="allow-2-show {{$autoHide}}" scope="col">#</th>
			<th class="allow-2-hide {{$autoHide}}" scope="col">Id</th>
		@admin
			<th class="allow-2-hide {{$autoHide}}" scope="col">TID</th>
		@endadmin
			
			<th scope="col">Image</th>
			<th scope="col">Name</th>
			<th scope="col">Followers</th>
			<th scope="col">Following</th>
			
		@admin
			<th class="allow-2-hide {{$autoHide}}" scope="col">Last Update</th>
			<th class="allow-2-hide {{$autoHide}}" scope="col">Created At</th>
			
			@if(!isset($actions) || $actions === true)
				<th class="allow-2-hide" scope="col">
					<img class="clickable" onclick="userLimitsPage.call(this, !0);" src="{{asset('img/list-add.png')}}" width="16" height="16">
				</th>
			
				<th class="allow-2-hide" scope="col">
					<img class="clickable" onclick="confirm('Are you sure?') && refreshAll.call(this, !0);" src="{{asset('img/refresh_icon.gif')}}" width="16" height="16">
				</th>
			
				<th class="allow-2-hide {{$autoHide}}" scope="col">
					<img class="clickable" onclick="confirm('Are you sure?') && disableAll.call(this, !0);" src="{{asset('img/disable.png')}}" width="16" height="16">
				</th>
			
				<th class="allow-2-hide {{$autoHide}}" scope="col">
					<img class="clickable" onclick="confirm('Are you sure?') && deleteAll.call(this, !0);" src="{{asset('img/delete.png')}}" width="16" height="16">
				</th>
			@endif
		@endadmin
		
		</tr>
	</thead>
	
	<tbody>
		@if(isset($data) && is_array($data))
			@foreach($data as $_id=>$user)
				@php
					$trClass = $user['t_is_suspended'] === "Y" ? ' suspended' : '';
					$trClass .= !User($user['id'])->isActive() ? ' inactive' : '';
				@endphp
				<tr tid="{{$user['id']}}" class="{{$trClass}}" cachables="[tfield], th, td">
					<th nowrap class="allow-2-show {{$autoHide}}" scope="row" not-cachable>{{$_id+1}}</th>
					
<!-- region IDS -->
					<td nowrap class="allow-2-hide {{$autoHide}}" tfield="id" cachable>{{$user['id']}}</td>
				@admin
					<td nowrap class="allow-2-hide {{$autoHide}}" tfield="t_id" not-cachable>{{$user['t_id']}}</td>
				@endadmin
<!-- endregion IDS -->
					
					
					<td nowrap tfield="t_image" cachable>{!! $user['t_image'] !!}</td>
					<td nowrap tfield="t_screen_name" tformat="<b>[t_name]</b> [[t_screen_name]]" cachable>
						<b>{{$user['t_name']}}</b> [{!! $user['t_screen_name'] !!}]
					</td>
					
					
<!-- region followers/friends -->
					<td nowrap tfield="t_followers_count" cachable>
					@admin
						<label class="clickable readFollowersRow text-danger" onclick="confirmFor(readFollowersRow.bind(this));">
					@endadmin
							{{$user['t_followers_count']}}
					@admin
						</label>
					@endadmin
					</td>
					
					<td nowrap tfield="t_following_count" cachable>
					@admin
						<label class="clickable readFriendsRow text-danger" onclick="readFriendsRow.call(this, 1);">
					@endadmin
							{{$user['t_following_count']}}
					@admin
						</label>
					@endadmin
					</td>
<!-- endregion followers/friends -->
					

<!-- region status -->
				@admin
					<td nowrap class="allow-2-hide {{$autoHide}}" tfield="t_last_update" cachable>{{$user['t_last_update']}}</td>
					<td nowrap class="allow-2-hide {{$autoHide}}" tfield="t_created_at" cachable>{{$user['t_created_at']}}</td>
<!-- endregion status -->

					@php
						$actions = !isset($actions) ? true : $actions;
					@endphp
					
					@if(!isset($actions) || $actions === true)
						<td nowrap class="allow-2-hide">
							<img class="clickable userLimitsPage" onclick="userLimitsPage.call(this);" src="{{asset('img/list-add.png')}}" width="16" height="16" alt="{{$user['id']}}" title="{{$user['id']}}">
						</td>
					
						<td nowrap class="allow-2-hide">
							<img class="clickable refreshRow" onclick="refreshRow.call(this);" src="{{asset('img/refresh_icon.gif')}}" width="16" height="16" alt="{{$user['id']}}" title="{{$user['id']}}">
						</td>

						<td nowrap class="allow-2-hide {{$autoHide}}">
							<img class="clickable disableRow" onclick="disableRow.call(this, 1);" src="{{asset('img/disable.png')}}" width="16" height="16" alt="{{$user['id']}}" title="{{$user['id']}}">
						</td>
					
						<td nowrap class="allow-2-hide {{$autoHide}}">
							<img class="clickable deleteRow" onclick="deleteRow.call(this, 1);" src="{{asset('img/delete.png')}}" width="16" height="16" alt="{{$user['id']}}" title="{{$user['id']}}">
						</td>
					@endif
				@endadmin
				
				</tr>
			@endforeach
		@endif
	</tbody>
</table>

@if(!isset($load_scripts) || $load_scripts !== false)
	@include('layouts.partials.users_table_scripts')
@endif