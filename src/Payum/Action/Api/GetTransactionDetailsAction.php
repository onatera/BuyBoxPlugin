<?php
namespace Onatera\SyliusBuyboxPlugin\Payum\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Onatera\SyliusBuyboxPlugin\Payum\Api;
use Onatera\SyliusBuyboxPlugin\Payum\Request\Api\GetTransactionDetails;
use Payum\Core\Exception\RequestNotSupportedException;

class GetTransactionDetailsAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request GetTransactionDetails */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $transactionIndex = 'PAYMENTREQUEST_'.$request->getPaymentRequestN().'_TRANSACTIONID';
        if (false == $model[$transactionIndex]) {
            throw new LogicException($transactionIndex.' must be set.');
        }

        $result = $this->api->getTransactionDetails(array('TRANSACTIONID' => $model[$transactionIndex]));
        foreach ($result as $name => $value) {
            if (in_array($name, $this->getPaymentRequestNFields())) {
                $model['PAYMENTREQUEST_'.$request->getPaymentRequestN().'_'.$name] = $value;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetTransactionDetails &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

    /**
     * @return array
     */
    protected function getPaymentRequestNFields()
    {
        return array(
            'TRANSACTIONID',
            'PARENTTRANSACTIONID',
            'RECEIPTID',
            'TRANSACTIONTYPE',
            'PAYMENTTYPE',
            'ORDERTIME',
            'AMT',
            'CURRENCYCODE',
            'FEEAMT',
            'SETTLEAMT',
            'TAXAMT',
            'EXCHANGERATE',
            'PAYMENTSTATUS',
            'PENDINGREASON',
            'REASONCODE',
        );
    }
}
