<?php

namespace AdeptDigital\WpBaseTheme;

use AdeptDigital\WpBaseTheme\Exception\ThemeException;

/**
 * Base Child Theme
 */
abstract class AbstractChildTheme extends AbstractTheme
{
    /**
     * Namespace of parent theme
     *
     * @var string
     */
    private string $parentNamespace;

    /**
     * Parent theme
     *
     * @var ThemeInterface
     */
    private ThemeInterface $parent;

    /**
     * Base child theme constructor.
     *
     * @param string $namespace
     * @param string $parentNamespace
     * @param string $file
     */
    public function __construct(string $namespace, string $parentNamespace, string $file)
    {
        parent::__construct($namespace, $file);
        $this->parentNamespace = $parentNamespace;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(): void
    {
        parent::__invoke();
        add_action("{$this->parentNamespace}_boot", [$this, 'setParent']);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return get_stylesheet();
    }

    /**
     * @inheritDoc
     */
    public function getBasePath(): string
    {
        return get_stylesheet_directory();
    }

    /**
     * @inheritDoc
     */
    public function getBaseUri(): string
    {
        return get_stylesheet_directory_uri();
    }

    /**
     * Get the parent theme
     *
     * @return ThemeInterface
     */
    public function getParent(): ThemeInterface
    {
        if (!isset($this->parent)) {
            throw new ThemeException('Cannot get parent theme before parent theme has booted');
        }

        return $this->parent;
    }

    /**
     * Set the parent theme
     *
     * @param ThemeInterface $parent
     * @return void
     */
    public function setParent(ThemeInterface $parent): void
    {
        if ($parent instanceof self) {
            throw new ThemeException('Cannot use child theme as parent.');
        }

        $this->parent = $parent;
    }
}