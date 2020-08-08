const
    moduleID = 'lib',

    /**
     * all registred libConfig
     */
    modules = {},

    /**
     * memoized export objects - lib exports storage
     *
     * @type {{id: {boolean}}}
     */
    exportsStorage = {},

    /**
     * check by id if libConfig exists
     *
     * @param id
     * @returns {*|boolean}
     */
    isIncluded = function(id) {
		return (id && modules && modules[id] && true) || false;
	},

    /**
     * check by id if lib loaded and exported
     *
     * @param id
     * @returns {*|boolean}
     */
    isExported = function (id) {
        return isIncluded(id) && exportsStorage[id] || false;
    },

    /**
    * create libConfig object
    */
    libConfig = function libConfig(id) {
        let $this = this;
        if(isIncluded(id)) return modules[id];

        if($this instanceof libConfig) {
            $this._id = id;
        } else {
            return $this = new libConfig(id);
        }

        $this._module = modules[$this._id] = {
            id: $this._id,
            file: null,
            requires: [],
            exports: {
                default: null
            },
            /** default response keys */
            exportsMap: [],
        };

        $this._exported = {
            _id: $this._id,
            loaded: false,
            load: function () {
                return this.load();
            }.bind($this),
        };

        exportsStorage[$this._id] = $this._exported.loaded;
        return $this;
    };

    /**
     * libConfig prototypes
     */
    libConfig.prototype = {
        libConfig: '1.0.0',
        constructor: libConfig,

        default: function (x) {
            if(arguments.length === 0) return modules[this._id].exports.default;

            modules[this._id].exports.default = x;
            return this;
        },

        export: function (name, v) {
            if(arguments.length === 0) return modules[this._id].exports;
            if(arguments.length === 1) return modules[this._id].exports[name];

            modules[this._id].exports[name] = v;
            return this;
        },

        exportsMap: function (v, index = null) {
            let newMap = {}, $this_id = this._id;
            $.map(modules[$this_id].exportsMap, function ($v, $i) {
                newMap[$v] = modules[$this_id].exports[$v];
                return $v;
            });

            if(arguments.length === 0)
                return Object.keys(newMap).length && newMap || [];
            if(arguments.length === 1)
                modules[$this_id].exportsMap.push(v);
            if(arguments.length === 2)
                modules[$this_id].exportsMap[index] = v;

            return this;
        },

        file: function (path) {
            if(arguments.length === 0) return modules[this._id].file;

            modules[this._id].file = path;
            return this;
        },

        require: function (path) {
            if(arguments.length === 0) return modules[this._id].requires;

            modules[this._id].requires.push(path);
            return this;
        },

        module: function (attr, v) {
            if(arguments.length === 0) return modules[this._id];
            if(arguments.length === 1) return modules[this._id][attr];

            modules[this._id][attr] = v;
            return this;
        },

        loaded: function (x = true) {
            this._exported.loaded = exportsStorage[this._id] = x;
            return this;
        },

        load: function () {
            if(isExported(this._id)) return this._exported.exports || null;

            let defaultExporting = this._module.exports.default || modules[this._id].exports.default;

            if(defaultExporting && _z.isFunction(defaultExporting)) {
                this._exported.exports = defaultExporting();
                this.loaded( this._exported.exports !== 'undefined' );

                if(isExported(this._id)) return this._exported.exports || null;
            } else {
                let $exportsMapExpectingMsg = `, Expecting data as : `,
                    $fileName = this.file(),
                    $error = {
                        module: this._module,
                        error: null
                    };

                $exportsMapExpectingMsg += Object.keys(this.exportsMap()).length && this.exportsMap() || "(Map Length = '0' !?)";
                if(!$fileName)
                    $error.error =
                        lib.last_error =
                            `Module[${this._id}]: Invalid Source! (${$fileName})${$exportsMapExpectingMsg}`;

                if(!$error.error && !this.default())
                    $error.error =
                        lib.last_error =
                            `Module[${this._id}]: Data Not Exported Yet!${$exportsMapExpectingMsg}`;

                if($error.error) {
                    throw new ReferenceError($error.error);
                } else {
                    throw new SyntaxError("Can not find errors !");
                }
            }

            return this;
        },
    };
    /**
     * libConfig constructor
     */
    libConfig.prototype.constructor = libConfig.constructor = libConfig;

const
    /**
     * Lib: lib
     */
    lib = (function () {
        // don't want outsider redefining "lib" and don't want
        // to use arguments.callee so name the function here.
        return function lib(id) {
            // new
            if(!isIncluded(id)) return libConfig(id);

            // exists
            if(isExported(id)) return exportsStorage[id];

            // idk wtf after that #todo
            throw new Error('Module can not load!');
        };
    })(),

    /**
     * Lib: run lib without return
     */
    runLib = function (id) {
        lib(id); // doesn't return exports
    },

    /**
     * Lib: find libConfig by id
     * @param x Lib id
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
    },

    /**
     * Lib: get exports by lib id
     * @param x Lib id
     * @returns {{module: *, exports, controller: null}}
     */
    getExportedData = function getExportedData(x) {
        let dataLibRequested = (x && (x instanceof libConfig));

        // module
        if(typeof module === 'undefined') {
            var module = dataLibRequested ?
                x.module() : {
                    exports: (typeof exports === 'undefined') ? {} : exports
                };
        } else {
            module = dataLibRequested ? x.module() : module;
        }

        // exports
        if(typeof exports === 'undefined') {
            var exports = dataLibRequested ? x.export() : module.exports;

            exports.default = typeof exports.default === 'undefined' ? {} : exports.default;
        } else {
            exports = dataLibRequested ? x.export() : exports;
        }

        // module = x;
        return {
            module: module,
            exports: exports,
            controller: dataLibRequested ? x : null,
        };
    };

    /**
     * check for: module && exports
     */
    if(typeof module === 'undefined') {
        var module = {
            exports: (typeof exports === 'undefined') ? {} : exports
        };
        var exports = module.exports;
        exports.default = (typeof exports.default === 'undefined') ? {} : exports.default;
    }


/**
 * lib Tools
 *//*
module.exports = {
    isIncluded,
    isExported,

    libConfig,
    allLibs,
    // lib,

    runLib,
    findLib,

    getExportedData,
};*/
module.id = moduleID;
module.file = '/js/jsModules/lib.js';
module.requires = [];
// exports =
module.exports = {
    /**
     * lib Tools
     */
    isIncluded,
    isExported,

    libConfig,
    allLibs,
    // lib,

    runLib,
    findLib,

    getExportedData
};
module.exports.default = lib;

export const MODULE = module;
export { moduleID };
/**
 * lib Tools
 *//*
export {
        isIncluded,
    isExported,

    libConfig,
    allLibs,
    // lib,

    runLib,
    findLib,

    getExportedData
};*/

/** export lib */
export default module.exports.default;

/**
 * module system
 */
// export { module, exports/*, libConfig*/ };
