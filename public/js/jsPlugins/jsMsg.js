;(factory(this, (function () {
	'use strict';
	
	// swal methods:
	//      swal.close()
	//      swal.getState()
	//      swal.setActionValue({confirm: 'Text from input'})
	//      swal.stopLoading()
	
	const
		swalFunc = {
			close: function () {
				return swal && swal.close ? swal.close(...arguments) : undefined;
			},
			getState: function () {
				return swal && swal.close ? swal.getState(...arguments) : undefined;
			},
			setActionValue: function () {
				return swal && swal.close ? swal.setActionValue(...arguments) : undefined;
			},
			stopLoading: function () {
				return swal && swal.close ? swal.stopLoading(...arguments) : undefined;
			},
			
			// show swal
			show: function () {
				if(!(this instanceof jsMsg)) {
					return jsMsg.current && jsMsg.current.super.show() || swal();
				}
				
				jsMsg.current = swal(this.toData());
				jsMsg.current.super = this;
				
				if(this.callback()) jsMsg.current.then(this.callback());
				
				return jsMsg.current;
			},
		},
		
		extendMethod = function () {
		let extender = (_z && _z.extend) || ($ && $.extend) || (function (x) {
				throw new Error('extend function not found!');
			})();
		return extender(true, this, ...arguments);
	},
		
		/**
		 * Plugins Defaults
		 * @type {{name: {key: string}, button: {text: string}, data: {callback: (function(*=): *)}}}
		 */
		$defaults = {
			button: {
				data: {
					text: "OK"
				},
				name: {
					key: 'confirm'
				}
			},
			jsMsg: {
				data: {
					callback: function (isConfirm) {
						console.warn(isConfirm);
						return isConfirm;
					},
				},
			},
		};

// region Button
	/**
	 * Class Button
	 *
	 * @param x
	 *
	 * @return {*}
	 * @constructor
	 */
	const Button = function Button(x) {
		let $this;
		
		if(this instanceof Button) {
			$this = this;
			$this.extend({
				time: (new Date()).getTime(),
				data: extendMethod.call({}, $defaults.button.data),
			});
			$this.set({});
			
			let newName = this.generateName();
			
			/**
			 * check options if has name to set
			 */
			if(_z.isObject(x)) {
				let [xName, xRow] = Row(x, !0);
				
				/**
				 * case:
				 * x = {
				 *      KEY: {
				 *         name: 'NAME'
				 *      }
				 * }
				 */
				if(_z.isObject(xRow)) {
					xRow['name'] = _z.camelCase(xRow['name'] || xName);
					x = xRow;
					
				} else {
					/**
					 * has options
					 */
					if(_z.size(x)) {
						x[xName] = xRow;
					}
					/**
					 * case:
					 * x = {
					 *      NAME: TEXT
					 * }
					 */
					else {
						x = {
							name: _z.camelCase(xName),
							text: xRow
						};
					}
					
					/**
					 * assign random name
					 */
					x['name'] = x['name'] || newName;
				}
			}
			/**
			 * case:
			 * x = NAME
			 */
			else {
				x = {
					name: _z.camelCase(x),
					text: x,
				};
			}
			
			let $name = x && x['name'] || newName;
			$this.name($name);

			if(_z.isObject(x)) $this.state(x);
			
			return $this;
		}
		
		
		return new Button(...arguments);
	};
	
	Button.prototype = {
		Button: '1.0.0',
		constructor: Button,
		
		/** PROTOTYPE extends prototype */
		extend: extendMethod,
		
		/**
		 * new name generator
		 */
		generateName: function () {
			this.time = this.time || (new Date()).getTime();
			return `button_${this.time}`;
		},
	};
	/** CLASS extends class */
	Button.implement = extendMethod;
	/** CLASS extends prototype */
	Button.include = function (propName, propValue) {
		let props = {};
		if(arguments.length && _z.isString(propName))
			extendMethod.call(props, ...[{[propName]: propValue}, ...[...arguments].splice(2)]);
		
		extendMethod.call(Button.prototype, ...arguments);
		return this;
	};
	
// region Button props
	// DEFAULTS
	Button.implement({
		/**
		 * get/set defaults
		 *
		 * @return {Object}
		 */
		defaults: function () {
			return _z.extend(true, $defaults.button, {});
		},
	});
	
	// custom properties
	Button.include({
		/** access this.options */
		set: function (x, o = null) {
			if(!this.options) this.options = {};
			
			if(x && !_z.isObject(x)) {
				x = [{[x]: o || undefined}, ...[...arguments].splice(2)];
			} else {
				x = [...arguments];
			}
			
			if(x && _z.isArray(x) && x.length) this.extend.call(this.options, {}, ...x);
			
			return this;
		},
		
		/** access this.data */
		state: function (prop, val) {
			let argsLen = arguments.length;
			if(argsLen === 1 && _z.isString(prop) || !argsLen) {
				return (argsLen && prop in this.data && this.data[prop]) || (!argsLen && this.data) || undefined;
			} else if(argsLen >= 2) {
				if(!_z.isObject(prop)) {
					prop = extendMethod.call(...[{[prop]: val}, ...[...arguments].splice(2)]);
				} else prop = extendMethod.call(...arguments);
			}
			
			prop = prop && !_z.isObject(prop) && {[prop]: undefined} || prop;
			
			if(_z.isObject(prop))
				extendMethod.call(this.data, prop);
			
			return this;
		},
		
		/** access this.local_name */
		name: function (x = null) {
			this.local_name = this.local_name || _z.extend(true, {}, $defaults.button.name);
			
			if(x === null) {
				return this.local_name.key;
			}
			
			x = x && !_z.isObject(x) && {key: x} || x;
			this.extend.call(this.local_name, x);
			
			return this;//.name();
		},
	});
	// transforms
	Button.include({
		toButtonData: function () {
			return {[this.name()]: this.toData()};
		},
		toData: function () {
			return this.data;
		},
	});
	// object options
	Button.include({
		closeModal: function (x = undefined) {
			return (x === undefined) ? this.data.closeModal : this.state({closeModal: x});
		},
		className: function (x = undefined) {
			return (x === undefined) ? this.data.className : this.state({className: x});
		},
		visible: function (x = undefined) {
			return (x === undefined) ? this.data.visible : this.state({visible: x});
		},
		value: function (x = undefined) {
			return (x === undefined) ? this.data.value : this.state({value: x});
		},
		text: function (x = undefined) {
			return (x === undefined) ? this.data.text : this.state({text: x});
		},
	});
	// reconfigure
	Button.include({
		typeCancel: function (x = null) {
			x = x && !_z.isObject(x) && {text: x} || x;
			this
				.text("Cancel")
				.value(null)
				.visible(true)
				.className("")
				.closeModal(true);
			
			x && _z.isObject(x) && this.extend.call(this.data, x);
			
			return this;
		},
		typeConfirm: function (x = null) {
			x = x && !_z.isObject(x) && {text: x} || x;
			this
				.text("Confirm")
				.value(true)
				.visible(true)
				.className("")
				.closeModal(true);
			
			x && _z.isObject(x) && this.extend.call(this.data, x);
			
			return this;
		},
	});
// endregion Button props

// endregion Button

// region jsMsg
	/**
	 * Class jsMsg
	 *
	 * @param x
	 * @return {*}
	 */
	const jsMsg = function jsMsg(x) {
		let $this;
		
		if(this instanceof jsMsg) {
			$this = this;
			$this.extend({
				time: (new Date()).getTime(),
				data: extendMethod.call({}, $defaults.jsMsg.data),
			});
			
			$this.set({});
			$this.autoShow(false);
			x = x && !_z.isObject(x) && {text: x} || x;
			
			if(_z.isObject(x)) $this.state(x);
		} else {
			$this = new jsMsg(...arguments);
		}
		
		return $this;
	};
	jsMsg.prototype = {
		jsMsg: '1.0.0',
		constructor: jsMsg,
		
		/** PROTOTYPE extends prototype */
		extend: extendMethod,
	};
	/** CLASS extends class */
	jsMsg.implement = extendMethod;
	/** CLASS extends prototype */
	jsMsg.include = function (propName, propValue) {
		let props = {};
		if(arguments.length && _z.isString(propName))
			extendMethod.call(props, ...[{[propName]: propValue}, ...[...arguments].splice(2)]);
		
		extendMethod.call(jsMsg.prototype, ...arguments);
		return this;
	};

// region jsMsg props
	// DEFAULTS
	jsMsg.implement({
		/**
		 * get/set defaults
		 *
		 * @return {Object}
		 */
		defaults: function () {
			return _z.extend(true, $defaults.jsMsg, {});
		},
	});
	// swal
	jsMsg.implement(swalFunc);
	// icons
	jsMsg.implement({
		icons: {
			warning: "warning",
			error: "error",
			success: "success",
			info: "info",
		},
	});
	// instances
	jsMsg.implement({
		// standard message
		msg: function (text, title = null, options = {}) {
			const JM = jsMsg(text).autoShow(true);
			if(!arguments.length) return JM;
			
			title && JM.title(title);
			if(options && _z.isObject(options) && _z.size(options)) JM.state(options);
			
			return JM.show();
		},
		
		// xhr msg
		xhr: function (title = "Are you sure?") {
			swal({
				text: 'Search for a movie. e.g. "La La Land".',
				content: "input",
				button: {
					text: "Search!",
					closeModal: false,
				},
			})
				.then(name => {
					if(!name) throw null;
					
					return fetch('https://jsonplaceholder.typicode.com/posts', {
						method: 'POST',
						body: 'title=' + encodeURIComponent('My awesome new article') + '&body=' + encodeURIComponent('This is the text of my article'),
						header: {
							'Content-Type': 'application/json'
						},
						referrer: 'no-referrer'
					}).then(function (response) {
						
						// The API call was successful!
						if(response.ok) {
							return response.json();
						}
						
						// There was an error
						return Promise.reject(response);
						
					}).then(function (data) {
						// This is the JSON from our response
						console.log(data);
					}).catch(function (err) {
						// There was an error
						console.warn('Something went wrong.', err);
					});
				})
				.then(results => {
					return results.json();
				})
				.then(json => {
					const movie = json.results[0];
					
					if(!movie) {
						return swal("No movie was found!");
					}
					
					const name = movie.trackName;
					const imageURL = movie.artworkUrl100;
					
					swal({
						title: "Top result:",
						text: name,
						icon: imageURL,
					});
				})
				.catch(err => {
					if(err) {
						swal("Oh noes!", "The AJAX request failed!", "error");
					} else {
						swal.stopLoading();
						swal.close();
					}
				});
			
			return jsMsg(title).dangerMode(true).newButton(true).show();
			// swal(title, {
			// 	dangerMode: true,
			// 	buttons: true,
			// });
		},
		// confirm msg
		simpleConfirm: function (title = "Are you sure?") {
			return jsMsg(title).dangerMode(true).newButton(true).show();
			// swal(title, {
			// 	dangerMode: true,
			// 	buttons: true,
			// });
		},
		confirm: function (text, title = null, options = {}) {
			const JM = jsMsg(text).autoShow(true);
			if(!arguments.length) {
				return JM;
			}
			
			title && JM.title(title);
			let buttonsText = {
				confirm: {
					text: 'Confirm',
					value: 1,
				},
				cancel: {
					text: 'Cancel',
					visible: true,
					value: 0,
				},
			};
			if(options && _z.isObject(options) && options['buttons']) {
				_z.extend(true, buttonsText || {}, options['buttons'] || {});
				delete options['buttons'];
			}
			
			let singleButton;
			while (_z.size(buttonsText)) {
				singleButton = Row(buttonsText, !0);
				JM.button(Button({
					[singleButton[0]]: singleButton[1]
				}));
			}
			
			JM.closeOnClickOutside(false)
				.closeOnEsc(false)
				.dangerMode(true);
			
			if(options && _z.isObject(options) && _z.size(options)) {
				JM.state(options);
			}
			
			return JM.show();
		},
		
		// static msg
		static: function (text, title = null, options = {}) {
			const JM = jsMsg(text).autoShow(true);
			if(!arguments.length) {
				return JM;
			}
			
			title && JM.title(title);
			
			JM.closeOnClickOutside(false)
				.closeOnEsc(false)
				.newButton(false);
			
			if(options && _z.isObject(options) && _z.size(options)) {
				JM.state(options);
			}
			
			return JM.show();
		},
		
		// success msg
		success: function (text, title = null, options = {}) {
			return jsMsg.msg(text, title, _z.extend(true, {}, options || {}, {icon: jsMsg.icons.success}));
		},
		
		// error msg
		error: function (text, title = null, options = {}) {
			return jsMsg.msg(...arguments).super.error().show();
		},
		
		// info msg
		info: function (text, title = null, options = {}) {
			return jsMsg.msg(...arguments).super.info().show();
		},
		
		// warning msg
		warning: function (text, title = null, options = {}) {
			return jsMsg.msg(...arguments).super.warning().show();
		},
	});
	
	// custom props 
	jsMsg.include({
		autoShow: function (x) {
			this.extend({auto_show: !!x});
			return this;
		},
		
		/** access this.options */
		set: function (x, o = null) {
			if(!this.options) this.options = {};
			
			if(x && !_z.isObject(x)) {
				x = [{[x]: o || undefined}, ...[...arguments].splice(2)];
			} else {
				x = [...arguments];
			}
			
			if(x && _z.isArray(x) && x.length) this.extend.call(this.options, {}, ...x);
			
			return this;
		},
		
		/** access this.data */
		state: function (prop, val) {
			let argsLen = arguments.length;
			if(argsLen === 1 && _z.isString(prop) || !argsLen) {
				return (argsLen && prop in this.data && this.data[prop]) || (!argsLen && this.data) || undefined;
			} else if(argsLen >= 2) {
				if(!_z.isObject(prop)) {
					prop = extendMethod.call(...[{[prop]: val}, ...[...arguments].splice(2)]);
				} else prop = extendMethod.call(...arguments);
			}
			
			prop = prop && !_z.isObject(prop) && {[prop]: undefined} || prop;
			
			if(_z.isObject(prop))
				extendMethod.call(this.data, prop);
			
			return this;
		},
		
		/**
		 * convert class jsMsg to object for swal
		 *
		 * @param $data
		 *
		 * @return {*[]|*}
		 */
		toData: function ($data) {
			$data = $data || _z.clone(this.data);
			if($data['buttons'])
				$data.buttons = this.parseButtons() || {};
			
			if(_z.isBoolean($data)) return {};
			
			$data = $data && !(_z.isPlainObject($data) || _z.isArray($data)) ? [$data] : $data;
			
			if($data) {
				if($data.data && $data.data.buttons && $data.data.button) {
					delete $data.data.button;
				} else if($data && $data.buttons && $data.button && !_z.isFunction($data.button) && !_z.isFunction($data.buttons)) {
					delete $data.button;
				}
				
				$data.each(function (i, v) {
					if(v['buttons']) {
						v.buttons = this.parseButtons() || {};
					} else if(i === 'buttons') {
						v = this.parseButtons() || {};
					}
					
					$data[i] = v;
				}.bind(this));
			}
			
			if(!$data['title'] && $data['text']) {
				$data['title'] = $data['text'];
				delete $data['text'];
			}
			
			return $data;
		},
	});
	// icon
	jsMsg.include({
		icon: function (x) {
			if(x) return this.state({icon: x===true ? jsMsg.icons.success : x});
			else if(this.data.icon) delete this.data.icon;
			return this;
		},
		noIcon: function (x = true) {
			return this.icon(!x);
		},
		warning: function () {
			return this.icon('warning');
		},
		error: function () {
			return this.icon('error');
		},
		success: function () {
			return this.icon('success');
		},
		info: function () {
			return this.icon('info');
		},
	});
	// buttons
	jsMsg.include({
		/**
		 * parse this.data.buttons
		 *
		 * @param x
		 *
		 * @return {*}
		 */
		parseButtons: function (x = null) {
			if(!this['data']) {
				throw new Error(this);
			}
			if(this.data && _z.isBoolean(this.data['buttons']))
				return this.data['buttons'];
			
			let $data = _z.clone(x || this.data && this.data['buttons'] || {});
			
			_z.for($data, function (i, v) {
				$data[i] = {};
				v.toButtonData && ($data = _z.extend(true, $data, v.toButtonData()))
			});
			
			return $data;
		},
		
		/**
		 * Find button
		 *
		 * @param x
		 *
		 * @return {*}
		 */
		button: function (x = null) {
			// name to find by
			let xStr;
			
			/**
			 * x = new Button
			 */
			if(x && x instanceof Button) {
				xStr = x.name();
				x.name(xStr = (_z.camelCase(xStr || x.generateName())));
				
			}
			/**
			 * x = name & text
			 */
			else if(_z.isString(x)) {
				xStr = _z.camelCase(x);
				
			} else if(_z.isObject(x)) {
				/**
				 * x = object and no name
				 */
				if(!_z.hasProp(x, 'name')) {
					let [rowK, rowV] = _z.Row(x, true);
					
					/**
					 * for:
					 *      {
					 *          key: {
					 *              ...options
					 *          }
					 *      }
					 */
					if(_z.isObject(rowV)) {
						if(_z.hasVar(rowV, 'name')) {
							if(rowK === _z.lowerCase(rowV['name'])) {
								rowV['name'] = xStr = _z.camelCase(rowK);
							} else if(rowK === _z.camelCase(rowV['name'])) {
								xStr = rowV['name'];
							}
						} else {
							rowV['name'] = xStr = _z.camelCase(rowK);
						}
						x = rowV;
						
					}
					
					/**
					 * for:
					 *      {
					 *          key: name
					 *      }
					 */
					else if(_z.isString(rowV)){
						x = {
							name: xStr = _z.camelCase(rowK),
							text: rowV,
						};
						
					/**
					 * unknown
					 */
					} else {
						x[rowK] = rowV;
						x = Button(x);
						this.newButton(x);
						return x;
					}
				}
				/**
				 * x = object and has name
				 */
				else {
					x['name'] = xStr = _z.camelCase(x['name']);
				}
				
			} else return this;
			
			/**
			 * find old button
			 */
			if(xStr && this.data && this.data['buttons'] && this.data['buttons'][xStr]) {
				return this.data['buttons'][xStr];
			}
			
			/**
			 * create button
			 */
			this.newButton(x instanceof Button ? x : (x = Button(x)));
			
			/**
			 * return button
			 */
			return x;
		},
		
		/**
		 *
		 * @param $name
		 * @param $text
		 * @return {newButton}
		 */
		newButton: function ($name, $text = null) {
			$text = $text || $text !== false ? $text : false;
			let $btn;
			
			if(_z.isBoolean($name)) {
				this.data.buttons = $name;
				return this;
			} else if($name instanceof Button) {
				$btn = $name;
			} else {
				$btn = Button($name);
			}
			
			$text !== null && $btn.text($text);
			
			let $btns = {
				[$btn.name()]: $btn
			};
			this.data.buttons = (_z.isBoolean(this.data.buttons) ? {} : this.data.buttons) || {};
			this.data.buttons[$btn.name()] = $btn;
			
			// this.extend.call(this.data.buttons, $btns);
			return this;
		},
		
		/**
		 *
		 * @param $name
		 * @return {*}
		 */
		remButton: function ($name = null) {
			let $btn;
			if($name === null) {
				this.data.buttons = undefined;
				return this;
			} else if(_z.isBoolean($name)) {
				this.data.buttons = false;
				return this;
			}
			
			if($name) {
				if($name in this.data.buttons)
					delete this.data.buttons[$name];
			} else if(this.data.buttons) {
				delete this.data.buttons;
			}
			
			return this;
		},
	});
	// object options
	jsMsg.include({
		text: function (x = null) { return this.state(x === null ? 'text' : { text: x }); },
		title: function (x = null) { return this.state(x === null ? 'title' : {title: x}); },
		
		/**
		 * Add/remove {content: *}
		 *
		 * @param placeholder
		 * @param type
		 *
		 * @return {input}
		 */
		input: function (placeholder = "", type = "text") {
			if(placeholder === false) {
				let $data = {};
				this.data.each(function (i, v) {
					if(i !== 'content')
						$data[i] = v;
				});
				this.data = $data;
			} else {
				this.data.content = {
					element: "input",
					attributes: {
						placeholder: placeholder,
						type: type,
					},
				};
			}
			
			return this;
		},
		
		className: function (x = null) { return this.state(x === null ? 'className' : {className: x}); },
		
		closeOnClickOutside: function (x = null) { return this.state(x === null ? 'closeOnClickOutside' : {closeOnClickOutside: x}); },
		closeOnEsc: function (x = null) { return this.state(x === null ? 'closeOnEsc' : {closeOnEsc: x}); },
		dangerMode: function (x = null) { return this.state(x === null ? 'dangerMode' : {dangerMode: x}); },
		timer: function (x = null) { return this.state(x === null ? 'timer' : {timer: x}); },
		callback: function (x = null) { return this.state(x === null ? 'callback' : {callback: x}); },
		
	});
	// swal
	jsMsg.include(swalFunc);
// endregion jsMsg props

// endregion jsMsg
	
	return {
		jsMsg: jsMsg,
		Button: Button,
	};
})));