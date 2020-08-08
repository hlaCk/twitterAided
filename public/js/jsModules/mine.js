console.clear();
console.info('snipper updated');

if(window.jsMsg) {
	void ((typeof $a) !== "undefined" || ($a = jsMsg("Input").input("text", "text ?")));
}

import('/js/jsModules/lib.js')
	.then(function (lib) { return lib.default; })
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
