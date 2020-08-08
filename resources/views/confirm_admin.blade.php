@extends('layouts.app')

@include('layouts.partials.auto_assigner')
{{--@section('title', 'ATTINTION!')--}}

@push('scripts')
<script title="confirm">
	
	const l = function () {
			_z.for([...arguments], function (v, i) {
				console.trace(v, i);
			});
		},
		le = function () {
			_z.for([...arguments], function (v, i) {
				console.error(v, i);
			});
		},
		lw = function () {
			_z.for([...arguments], function (v, i) {
				console.warn(v, i);
			});
		};
	
	$(document).ready(function () {
		let $confirm;
		$confirm = jsMsg.confirm('{{$confirm['user']}}', '{{$confirm['message']??""}}');
		$confirm.super.button('cancel').text('{{__('No')}}');
		$confirm.super.button('confirm').text('{{__('Yes')}}');
		
		$confirm.super.show()
			.then(function (result) {
				jsMsg.static('{{__('Please Wait ...')}}', '{{__('Requesting ...')}}');
				
				return $.post({
					url: '{{route('twitter.confirm_admin')}}',
					data: {
						_method: 'PUT',
						csrf: '{{ csrf_token() }}',
						result: result
					}
				}).done(function (x) {
					if(x.error) {
						jsMsg.static('{{__('Please Wait ...')}}', '{{__('Requested Fail !')}}')
						.super.icon('error').timer(3000).show()
						.then(()=>{
							x.logout && document.getElementById('logout-form').submit();
							x.url && (location.href = x.url);
						});
					} else {
						jsMsg.static('{{__('Please Wait ...')}}', '{{__('Requested successfully !')}}')
						.super.icon('success').timer(2000).show()
						.then(() => {
							x.logout && document.getElementById('logout-form').submit();
							x.url && (location.href = x.url);
						});
					}
				}).fail(function () {
					le(arguments, this);
				});
			})
			.catch(function (result) {
				le(arguments);
			});
	
	});
</script>
@endpush

@section('content')
	<form id="logout-form" action="{{ route('twitter.logout') }}" method="POST" style="display: none;">@csrf</form>
@endsection
