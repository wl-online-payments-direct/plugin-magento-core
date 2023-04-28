<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Worldline\PaymentCore\Model\Payment\PaymentIdFormatter;

class PaymentIdFormatterTest extends TestCase
{
    /**
     * @var PaymentIdFormatter
     */
    private $paymentIdFormatter;

    protected function setUp(): void
    {
        $this->paymentIdFormatter = new PaymentIdFormatter;
    }

    /**
     * @param string $input
     * @param string $output
     * @param bool $postfix
     * @return void
     *
     * @dataProvider dataProviderSuccessFormat
     * @throws LocalizedException
     */
    public function testSuccessValidateAndFormat(string $input, string $output, bool $postfix): void
    {
        $this->assertEquals(
            $this->paymentIdFormatter->validateAndFormat($input, $postfix),
            $output
        );
    }

    public function dataProviderSuccessFormat(): array
    {
        return [
            ['9000003261218192000', '3261218192', false],
            ['9000003261218192000', '3261218192_0', true],
            ['3261218192', '3261218192', false],
            ['3261218192', '3261218192_0', true],
            ['3261218192_0', '3261218192_0', true],
            ['3261218192_0', '3261218192', false],
        ];
    }

    /**
     * @param string $input
     * @return void
     *
     * @dataProvider dataProviderFailedFormat
     */
    public function testFailedValidateAndFormat(string $input): void
    {
        $this->expectException(LocalizedException::class);
        $this->paymentIdFormatter->validateAndFormat($input);
    }

    public function dataProviderFailedFormat(): array
    {
        return [
            ['19000003261218192000'],
            ['13261218192'],
            ['111'],
        ];
    }
}
