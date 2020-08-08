<?php

use App\User;

if(!defined('e'))
    define('e', 'else');

//class isPlainVar { public function __construct($var="var") { $this->plain = $var; } };
if(!defined('UNUSED'))
    define('UNUSED', gzcompress(serialize(['plain'=>0x0011]), 9));
//    define('UNUSED', gzcompress(serialize(new isPlainVar('Variable')), 9));

//dd(UNUSED);

/**
 * by myth
 */
if( !function_exists('toLocaleDate') ){

    function toLocaleDate( $date ){
        $ar = [
            "الأحد",
            "أح",
            "الإثنين",
            "إث",
            "الثلاثاء",
            "ث",
            "الأربعاء",
            "أر",
            "الخميس",
            "خ",
            "الجمعة",
            "ج",
            "السبت",
            "س",
            "ص",
            "ص",
            "م",
            "م",
            "يناير",
            "يناير",
            "فبراير",
            "فبراير",
            "مارس",
            "مارس",
            "أبريل",
            "أبريل",
            "مايو",
            "مايو",
            "يونيو",
            "يونيو",
            "يوليو",
            "يوليو",
            "أغسطس",
            "أغسطس",
            "سبتمبر",
            "سبتمبر",
            "اكتوبر",
            "اكتوبر",
            "نوفمبر",
            "نوفمبر",
            "ديسمبر",
            "ديسمبر",
        ];
        $notAr = [
            "Sunday",
            "Sun",
            "Monday",
            "Mon",
            "Tuesday",
            "Tue",
            "Wednesday",
            "Wed",
            "Thursday",
            "Thu",
            "Friday",
            "Fri",
            "Saturday",
            "Sat",
            "am",
            "AM",
            "pm",
            "PM",
            "January",
            "Jan",
            "February",
            "Feb",
            "March",
            "Mar",
            "April",
            "Apr",
            "May",
            "May",
            "June",
            "Jun",
            "July",
            "Jul",
            "August",
            "Aug",
            "September",
            "Sep",
            "October",
            "Oct",
            "November",
            "Nov",
            "December",
            "Dec",
        ];

//        $timestamp = strtotime('2019-01-01');
//        $months = [];
//
//        for ($i = 0; $i < 12; $i++) {
//            $months[] = strftime('%B', $timestamp);
//            $months[] = strftime('%b', $timestamp);
//            $timestamp = strtotime('+1 month', $timestamp);
//        }
//        dd($months);


        try{
            if(! app()->isLocale('ar') || !$date )
                return $date;


            return str_ireplace(
                $notAr,
                $ar,
                $date
            );
        }
        catch (\Exception $exception){ return $date; }
    }
}

if(!function_exists('CurrentTUser')) {
    function CurrentTUser($field = null) {
        return session('access_token' . ($field ? ".{$field}" : ''));
    }
}
if (!function_exists('fractal')) {
    /**
     * @param null|mixed                                         $data
     * @param null|callable|\League\Fractal\TransformerAbstract  $transformer
     * @param null|\League\Fractal\Serializer\SerializerAbstract $serializer
     *
     * @return \Spatie\Fractal\Fractal
     */
    function fractal($data = null, $transformer = null, $serializer = null) {
        $fractalClass = config('fractal.fractal_class') ?? \Spatie\Fractal\Fractal::class;

        return $fractalClass::create($data, $transformer, $serializer);
    }
}


if (!function_exists('carbon')) {
    /**
     * Returns Carbon class
     *
     * @return \Carbon\Carbon
     */
    function carbon() {
        return app(\Carbon\Carbon::class);
    }
}

if (!function_exists('globalCompacts')) {
    /**
     * get global vars to compact array.
     *
     * @return array
     */
    function globalCompacts() {
        global $auth_user;

        // Share user logged in
        $auth_user = AuthUser();

        return compact('auth_user');
    }
}

if (!function_exists('appendGlobalCompacts')) {
    /**
     * add global vars to compact array.
     *
     * @param array $compactValues
     *
     * @return array
     */
    function appendGlobalCompacts(array $compactValues)
    {
        return collect($compactValues?:[])->merge(globalCompacts())->all();
    }
}

