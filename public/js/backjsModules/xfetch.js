import { getExportedData, seek } from './seek.js';
import { mix } from './mix.js';
const moduleID = 'xfetch';

var {module, exports, controller} = getExportingVars(seek(moduleID));

(function (root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports.default = factory(root, exports);
	else if(typeof define === 'function' && define.amd)
		define(['root', 'exports'], factory);
	else if(typeof exports === 'object')
		exports = factory(root, exports);
		// return exports["xfetch"] = factory();
	else
		root.returnExports = factory(root, exports);
	
})(typeof self !== 'undefined' ? self : this, function (global, exports) {
	controller && controller.file('/js/jsModules/xfetch.js');
	// module.file = '/js/jsModules/xfetch.js';
	// module.requires = [];
	module.exportsMap = [
		'makeWarpper',
		'all',
		'del',
		'get',
		'patch',
		'post',
		'put'
	];
	
//8
	function check(resp) {
		var dataType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'json';
		
		var typeList = ['arrayBuffer', 'blob', 'formData', 'json', 'text'];
		var included = typeList.indexOf(dataType) !== -1;
		
		if(resp.ok && included) {
			return resp[dataType]().then(function (data) {
				let $Response = {};
				resp && resp.headers && resp.headers.forEach && resp.headers.forEach((v, x) => $Response[x] = v);
				
				return {
					data: data,
					status: resp.status,
					statusText: resp.statusText,
					headers: resp.headers,
					contentType: resp.headers.get('content-type'),
					responseHeaders: $Response
				};
			});
		}
		
		var errorMerssage = !included ? 'Invalid data type' : resp.status + ' - ' + resp.statusText + '.';
		
		throw new Error(errorMerssage);
	}

//7
	function transformParams(source) {
		var list = [];
		
		for (var key in source) {
			if({}.hasOwnProperty.call(source, key)) {
				list.push(encodeURIComponent(key) + '=' + encodeURIComponent(source[key]));
			}
		}
		
		return list.length ? '?' + list.join('&') : '';
	}

//6
	function createConfig(method) {
		var _ref = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
			body = _ref.body,
			_ref$cache = _ref.cache,
			cache = _ref$cache === undefined ? 'default' : _ref$cache,
			credentials = _ref.credentials,
			_ref$headers = _ref.headers,
			headers = _ref$headers === undefined ? {'Content-Type': 'application/json'} : _ref$headers,
			integrity = _ref.integrity,
			_ref$mode = _ref.mode,
			mode = _ref$mode === undefined ? 'cors' : _ref$mode,
			redirect = _ref.redirect,
			referrer = _ref.referrer,
			referrerPolicy = _ref.referrerPolicy;
		
		var data = void 0;
		
		if(body) {
			data = JSON.stringify(body);
		}
		
		var result = {
			body: data,
			cache: cache,
			credentials: credentials,
			headers: headers,
			method: method,
			mode: mode,
			redirect: redirect,
			referrer: referrer
		};
		
		if(integrity) {
			result.integrity = integrity;
		}
		
		if(referrerPolicy) {
			result.referrerPolicy = referrerPolicy;
		}
		
		return result;
	}

//3
	function request(method, url) {
		var req = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
		
		var paramsEncoded = '';
		if(req.params) {
			if(method.toLowerCase() == 'post') {
				if(req.body) {
					paramsEncoded = transformParams(req.params);
				} else {
					req.body = req.params;
					req.params = undefined;
				}
			} else {
				paramsEncoded = transformParams(req.params);
			}
		}
		
		var configObj = createConfig(method, req);
		console.warn(configObj);
		console.warn(req);
		var call = fetch(url + paramsEncoded, configObj);
		
		return req.raw ? call : call.then(function (resp) {
			return check(resp, req.dataType);
		});
	}
	
	function intializer(rqst) {
		function get(url, req) {
			return rqst('GET', url, req);
		}
		
		function post(url, req) {
			return rqst('POST', url, req);
		}
		
		function put(url, req) {
			return rqst('PUT', url, req);
		}
		
		function patch(url, req) {
			return rqst('PATCH', url, req);
		}
		
		function del(url, req) {
			return rqst('DELETE', url, req);
		}
		
		function all(promises) {
			return Promise.all(promises);
		}
		
		function wrapper() {
			var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
				_ref$config = _ref.config,
				config = _ref$config === undefined ? {} : _ref$config,
				_ref$callback = _ref.callback,
				callback = _ref$callback === undefined ? function (promise) {
					return promise;
				} : _ref$callback;
			
			var customRqst = function customRqst(method, url, req) {
				return request(method, url, mix({}, config, req)).then(callback);
			};
			
			return intializer(customRqst);
		}
		
		function makeWarpper(opt = {
			config: {},
			callback: x => x
		}) {
			return Object.keys(opt).length === 0 ? this : wrapper(opt);
		}
		
		let $props = {
			make: makeWarpper,
			
			all: all,
			
			del: del,
			get: get,
			patch: patch,
			post: post,
			put: put
		};
		
		window.fetch && mix(window.fetch || {}, $props);
		mix(makeWarpper || {}, $props);
		return makeWarpper;
	}

//0
	const xfetch = intializer(request);
	// exports.default = xfetch;
	
	exports.makeWarpper = xfetch.make;
	exports.all         = xfetch.all;
	exports.del         = xfetch.del;
	exports.get         = xfetch.get;
	exports.patch       = xfetch.patch;
	exports.post        = xfetch.post;
	exports.put         = xfetch.put;
	
	// export as default
	return xfetch;
});

/*

let tt=
(function (module, exports, seek) {
	if(typeof module === 'undefined')
		module = seek(moduleID);
	
	if(typeof module.exports === 'undefined')
		module.exports = {
			default: null,
		};
	
	if(typeof exports === 'undefined')
		exports = module.exports;
	
	mix(module.exports, exports);
	
	return module;
})(module, exports, seek);
*/

export const MODULE = module;
export { moduleID };
// export const { id, file, requires } = $module;
// export { exports } = module.controller.exportsMap();
export default exports.default;