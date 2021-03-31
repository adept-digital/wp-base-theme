<?php

namespace AdeptDigital\WpBaseTheme\Tests\Unit;

use AdeptDigital\WpBaseTheme\AbstractChildTheme;
use AdeptDigital\WpBaseTheme\AbstractTheme;
use AdeptDigital\WpBaseTheme\Exception\ThemeException;
use PHPUnit\Framework\TestCase;

class AbstractChildThemeTest extends TestCase
{
    private const TEST_CHILD_THEME_DIR = TEST_DATA_DIR . '/test-theme-child';
    private const TEST_CHILD_THEME = self::TEST_CHILD_THEME_DIR . '/functions.php';
    private const TEST_THEME_DIR = TEST_DATA_DIR . '/test-theme';
    private const TEST_THEME = self::TEST_THEME_DIR . '/functions.php';

    protected function setUp(): void
    {
        switch_theme('test-theme-child');
    }

    protected function tearDown(): void
    {
        switch_theme('test-theme-child');
    }

    private function createMockChildTheme(): AbstractChildTheme
    {
        return $this->getMockBuilder(AbstractChildTheme::class)
            ->setConstructorArgs(['def', 'abc', self::TEST_CHILD_THEME])
            ->getMockForAbstractClass();
    }

    private function createMockTheme(): AbstractTheme
    {
        return $this->getMockBuilder(AbstractTheme::class)
            ->setConstructorArgs(['abc', self::TEST_THEME])
            ->getMockForAbstractClass();
    }

    public function testConstruct()
    {
        $this->expectNotToPerformAssertions();
        $this->createMockChildTheme();
    }

    public function testInvoke()
    {
        $theme = $this->createMockChildTheme();
        $theme();
        $this->assertIsInt(has_action('abc_boot', [$theme, 'setParent']));
        $this->assertIsInt(has_action('after_setup_theme', [$theme, 'doBoot']));
        $this->assertIsInt(has_action('init', [$theme, 'doInit']));
    }

    public function testGetId()
    {
        $theme = $this->createMockChildTheme();
        $this->assertEquals('test-theme-child', $theme->getId());
    }

    public function testGetBasePath()
    {
        $theme = $this->createMockChildTheme();
        $this->assertStringMatchesFormat('/%s/wp-content/themes/test-theme-child', $theme->getBasePath());
        $this->assertEquals(get_stylesheet_directory(), $theme->getBasePath());
        $this->assertNotEquals(get_template_directory(), $theme->getBasePath());
    }

    public function testGetPath()
    {
        $theme = $this->createMockChildTheme();
        $theme->setParent($this->createMockTheme());
        $this->assertStringEndsWith('/test-theme-child/style.css', $theme->getPath('style.css'));
        $this->assertStringEndsWith('/test-theme/index.php', $theme->getPath('index.php'));
    }

    public function testGetBaseUri()
    {
        $theme = $this->createMockChildTheme();
        $this->assertStringMatchesFormat('http://%s/wp-content/themes/test-theme-child', $theme->getBaseUri());
        $this->assertEquals(get_stylesheet_directory_uri(), $theme->getBaseUri());
        $this->assertNotEquals(get_template_directory_uri(), $theme->getBaseUri());
    }

    public function testGetUri()
    {
        $theme = $this->createMockChildTheme();
        $theme->setParent($this->createMockTheme());
        $this->assertStringEndsWith('/test-theme-child/style.css', $theme->getUri('style.css'));
        $this->assertStringEndsWith('/test-theme/index.php', $theme->getUri('index.php'));
    }

    public function testSetGetParent()
    {
        $theme = $this->createMockChildTheme();
        $parent = $this->createMockTheme();
        $theme->setParent($parent);
        $this->assertSame($parent, $theme->getParent());
    }

    public function testGetParentNull()
    {
        $theme = $this->createMockChildTheme();
        $this->expectException(ThemeException::class);
        $theme->getParent();
    }

    public function testSetParentSelf()
    {
        $theme = $this->createMockChildTheme();
        $this->expectException(ThemeException::class);
        $theme->setParent($theme);
    }
}