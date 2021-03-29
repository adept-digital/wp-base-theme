<?php

namespace AdeptDigital\WpBaseTheme\Exception;

use RuntimeException;

/**
 * Invalid Theme Exception
 *
 * Thrown when a theme is invalid, inactive or missing.
 */
class InvalidThemeException extends RuntimeException implements ThemeExceptionInterface
{
    /**
     * Invalid theme file
     *
     * @var string
     */
    private string $themeFile;

    /**
     * Invalid Theme Exception constructor.
     *
     * @param string $themeFile
     */
    public function __construct(string $themeFile)
    {
        parent::__construct("Invalid, inactive, or missing theme: {$themeFile}");
        $this->themeFile = $themeFile;
    }

    /**
     * Get the invalid theme file.
     *
     * @return string
     */
    public function getThemeFile(): string
    {
        return $this->themeFile;
    }
}