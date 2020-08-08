<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 3/8/2019
 * Time: 03:11 ص
 */

namespace App\Utilities;



class HBladeArg extends ArrayObject
{
	public $argumentNames = ["{arg}", "{argument}"],
			$code = "",
			$name = "";
	
	public function __construct($data, $name = null) {
		$data = collect($data);
		$data->put('else', $data->get('else', false));
		$data->put('data', $data->get('data', []));
		
		parent::__construct($data->toArray());
		$this->name = $name;
	}
	
	public function toElse() {
		return $this->else ? " elseif" : " if";
	}
	
	public function code($code = "") {
		$this->code = $code;
		return $this;
	}

	public function toIfCode($code = null) {
		$code = is_null($code) ? $this->code : $code;
		return $this->toPHPCode($this->toElse() . " ($code):");
	}

	public function toPHPCode($code = null) {
		$code = is_null($code) ? $this->code : $code;
		$_code = "<?php {$code} ?>";
		$_code = str_ireplace($this->argumentNames, "%s", $_code);
		
		return $this->printf($_code);
	}
	
	public function getData($i = null) {
		$d = collect($this->data);
		return $i !== null ? $d->get($i, null) : $d->toArray();
	}
	
	public function printf($code) {
		$arr = collect($this->data)->map(function ($a) {
			try {
				return trim($a);
			} catch (\Exception $exception) {
				return $a;
			}
		});
		
		$code = str_ireplace($this->argumentNames, "%s", $code);
		
		$codesCount = collect(explode('%s', $code))->count() - 1;
		$arrsCount = $arr->count();
		
		if ($arrsCount > $codesCount) {
			$arr = $arr->splice(0, $codesCount);
		} else if ($arrsCount < $codesCount) {
			$arr = $arr->merge(array_fill($arrsCount, $codesCount - $arrsCount, ''));
		}
		
		return call_user_func_array('sprintf', array_merge((array) $code, $arr->toArray()));
	}
	
}