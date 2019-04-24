<?php

namespace Kolyunya\Codeception\Tests\Lib\MarkupValidator;

use Exception;
use Kolyunya\Codeception\Lib\MarkupValidator\DefaultMessageFilter;
use Kolyunya\Codeception\Lib\MarkupValidator\MarkupValidatorMessage;
use Kolyunya\Codeception\Lib\MarkupValidator\MarkupValidatorMessageInterface;
use PHPUnit\Framework\TestCase;

class DefaultMessageFilterTest extends TestCase
{
    /**
     * @var DefaultMessageFilter
     */
    private $filter;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->filter = new DefaultMessageFilter();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
    }

    /**
     * @dataProvider filterMessagesDataProvider
     */
    public function testFilterMessages($sourceMessages, $filteredMessagesExpected)
    {
        $this->filter->setConfiguration(array(
            'ignoreWarnings' => false,
            'ignoredErrors' => array(),
        ));

        $filteredMessagesActual = $this->filter->filterMessages($sourceMessages);

        $this->assertEquals(count($filteredMessagesExpected), count($filteredMessagesActual));
        $this->assertArraySubset($filteredMessagesExpected, $filteredMessagesActual);
    }

    /**
     * @dataProvider errorCountThresholdDataProvider
     */
    public function testerrorCountThreshold($messages, $threshold, $filteredMessagesExpected)
    {
        $this->filter->setConfiguration(array(
            'errorCountThreshold' => $threshold,
        ));

        $filteredMessagesActual = $this->filter->filterMessages($messages);

        $this->assertEquals(count($filteredMessagesExpected), count($filteredMessagesActual));
        $this->assertArraySubset($filteredMessagesExpected, $filteredMessagesActual);
    }

    /**
     * @dataProvider ignoreWarningsDataProvider
     */
    public function testIgnoreWarnings($messages, $filteredMessagesExpected)
    {
        $this->filter->setConfiguration(array(
            'ignoreWarnings' => true,
        ));

        $filteredMessagesActual = $this->filter->filterMessages($messages);

        $this->assertEquals(count($filteredMessagesExpected), count($filteredMessagesActual));
        $this->assertArraySubset($filteredMessagesExpected, $filteredMessagesActual);
    }

    /**
     * @dataProvider ignoredErrorsDataProvider
     */
    public function testIgnoredErrors($messages, $ignoredErrors, $filteredMessagesExpected)
    {
        $this->filter->setConfiguration(array(
            'ignoredErrors' => $ignoredErrors,
        ));

        $filteredMessagesActual = $this->filter->filterMessages($messages);

        $this->assertEquals(count($filteredMessagesExpected), count($filteredMessagesActual));
        $this->assertArraySubset($filteredMessagesExpected, $filteredMessagesActual);
    }

    public function testInvaliderrorCountThresholdConfig()
    {
        $this->expectExceptionMessage('Invalid «errorCountThreshold» config key.');

        $warning = new MarkupValidatorMessage();
        $warning->setType(MarkupValidatorMessageInterface::TYPE_WARNING);

        $this->filter->setConfiguration(array(
            'errorCountThreshold' => true,
        ));
        $this->filter->filterMessages(array($warning));
    }

    public function testInvalidIgnoreWarningsConfig()
    {
        $this->expectExceptionMessage('Invalid «ignoreWarnings» config key.');

        $warning = new MarkupValidatorMessage();
        $warning->setType(MarkupValidatorMessageInterface::TYPE_WARNING);

        $this->filter->setConfiguration(array(
            'ignoreWarnings' => array(
                'foo' => false,
                'bar' => true,
            ),
        ));
        $this->filter->filterMessages(array($warning));
    }

    public function testInvalidIgnoreErrorsConfig()
    {
        $this->expectExceptionMessage('Invalid «ignoredErrors» config key.');

        $error = new MarkupValidatorMessage();
        $error->setType(MarkupValidatorMessageInterface::TYPE_ERROR);

        $this->filter->setConfiguration(array(
            'ignoredErrors' => false,
        ));
        $this->filter->filterMessages(array($error));
    }

    public function errorCountThresholdDataProvider()
    {
        return array(
            array(
                array(
                ),
                0,
                array(

                ),
            ),
            array(
                array(
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                ),
                1,
                array(

                ),
            ),
            array(
                array(
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                ),
                2,
                array(

                ),
            ),
            array(
                array(
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                ),
                5,
                array(

                ),
            ),
            array(
                array(
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                ),
                -1,
                array(
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                ),
            ),
            array(
                array(
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                ),
                2,
                array(
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                    new MarkupValidatorMessage(MarkupValidatorMessageInterface::TYPE_ERROR),
                ),
            ),
        );
    }

    public function filterMessagesDataProvider()
    {
        return array(
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_UNDEFINED)
                ),
                array(

                ),
            ),
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_INFO)
                ),
                array(

                ),
            ),
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_WARNING)
                        ->setSummary('Warning text.')
                        ->setMarkup('<h1></h1>')
                    ,
                ),
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_WARNING)
                        ->setSummary('Warning text.')
                        ->setMarkup('<h1></h1>')
                    ,
                ),
            ),
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                        ->setSummary('Error text.')
                        ->setMarkup('<title></title>')
                    ,
                ),
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                        ->setSummary('Error text.')
                        ->setMarkup('<title></title>')
                    ,
                ),
            ),
        );
    }

    public function ignoreWarningsDataProvider()
    {
        return array(
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_WARNING)
                    ,
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                    ,
                ),
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                    ,
                ),
            ),
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                    ,
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_WARNING)
                    ,
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                    ,
                ),
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                    ,
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                    ,
                ),
            ),
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_WARNING)
                    ,
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_WARNING)
                    ,
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_WARNING)
                    ,
                ),
                array(

                ),
            ),
        );
    }

    public function ignoredErrorsDataProvider()
    {
        return array(
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                        ->setSummary('Some error message.')
                    ,
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                        ->setSummary('Some cryptic error message.')
                    ,
                ),
                array(
                    '/some error/i',
                    '/cryptic error/',
                    '/other error/',
                ),
                array(

                ),
            ),
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                        ->setSummary('Some cryptic error message.')
                    ,
                ),
                array(
                    '/some error/',
                    '/other error/',
                ),
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                        ->setSummary('Some cryptic error message.')
                    ,
                ),
            ),
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                        ->setSummary('Some cryptic error message.')
                    ,
                ),
                array(
                    '/cryptic error/',
                ),
                array(

                ),
            ),
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                        ->setSummary('Case insensitive error message.')
                    ,
                ),
                array(
                    '/case insensitive error message./i',
                ),
                array(

                ),
            ),
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                        ->setSummary('Текст ошибки в UTF-8.')
                    ,
                ),
                array(
                    '/Текст ошибки в UTF-8./u',
                ),
                array(

                ),
            ),
            array(
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                    ,
                ),
                array(
                    '/error/',
                ),
                array(
                    (new MarkupValidatorMessage())
                        ->setType(MarkupValidatorMessageInterface::TYPE_ERROR)
                    ,
                ),
            ),
        );
    }
}
