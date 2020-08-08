var modules = {};

const
	isSeeked = function(id) {
		return (id && modules && modules[id] && true) || false;
	},
	seek = (function () {

	var
		// memoized export objects
		exportsObjects = {},

		// module loader
		moduleMaker = function moduleMaker(id) {
			let $this = this;
			if(isSeeked(id)) return modules[id];

			if($this instanceof moduleMaker) {
				$this._id = id;
			} else {
				$this = new moduleMaker(id);
				return $this;
			}

			$this._module = modules[$this._id] = {
				id: $this._id,
				file: null,
				requires: [],
				exports: {
					default: null
				},
				/**
				 * register keys list
				 */
				exportsMap: [],
			};

			$this._exported = exportsObjects[$this._id] = {
				_id: $this._id,
				loaded: false,
				load: function () {
					return this.load();
				}.bind($this),
			};

			return $this;
		};
		moduleMaker.prototype = {
			moduleMaker: '1.0.0',
			constructor: moduleMaker,

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
				let newMap = {};
				$.map(modules[this._id].exportsMap, function ($v, $i) {
					newMap[$v] = modules[this._id].exports[$v];
					return $v;
				});

				if(arguments.length === 0) return Object.keys(newMap).length && newMap || [];
				if(arguments.length === 1)
					modules[this._id].exportsMap.push(v);
				if(arguments.length === 2)
					modules[this._id].exportsMap[index] = v;

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
				exportsObjects[this._id].loaded = x;
				return this;
			},

			load: function () {
				if(exportsObjects[this._id].loaded)
					return this._exported.exports || exportsObjects[this._id].exports;

				let d = this._module.exports.default || modules[this._id].exports.default;

				if(d && _z.isFunction(d)) {
					exportsObjects[this._id].exports = this._exported.exports = d();
					this.loaded(
						exportsObjects[this._id].exports !== 'undefined' &&
						this._exported.exports !== 'undefined'
					);

					if(exportsObjects[this._id].loaded)
						return this._exported.exports || exportsObjects[this._id].exports;
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
							seek.last_error =
								`Module[${this._id}]: Invalid Source! (${$fileName})${$exportsMapExpectingMsg}`;

					if(!$error.error && !this.default())
						$error.error =
							seek.last_error =
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
		moduleMaker.prototype.constructor = moduleMaker;
		moduleMaker.constructor = moduleMaker;
		moduleMaker.allModules = function () {
			return modules;
		};

	// don't want outsider redefining "seek" and don't want
	// to use arguments.callee so name the function here.
	let seek = function (name) {
		if(!isSeeked(name))
			return moduleMaker(name);

		if(exportsObjects.hasOwnProperty(name)) {
			return exportsObjects[name].loaded ?
					exportsObjects[name].exports :
						exportsObjects[name].load();
		} else {
			// todo
			throw new Error('Module can not load!');
		}


	};

	seek.moduleMaker = moduleMaker;

	return seek;
})();

const moduleMaker = seek.moduleMaker;

var run = function (name) {
	seek(name); // doesn't return exports
};

// module
if(typeof module === 'undefined') {
	var module = {
		exports: (typeof exports === 'undefined') ? {} : exports
	};
	var exports = module.exports;
	exports.default = typeof exports.default === 'undefined' ? {} : exports.default;
}

const getExportedData = function getExportedData(x) {
	if(x && (x instanceof moduleMaker)) {
		// module
		if(typeof module === 'undefined') {
			var module = x.module();
		} else {
			module = x.module();
		}
		// exports
		if(typeof exports === 'undefined') {
			var exports = x.export();
		} else {
			exports = x.export();
		}

		// module = x;
		return {
			module: module,
			exports: exports,
			controller: x,
		};
	}

	// module
	if(typeof module === 'undefined') {
		var module = {
			exports: (typeof exports === 'undefined') ? {} : exports
		};
	}
	// exports
	if(typeof exports === 'undefined') {
		var exports = module.exports;
		exports.default = typeof exports.default === 'undefined' ? {} : exports.default;
	}

	return {
		module: module,
		exports: exports,
		controller: undefined
	};
};

export default seek;
export { run, seek, getExportedData };
export { module, exports, moduleMaker };
