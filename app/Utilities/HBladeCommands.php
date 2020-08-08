<?php

namespace App\Utilities;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class HBladeCommands
{
    public static $prefix_view = 'home::template.partials.models.';

    public static function registerCommands()
    {
        Blade::directive('modelRow',
            function ($arguments) {
                return "<?php echo \Modules\Tools\Entities\HBladeCommands::modelRow($arguments) ?>";
            }
        );

        Blade::directive('modelRows',
            function ($arguments) {
                return "<?php echo \Modules\Tools\Entities\HBladeCommands::modelRows($arguments) ?>";
            }
        );

        Blade::directive('paths',
            function ($arguments) {
                return $arguments ? "<?php echo \App\Utilities\HBladeCommands::modelRow('".
                    str_start('page.breadcrumb', self::$prefix_view)
                    ."', $arguments) ?>" : "";
            }
        );

        /*
        Blade::component(
//            'home::components.home.breadcrumb'
            str_start('home.breadcrumb', 'home::components.')
            , 'breadcrumb');*/
    }

    /**
     * get render one row, second argument is model
     *
     * @param $view
     * @param Model|array|Collection $__model
     * @param array $extraData
     * @return string
     *
     * @throws \Throwable
     */
    public static function modelRow($view, $__model = null, $extraData = [])
    {
        if(!is_array($extraData) && !($extraData instanceof Arrayable)) {
            $extraData = toCollect($extraData)->toArray();
        }

        $result = '';
        $_extraData = array_merge($extraData, [
            '__model'=>$__model??null
        ]);
        $result .= view(str_start($view, self::$prefix_view), $_extraData)->render();

        return $result;
    }

    /**
     * get render multi rows, second argument is array of models
     *
     * @param $view
     * @param Model|array|Collection $__model
     * @param array $extraData
     * @return string
     *
     * @throws \Throwable
     */
    public static function modelRows($view, $__model = null, $extraData = [])
    {
//        du(func_get_args());
        if(!is_array($extraData)) {
            $extraData = $extraData instanceof Arrayable ? $extraData : toCollect($extraData)->toArray();
        }

        $__model_ = toCollect($__model);//!($__model instanceof Arrayable || is_array($__model)) ? [$__model] : $__model;

        $result = '';
        $__model_->each(function ($v) use($extraData, $__model, &$view, &$result){
            $_extraData = array_merge($extraData, [
                '__model'=>$v??null
            ]);
            $result .= view(str_start($view, self::$prefix_view), $_extraData)->render();
        });

        return $result;
    }


}
