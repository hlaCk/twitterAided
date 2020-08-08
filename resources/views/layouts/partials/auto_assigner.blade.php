@if(isset($title) && $title)
	@section('title', $title)
@endif

@if(isset($extra_title) && $extra_title)
	@section('extra_title', $extra_title)
@endif