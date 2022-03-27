<?php
namespace adjai\backender\core;

class Response {
    private $success;
    private $message;
    private $data;
    private $code;

    public function __construct($status, $message = '', $data = [], $code = 200) {
        $this->success = $status;
        $this->message = $message;
        $this->data = $data;
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @return mixed|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return array|mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public static function isResponse($response) {
        return is_a($response, self::class);
    }

    public static function isResponseError($error) {
        return self::isResponse($error) && !$error->getSuccess();
    }

    public function getOutput() {
        return [
            'success' => $this->getSuccess(),
            'message' => $this->getMessage(),
            'data' => $this->getData(),
        ];
    }

    /**
     * @return int|mixed
     */
    public function getCode() {
        return $this->code;
    }

}
