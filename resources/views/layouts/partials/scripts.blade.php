{{--
	<script type="module" src="/js/jsModules/main.js">
		// allowed libs
		// [
		// 	'asd',
		// 	'mix'
		// ] && {}
	</script>
	
	<script type="module">
		
		function _______a() {
			import('/js/jsModules/lib.js')
				.then(x=>x.default)
				.then(function (lib) {
					let allMods = [
						// './js/jsModules/test.js',
						// './js/jsModules/lib.js',
						'./js/jsModules/mix.js',
						// './js/jsModules/fetch.js',
						// './js/jsModules/xfetch.js',
					];
					allMods.forEach(function (x) {
						this.lib = lib;
						return import(x)
							.then(function (module) {
								return module;
							})
							.catch(function ($e) {
								throw $e;
							});
					});
					console.warn(allMods);
				})
				.catch(function ($e) {
					throw $e;
				});
			window.module =
				(function (path) {
					return function (x) {
						if(x) {
							return lib(path + x);
						}
						
						return lib;
					};
				})('./js/jsModules/');
		}
	</script>
--}}

	<script src="{{ asset('js/underz/_z.js') }}"></script>
	<script src="{{ asset('js/helpers.js') }}"></script>

	<!-- declare lib -->
{{--	<script src="{{ asset('js/jsModules/declare.js') }}"></script>--}}
{{--	<script src="{{ asset('js/jsModules/xfetch.js') }}"></script>--}}
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    {{--<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>--}}
    <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/jsPlugins/factory.js') }}"></script>
    <script src="{{ asset('js/jsPlugins/jsMsg.js') }}"></script>
	{{--<script type="text/javascript" src="http://livejs.com/live.js"></script>--}}

{{--    <script src="{{ asset('js/jsPlugins/xfetch.js') }}"></script>--}}
	
	{{--
<style>
	.swal-overlay {
		background-color: rgba(43, 165, 137, 0.45);
	}
	
	.swal-title {
		margin: 0px;
		font-size: 16px;
		box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.21);
		margin-bottom: 28px;
	}
	
	.swal-footer {
		background-color: rgb(245, 248, 250);
		margin-top: 32px;
		border-top: 1px solid #E9EEF1;
		overflow: hidden;
	}
	
</style>
	--}}
    <!-- sweetalert -->
	{{--<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>--}}
    <script src="{{asset('js/sweetalert.min.js')}}"></script>
    {{--<script src="{{asset('js/sweetalert.js')}}"></script>--}}
    <!-- sweetalert -->
	
	<!-- Scripts -->
    {{--<script src="{{ asset('js/app.js') }}" defer></script>--}}


