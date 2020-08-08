'use strict';

const
    /**
     * all registred Library
     */
    modules = {},

    /**
     * Config data
     *
     * @type {*}
     */
    $config = {},

	$resolve = function(data) {
        return new Promise(function (resolve, reject) {
		    resolve(data);
	    });
	},
	
	$reject = function(data) {
    	return new Promise(function (resolve, reject) {
		    reject(data);
	    });
	},
	
	endsWith = function(string, suffix = '.js') {
    	return string && string.slice(suffix.length * -1) === suffix || false;
	},
	
	suffixWith = function(string, suffix = '.js') {
    	return endsWith(string, suffix) ? string : string + suffix;
	},
	
    /**
     * check by id if Library exists
     *
     * @param id
     * @returns {*|boolean}
     */
    isDefined = function(id) {
		return (id && modules && modules[id] && true) || false;
	},
	
	/**
	 * Config data.
	 *
	 * config(): get all
	 * config(key): get key
	 * config(key, value): set key = value
	 * config({ key: value }): get key || set key = value
	 *
	 * @param key
	 * @param value
	 *
	 * @return {*}
	 */
	config = function (key, value) {
		if(arguments.length === 0) return $config;
		if(arguments.length === 1) {
			if(typeof key === 'function') {
				return key($config);
				// return (this && this.async) ? $resolve($config).then(key) : key($config);
			}
			else if(typeof key === 'object') {
				let
					$keys = Object.keys(key),
					value = key[$keys[0]];
					key = $keys[0];
				return $config[key] || ($config[key] = value || null);
			}
			return $config[key] || ($config[key] = null);
		}
		
		$config[key] = value;
        return this;
    },
	
	/**
	 * Returns suffix file with path.
	 *
	 * @param file
	 * @param path
	 *
	 * @return {string}
	 */
	makeFilePath = function(file, path = null) {
		path = path && suffixWith(path, '/') || "";
		file = file && suffixWith(file) || "";
		return path && path + file || config(cfg => (cfg.path || "") + file || "");
	},
	
    /**
    * create Library object
    */
    Library = function Library(id) {
        let $this = this;
        if(isDefined(id)) return modules[id];

        if(!($this instanceof Library)) {
            return $this = new Library(id);
        }
		
	    $this.loaded = false;
	    $this.info = {
		    id: $this._id = id,
		    file: null,
		    requires: [],
		    exports: $this.exports = {
			    default: null
		    },
		    /** default response keys */
		    exportsMap: [],
	    };
	    this.file(id);
	    modules[$this._id] = $this;
	    
        return $this;
    };

    /**
     * Library prototypes
     */
    Library.prototype = {
	    Library: '1.0.0',
        constructor: Library,

        moduleInfo: function (k, v, subV = undefined) {
            if(arguments.length === 0) return this.info || (this.info = {});
            if(arguments.length === 1) return this.info && this.info[k] || (this.info = {...(this.info||{}), [k]: {} })[k];
            if(arguments.length === 3) {
            	this.info = this.info || {};
	            this.info[k] = this.info[k] || {};
	            this.info[k][v] = subV;
            	return this;
            }
	
	        this.info[k] = this.info[k] || null;
            if(Array.isArray(this.info[k])) this.info[k].push(v);
            else if(typeof(this.info[k]) === 'object' && typeof(v) === 'object') this.info[k] = {...this.info[k], ...v};
            else this.info[k] = v;
            return this;
        },
	    
        default: function (x) {
            if(arguments.length === 0) return this.export('default');
	
	        return this.export('default', x);
        },

        export: function (name, v) {
            if(arguments.length === 0) return this.exports || (this.exports = {});
            if(arguments.length === 1) return this.exports[name] || (this.exports[name] = {});
	
	        this.exports[name] = v;
	        return this.exportMap(name);
        },

        exportMap: function (name, v = {}) {
        	let $exportsMap = this.moduleInfo('exportsMap');
	        if(arguments.length === 0) return $exportsMap;
	        if(arguments.length === 1) $exportsMap.push(name);
	        if(arguments.length === 2) return this.export(name, v);
	
	        return this;
        },

        file: function (path) {
        	if(path) {
		       return this.moduleInfo('file', makeFilePath(path) || null);
	        }
	        return this.moduleInfo('file');
        },
	
	    require: function (path) {
		    if(path) {
			    return this.moduleInfo('requires', makeFilePath(path) || null);
		    }
		    
	        return this.moduleInfo('requires');
        },
	    
	    config: function (dollar = false) {
		    return {
		        [(dollar && '$' || '') + 'MODULE']: this,
		        [(dollar && '$' || '') + 'default']: this.default(),
			    [(dollar && '$' || '') + 'info']: this.moduleInfo(),
			    [(dollar && '$' || '') + 'exports']: this.export()
	        };
	    },
	
	    declared: function declared(declared) {
        	this.loaded = arguments.length ? !!declared : true;
        	
        	return this;
	    },
	    
	    declare: function declare() {
        	if(this.loaded) return Promise.resolve(this);
        	
        	return this.file()
		                ? import(this.file())
			                .catch(x=>Promise.reject(`Module Load Faild. Error:\n\t ${x.name}: ${x.message}`))
		                : Promise.reject("No Module Path !");
	    },
	
	    // getDeclared: async function getDeclared() {
        	// return await this.declare().then(x=>x.export());
	    // },
    };
    /**
     * Library constructor
     */
    Library.prototype.constructor = Library.constructor = Library;

