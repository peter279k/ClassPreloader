<?php

use ClassPreloader\ClassPreloader;
use ClassPreloader\Parser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

class ClassPreloaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * This tests the correct detection and stripping of strict_types declarations while preloading files.
     */
    public function testStripStrictTypeDeclaration()
    {
        $printer = $this->getMockBuilder(PrettyPrinter::class)
            ->disableOriginalConstructor()
            ->setMethods(['prettyPrint']);
        $parser = $this->getMockBuilder(Parser::class)
            ->disableOriginalConstructor()
            ->setMethods(['parse', 'getErrors']);
        $traverser = $this->getMockBuilder(NodeTraverser::class)
            ->disableOriginalConstructor()
            ->setMethods(['traverseFile']);

        $parserMock = $parser->getMock();
        $parserMock->expects($this->once())
            ->method('parse')
            ->willReturn([]);
        $traverserMock = $traverser->getMock();
        $traverserMock->expects($this->once())
            ->method('traverseFile')
            ->willReturn([]);
        $printerMock = $printer->getMock();
        $printerMock->expects($this->once())
            ->method('prettyPrint')
            ->willReturn(
                file_get_contents(__DIR__.'/stubs/StrictClassWithComments.php')
            );

        $classPreloader = new ClassPreloader(
            $printerMock,
            $parserMock,
            $traverserMock
        );

        $code = $classPreloader->getCode(__DIR__.'/stubs/StrictClassWithComments.php');

        // $code should not have 'declare(strict_types=1)' declarations.
        $this->assertNotRegExp(
            '/(.*?)declare\s*\(strict_types\s*=\s*1\)(.*?)/mi',
            $code,
            'Generated ClassPreloader output should correctly detect and strip strict_type declare statements.'
        );
    }
}
