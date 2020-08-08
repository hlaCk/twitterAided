'use strict';

const
	/**
	 * Returns module path + module file
	 *
	 * @param x module name
	 *
	 * @return {string} module path
	 */
	getFullPath = (x) => `/js/jsModules/${x}`,
	
	/**
	 * Check if the given object is module.
	 *
	 * @param y module object
	 *
	 * @return {boolean}
	 */
	isModule = function (y) {
		let
			[instance, type] =
				(Object.prototype.toString.call(y)).toLowerCase().replace(/^\[(\S+)[\s](.*)\]$/i, (...x) => {
					return ((ins, type) => {
						return ins + '::' + type
					})(...[...x.slice(1, 3), ...x.slice(-1)]);
				}).split('::');
		return type === "module" && "object" === instance;
	},
	
	/**
	 * Check if the given object is Library;
	 *
	 * @param x module name
	 *
	 * @return {boolean|*}
	 */
	isLibrary = (x) => typeof x === 'object' && 'default' in x && (isModule(x) || (!(lib instanceof Promise) && x instanceof lib.make) || 'Library' in x),
	
	/**
	 * Library path
	 *
	 * @type {string}
	 */
	libJS = getFullPath('lib.js')
;

/**
 * get allowed libs files
 */
let
	$_scripts = document.getElementsByTagName('script'),
	$_script = [...$_scripts].filter(x => x.type === 'module' && x.src && x.src.split('main.js').length > 1).slice(-1),
	[$_current_script] = $_script,
	$allowedLibs = [],
	lib;

try {
	$allowedLibs = eval($_current_script.innerHTML.trim());
	if(!Array.isArray($allowedLibs)) {
		$allowedLibs = fetch(getFullPath('libs.json'), {
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
			$allowedLibs = Promise.resolve(Array.from(data));
			// This is the JSON from our response
			// let dated = Array.from(data).forEach(x=> window.lib(data));
			// console.log(data, $allowedLibs);
			return $allowedLibs;
		}).catch(function (err) {
			// There was an error
			console.warn('Something went wrong.', err);
		});
	} else {
		$allowedLibs = Promise.resolve($allowedLibs);
	}
} catch (e) {
	$allowedLibs = Promise.resolve([]);
}

async function doImport(b64moduleData) {
	const module = import(b64moduleData);
	
	return await (lib = module);
};

let doDeclare = function doDeclare(lib) {
	window.lib              =
			lib             = isLibrary(lib) ? lib.default() : (isModule(lib) ? lib.exports.default : lib);
	window.lib.getFullPath  = getFullPath;
	window.lib.isModule     = isModule;
	window.lib.isLibrary    = isLibrary;
	window.demand           = lib.demand;
	window.demandDefault    = lib.demandDefault;
	window.demandExports    = lib.demandExports;
	window.instance         = lib.instance;
	window.require          = lib.require;
	window.need             = lib.need;

	lib.config('path', getFullPath(''));
	
	return $allowedLibs.then(allowedLibs => {
		lib.config('allowed-libs', allowedLibs);
		lib.config('libs', allowedLibs.map(function (x) {
			try {
				let $x = lib(x['name']);
				let
					req,
					reqsMap = lib.config('reqsMap') || {},
					reqs = Array.from(x['requires']),
					$declearer,
					PromiseInstances = [];
				$declearer = L=>new Promise((r, j)=>{
					if(L instanceof Promise) {
						return L.then(R=>r(R)).catch(J=>j(J));
					} else {
						return L && r(L) || j(L);
					}
				});

				while(!1 && reqs.length) {
					req = reqs.shift();
					reqsMap[req] = demand(req).then($req=> {
							console.dir(PromiseInstances);
							console.dir(reqsMap);
							console.dir($req);
							console.dir(req);
							return $req;
						}
					);
					PromiseInstances.push(reqsMap[req]);
				}

				lib.config('reqsMap', reqsMap);

				return Promise.all(PromiseInstances).then(vv=>{
					// console.warn(vv.slice(0, 1), typeof (vv), vv[0])
					return Array.from(vv).filter(vvFilter=>vvFilter);
				})
					.finally(()=>{
						$x.declare().catch($$x => $$x.message).then((...$e) => ($e=$e.filter($xx => $xx && $xx)) && $e.length && $e.pop() || $e);
					});

				return PromiseInstance ? PromiseInstance.then(()=>$x) : $x;
			} catch (e) {
				return e;
			}
		}));
		lib.config('loadedLibs', ()=> lib.config('libs').filter(l=>l.loaded) );
		return lib.config('loadedLibs')();
	});
	
};

lib = doImport(libJS).then(doDeclare);