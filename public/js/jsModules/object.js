'use strict';

const
	$mObject = {

		/**
		 * This method returns an object composed from key-value `pairs`.
		 *
		 * @param {Array} pairs The key-value pairs.
		 *
		 * @returns {Object} Returns the new object.
		 *
		 * @example
		 *
		 * fromPairs([ 'a', 1 ], [ 'b', 2 ]); // { 'a': 1, 'b': 2 }
		 */
		fromPairs: function fromPairs(...pairs) {
			var i = -1,
				l = pairs == null ? 0 : pairs.length,
				result = {};

			while (++i < l) {
				let [$key, $value] = pairs[i];
				result[$key] = $value;
			}

			return result;
		},

		/**
		 * This method returns an array key-value of the given object.
		 *
		 * @param {object} object to get pairs.
		 *
		 * @returns {Array} Returns array key-value of the given object.
		 *
		 * @example
		 *
		 * fromPairs([ 'a', 1 ], [ 'b', 2 ]); // { 'a': 1, 'b': 2 }
		 */
		toPairs: function toPairs(object) {
			let keys = Object.keys(object || {}) || [];
			var i = -1,
				l = keys.length,
				result = [];


			while (++i < l) {
				result.push([keys[i], object[ keys[i] ]]);
			}

			return result;
		},

	};

let { fromPairs, toPairs } = $mObject;


export let {
	$info: info,
	$exports: exports,
	$MODULE: MODULE
} = lib('object')
	.declared(true)
	.default($mObject)
	.file('object')
	.config(true);

export default exports.default = $mObject;