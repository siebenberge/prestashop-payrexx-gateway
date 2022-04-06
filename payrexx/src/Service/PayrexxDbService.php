<?php

namespace Payrexx\PayrexxPaymentGateway\Service;

use Db;
use PrestaShop\PrestaShop\Adapter\Module\Module;

class PayrexxDbService
{
    /**
     *
     * @param int $id_cart cart id
     * @param int $id_gateway gateway id
     * @return boolean
     */
    public function insertCartGatewayId($id_cart, $id_gateway)
    {
        if (empty($id_cart) || empty($id_gateway)) {
            return false;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'payrexx_gateway` (`id_cart`, `id_gateway`)
            VALUES (' . (int)$id_cart . ',' . (int)$id_gateway . ')'
            . 'ON DUPLICATE KEY UPDATE id_gateway = ' . (int)$id_gateway . '
        ');
    }

    /**
     *
     * @param int $id_cart cart id
     * @return int
     */
    public function getCartGatewayId($id_cart)
    {
        if (empty($id_cart)) {
            return null;
        }

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT id_gateway FROM `' . _DB_PREFIX_ . 'payrexx_gateway`
            WHERE id_cart = ' . (int)$id_cart);
    }
    /**
     *
     * @param int $id_cart cart id
     * @return int
     */
    public function getGatewayCartId($id_gateway)
    {
        if (empty($id_gateway)) {
            return null;
        }

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT id_cart FROM `' . _DB_PREFIX_ . 'payrexx_gateway`
            WHERE id_gateway = ' . (int)$id_gateway);
    }
}