if (!function_exists('fixUser')) {
    /**
     * Reassign auth user with model \Modules\User\Entities\User, this is to remove App\User
     *
     * @param \App\Models\Auth\User $user
     *
     * @return \App\Models\Auth\User
     */
    function fixUser($user = null)
    {
        if($user && $user instanceof \App\User) {
            $user = $current = \App\Models\Auth\User::find($user->id);
            $current = Auth::user();
            if($current && $current instanceof \App\User) {
                app('auth')->setUser($current = \App\Models\Auth\User::find($current->id));
            }
        }

        return $user;
    }
}

if (!function_exists('AuthUser')) {
    /**
     * get Auth::User, current logged in user
     *
     * @param \App\Models\Auth\User|null $default
     *
     * @return \App\Models\Auth\User
     */
    function AuthUser(\App\Models\Auth\User $default = null)
    {
        $user = Auth::user() ?: $default;
        return fixUser($user);
    }
}

if (!function_exists('User')) {
    /**
     * get Auth::User, current logged in user
     *
     * @return \App\Models\Auth\User
     */
    function User($id = false) {
        if (!is_null($id) && $id !== false && !($id instanceof \Illuminate\Foundation\Auth\User))
            $user = \App\Models\Auth\User::status()->find(intval($id));
        else if(is_null($id) && $id !== false && !($id instanceof \Illuminate\Foundation\Auth\User))
            $user = new \App\Models\Auth\User();
        else if (!is_null($id) && $id !== false && ($id instanceof \Illuminate\Foundation\Auth\User))
            $user = User($id->id);
        else
            $user = app('auth')->user() ?: new \App\Models\Auth\User();

        return fixUser($user);
    }
}

if (!function_exists('userPermissions')) {
    /**
     * Get all user permissions
     *
     * @param \App\Models\Auth\User|null $user null = AuthUser()
     *
     * @return array All user permissions
     */
    function userPermissions(\App\Models\Auth\User $user = null) : array
    {

        $userHandler = is_null($user) ? AuthUser() : $user;

        $userRoleHandler = $userHandler ? $userHandler->role : null;

        $rolePermissions = $userRoleHandler ? $userRoleHandler->permissions()->get() : collect();

        $userHandlerPermissions = $userHandler->permissions()->get();
        $userHandlerPermissions = $userHandlerPermissions ?: collect();

        $permissions = $userHandler ?
            $userHandlerPermissions
                ->merge( $rolePermissions )
                ->pluck('name')
                ->unique() :
            collect();

        return $permissions->toArray();
    }
}

