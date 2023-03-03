<?php
/**
 * Payrexx Payment Gateway.
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 */
namespace Payrexx\PayrexxPaymentGateway\Service;

use Db;

class PayrexxDbService
{
    /**
     * @param int $idCart cart id
     * @param int $idGateway gateway id
     * @return bool
     */
    public function insertCartGatewayId($idCart, $idGateway)
    {
        if (empty($idCart) || empty($id_gateway)) {
            return false;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'payrexx_gateway` (`id_cart`, `id_gateway`)
            VALUES (' . (int) $idCart . ',' . (int) $idGateway . ')'
            . 'ON DUPLICATE KEY UPDATE id_gateway = ' . (int) $idGateway
        );
    }

    /**
     * @param int $idCart cart id
     * @return int
     */
    public function getCartGatewayId($idCart)
    {
        if (empty($idCart)) {
            return null;
        }

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT id_gateway FROM `' . _DB_PREFIX_ . 'payrexx_gateway`
            WHERE id_cart = ' . (int) $idCart
        );
    }
    /**
     * @param int $idGateway cart id
     * @return int
     */
    public function getGatewayCartId($idGateway)
    {
        if (empty($idGateway)) {
            return null;
        }

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT id_cart FROM `' . _DB_PREFIX_ . 'payrexx_gateway`
            WHERE id_gateway = ' . (int) $idGateway
        );
    }
}
