<?php

namespace AdeptDigital\WpBaseTheme\Exception;

use RuntimeException;

/**
 * Generic Theme Exception
 */
class ThemeException extends RuntimeException implements ThemeExceptionInterface
{
    /**
     * Theme Exception constructor.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}