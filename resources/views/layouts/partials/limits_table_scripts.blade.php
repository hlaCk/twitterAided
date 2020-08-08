@push('scripts')
<script class="main-table-script">
	const
		ajaxingFor = function(elm, status) {
			let self = $(elm),
				cb = function(status) {
					return !status && $(self).removeClass('.ajaxing') || $(self).addClass('.ajaxing');
				};
			return arguments.length > 1 ? cb(status) : cb;
		},
		isAjaxing = function(status) {
			return !status && $(this).removeClass('.ajaxing') || $(this).addClass('.ajaxing');
		};

	var failedIdAttrName = 'tfield';
	var failedFormatAttrName = 'tformat';
	var rowIdAttrName = 'rtype';
	
	var refreshAll = ()=>$('.clickable.refreshRow').click();
	
	var refreshRow = function () {
		let parent = $(this).closest('tr'),
			parentID = parent.attr(rowIdAttrName);
		
		let changeThis = parent.find('['+ failedIdAttrName + ']');
		changeThis.each(function () {
			let field = _z($(this).get()).hasAttr(failedIdAttrName) || false;
			if(field) {
				$(this).html("<img src='{{asset("img/ajax-loader.gif")}}' class='ajax-loading'>");
			}
		});
		$(this).hide();
		let showImg = () => $(this).show();
		
		_z.post("{{route('utl.ajax.refresh_limiter')}}", {
				res: parentID,
				_token: '{{csrf_token()}}'
			}, function (x) {
				if(_z.isJson(x)) {
					let j = _z.parse.json(x);
					let resData;
					if(j.error || !(resData = j.data && j.data[0] || false)) {
						console.error("POSTJSON ERROR: ", j.data);
					} else {
						let $v;
						
						($v = resData['method'] || null) && parent.attr(rowIdAttrName, $v);
						
						parent.find('['+ failedIdAttrName + ']').each(function () {
							let $e = _z($(this).get());
							let field = $e.attr(failedIdAttrName) || false;
							// let fFormat = $e.attr(failedFormatAttrName) || false;
							
							if(field && _z.isset(resData[field])) {
								let resValue = resData[field];
								$($e).setFormatedText(resData);
								// if(fFormat) {
								// 	resValue = $($e).formatText(resData);
								// 	resValue = resValue === false ? resData[field] : resValue;
								// }
								
								// $(this).html(resValue)
							}
						});
					}
					showImg();
				}
			})
			.fail(function () {
				console.error("fail: POSTJSON ERROR: ", arguments);
				showImg();
			});
		
		return false;
	};
	
	var readFollowersRow = function () {
		let self = this;
		let isAjaxing = readFollowersRow.isAjaxing = ajaxingFor(self);

		if ($(self).hasClass('.ajaxing')) return false;

		let parent = $(self).closest('tr'),
				parentID = parent.attr('tid');

		isAjaxing(true);
		$(self).html(imgAjaxLoading);

		return _z.post("{{route('utl.ajax.read_followers.todo', [null])}}/" + parentID, {
			user_id: parentID,
			_token: '{{csrf_token()}}'
		}, function (x) {
			let resData = '-',
					j;

			j = _z.isObject(x) ? x : (
					_z.isJson(x) ? _z.parse.json(x) : false
			);

			if (j) {
				resData = j.data && j.data.count || false;
			} else {
				console.warn(x, resData);
			}

			isAjaxing(false).html(resData);

			if(j.error && (resData = j.message || false)) {
				jsMsg.error(resData, null, {callback: (x)=>x});
			}
		})
				.always(function () {
					isAjaxing(false);
				})
				.fail(function () {
					console.error("followers: POSTJSON AJAX FAIL, args: ", arguments);
					isAjaxing(false).html('-');
				});


	};

	var readFollowersRow1 = function () {
		let toggleAjaxing = (x) => {
			return x && $(this).removeClass('.ajaxing') || $(this).addClass('.ajaxing');
		};
		
		if($(this).hasClass('.ajaxing')) return false;
		
		let parent = $(this).closest('tr'),
			parentID = parent.attr('tid');
		
		
		toggleAjaxing(true);
		$(this).html("<img src='{{asset("img/ajax-loader.gif")}}' class='ajax-loading'>");
		
		_z.post("{{route('utl.ajax.read_followers')}}", {
				user_id: parentID,
				_token: '{{csrf_token()}}'
			}, function (x) {
				let resData = '-',
					j;
				
				if(_z.isJson(x)) {
					j = _z.parse.json(x);
					
					if(j.error || !(resData = j.data && j.data[0] || false)) {
						console.error("followers: POSTJSON DATA ERROR, Data: ", j.data);
					} else {
					}
				}
				
				toggleAjaxing(false).removeClass('.ajaxing').html(resData);
				
			})
			.fail(function () {
				console.error("followers: POSTJSON AJAX FAIL, args: ", arguments);
				toggleAjaxing().removeClass('.ajaxing').html('-');
				
				console.timeEnd("concatenation");
			});
		
		return false;
	};
	
	var readFriendsRow = function ($alert) {
		$alert = $alert || 0;
		
		let toggleAjaxing = (x) => {
			return x && $(this).removeClass('.ajaxing') || $(this).addClass('.ajaxing');
		};
		
		if($(this).hasClass('.ajaxing')) return false;
		
		if($alert) {
			if(!confirm('Are you sure?')) {
				return false;
			}
		}
		
		let parent = $(this).closest('tr'),
			parentID = parent.attr('tid');
		
		
		toggleAjaxing(true);
		$(this).html("<img src='{{asset("img/ajax-loader.gif")}}' class='ajax-loading'>");

// Start timing now
		console.time("concatenation");
		
		function endTiming() {
			console.timeEnd("concatenation");
		}
		
		_z.post("{{route('utl.ajax.read_friends')}}", {
				user_id: parentID,
				_token: '{{csrf_token()}}'
			})
			.done(function (x) {
				let resData = '-',
						j;
				console.dir(arguments);
				if(_z.isJson(x)) {
					j = _z.parse.json(x);

					if(j.error || !(resData = j.data && j.data[0] || false)) {
						console.error("friends: POSTJSON DATA ERROR, Data: ", j.data);
					} else {
					}
				}

				toggleAjaxing(false).removeClass('.ajaxing').html(resData);

				console.timeEnd("concatenation");
			})
			.fail(function () {
				console.error("friends: POSTJSON AJAX FAIL, args: ", arguments);
				toggleAjaxing().removeClass('.ajaxing').html('-');
				
				console.timeEnd("concatenation");
			});
		
		return false;
	};
	
	$(document).ready(function () {
		
		return;
		$('.main-table')
			.dblclick(()=>$('.allow-2-hide:not(.ignore-hide)').toggle())
			.dblclick();
	});
	
</script>
@endpush