if (!function_exists('ViewMode')) {
    /**
     * get current route
     *
     * @return \Illuminate\Foundation\Application|\Illuminate\Routing\Route|mixed
     */
    function ViewMode() {
        try {
            return @end(explode('.', CurrentRoute()->getName()));
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('real_path')) {
    /**
     * return given path without ../
     *
     * @param null   $path
     * @param string $DIRECTORY_SEPARATOR
     *
     * @return string
     */
    function real_path($path = null, $DIRECTORY_SEPARATOR = "/") {
        $_DIRECTORY_SEPARATOR = $DIRECTORY_SEPARATOR == "/" ? "\\" : "/";
        if ($path) $path = str_ireplace($_DIRECTORY_SEPARATOR, $DIRECTORY_SEPARATOR, $path);

        $backslash = "..{$DIRECTORY_SEPARATOR}";
        if (stripos($path, $backslash) !== false) {
            $path = collect(explode($backslash, $path))->reverse();
            $path = $path->map(function ($v, $i) use ($path) {
                $_v = dirname($v);

                return $i == $path->count() - 1 ? $v :
                    ($_v == '.' ? '' : $_v);
            });
            $path = str_ireplace(
                $DIRECTORY_SEPARATOR . $DIRECTORY_SEPARATOR,
                $DIRECTORY_SEPARATOR,
                $path->reverse()->implode($DIRECTORY_SEPARATOR)
            );
        }

        return collect($path)->first();
    }
}

if (!function_exists('HBlade')) {
    /**
     * Create new blade cmd
     *
     * @return \App\Utilities\HBlade
     */
    function HBlade() {
        return new \App\Utilities\HBlade(...func_get_args());
    }
}

if (!function_exists('ApiResponse')) {
    /**
     * Create a new ApiResource instance.
     *
     * @param  mixed        $data []
     * @param  int          $status 200
     * @param  bool         $error false
     * @param  string|null  $message null
     *
     * @return \App\Utilities\ApiResource
     */
    function ApiResponse($data = [], $status = 200, $error = false, $message = null) {
        return new \App\Utilities\ApiResource($data, $status, $error, $message);
    }
}

if (! function_exists('whenUsed')) {
    function whenUsed($var, callable $callable): bool {
        if($var !== UNUSED) {
            $callable($var);
            return true;
        }

        return false;
    }
}


#region IS
if (!function_exists('Support')) {
    /**
     * get if Auth::User is support user
     *
     * @return bool
     */
    function Support() {
        $auth_user = User();

//        return ($auth_user != null && config("user.support.email") != null &&
//            $auth_user->email == config("user.support.email"));

        return ($auth_user != null && $auth_user->email == "admin@admin.com");
    }
}

if (!function_exists('isSupport')) {
    /**
     * Alias for function {@see Support()}
     *
     * @return bool
     */
    function isSupport() { return Support(); }
}

if (!function_exists('isViewMode')) {
    /**
     * get current route
     *
     * @return \Illuminate\Foundation\Application|\Illuminate\Routing\Route|mixed
     */
    function isViewMode($mode) {
        return strtolower(trim($mode)) == strtolower(trim(ViewMode()));
    }
}

if (! function_exists('is_collection')) {
    function is_collection(&$var): bool
    {
        return $var instanceof \Illuminate\Support\Collection;
    }
}

if (! function_exists('isUsedCount')) {
    function isUsedCount(&...$var): int {
        $unUsedArgs = array_filter($var, function ($_var) {
            return isUsed($_var);
        });

        return count($unUsedArgs);
    }
}

if (! function_exists('isUsedAll')) {
    function isUsedAll(&...$var): bool {
        return isUsedCount(...$var) === count($var);
    }
}

if (! function_exists('isUsedAny')) {
    function isUsedAny(&...$var): bool {
        return isUsedCount(...$var) > 0;
    }
}

if (! function_exists('isUsed')) {
    function isUsed(&$var): bool {
        return $is_used = $var !== UNUSED;
    }
}

if (! function_exists('isPlain')) {
    function isPlain(&$var): bool {
        return $var === UNUSED;
    }
}

if (! function_exists('ifSet')) {
    function ifSet(&$var, $true = UNUSED, $false = UNUSED) {
        $true = isUsed($true) ? $true : (isset($var) ? $var : true);
        $false = isUsed($false) ? $false : null;
        return isset($var) ? $true : $false;
    }
}

if (! function_exists('firstSet')) {
    function firstSet(&...$var) {
        foreach ($var as $_var)
            if(isset($_var))
                return $_var;

        return null;
    }
}

if (! function_exists('fstSet')) {
    function &massSet($value, &...$var) {
        foreach ($var as &$_var) {
            $_var = $value;
        }

        return $var;
    }
}

if (!function_exists('isLogged')) {
    /**
     * get Auth::User, current logged in user
     *
     * @return \App\User|null
     */
    function isLogged() {
        return Auth::check();
    }
}

#endregion

#region HAS
if (!function_exists('hasTrait')) {
    /**
     * Check if given class has {@link \Modules\Tools\Traits\HasStatus HasStatus} trait.
     *
     * @param mixed $class <p>
     * Either a string containing the name of the class to
     * check, or an object.
     * </p>
     * @param string $traitName <p>
     * Trait name to check
     * </p>
     *
     * @return bool
     */
    function hasTrait($class, $traitName) {
        try {
            $traitName = str_contains($traitName, "\\") ? class_basename($traitName) : $traitName;

            $hasTraitRC = new ReflectionClass($class);
            $hasTrait = collect($hasTraitRC->getTraitNames())->map(function ($name) use ($traitName) {
                    $name = str_contains($name, "\\") ? class_basename($name) : $name;

                    return $name == $traitName;
                })->filter()->count() > 0;
        } catch (ReflectionException $exception) {
            $hasTrait = false;
        } catch (Exception $exception) {
            d($exception->getMessage());
            $hasTrait = false;
        }

        return $hasTrait;
    }
}

if (!function_exists('hasKey')) {
    /**
     * Check if given array has key if has key call $callable.
     *
     * @param array         $array
     * @param string        $key
     * @param Closure|null  $callable
     *
     * @return bool|mixed
     */
    function hasKey($array, $key, Closure $callable = null) {
        try {
            $has = array_key_exists($key, $array);
            if($callable && is_callable($callable)) {
                $array = new \App\Utilities\ArrayAndObject($array);
                return $callable->call($array, $array);
            }

            return $has === true;
        } catch (Exception $exception) {
            d($exception->getMessage());
            $hasTrait = false;
        }

        return false;
    }
}

if (!function_exists('hasScope')) {
    /**
     * Check if given class has the given scope name.
     *
     * @param mixed $class <p>
     * Either a string containing the name of the class to
     * check, or an object.
     * </p>
     * @param string $scopeName <p>
     * Scope name to check
     * </p>
     *
     * @return bool
     */
    function hasScope($class, $scopeName) {
        try {
            $hasScopeRC = new ReflectionClass($class);
            $scopeName = strtolower(studly_case($scopeName));
            $scopeName = starts_with($scopeName, "scope") ? substr($scopeName, strlen("scope")) : $scopeName;

            $hasScope = collect($hasScopeRC->getMethods())->map(function ($c) use ($scopeName) {
                    /**
                     * @var $c ReflectionMethod
                     */

                    $name = strtolower(studly_case($c->getName()));
                    $name = starts_with($name, "scope") ? substr($name, strlen("scope")) : false;

                    return $name == $scopeName;
                })->filter()->count() > 0;
        } catch (ReflectionException $exception) {
            $hasScope = false;
        } catch (Exception $exception) {
            $hasScope = false;
        }

        return !!$hasScope;
    }
}
#endregion

#region CURRENT
if( !function_exists('currentController') ) {
    /**
     * @return \Illuminate\Routing\Controller|null
     */
    function currentController() {
        return Route::getCurrentRoute()->controller;
    }
}

if (!function_exists('currentRoute')) {
    /**
     * Returns current route
     *
     * @return \Illuminate\Foundation\Application|\Illuminate\Routing\Route|mixed
     */
    function currentRoute() {
        return app(\Illuminate\Routing\Route::class);
    }
}

if (! function_exists('currentNamespace')) {
    /**
     * Returns namespace of current controller
     *
     * @return null|string Namespace
     */
    function currentNamespace() {
        $class = get_class(currentController());
        try {
            $namespace = (new ReflectionClass($class))->getNamespaceName();
        } catch (ReflectionException $exception) {
            return null;
        }

        return $namespace;
    }
}
#endregion

#region GET
if( !function_exists('getRequestedPage') ) {
    /**
     * Returns page from request
     *
     * @return int
     */
    function getRequestedPage() {
        $page = request()->get('page', 1);
        return strtolower($page) === 'all' ? 0 : $page;
    }
}

if( !function_exists('getMethodName') ) {
    /**
     * Returns method name by given Route->uses
     *
     * @param string $method
     *
     * @return string
     */
    function getMethodName(string $method) {
        if(empty($method)) return '';

        $method = collect(explode('::', $method))->last();
        $method = collect(explode('@', $method))->last();

        return $method;
    }
}

if (! function_exists('getCurrentNamespace')) {
    /**
     * Returns current namespace of current class|object
     *
     * @param null $append
     *
     * @return null|string
     */
    function getCurrentNamespace($append = null) {
        $caller = debug_backtrace();
        $caller = $caller[1];
        $class = null;
        try {
            if (isset($caller['class'])) {
                $class = (new ReflectionClass($caller['class']))->getNamespaceName();
            }
            if (isset($caller['object'])) {
                $class = (new ReflectionClass(get_class($caller['object'])))->getNamespaceName();
            }
        } catch (ReflectionException $exception) {
//			d($exception);
            return null;
        }
        if($append) $append = str_ireplace("/", "\\", $append);
        if($class) $class = str_ireplace("/", "\\", $class);

        if($class) $class = real_path("{$class}" . ($append ? "\\{$append}" : ""));

        return $class;
    }
}


if (!function_exists('getNamespacePermission')) {
    /**
     * return perm name from controller full namespace
     *
     * @return string
     */
    function getNamespacePermission()
    : string {
        $route = app(\Illuminate\Routing\Route::class);

        // Get the controller array
        $arr = array_reverse(explode('\\', explode('@', $route->getAction()['uses'])[0]));

        $controller = '';

        // Add folder
        if (strtolower($arr[1]) != 'controllers') {
            $controller .= kebab_case($arr[1]) . '-';
        }

        // Add module
        if (isset($arr[3]) && isset($arr[4]) && (strtolower($arr[4]) == 'modules')) {
            $controller .= kebab_case($arr[3]) . '-';
        }

        // Add file
        $controller .= kebab_case($arr[0]);

        return $controller;
    }
}

if (!function_exists('getControllerPermissionPrefix')) {
    /**
     * Returns prefix of permissions name
     *
     * @param \Illuminate\Routing\Controller|string|null $controller          Controller or controller name, default: {@see currentController()}
     * @param string|null                                $permission_name     Permission name
     * @param string                                     $separator           Permission name separator
     *
     * @return string
     */
    function getControllerPermissionPrefix($controller = null, $permission_name = null, $separator = "_") : string {
        $controller = $controller instanceof \Illuminate\Routing\Controller ? get_class($controller) : ($controller ? trim($controller) : get_class(currentController()));

        $controller = str_before(class_basename($controller), "Controller");

        $controller .= $permission_name ? ucfirst($permission_name) : '';

        $controller = snake_case($controller);

        $controller = $permission_name ? $controller : str_finish($controller, "_");

        return str_ireplace("_", $separator, $controller);
    }
}

if (!function_exists('toCollect')) {
    /**
     * Returns $var as collection
     *
     * @param $var
     *
     * @return \Illuminate\Support\Collection
     */
    function toCollect($var) : \Illuminate\Support\Collection {
        return is_collection($var) ? $var : collect($var);
    }
}

if (!function_exists('collectGet')) {
    /**
     * Returns value from collection by key
     *
     * @param $collect
     * @param $key
     * @param string $default
     *
     * @return mixed
     */
    function collectGet($collect, $key, $default = UNUSED) {
        return toCollect($collect)->get($key, UNUSED);
    }
}

if (!function_exists('toArrayObject')) {
    /**
     * Returns object of ArrayAndObject
     *
     * @param $array
     * @param callable|null $each
     *
     * @return \App\Utilities\ArrayAndObject
     */
    function toArrayObject($array, callable $each = null) : \App\Utilities\ArrayAndObject {
        return \App\Utilities\ArrayAndObject::from($array, $each);
    }
}

if (!function_exists('toBoolValue')) {
    /**
     * Returns value as boolean
     *
     * @param $var
     *
     * @return bool
     */
    function toBoolValue($var):bool {
        if(is_bool($var)) return boolval($var);

        !is_bool($var) && ($var = strtolower(trim($var)));
        !is_bool($var) && ($var = $var === 'false' ? false : $var);
        !is_bool($var) && ($var = $var === 'true' ? true : $var);
        !is_bool($var) && ($var = $var === '1' ? true : $var);
        !is_bool($var) && ($var = $var === '0' ? false : $var);

        return boolval($var);
    }
}

if (!function_exists('toVar')) {
    /**
     * Returns value as boolean
     *
     * @param $var
     *
     * @return bool
     */
    function toVar($value = null, callable $callable = null) :Closure {
        if($callable && is_callable($callable)) {
            return function () use ( &$callable, &$value ){
                return $callable->call(new class ($value) {
                    public $var = null;
                    public function __construct(&$var = null) {
                        $this->var = &$var;
                    }
                    public function __toString() {
                        return (string) $this->var;
                    }
                }, ...func_get_args());
            };
        } else {
            return function () use ( &$value ) {
                return $value;
            };
        }
    }
}
if (!function_exists('suffixerMaker')) {
    /**
     * Alias for: {@link \App\Utilities\Suffixer::makeer}
     *
     * @return Closure
     */
    function suffixerMaker() :Closure {
        return \App\Utilities\Suffixer::makeer(...func_get_args());
    }
}
if (!function_exists('str_prefix')) {
    /**
     * Add a prefix to string but only if string2 is not empty.
     *
     * @param  string       $string     string to prefix
     * @param  string       $prefix     prefix
     * @param  string|null  $string2    string2 to prefix the return
     *
     * @return string|null
     */
    function str_prefix($string, $prefix, $string2 = null) {
        $newString = rtrim(is_null($string2) ? '' : $string2, $prefix) .
            $prefix .
            ltrim($string, $prefix);

        return ltrim($newString, $prefix);
    }
}
if (!function_exists('str_suffix')) {
    /**
     * Add a suffix to string but only if string2 is not empty.
     *
     * @param  string       $string     string to suffix
     * @param  string       $suffix     suffix
     * @param  string|null  $string2    string2 to suffix the return
     *
     * @return string|null
     */
    function str_suffix($string, $suffix, $string2 = null) {
        $newString = ltrim($string, $suffix).$suffix.rtrim(is_null($string2) ? '' : $string2, $suffix);

        return trim($newString, $suffix);
    }
}

if (!function_exists('str_words_limit')) {
    /**
     * Limit string words.
     *
     * @param  string       $string    string to limit
     * @param  int          $limit     word limit
     * @param  string|null  $suffix    suffix the string
     *
     * @return string
     */
    function str_words_limit($string, $limit, $suffix = '...') {
        $start = 0;
        $stripped_string =strip_tags($string); // if there are HTML or PHP tags
        $string_array =explode(' ',$stripped_string);
        $truncated_array = array_splice($string_array, $start, $limit);

        $lastWord = end($truncated_array);
        $return = substr($string, 0, stripos($string, $lastWord)+strlen($lastWord)) . ' ' . $suffix;

        $m=[];
        if(preg_match_all('#<(\w+).+?#is', $return, $m)) {
            $m = is_array($m) && is_array($m[1]) ? array_reverse($m[1]) : [];
            foreach ($m as $HTMLTAG) {
                $return .= "</{$HTMLTAG}>";
            }
        }

        return $return;
    }
}

if (!function_exists('twitter')) {
    /**
     * Returns Twitter object
     *
     * @param array|null $data
     *
     * @return \App\Utilities\Twitter
     */
    function twitter($data = null) {
        $o = new \App\Utilities\Twitter($data);
        return $data ? $o->reconfig($data) : $o;
    }
}

#endregion




#region DEBUG
if (! function_exists('du')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed  $args
     * @return void
     */
    function du(...$args)
    {
        try {
            $debug = @debug_backtrace();
            if(!empty($debug) AND is_array($debug))
            {
                $call = @current($debug);
            } else throw new \Exception(__LINE__ . " function debug_backtrace() returned: {$debug}");
            $line = (isset($call['line'])?$call['line']:__LINE__);
            $file = (isset($call['file'])?$call['file']:__FILE__);
            $file = @basename($file);
//            dump( $debugFiles );
            if(App::runningInConsole()) {
                echo(
                "\n\n[{$file}] Line ({$line}): \n"
                );
            } else  $args = \Illuminate\Support\Arr::prepend($args, "{$file}:{$line}");

            collect($args)->each(function ($e) {
                dump($e);
            });

            if(App::runningInConsole()) {
                echo(
                    "\n\n\n :" . __LINE__ . ""
                );
            } else echo(
                "<small>By: \n" . __FILE__ . ":" . __LINE__ . "  \n" . __CLASS__ . "@" . __METHOD__ . "</small>"
            );
        }
        catch(\Exception $e) {
            if (App::runningInConsole()) {
                echo $msg = (__LINE__ . " ERROR: Function (".__FUNCTION__."), File (".__FILE__."), Line (".__LINE__."): ".$e->getMessage());
            } else
                echo $msg = (__LINE__ . " ERROR: Function (".__FUNCTION__."), File (".__FILE__."), Line (".__LINE__."): ".$e->getMessage());
            dump(
                $e->getMessage(),
                $msg,
                debug_backtrace()
            );
        }
    }
}

if (! function_exists('d')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed  $args
     * @return void
     */
    function d(...$args)
    {
        try {
            $debug = @debug_backtrace();
            if(!empty($debug) AND is_array($debug))
            {
                $call = @current($debug);
            } else throw new \Exception(__LINE__ . " function debug_backtrace() returned: {$debug}");
            $line = (isset($call['line'])?$call['line']:__LINE__);
            $file = (isset($call['file'])?$call['file']:__FILE__);
            $file = @basename($file);

            if(App::runningInConsole()) {
                echo(
                "\n\n[{$file}] Line ({$line}): \n"
                );
            } else echo( "[{$file}] Line ({$line}): <br>" );

            collect($args)->each(function ($e) {
                dump($e);
            });

            if(App::runningInConsole()) {
                echo(
                    "\n\n\n :" . __LINE__ . ""
                );
            } else echo(
                "<small>By: \n" . __FILE__ . ":" . __LINE__ . "  \n" . __CLASS__ . "@" . __METHOD__ . "</small>"
            );
            exit;
        }
        catch(\Exception $e) {
            if (App::runningInConsole()) {
                echo $msg = (__LINE__ . " ERROR: Function (".__FUNCTION__."), File (".__FILE__."), Line (".__LINE__."): ".$e->getMessage());
            } else
                echo $msg = (__LINE__ . " ERROR: Function (".__FUNCTION__."), File (".__FILE__."), Line (".__LINE__."): ".$e->getMessage());
            dd(
                $e->getMessage(),
                $msg,
                debug_backtrace()
            );
            exit;
        }
    }
}

if (!function_exists('df')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed $args
     *
     * @return void
     */
    function df(...$args) {
        try {
            $debug = @debug_backtrace();
            if (!empty($debug) AND is_array($debug)) {
                $_nl = $debugFiles = "<br>\r\n";
                $debugFiles = "";

                $call = @current($debug);
                /*foreach ($debug as $issues)
                {
                    $_line = isset($issues['line']) ? $issues['line'] : 0;
//                    $debugFiles .= (!empty($debugFiles)?$_nl:"");
//                    $debugFiles .= "[" . @basename($issues['file']) . "] Line ({$_line})";// . $_nl;
                }*/
            } else throw new \Exception(__LINE__ . " function debug_backtrace() returned: {$debug}");
            d(
                collect($debug)->mapWithKeys(function ($v, $k) {
                    $k = isset($v['file']) ? basename($v['file']) : __FILE__;
                    $k .= isset($v['line']) ? ":" . $v['line'] : "";

                    return [
                        $k => $v
                    ];
                })->take(4)->toArray(),
                ...$args
            );
            $line = (isset($call['line']) ? $call['line'] : __LINE__);
            $file = (isset($call['file']) ? $call['file'] : __FILE__);
            $file = @basename($file);

//            dump( $debugFiles );
            if (App::runningInConsole()) {
                echo(
                "\n\n[{$file}] Line ({$line}): \n"
                );
            } else echo("[{$file}] Line ({$line}): <br>");

            collect($args)->each(function ($e) {
                dump($e);
            });

            if (App::runningInConsole()) {
                echo(
                    "\n\n\n :" . __LINE__ . ""
                );
            } else echo(
                "<small>By: \n" . __FILE__ . ":" . __LINE__ . "  \n" . __CLASS__ . "@" . __METHOD__ . "</small>"
            );
            exit;
        } catch (\Exception $e) {
            if (App::runningInConsole()) {
                echo $msg = (__LINE__ . " ERROR: Function (" . __FUNCTION__ . "), File (" . __FILE__ . "), Line (" . __LINE__ . "): " . $e->getMessage());
            } else
                echo $msg = (__LINE__ . " ERROR: Function (" . __FUNCTION__ . "), File (" . __FILE__ . "), Line (" . __LINE__ . "): " . $e->getMessage());
            dd(
                $e->getMessage(),
                $msg,
                debug_backtrace()
            );
            exit;
        }

        die(1);
    }
}

if (! function_exists('dx')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed  $args
     * @return void
     */
    function dx(...$args)
    {
        try {
            $debug = @debug_backtrace();
            if(!empty($debug) AND is_array($debug))
            {
                $call = @current($debug);
            } else throw new \Exception(__LINE__ . " function debug_backtrace() returned: {$debug}");
            $line = (isset($call['line'])?$call['line']:__LINE__);
            $file = (isset($call['file'])?$call['file']:__FILE__);
            $file = @basename($file);

            echo( "[{$file}] Line ({$line}): <br>" );

            collect($args)->each(function ($e) {
                dump($e);
            });
            echo(
                "<small>By: \n" . __FILE__ . ":" . __LINE__ . "  \n" . __CLASS__ . "@" . __METHOD__ . "</small>"
            );
        }
        catch(\Exception $e)
        {
            echo $msg = (__LINE__ . " ERROR: Function (".__FUNCTION__."), File (".__FILE__."), Line (".__LINE__."): ".$e->getMessage());
            dd(
                $e->getMessage(),
                $msg,
                debug_backtrace()
            );
            exit;
        }
    }
}
if (! function_exists('dxx')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed  $args
     * @return void
     */
    function dxx(...$args) { }
}
#endregion

