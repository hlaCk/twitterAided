const moduleID = 'mix';

/**
 * check for: module && exports
 */
if(typeof module === 'undefined') {
    var module = {
        exports: (typeof exports === 'undefined') ? {default: null} : exports
    };
    var exports = module.exports;
    exports.default = typeof exports.default === 'undefined' ? {default: null} : exports.default;
}

// arg1, arg2, ... assign all prototypes of all args in arg1 from  UnderZ
const mix = function mix(arg1) {
	let argsLen = arguments.length || 0;
	if(argsLen <= 1) return arg1 || {};

	let i = 1, j, newObj = arg1 || {};
	for (; i < argsLen; i++)
		for (j in arguments[i])
			if(arguments[i].hasOwnProperty(j))
				newObj[j] = arguments[i][j];

	return newObj;
};

module.exports.default = mix;
module.exports.moduleID = moduleID;
exports = module.exports;

// export default exports.default;
// export { mix, moduleID };


/**
 * module system
 */
export const MODULE = module;
export { moduleID };

export default exports.default;
