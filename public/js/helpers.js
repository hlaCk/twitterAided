
const
    Dir = function Dir(...$e) {
        if(this+0 === -1)
            console.groupCollapsed('-+#Dir [cD]:');
        else
            console.group('-+Dir [cD]:');

        $e.forEach($E=>console.dir($E));
        console.groupEnd();
    },
        cD = Dir.bind(-1),

    Log = function Log(...$e) {
        if(this+0 === -1)
            console.groupCollapsed('#Log [cL]:');
        else
            console.group('Log [cL]:');

        $e.forEach($E=>console.log('Log', $E));
        console.groupEnd();
    },
        cL = Log.bind(-1),

    Error = function Error(...$e) {
        if(this+0 === -1)
            console.groupCollapsed('--+#Error [cE]:');
        else
            console.group('--+Error [cE]:');

        $e.forEach($E=>console.error('Error', $E));
        console.groupEnd();
    },
        cE = Error.bind(-1),

    Warn = function Warn(...$e) {
        if(this+0 === -1)
            console.groupCollapsed('+#Warn [cW]:');
        else
            console.group('+Warn [cW]:');

        $e.forEach($E=>console.warn('Warn', $E));
        console.groupEnd();
    },
        cW = Warn.bind(-1),

    Trace = function Trace(...$e) {
        if(this+0 === -1)
            console.groupCollapsed('---+#Trace [cT]:');
        else
            console.group('---+Trace [cT]:');

        $e.forEach($E=>console.trace('Trace', $E));
        console.groupEnd();
    },
        cT = Trace.bind(-1),

    Assert = function Assert(...$e) {
        if(this+0 === -1)
            console.groupCollapsed('----+#Assert [cA]:');
        else
            console.group('----+Assert [cA]:');

        $e.forEach($E=>console.assert($E));
        console.groupEnd();
    },
        cA = Assert.bind(-1);
