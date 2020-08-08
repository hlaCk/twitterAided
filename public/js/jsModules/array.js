'use strict';
let getType;
demand('var').then($var=>getType = $var);

const
	$mArray = {

		// toArray
		toArray: function toArray() {
			let sliced;
			sliced = ( sliced = Array.prototype.slice.call( arguments.length&&arguments[0] || this ) ).length && sliced || false;

			if( arguments.length && sliced === false /*&& !is_z( arguments[0] ) */)
				try { sliced = [ ...arguments[0] ]; } catch (e) { sliced = [ arguments[0] ]; }

			return sliced === false ? [] : sliced;
		},

		// argument to array
		$toArray: function $toArray( input ) {
			input = input || [];
			if( getType(input) === 'string' ) input = [ input ];

			return $mArray.toArray(input);
		},

		// Keys
		Keys: function Keys (x) {
			return Object.keys(x);
		},

		// Row
		Row: function Row(x, deleteRow = false) {
			let $keys   = Keys(x),
				$key, $value;

			$value = x[($key = $keys.shift())];

			if(deleteRow && $key)
				delete x[$key];

			return [$key, $value];
		},

		/**
		 * get item by index
		 *
		 * @param {Array} array The array to query.
		 * @param {number} n The index of the element to return.
		 *
		 * @returns {*} Returns the nth element of `array`.
		 */
		itemAt: function itemAt(array, n) {
			let l;
			if (!(l = array.length)) return;

			n += n < 0 ? l : 0;
			try {
				return n < l ? array[n] : undefined;
			} catch (e) {
				return  undefined;
			}
		},

	};

let { toArray, $toArray, Keys, Row, itemAt } = $mArray;

export let {
	$info: info,
	$exports: exports,
	$MODULE: MODULE
} = lib('array')
	.declared(true)
	.default($mArray)
	.file('array')
	.require('var')
	.config(true);

export default exports.default = $mArray;