<script>
	const imgAjaxLoading = "<img src='{{asset("img/ajax-loader.gif")}}' class='ajax-loading'>",
    toggleAutoHides = function (e) {
    	e.preventDefault();
    	$('.toggle-auto-hide').toggleClass(['bold', 'text-danger', 'data-is-toggled']);
    	
    	if($(this).is('.toggle-auto-hide')) {
		    _z.cookie.set('toggle-auto-hide', !$(this).hasClass('data-is-toggled'));
	    }
	    
	    return autoHidesEvent.call($('.has-auto-hide'));
    },
    autoHidesEvent = function () {
        return $('.allow-2-hide:not(.ignore-hide), .allow-2-show:not(.ignore-hide)', this).toggle();
    };
    
	$(document).ready(function () {
		
		$('.has-auto-hide')
			.dblclick(toggleAutoHides)
			.dblclick()
			.find('.allow-2-show:not(.ignore-hide)')
			.toggle();
		
		let toggleAutoHideBtn = $('.toggle-auto-hide');
		toggleAutoHideBtn.click(toggleAutoHides);
		
		if(_z.cookie.get('toggle-auto-hide'))
			toggleAutoHideBtn.click();
		
		let ajaxSetup = {
			headers: {
				// '_token': '{{ csrf_token() }}'
				'X-CSRF-TOKEN': '{{ csrf_token() }}'
				// $('meta[name="csrf-token"]').attr('content')
			},
			
			
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
				Language: $('html').attr('lang')
			},
			beforeSend: function () {
				// show ajaxer loading
			},
			complete: function () {
				// hide ajaxer loading
			},
			jsonp: "callback",
			jsonpCallback: function () {
				var callback = oldCallbacks.pop() || (jQuery.expando + "_" + (nonce++));
				this[callback] = true;
				return callback;
			}
			
		};
		
		$.ajaxSetup(ajaxSetup);
		_z.ajaxSetup(ajaxSetup);
	});

	$.fn.formatText = function (data, attr) {
		attr = attr || $.fn.formatText.attrName;
		let format = $(this).attr(attr);
		
		if(format === undefined) return false;
		
		return format && format.replace(/[\[](\w+)[\]]/gi, (x, y) => {
			return y in data && data[y] || y;
		}) || "";
	};
	$.fn.setFormatedText = function (data, attr) {
		attr = attr || $.fn.formatText.attrName;
		let format = $(this).attr(attr);
		let field = $(this).attr('tfield') || false;
		
		// console.log(format, $(this), data);
		if(format === undefined)
			format = '['+ field+']';
		
		if(format === undefined) {
			if(field) {
				$(this).html(field in data ? data[field] : "");
			} else {
				console.error("cannot set formated or unformated. data: ", data, " element: ", $(this));
				return false;
			}
		}
		
		let $text = format && format.replace(/[\[](\w+)[\]]/gi, (x, y) => {
			return y in data && data[y] || y;
		}) || "";
		
		$(this).html($text);
		return $(this);
	};
	$.fn.formatText.attrName = 'tformat';
	_z.cachable = function () {
		// [cachable]
		let $Cachables = _z('[cachables]:not([cachables=""])');
		return $Cachables.add(
				...$Cachables.for(function (i, e) {
					e = _z(e);
					let cachables = e.attr('cachables') || "";

					if(cachables) {
						$return = e.find(cachables);
					} else {
						$return = e.children();
					}

					return _z.Array($return);
				})
		)
				.addThis(
					_z('[cachable]:not([not-cachable])')
				)
				.filter(':not([not-cachable])');

		// return _z('[cachable="true"]').cache();
	};
	_z.$.cache = function () {
		let elms = this.not('[cached="true"]').for(function(i, e) {
			e = _z(e);
			let $eText = e.text(),
					$eHtml = e.html(),
					$eData = _z.extend(e.data('cache') || {}, {
								'text': $eText,
						'html': $eHtml,
					});

			e.data('cache', $eData);
			e.un('.cachable')
					.on('applyCachedData.cachable', function () {
						_z(this).html($eHtml);
					});

			return e.attr('cached', true)[0];
		});

		return _z(elms);
	};

	$(document).ready(function () {
		_z.cachable().cache();
	});

	const confirmFor = function confirmFor($callback, text = 'Please Confirm!', title = 'Are you sure?') {
		if(arguments.length === 1 && _z.isObject($callback)) {
			return jsMsg.confirm($callback);
			//.then(_z.isFunction($callback) ? $callback : (x) => x);
		}

		return jsMsg.confirm({
			callback: (x)=> x && $callback() || x,
			text: text,
			title: title,
			icon: jsMsg.icons.info
		})
			.then((x) => console.log(x))
			// .then((x) => x);
			// .then(_z.isFunction($callback) ? $callback : (x) => console.log(x))
		;
	};
	
	const aO = function aO($var, $v=undefined) {
		if(this === window) return aO.call({}, $var, $v);
		$V = ($val)=>{
			$val && (this[$var] = $val);
			return ($val && this) || this[$var];
		};
		return $v !== undefined ? $V($v) : $V;
	};
</script>
