<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Worldline\PaymentCore\Api\Data\EmailSendingListInterface;
use Worldline\PaymentCore\Api\Data\EmailSendingListInterfaceFactory;
use Worldline\PaymentCore\Api\EmailSendingListRepositoryInterface;
use Worldline\PaymentCore\Model\ResourceModel\EmailSendingList as EmailSendingListResource;
use Worldline\PaymentCore\Model\ResourceModel\EmailSendingList\CollectionFactory;

class EmailSendingListRepository implements EmailSendingListRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var EmailSendingListResource
     */
    private $emailSendingListResource;

    /**
     * @var EmailSendingListInterfaceFactory
     */
    private $emailSendingListFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        EmailSendingListResource $emailSendingListResource,
        EmailSendingListInterfaceFactory $emailSendingListFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->emailSendingListResource = $emailSendingListResource;
        $this->emailSendingListFactory = $emailSendingListFactory;
    }

    public function count(string $incrementId, string $level): int
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(EmailSendingListInterface::INCREMENT_ID, ['eq' => $incrementId]);
        $collection->addFieldToFilter(EmailSendingListInterface::LEVEL, ['eq' => $level]);

        return $collection->count();
    }

    public function save(EmailSendingListInterface $emailSendingList): EmailSendingListInterface
    {
        try {
            $this->emailSendingListResource->save($emailSendingList);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save email sending entity: %1', $exception->getMessage()));
        }

        return $emailSendingList;
    }

    public function setQuoteToEmailList(string $incrementId, string $level): void
    {
        /** @var EmailSendingListInterface $emailSendingList */
        $emailSendingList = $this->emailSendingListFactory->create();
        $emailSendingList->setIncrementId($incrementId);
        $emailSendingList->setLevel($level);

        $this->save($emailSendingList);
    }
}
