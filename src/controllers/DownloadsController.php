<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\controllers;

use Craft;
use craft\commerce\Plugin;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Class Downloads Controller
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class DownloadsController extends BaseFrontEndController
{
    // Public Methods
    // =========================================================================

    /**
     * @return Response
     * @throws HttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionPdf(): Response
    {
        $number = Craft::$app->getRequest()->getQueryParam('number');
        $option = Craft::$app->getRequest()->getQueryParam('option', '');
        $order = Plugin::getInstance()->getOrders()->getOrderByNumber($number);

        if (!$order) {
            throw new HttpException('No Order Found');
        }

        $pdf = Plugin::getInstance()->getPdf()->renderPdfForOrder($order, $option);
        $filenameFormat = Plugin::getInstance()->getSettings()->orderPdfFilenameFormat;

        $fileName = $this->getView()->renderObjectTemplate($filenameFormat, $order);

        if (!$fileName) {
            $fileName = 'Order-' . $order->number;
        }

        return Craft::$app->getResponse()->sendContentAsFile($pdf, $fileName . '.pdf', [
            'mimeType' => 'application/pdf'
        ]);
    }

    /**
     * @throws HttpException
     */
    public function actionCsv(): Response
    {
        $this->requirePermission('commerce-manageOrders');

        $startDate = Craft::$app->getRequest()->getRequiredParam('startDate');
        $endDate = Craft::$app->getRequest()->getRequiredParam('endDate');
        $source = Craft::$app->getRequest()->getRequiredParam('source');

        if (strpos($source, ':') !== false) {
            $sourceHandle = explode(':', $source)[1];
        }

        $orderStatusId = isset($sourceHandle) ? Plugin::getInstance()->getOrderStatuses()->getOrderStatusByHandle($sourceHandle)->id : null;

        $csv = Plugin::getInstance()->getReports()->getOrdersCsv($startDate, $endDate, $orderStatusId);

        return Craft::$app->getResponse()->sendContentAsFile($csv, 'orders.csv', [
            'mimeType' => 'text/csv'
        ]);
    }
}
