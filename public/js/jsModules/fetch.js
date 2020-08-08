'use strict';
let mix = need('mix');


const xfetch = (function (global) {
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
            if(method.toLowerCase() === 'post') {
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
        // console.warn(configObj);
        // console.warn(req);
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
	    /**
	     * usage:
	     *      let { data: f } = await fetch.getJson("/js/jsModules/libs.json")
	     *
	     *
	     * @param url
	     * @param req
	     *
	     * @return {*}
	     */
	    $props['getJson'] = function (url, req = {}) {
		    return this.get(url, {...(req || {}),
			    ...{
		    	
			    dataType: 'json'
		    }});
	    };
	    /**
	     * usage:
	     *      let { data: f } = await fetch.postLaravel("/js/jsModules/libs.json")
	     *
	     *
	     * @param url
	     * @param req
	     *
	     * @return {*}
	     */
	    $props['postLaravel'] = function (url, req = {}) {
	    	return this.post(url, {
			    headers: {
				    "Content-Type": "application/json",
				    "Accept": "application/json, text-plain, */*",
				    "X-Requested-With": "XMLHttpRequest",
				    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			    },
			    method: 'post',
			    credentials: "same-origin",
			    body: {
				    name: 'name',
				    number: 'number'
			    },
		    raw: true, // return the raw output of fetch()
			 //    //   redirect: 'follow',
			    ...(req || {})
		    }).then(x=>x.json())
			    // .then(x=>{ console.log(x); return x; })
			    ;
	    };
	    
        window.fetch && mix(window.fetch || {}, $props);
        mix(makeWarpper || {}, $props);
        return makeWarpper;
    }

//0
    const xfetch = intializer(request);
	
	global.lib('fetch')
			.export('makeWarpper', xfetch.make)
			.export('all', xfetch.all)
			.export('del', xfetch.del)
			.export('get', xfetch.get)
			.export('getJson', xfetch.getJson)
			.export('postLaravel', xfetch.postLaravel)
			.export('patch', xfetch.patch)
			.export('post', xfetch.post)
			.export('put', xfetch.put)
				.default(xfetch);
	
    return xfetch;
})(typeof self !== 'undefined' ? self : this);

// console.warn(lib('fetch'));
/** Module System */
export let {
	$info: info,
	$exports: exports,
	$MODULE: MODULE
} = lib('fetch')
	.declared(true)
	// .default(mix)
	.file('fetch')
	.config(true);

/** Export Lib */
export default exports.default = xfetch;