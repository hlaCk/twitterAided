<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 30/10/2019
 * Time: 12:10 PM
 */

namespace App\Utilities;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\ResourceResponse;

/**
 * ##Class ApiResource
 *
 * -----------------------
 *
 * {@link Array}
 * **Response Members**:
 *  * data:     _{@link array}_                 `null`**(Exist if no errors)**
 *          Array of data.
 *  * error:    _{@link Boolean}|{@link Null}_  `false`**(Exist if no errors)**
 *          Error value.
 *  * message:  _{@link String}_                **(Exist if not null)**
 *          Message.
 *  * errors:   _{@link array}_                 **(Exist if not null)**
 *          List of errors.
 *
 * -----------------------
 *
 *
 * @package Modules\Tools\Entities
 */
class ApiResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string
     */
    public static $wrap = 'data';

    /**
     * @var int Response status
     */
    public
        $error      = false,
        $message    = null,
        $errors     = [],
        $status     = 200;

    /**
     * Create a new ApiResource instance.
     *
     * @param  mixed        $data []
     * @param  int          $status 200
     * @param  bool         $error false
     * @param  string|null  $message null
     *
     * @return void
     */
    public function __construct($data = [], $status = null, $error = null, $message = null) {
        parent::__construct($data);

        $this->status   = !is_null($status)  ?   $status : 200;
        $this->error    = !is_null($error)   ?   $error  : false;
        $this->message  = !is_null($message) ?   $message: null;

        $this->buildAdditionals();
    }

    public function buildAdditionals() {
        $response = [];
        if(!is_null($this->message)) $response['message'] = $this->message;
        if(!is_null($this->error)) $response['error'] = $this->error;
        if($this->errors) $response['errors'] = $this->errors;

        $this->with = $response;

        return $this;
    }

    /**
     * Customize the response for a request.
     *
     * @api
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Http\JsonResponse $response
     * @return void
     */
    public function withResponse($request, $response)
    {
        $this->status && $response->setStatusCode($this->status);
        $this->buildAdditionals();
    }

    /**
     * Create a new resource instance.
     *
     * @param  mixed  ...$parameters
     * @return static
     */
    public static function make(...$parameters) {
        return new static(...$parameters);
    }

    /**
     * Set resource instance.
     *
     * @param  mixed  $resource
     * @return self
     */
    public function setData($resource) {
        $this->resource = $resource;
        return $this;
    }
    /**
     * Set `error` value.
     *
     * @param  bool  $value true
     * @return self
     */
    public function setError($value = true) {
        $this->error = $value;
        return $this;
    }
    /**
     * Set `message` value.
     *
     * @param  string|null  $value null
     * @return self
     */
    public function setMessage($value = null) {
        $this->message = $value;
        return $this;
    }
    /**
     * Set `errors` value.
     *
     * @param  array  $value []
     * @return self
     */
    public function setErrors($value = []) {
        $this->errors = $value;
//        return $this->buildAdditionals();
        return $this;
    }
    /**
     * Append `errors`.
     *
     * @param  mixed  $value
     * @return self
     */
    public function addError($value) {
        $this->errors = toCollect($this->errors)->merge(toCollect($value))->toArray();
        return $this;
    }
    /**
     * Set response status.
     *
     * @param  int  $value 200
     * @return self
     */
    public function setStatus($value = 200) {
        !is_null($value) && ($this->status = $value);
        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        return (new ResourceResponse(
            $this->buildAdditionals()
        ))->toResponse($request);
    }
}
