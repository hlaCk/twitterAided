@php
	$routeName = isset($routeName) ? $routeName : (isset($route) ? $route : '');
	$href = isset($href) ? $href : ($routeName ? (route($routeName)?:'') : 'javascript:void(0);');
	$class = isset($class) ? $class : '';
	$a_class = isset($a_class) ? $a_class : '';
	$text = isset($text) ? $text : '';
	
	$classActive = $routeName && currentRoute()->getName() == $routeName ? ' bold active' : '';
@endphp

<li class="nav-item {{$class}} {{$classActive}}">
    <a class="nav-link {{$a_class}}" href="{{$href}}">
        {!! $text !!}
    </a>
</li>
