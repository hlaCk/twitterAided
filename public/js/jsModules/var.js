'use strict';

const
	$mVar = {

		/**
		 * Returns Type of given var
		 *
		 * @param val {*}
		 * @returns {String}
		 */
		getType: function getType( val ) {
			return Object.prototype.toString.call( val ).toLowerCase().replace(/^\[object (\w+)\]$/, '$1').trim();
		},

	};

let { getType } = $mVar;



export let {
	$info: info,
	$exports: exports,
	$MODULE: MODULE
} = lib('var')
	.declared(true)
	.default($mVar)
	.file('var')
	.config(true);

export default exports.default = $mVar;