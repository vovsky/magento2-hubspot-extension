<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Helper;

use Magento\Framework\App\Response\Http;
use Magento\Framework\Serialize\Serializer\Json;

class Error
{
    /**
     * @var Http
     */
    private $response;

    /**
     * @var Json
     */
    private $json;

    public function __construct(Http $response, Json $json)
    {
        $this->response = $response;
        $this->json = $json;
    }

    public function prepareResponse($code = null, $message = '', $details = '', $callback = null)
    {
        if ((int)$code < 100) {
            $code = 500;
        }

        $data = [
            'error_code'    => $code,
            'error_message' => $message,
            'error_details' => $details,
        ];

        $response = $this->response;
        $response->setStatusCode($code);
        $response->representJson($this->json->serialize($data));
        if (is_callable($callback)) {
            $callback($response);
        }

        return $response;
    }
}
