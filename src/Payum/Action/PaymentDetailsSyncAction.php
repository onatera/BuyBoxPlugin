<?php
namespace Onatera\SyliusBuyboxPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;
use Payum\Core\Exception\RequestNotSupportedException;
use Onatera\SyliusBuyboxPlugin\Payum\Api;
use Onatera\SyliusBuyboxPlugin\Payum\Request\Api\GetExpressCheckoutDetails;
use Onatera\SyliusBuyboxPlugin\Payum\Request\Api\GetTransactionDetails;

class PaymentDetailsSyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Sync */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['TOKEN']) {
            return;
        }

        $copiedModel = new ArrayObject(array(
            'TOKEN' => $model['TOKEN'],
        ));

        $this->gateway->execute(new GetExpressCheckoutDetails($copiedModel));
        if (Api::L_ERRORCODE_SESSION_HAS_EXPIRED != $copiedModel['L_ERRORCODE0']) {
            $model->replace($copiedModel);
        }

        foreach (range(0, 9) as $index) {
            if ($model['PAYMENTREQUEST_'.$index.'_TRANSACTIONID']) {
                $this->gateway->execute(new GetTransactionDetails($model, $index));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof Sync) {
            return false;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return false;
        }

        return false == isset($model['BILLINGPERIOD']);
    }
}
