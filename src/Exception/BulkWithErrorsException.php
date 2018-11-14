<?php

namespace Vairogs\Utils\Search\Exception;

use Exception;
use Vairogs\Utils\Core\Exception\VairogsException;

class BulkWithErrorsException extends VairogsException
{
    /**
     * @var array
     */
    protected $response;

    /**
     * {@inheritdoc}
     */
    public function __construct($message = '', $code = 0, Exception $previous = null, $response = [])
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }
}
