<?php

namespace AdeptDigital\WpBaseTheme;

use AdeptDigital\WpBaseComponent\AbstractComponent;
use AdeptDigital\WpBaseTheme\Exception\InvalidThemeException;
use WP_Theme;

/**
 * Base Theme
 */
abstract class AbstractTheme extends AbstractComponent implements ThemeInterface
{
    /**
     * Codes returned by WP_Theme which indicated the theme is invalid
     *
     * @var array
     */
    private const INVALID_THEME_CODES = [
        'theme_not_found',
        'theme_no_stylesheet',
        'theme_stylesheet_not_readable'
    ];

    /**
     * WordPress Theme object
     *
     * @var WP_Theme|null
     */
    private $theme;

    /**
     * Base theme constructor.
     *
     * @param string $namespace
     * @param string $file
     */
    public function __construct(string $namespace, string $file)
    {
        parent::__construct($namespace, $file);

        $themeDir = dirname($file);
        $basePath = $this->getBasePath();
        if (
            $themeDir !== $basePath &&
            $themeDir !== realpath($basePath)
        ) {
            throw new InvalidThemeException($this->getFile());
        }
    }

    /**
     * @inheritDoc
     */
    public function __invoke(): void
    {
        parent::__invoke();
        add_action('after_setup_theme', [$this, 'doBoot']);
    }

    /**
     * Triggers namespaced boot action.
     *
     * Passes `$this` as the first parameter to allow hooking into the
     * theme.
     *
     * @return void
     */
    public function doBoot(): void
    {
        do_action($this->getNamespace('boot'), $this);
    }

    /**
     * @inheritDoc
     */
    public function getFile(): string
    {
        return dirname(parent::getFile()) . '/style.css';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return get_template();
    }

    /**
     * @inheritDoc
     */
    public function getBasePath(): string
    {
        return get_template_directory();
    }

    /**
     * @inheritDoc
     */
    public function getBaseUri(): string
    {
        return get_template_directory_uri();
    }

    /**
     * @inheritDoc
     */
    public function getMetaData(string $name): ?string
    {
        if (!isset($this->theme)) {
            $theme = wp_get_theme($this->getId());
            $error = $theme->errors() ? $theme->errors()->get_error_code() : null;
            if (in_array($error, self::INVALID_THEME_CODES)) {
                throw new InvalidThemeException($this->getFile());
            }
            $this->theme = $theme;
        }

        $value = $this->theme->display($name, false, true);
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        return is_string($value) && $value !== '' ? $value : null;
    }
}