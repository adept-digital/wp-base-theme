<?php

namespace AdeptDigital\WpBaseTheme\Tests\Unit;

use AdeptDigital\WpBaseTheme\AbstractTheme;
use AdeptDigital\WpBaseTheme\Exception\InvalidThemeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractThemeTest extends TestCase
{
    public const TEST_THEME_DIR = TEST_DATA_DIR . '/test-theme';
    public const TEST_THEME = self::TEST_THEME_DIR . '/functions.php';

    protected function setUp(): void
    {
        switch_theme('test-theme');
    }

    protected function tearDown(): void
    {
        switch_theme('test-theme');
    }

    private function createMockTheme(string $file = self::TEST_THEME): AbstractTheme
    {
        return $this->getMockBuilder(AbstractTheme::class)
            ->setConstructorArgs(['abc', $file])
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

    public function testConstructMissingEntryFile()
    {
        $this->expectException(InvalidThemeException::class);
        $this->createMockTheme('/not-found.php');
    }

    public function testConstructInactive()
    {
        switch_theme(WP_DEFAULT_THEME);
        $this->expectException(InvalidThemeException::class);
        $this->createMockTheme();
    }

    public function testConstructChildActive()
    {
        $this->expectNotToPerformAssertions();
        switch_theme('test-theme-child');
        $this->createMockTheme();
    }

    public function testInvoke()
    {
        $theme = $this->createMockTheme();
        $theme();
        $this->assertIsInt(has_action('after_setup_theme', [$theme, 'doBoot']));
        $this->assertIsInt(has_action('init', [$theme, 'doInit']));
    }

    public function testDoBoot()
    {
        $theme = $this->createMockTheme();
        $callable = $this->createMockCallable();
        $callable->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($theme));

        add_action('abc_boot', $callable);
        $theme->doBoot();
    }

    public function testGetFile()
    {
        $theme = $this->createMockTheme();
        $this->assertStringMatchesFormat('/%s/test-theme/style.css', $theme->getFile());

        $theme = $this->createMockTheme(self::TEST_THEME_DIR . '/index.php');
        $this->assertStringMatchesFormat('/%s/test-theme/style.css', $theme->getFile());
    }

    public function testGetId()
    {
        $theme = $this->createMockTheme();
        $this->assertEquals('test-theme', $theme->getId());
    }

    public function testGetBasePath()
    {
        $theme = $this->createMockTheme();
        $this->assertStringMatchesFormat('/%s/wp-content/themes/test-theme', $theme->getBasePath());
        $this->assertEquals(get_template_directory(), $theme->getBasePath());
        $this->assertEquals(get_stylesheet_directory(), $theme->getBasePath());
    }

    public function testGetBaseUri()
    {
        $theme = $this->createMockTheme();
        $this->assertStringMatchesFormat('http://%s/wp-content/themes/test-theme', $theme->getBaseUri());
        $this->assertEquals(get_template_directory_uri(), $theme->getBaseUri());
        $this->assertEquals(get_stylesheet_directory_uri(), $theme->getBaseUri());
    }

    public function testGetMetaData()
    {
        $theme = $this->createMockTheme();
        $this->assertEquals(
            'My Test Theme',
            $theme->getMetaData('Name')
        );
        $this->assertEquals(
            'https://adeptdigital.com.au/wordpress/themes/my-test-theme/',
            $theme->getMetaData('ThemeURI')
        );
        $this->assertNull($theme->getMetaData('Description'));
        $this->assertNull($theme->getMetaData('NotAField'));
    }
}