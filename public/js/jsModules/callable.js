'use strict';
let $toArray = need('array');

const
	/**
	 * bind method with instance
	 *
	 * @param callable		method to bind
	 * @param thisArg		method context (this)
	 * @param $arguments	extra arguments to send to method
	 *
	 * @returns {Function} Function bindedMethod(...*)
	 */
	callableMaker = function callableMaker(callable, thisArg = null, $arguments) {
		let $This = this;
		if($This instanceof callableMaker) {
			$This.callable 	= callable;
			$This.thisArg 	= thisArg;
			$This.args 		= $toArray($arguments);

			return (function newProxy(fn, fn2, args, ...$arguments) {
				let newArgs = $toArray(args).filter(x=>x);
				return fn.apply( fn2 || this, [...$toArray($arguments), ...(newArgs.length ? [newArgs] : [])] );
			}).bind($This, $This.callable, $This.thisArg ? $This.thisArg : $This, $This.args || []);
		}

		return new callableMaker(...arguments);
	};

	callableMaker.prototype = {
		constructor: callableMaker,
		version: '0.0.2-b',
		g: (x) => {
			return [
				this.thisArg,
				this.callable,
				this.args,
				this
			];
		}
	};

	const
		$mCallable = {

			make: callableMaker,
		};

// let { make } = $mCallable;

export let {
	$info: info,
	$exports: exports,
	$MODULE: MODULE
} = lib('callable')
	.declared(true)
	.default($mCallable)
	.file('callable')
	.config(true);

export default exports.default = $mCallable;