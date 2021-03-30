<?php

namespace AdeptDigital\WpBaseTheme\Tests\Unit;

use AdeptDigital\WpBaseTheme\AbstractChildTheme;
use AdeptDigital\WpBaseTheme\AbstractTheme;
use AdeptDigital\WpBaseTheme\Exception\ThemeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractChildThemeTest extends TestCase
{
    private const TEST_THEME_DIR = TEST_DATA_DIR . '/test-theme-child';
    private const TEST_THEME = self::TEST_THEME_DIR . '/functions.php';

    protected function setUp(): void
    {
        switch_theme('test-theme-child');
    }

    protected function tearDown(): void
    {
        switch_theme('test-theme-child');
    }

    private function createMockTheme(string $file = self::TEST_THEME): AbstractChildTheme
    {
        return $this->getMockBuilder(AbstractChildTheme::class)
            ->setConstructorArgs(['def', 'abc', $file])
            ->getMockForAbstractClass();
    }

    private function createMockCallable(): MockObject
    {
        return $this
            ->getMockBuilder(\stdclass::class)
            ->addMethods(['__invoke'])
            ->getMock();
    }

    public function testConstruct()
    {
        $this->expectNotToPerformAssertions();
        $this->createMockTheme();
    }

    public function testInvoke()
    {
        $theme = $this->createMockTheme();
        $theme();
        $this->assertIsInt(has_action('abc_boot', [$theme, 'setParent']));
        $this->assertIsInt(has_action('after_setup_theme', [$theme, 'doBoot']));
        $this->assertIsInt(has_action('init', [$theme, 'doInit']));
    }

    public function testGetId()
    {
        $theme = $this->createMockTheme();
        $this->assertEquals('test-theme-child', $theme->getId());
    }

    public function testGetBasePath()
    {
        $theme = $this->createMockTheme();
        $this->assertStringMatchesFormat('/%s/wp-content/themes/test-theme-child', $theme->getBasePath());
        $this->assertEquals(get_stylesheet_directory(), $theme->getBasePath());
        $this->assertNotEquals(get_template_directory(), $theme->getBasePath());
    }

    public function testGetBaseUri()
    {
        $theme = $this->createMockTheme();
        $this->assertStringMatchesFormat('http://%s/wp-content/themes/test-theme-child', $theme->getBaseUri());
        $this->assertEquals(get_stylesheet_directory_uri(), $theme->getBaseUri());
        $this->assertNotEquals(get_template_directory_uri(), $theme->getBaseUri());
    }

    public function testSetGetParent()
    {
        $theme = $this->createMockTheme();
        $parent = $this->getMockForAbstractClass(AbstractTheme::class, ['abc', AbstractThemeTest::TEST_THEME]);
        $theme->setParent($parent);
        $this->assertSame($parent, $theme->getParent());
    }

    public function testGetParentNull()
    {
        $theme = $this->createMockTheme();
        $this->expectException(ThemeException::class);
        $theme->getParent();
    }

    public function testSetParentSelf()
    {
        $theme = $this->createMockTheme();
        $this->expectException(ThemeException::class);
        $theme->setParent($theme);
    }
}