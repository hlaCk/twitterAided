<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
    <a class="navbar-brand" href="{{route('home')}}">
	    {{ Auth::user()->twitter('name') }}
    </a>
	
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
	
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
	        @include('layouts.partials.navbar_item', [
	            'text'=>__('Home'),
	            'routeName'=>'home',
	            'class'=>'d-md-none',
	        ])
	        
            @guest
		        @include('layouts.partials.navbar_item', [
					'text'=>__('Login'),
					'routeName'=>'twitter.login',
				])
	        
	            @if (Route::has('register'))
			        @include('layouts.partials.navbar_item', [
						'text'=>__('Register'),
						'routeName'=>'register',
					])
	            @endif
	        @else
	            <li class="nav-item dropdown">
	                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		                    {{ AuthUser()->twitterName(config('app.name')) }} <span class="caret"></span>
	                </a>
	
	                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
	                    <a class="dropdown-item" href="{{ route('twitter.login', ['relogin'=>1]) }}">
	                        {{ __('Relogin (Switch)') }}
	                    </a>
	
	                    <a class="dropdown-item" href="{{ route('twitter.logout') }}" onclick="event.preventDefault();
		                                     document.getElementById('logout-form').submit();">
	                        {{ __('Logout') }}
	                    </a>
	
	                    <form id="logout-form" action="{{ route('twitter.logout') }}" method="POST" style="display: none;">
	                        @csrf
	                    </form>
	                </div>
	            </li>
		
		        @include('layouts.partials.navbar_item', [
					'text'=>__('Active Acc.'),
					'routeName'=>'accounts_list',
				])
	        
	            @admin
			        @include('layouts.partials.navbar_item', [
						'text'=>__('InActive Acc.'),
						'routeName'=>'disabled_accounts_list',
					])
		         
			        @include('layouts.partials.navbar_item', [
						'text'=>__('Limiter'),
						'routeName'=>'utl.limiter_index',
					])
		        @eif
	        
            @endguest
	        
	        {{--
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" href="#">Disabled</a>
            </li>--}}
	        
	        @auth
		        @include('layouts.partials.navbar_item', [
					'text'=>__('Toggle'),
					'a_class'=>' toggle-auto-hide bold text-danger',
				])
	        @endauth
        </ul>

        <form class="form-inline mt-2 mt-md-0 d-none">
            <input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>
    </div>
</nav>