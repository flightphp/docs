<?php

declare(strict_types=1);

namespace Tests\Unit;

use app\utils\DocsLogic;
use DOMDocument;
use flight\Cache;
use flight\Container;
use flight\Engine;
use Latte\Engine as LatteEngine;
use Latte\Essential\TranslatorExtension;
use Latte\Loaders\FileLoader;
use Parsedown;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Utils\CustomTranslator;

#[CoversClass(DocsLogic::class)]
final class DocsLogicTest extends UnitTestCase {
    private Container $container;
    private DocsLogic $docsLogic;
    private Engine $engine;

    function setUp(): void {
        $this->container = new Container;

        $this->container->set(
            Engine::class,
            function (): Engine {
                $this->engine = new Engine;
                $this->engine->register('translator', CustomTranslator::class);

                $this->engine->register(
                    'cache',
                    Cache::class,
                    [__DIR__ . '/../cache/']
                );

                $this->engine->register('parsedown', Parsedown::class);

                $this->engine->register(
                    'latte',
                    LatteEngine::class,
                    [],
                    function (LatteEngine $latte): void {
                        $latte->setTempDirectory(__DIR__ . '/../cache/');
                        $latte->setLoader(new FileLoader(__DIR__ . '/../views/'));

                        $latte->addExtension(new TranslatorExtension(
                            $this
                                ->container
                                ->get(CustomTranslator::class)
                                ->translate(...),
                        ));
                    }
                );

                return $this->engine;
            }
        );

        $this->docsLogic = $this->container->get(DocsLogic::class);
        $_SERVER['HTTP_HOST'] = 'localhost';
    }

    #[Test]
    function it_renders_a_page(): void {
        $this->engine->request()->url = '/test?var=value';

        self::expectOutputString(<<<'html'
        <a href="http://localhost/test"></a>
        <h1>value</h1>

        html);

        $this->docsLogic->renderPage('test.latte', ['variable' => 'value']);
    }

    #[Test]
    function it_can_setup_translator(): void {
        $translator = $this->docsLogic->setupTranslatorService('es', 'v3');

        assert($translator instanceof CustomTranslator);

        self::assertSame('es', $translator->getLanguage());
        self::assertSame('v3', $translator->getVersion());
    }

    #[Test]
    function it_compiles_single_page(): void {
        ob_start();
        $this->docsLogic->compileSinglePage('es', 'v3', 'about');
        $html = ob_get_clean();

        self::assertStringContainsString('composer require flightphp/core', $html);
    }

    #[Test]
    function it_compiles_scrollspy_page(): void {
        ob_start();
        $this->docsLogic->compileScrollspyPage('es', 'v3', 'install', 'install');
        $html = new DOMDocument;
        @$html->loadHTML(ob_get_clean());
        $navLinks = $html->getElementsByTagName('a');
        $target = $navLinks->item(0)->getAttribute('data-target');
        $id = $navLinks->item(0)->getAttribute('id');

        self::assertTrue($navLinks->count() >= 1);
        self::assertStringStartsWith('#', $target);
        self::assertStringStartsWith('#', $id);
    }
}
