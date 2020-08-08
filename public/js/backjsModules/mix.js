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

export default mix;
export { mix };
