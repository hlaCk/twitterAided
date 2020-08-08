@push('scripts')
	<script class="account-table-script">
	var $allRows,
		$TOClick = function () {
			setTimeout(function () {
				$( $allRows.pop() ).click();
				
				console.log($allRows.length);
				
				$allRows.length && $TOClick();
			}, _z.rnd(700, 2000));
		};
	
	var refreshAll = function () {
		$allRows = $('.account-table .clickable.refreshRow').get();
		
		setTimeout($TOClick, _z.rnd(700, 2000));
		return false;
	};
	
	var userLimitsPage = function (input) {
		input = input || false;
		
		let parent = $(this).closest('tr'),
			parentID = parent.attr('tid');
		
		if(input) {
			let userId = prompt("Enter User Id:", "{{AuthUser()->id}}");
			
			if(!userId) {
				return false;
			}
			
			parentID = userId;
		}
		
		if(parentID) {
			let w = window.open('{{route('utl.limiter_index')}}?user_id=' + parentID, 'limit_for_user_' + parentID);
		}
		
		return false;
	};
	
	var refreshRow = function () {
		let parent = $(this).closest('tr'),
			parentID = parent.attr('tid');
		
		let changeThis = parent.find('[tfield]');
		changeThis.each(function () {
			let field = _z($(this).get()).hasAttr('tfield') || false;
			if(field) {
				$(this).html("<img src='{{asset("img/ajax-loader.gif")}}' class='ajax-loading'>");
			}
		});
		$(this).hide();
		let showImg = () => $(this).show();
		
		_z.post("{{route('ajax.refresh_account')}}", {
				id: parentID,
				_token: '{{csrf_token()}}'
			}, function (x) {
				if(_z.isJson(x)) {
					let j = _z.parse.json(x);
					let resData;
					if(j.error || !(resData = j.data && j.data[0] || false)) {
						console.error("POSTJSON ERROR: ", j.data);
					} else {
						parent.find('[tfield]').each(function () {
							let field = _z($(this).get()).attr('tfield') || false;
							if(field && _z.isset(resData[field])) {
								$(this).setFormatedText(resData);//$(this).html(resData[field])
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
	
	var disableAll = function () {
		return $('.account-table .clickable.disableRow').click();
		
		location.reload();
		return false;
	};
	
	var disableRow = function ($alert) {
		$alert = $alert || 0;
		if($alert) {
			if(!confirm('Are you sure?')) {
				return false;
			}
		}
		let parent = $(this).closest('tr'),
			parentID = parent.attr('tid');
		
		let changeThis = parent.find('[tfield]');
		changeThis.each(function () {
			let field = _z($(this).get()).hasAttr('tfield') || false;
			if(field) {
				$(this).html("<img src='{{asset("img/ajax-loader.gif")}}' class='ajax-loading'>");
			}
		});
		$(this).hide();
		let showImg = () => $(this).show();
		
		_z.post("{{route('ajax.disable_account')}}", {
				id: parentID,
				_token: '{{csrf_token()}}'
			}, function (x) {
				if(_z.isJson(x)) {
					let j = _z.parse.json(x);
					let resData;
					if(j.error || ((resData = j.data) && j.data[0] || false)) {
						console.error("disable: POSTJSON ERROR with text: ", j);
						
						parent.find('[tfield]').each(function () {
							let field = _z($(this).get()).attr('tfield') || false;
							if(field && _z.isset(resData[field])) {
								$(this).setFormatedText(resData);//html(resData[field])
							}
						});
					} else {
						parent.remove();
					}
					showImg();
				}
			})
			.fail(function () {
				console.error("disable: POSTJSON ERROR: ", arguments);
				showImg();
			});
		
		return false;
	};
	
	var deleteAll = function () {
		return $('.account-table .clickable.deleteRow').click();
		
		location.reload();
		return false;
	};
	
	var deleteRow = function ($alert) {
		$alert = $alert || 0;
		if($alert) {
			if(!confirm('Are you sure?')) {
				return false;
			}
		}
		let parent = $(this).closest('tr'),
			parentID = parent.attr('tid');
		
		let changeThis = parent.find('[tfield]');
		changeThis.each(function () {
			let field = _z($(this).get()).hasAttr('tfield') || false;
			if(field) {
				$(this).html("<img src='{{asset("img/ajax-loader.gif")}}' class='ajax-loading'>");
			}
		});
		$(this).hide();
		let showImg = () => $(this).show();
		
		_z.post("{{route('ajax.delete_account')}}", {
				id: parentID,
				_token: '{{csrf_token()}}'
			}, function (x) {
				if(_z.isJson(x)) {
					let j = _z.parse.json(x);
					let resData;
					console.log(j);
					if(j.error || ((resData = j.data) && j.data[0] || false)) {
						console.error("delete: POSTJSON ERROR: ", j.data);
						
						parent.find('[tfield]').each(function () {
							let field = _z($(this).get()).attr('tfield') || false;
							if(field && _z.isset(resData[field])) {
								$(this).setFormatedText(resData); //$(this).html(resData[field])
							}
						});
					} else {
						parent.remove();
					}
					showImg();
				}
			})
			.fail(function () {
				console.error("delete: POSTJSON ERROR: ", arguments);
				showImg();
			});
		
		return false;
	};
	
	var readFollowersRow = function () {
		let toggleAjaxing = (x) =>{
			return x && $(this).removeClass('.ajaxing') || $(this).addClass('.ajaxing');
		};
		
		if($(this).hasClass('.ajaxing')) return false;
		
		let parent = $(this).closest('tr'),
			parentID = parent.attr('tid');
		
		toggleAjaxing(true);
		$(this).html(imgAjaxLoading);
		
		_z.post("{{route('utl.ajax.read_followers.todo', [null])}}/" + parentID, {
				user_id: parentID,
				_token: '{{csrf_token()}}'
			}, function (x) {
				let resData = '-',
					j;
				
				if(_z.isJson(x)) {
					j = _z.parse.json(x);
					
					if(j && (j.error || !(resData = j.data && j.data[0] || false))) {
						console.error("followers: POSTJSON DATA ERROR, Data: ", j.data);
						console.dir(j);
					} else {
						console.warn(resData);
					}
				}
				
				toggleAjaxing(false).removeClass('.ajaxing').html(resData);
			})
			.fail(function () {
				console.error("followers: POSTJSON AJAX FAIL, args: ", arguments);
				toggleAjaxing().removeClass('.ajaxing').html('-');
			});


{{--
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
					} else { }
				}
				
				toggleAjaxing(false).removeClass('.ajaxing').html(resData);
			})
			.fail(function () {
				console.error("followers: POSTJSON AJAX FAIL, args: ", arguments);
				toggleAjaxing().removeClass('.ajaxing').html('-');
			});
--}}

		return false;
	};
	
	var readFriendsRow = function ($alert) {
		$alert = $alert || 0;
		
		let toggleAjaxing = (x) =>{
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

		_z.post("{{route('utl.ajax.read_friends')}}", {
				user_id: parentID,
				_token: '{{csrf_token()}}'
			}, function (x) {
				let resData = '-',
					j;
				
				if(_z.isJson(x)) {
					j = _z.parse.json(x);
					
					if(j.error || !(resData = j.data && j.data[0] || false)) {
						console.error("friends: POSTJSON DATA ERROR, Data: ", j.data);
					} else { }
				}
				
				toggleAjaxing(false).removeClass('.ajaxing').html(resData);
			})
			.fail(function () {
				console.error("friends: POSTJSON AJAX FAIL, args: ", arguments);
				toggleAjaxing().removeClass('.ajaxing').html('-');
			});

		return false;
	};
	
	$(document).ready(function () {
		
		return;
		$('.account-table')
			.dblclick(function () {
				return $('.allow-2-hide:not(.ignore-hide), .allow-2-show:not(.ignore-hide)').toggle();
			})
			.dblclick()
			.find('.allow-2-show:not(.ignore-hide)')
			.toggle();
	});
</script>
@endpush