<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Setup;

use Exception;

class SetupException extends Exception
{

    /**
     * Constructor.
     *
     * @param string  $message The error message.
     * @param integer $code    The error code.
     *
     * @return void
     */
    public function __construct($message = '', $code = 0)
    {
        parent::__construct(sprintf('HubShop.ly setup error: %s', $message), $code);
    }

}
