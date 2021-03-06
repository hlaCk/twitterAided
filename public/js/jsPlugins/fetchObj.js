a = (function () {
	"use strict";
	
	function _extends() {
		return (_extends = Object.assign || function (e) {
			for (var t = 1; t < arguments.length; t++) {
				var n = arguments[t];
				for (var r in n) Object.prototype.hasOwnProperty.call(n, r) && (e[r] = n[r])
			}
			return e
		}).apply(this, arguments)
	}

// require("whatwg-fetch");
	var globalHeaders = {
			"Content-Type": "application/json"
		},
		globalOption = {
			headers: new Headers(globalHeaders),
			mode: "same-origin",
			credentials: "include",
			cache: "reload",
			redirect: "follow",
			timeout: 3e4,
			fetchStart: function (e) {
				return e
			}
		},
		mergeOptions = function () {
			for (var e = arguments.length, t = new Array(e), n = 0; n < e; n++) t[n] = arguments[n];
			var r = _extends.apply(void 0, [{}].concat(t)),
				o = _extends({}, globalHeaders, r.headers),
				c = null;
			return (c = _extends({}, globalOption, r)).headers = new Headers(o), {
				resultOptions: c,
				resultHealers: o
			}
		},
		setOptions = function (e) {
			globalOption = mergeOptions(e).resultOptions, globalHeaders = mergeOptions(e).resultHealers
		},
		parseJSON = function (n) {
			return n.text().then(function (t) {
				try {
					return JSON.parse(t)
				} catch (e) {
					throw new Error("JSON Parse Error: ".concat(e, ", URL: ").concat(n.url, ", ").concat(t.slice(0, 500)))
				}
			})
		},
		checkStatus = function (e) {
			if(200 <= e.status && e.status < 300 || 304 == e.status) return e;
			throw new Error("HTTP Status Code: ".concat(e.status, ", URL: ").concat(e.url))
		},
		setGetURL = function (e, t) {
			var n = 1 < arguments.length && void 0 !== t ? t : {};
			if("[object Object]" !== Object.prototype.toString.call(n) || 0 === Object.keys(n).length) return e;
			var r = [];
			for (var o in n) r.push(encodeURIComponent(o) + "=" + encodeURIComponent(n[o]));
			return e + (-1 === e.indexOf("?") ? "?" : "") + r.join("&")
		},
		getJSON = function (e, t, n) {
			var r = 1 < arguments.length && void 0 !== t ? t : {},
				o = mergeOptions({
					method: "GET"
				}, 2 < arguments.length && void 0 !== n ? n : {}).resultOptions,
				c = setGetURL(e, r);
			return _fetch(c, o).then(parseJSON).then(handleFetchPass, handleFetchError)
		},
		deleteJSON = function (e, t, n) {
			var r = 1 < arguments.length && void 0 !== t ? t : {},
				o = mergeOptions({
					method: "DELETE"
				}, 2 < arguments.length && void 0 !== n ? n : {}).resultOptions,
				c = setGetURL(e, r);
			return _fetch(c, o).then(parseJSON).then(handleFetchPass, handleFetchError)
		},
		postJSON = function (e, t, n) {
			var r = 1 < arguments.length && void 0 !== t ? t : {},
				o = 2 < arguments.length && void 0 !== n ? n : {},
				c = mergeOptions({
					method: "POST",
					body: JSON.stringify(r)
				}, o).resultOptions;
			return _fetch(e, c).then(parseJSON).then(handleFetchPass, handleFetchError)
		},
		putJSON = function (e, t, n) {
			var r = 1 < arguments.length && void 0 !== t ? t : {},
				o = 2 < arguments.length && void 0 !== n ? n : {},
				c = mergeOptions({
					method: "PUT",
					body: JSON.stringify(r)
				}, o).resultOptions;
			return _fetch(e, c).then(parseJSON).then(handleFetchPass, handleFetchError)
		},
		handleFetchPass = function (e) {
			return "function" == typeof globalOption.fetchSuccess && globalOption.fetchSuccess(e), e
		},
		handleFetchError = function (e) {
			throw "function" == typeof globalOption.fetchError && globalOption.fetchError(e), e = e instanceof Error ? e : new Error(e)
		},
		getJSONP = function (e, t, n) {
			var r = 1 < arguments.length && void 0 !== t ? t : {},
				o = 2 < arguments.length && void 0 !== n ? n : {},
				c = "jsonp" + +new Date,
				i = document.createElement("script");
			r[o.callbackName || "_callback"] = c;
			var s = setGetURL(e, r),
				a = document.head || document.querySelector("head") || document.documentElement;
			return i.setAttribute("src", s), i.setAttribute("charset", "utf-8"), i.setAttribute("defer", !0), i.setAttribute("async", !0), a.insertBefore(i, a.firstChild), new Promise(function (t, e) {
				window[c] = function (e) {
					t(e), a.removeChild(i)
				}, i.onerror = function () {
					e(), a.removeChild(i)
				}
			})
		},
		_fetch = function (n, c) {
			return new Promise(function (t, r) {
				var o = 0;
				Promise.resolve(c.fetchStart({
					url: n,
					fetchOption: c
				})).then(function (t) {
					if(!1 === t) {
						var e = new Error("".concat(t.url, " cancel"));
						e.fetchOption = t.fetchOption, r(e)
					}
					var n = new Request(t.url, t.fetchOption);
					return o = setTimeout(function () {
						var e = new Error("".concat(t.url, " timeout"));
						e.fetchOption = t.fetchOption, r(e)
					}, t.fetchOption.timeout), fetch(n)
				}).then(function (e) {
					clearTimeout(o), e.fetchOption = c, t(e)
				}, function (e) {
					clearTimeout(o), e.url = n, e.fetchOption = c, r(e)
				})
			}).then(checkStatus)
		},
		main = {
			setOptions: setOptions,
			getJSONP: getJSONP,
			getJSON: getJSON,
			postJSON: postJSON,
			putJSON: putJSON,
			deleteJSON: deleteJSON
		};
	
	return _z.mix(_fetch, main);
})()


a.setOptions({
	headers: {
		"Content-Type": "application/json",
		"Accept": "application/json, text-plain, */*",
		"X-Requested-With": "XMLHttpRequest",
		"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
	},
	method: 'post',
	credentials: "same-origin",
// 		body: JSON.stringify({
// 			name: 'name',
// 			number: 'number'
// 		})
});
a.postJSON('/utl/data', _z.extend({}, {d: 123}, jsMsg.icons), {
		method: 'post'
	})
	.then(function (data) {
		// This is the JSON from our response
		console.log(data);
	}).catch(function (err) {
	// There was an error
	console.warn('Something went wrong.', err);
});