const
    /**
     * Lib: lib
     */
    lib = (function () {
        // don't want outsider redefining "lib" and don't want
        // to use arguments.callee so name the function here.
        return function $lib(id) {
        	if(typeof id === 'string')
                return new Library(id);
        	
        	return $lib;
        };
    })(),
	
	/**
	 * send declare and get lib .. no async
	 *
	 * Lib: import library
	 */
	declaredLibrary = function declaredLibrary(library) {
		library && lib(library).declare();
		return library && lib(library) || undefined;
	},
	
	/**
	 * get exports after send declare.
	 * Returns async.
	 *
     * Lib: import library
     */
	demandLibrary = function demandLibrary(type = '*') {
		return async function demander(id) {
			return id && lib(id).declare().then(
				x => type === 'default' ? x.exports && x.exports.default : (
						type === 'exports' ? x.exports : x
					)
			) || Promise.reject("No Library data provided!");
		};
    },
	
	/**
	 * get default .. no async
	 *
	 * Lib: get declared lib
	 */
	instanceLibrary = function instanceLibrary(library) {
		return library && lib(library).exports.default || undefined;
	},
	
	/**
     * Lib: find Library by id
	 *
     * @param x Lib id
	 *
     * @returns {*|null}
     */
    findLib = function (x) {
        return x && modules && modules[x] || null;
    },

    /**
     * Lib: get all libConfigs
     */
    allLibs = function () {
        return modules || null;
    }
    ;


/**
 * {@link makeFilePath()}
 */
lib.makeFilePath    = makeFilePath;
/**
 * {@link suffixWith()}
 */
lib.suffixWith      = suffixWith;
/**
 * {@link endsWith()}
 */
lib.endsWith        = endsWith;

/**
 * {@link config()}
 */
lib.config          = config;
/**
 * {@link allLibs()}
 */
lib.all             = allLibs;
/**
 * {@link findLib()}
 */
lib.find            = findLib;

/**
 * {@link isDefined()}
 */
lib.isDefined       = isDefined;
/**
 * {@link Library()}
 */
lib.make            = Library;

/**
 * {@link declaredLibrary()}
 */
lib.declare         = declaredLibrary;
/**
 * get module after send declare. AS ASYNC
 * {@link demandLibrary()}
 */
lib.demand          = demandLibrary();
/**
 * get default after send declare. AS ASYNC
 * {@link demandLibrary()}
 */
lib.demandDefault   = demandLibrary('default');
/**
 * get exports after send declare. AS ASYNC
 * {@link demandLibrary()}
 */
lib.demandExports   = demandLibrary('exports');
/**
 * get default .. no async only when catch
 * {@link instanceLibrary()}
 */
lib.need            =
  lib.requireOrFail = function (library, fail = true) {
		return !fail && instanceLibrary(library) ||
			fail && (instanceLibrary(library) || Promise.reject(fail&& fail!==true && fail || "Fail!"));
	};
/**
 * get module after send declare. AS ASYNC
 * {@link instanceLibrary()}
 */
lib.instance        =
	lib.require     = (l, f = false) => lib.need(l, f)
;
export default lib;