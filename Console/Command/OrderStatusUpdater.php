<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Worldline\PaymentCore\Model\Order\Creation\OrderCreationProcessor;

class OrderStatusUpdater extends Command
{
    /**
     * @var OrderCreationProcessor
     */
    private $orderCreationProcessor;

    /**
     * @var State
     */
    private $state;

    public function __construct(
        OrderCreationProcessor $worldLineApiProcessor,
        State $state,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->orderCreationProcessor = $worldLineApiProcessor;
        $this->state = $state;
    }

    protected function configure(): void
    {
        $this->setName('worldline:update-order-status');
        $this->setDescription('Change status for all orders in processing status or for the given the order id');
        $this->addOption('increment-id', null, InputOption::VALUE_OPTIONAL, 'Order increment id');
        $this->setHelp(<<<EOT
<info>Execute the command to change the status for all orders in pending status</info>
<info>Specify option "--increment-id" to change status for the particular order</info>
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
            $this->orderCreationProcessor->process((string) $input->getOption('increment-id'));
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Cli::RETURN_FAILURE;
        }
    }
}
