<?php
/**
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Payrexx <support@payrexx.com>
 * @copyright  2017 Payrexx
 * @license MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Payrexx extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'payrexx';
        $this->tab = 'payments_gateways';
        $this->module_key = '0c4dbfccbd85dd948fd9a13d5a4add90';
        $this->version = '1.0.9';
        $this->author = 'Payrexx';
        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = array('min' => '1.6');
        $this->controllers = array('payment', 'validation', 'gateway');

        parent::__construct();

        $this->displayName = $this->l('Payrexx');
        $this->description = $this->l('Accept payments using Payrexx Payment gateway');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->validateDb();
    }

    private function validateDb()
    {
        Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'payrexx_gateway` (
                id_cart INT(11) NOT NULL UNIQUE,
                id_gateway INT(11) UNSIGNED DEFAULT "0" NOT NULL,
                PRIMARY KEY (`id_cart`)
            ) DEFAULT CHARSET=utf8');
        Db::getInstance()->execute('
            ALTER TABLE ' . _DB_PREFIX_ . 'cart DROP COLUMN id_gateway');
    }

    public function install()
    {
        // Install default
        if (!parent::install() || !$this->installDb() || !$this->registrationHook()) {
            return false;
        }

        if (!Configuration::updateValue('PAYREXX_LABEL', '')
            || !Configuration::updateValue('PAYREXX_API_SECRET', '')
            || !Configuration::updateValue('PAYREXX_INSTANCE_NAME', '')
            || !Configuration::updateValue('PAYREXX_USE_MODAL', '')
            || !Configuration::updateValue('PAYREXX_PAY_ICONS', '')
        ) {
            return false;
        }

        return true;
    }

    /**
     * Install DataBase table
     * @return boolean if install was successfull
     */
    private function installDb()
    {
         return Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'payrexx_gateway` (
                id_cart INT(11) NOT NULL UNIQUE,
                id_gateway INT(11) UNSIGNED DEFAULT "0" NOT NULL,
                PRIMARY KEY (`id_cart`)
            ) DEFAULT CHARSET=utf8');
    }

    /**
     * [registrationHook description]
     * @return [type] [description]
     */
    private function registrationHook()
    {
        if (_PS_VERSION_ >= '1.7' && !$this->registerHook('paymentOptions')) {
            return false;
        } elseif (_PS_VERSION_ < '1.7' &&
            (!$this->registerHook('payment') || !$this->registerHook('paymentReturn'))) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $config = array(
            'PAYREXX_LABEL',
            'PAYREXX_API_SECRET',
            'PAYREXX_INSTANCE_NAME',
            'PAYREXX_USE_MODAL',
            'PAYREXX_PAY_ICONS',
        );
        foreach ($config as $var) {
            Configuration::deleteByName($var);
        }

        //Uninstall DataBase
        if (!$this->uninstallDb()) {
            return false;
        }

        // Uninstall default
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    /**
     * Uninstall DataBase table
     * @return boolean if install was successfull
     */
    private function uninstallDb()
    {
        return Db::getInstance()->execute('DROP TABLE `' . _DB_PREFIX_ . 'payrexx_gateway`');
    }

    public function getContent()
    {
        $this->postProcess();

//        $options = array(
//            array('id_option' => 'masterpass', 'name' => 'Masterpass',),
//            array('id_option' => 'mastercard', 'name' => 'Mastercard',),
//            array('id_option' => 'visa', 'name' => 'Visa',),
//            array('id_option' => 'apple_pay', 'name' => 'Apple Pay',),
//            array('id_option' => 'maestro', 'name' => 'Maestro',),
//            array('id_option' => 'jcb', 'name' => 'JCB',),
//            array('id_option' => 'american_express', 'name' => 'American Express',),
//            array('id_option' => 'paypal', 'name' => 'PayPal',),
//            array('id_option' => 'bitcoin', 'name' => 'Bitcoin',),
//            array('id_option' => 'sofortueberweisung_de', 'name' => 'Sofort Ueberweisung',),
//            array('id_option' => 'airplus', 'name' => 'Airplus',),
//            array('id_option' => 'billpay', 'name' => 'Billpay',),
//            array('id_option' => 'bonuscard', 'name' => 'Bonus card',),
//            array('id_option' => 'cashu', 'name' => 'CashU',),
//            array('id_option' => 'cb', 'name' => 'Carte Bleue',),
//            array('id_option' => 'diners_club', 'name' => 'Diners Club',),
//            array('id_option' => 'direct_debit', 'name' => 'Direct Debit',),
//            array('id_option' => 'discover', 'name' => 'Discover',),
//            array('id_option' => 'elv', 'name' => 'ELV',),
//            array('id_option' => 'ideal', 'name' => 'iDEAL',),
//            array('id_option' => 'invoice', 'name' => 'Invoice',),
//            array('id_option' => 'myone', 'name' => 'My One',),
//            array('id_option' => 'paysafecard', 'name' => 'Paysafe Card',),
//            array('id_option' => 'postfinance_card', 'name' => 'PostFinance Card',),
//            array('id_option' => 'postfinance_efinance', 'name' => 'PostFinance E-Finance',),
//            array('id_option' => 'swissbilling', 'name' => 'SwissBilling',),
//            array('id_option' => 'twint', 'name' => 'TWINT'),
//        );
        $fields_form = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Title'),
                    'name' => 'payrexx_label',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('API Secret'),
                    'name' => 'payrexx_api_secret',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('INSTANCE NAME') .
                        "<br /><small style='color:#00f; font-weight:normal'>
                            (INSTANCE NAME is a part of the url where you access your payrexx installation. 
                            https://INSTANCE.payrexx.com)</small>",
                    'name' => 'payrexx_instance_name',
                    'required' => true
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Use Modal Checkout'),
                    'name' => 'payrexx',
                    'values' => array(
                        'query' => array(
                            array(
                                'id' => 'use_modal',
                                'val' => '1',
                            )
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
//                array(
//                    'type' => 'select',
//                    'label' => $this->l('Payment Icons'),
//                    'name' => 'payrexx_pay_icons',
//                    'multiple' => true,
//                    'options' => array(
//                        'query' => $options,
//                        'id' => 'id_option',
//                        'name' => 'name'
//                    )
//                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $fields_value = array(
            'payrexx_label' => Configuration::get('PAYREXX_LABEL'),
            'payrexx_api_secret' => Configuration::get('PAYREXX_API_SECRET'),
            'payrexx_instance_name' => Configuration::get('PAYREXX_INSTANCE_NAME'),
            'payrexx_use_modal' => (bool)Configuration::get('PAYREXX_USE_MODAL'),
            'payrexx_pay_icons' => unserialize(Configuration::get('PAYREXX_PAY_ICONS')),
        );
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->submit_action = 'payrexx_config';
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->tpl_vars = array(
            'fields_value' => $fields_value,
            'id_language' => $this->context->language->id,
            'back_url' => $this->context->link->getAdminLink('AdminModules')
                . '&configure=' . $this->name
                . '&tab_module=' . $this->tab
                . '&module_name=' . $this->name
                . '#paypal_params'
        );
        $form = $helper->generateForm($fields_form);

        return $form;
    }

    private function postProcess()
    {
        if (Tools::isSubmit('payrexx_config')) {
            Configuration::updateValue('PAYREXX_LABEL', Tools::getValue('payrexx_label'));
            Configuration::updateValue('PAYREXX_API_SECRET', Tools::getValue('payrexx_api_secret'));
            Configuration::updateValue('PAYREXX_INSTANCE_NAME', Tools::getValue('payrexx_instance_name'));
            Configuration::updateValue('PAYREXX_USE_MODAL', Tools::getValue('payrexx_use_modal'));
//            Configuration::updateValue('PAYREXX_PAY_ICONS', serialize(Tools::getValue('payrexx_pay_icons')));
        }
    }

    // Payment hook for version < 1.7
    public function hookPaymentReturn($params)
    {
        // By default Prestashop v1.6 will display the order confirmation message for the guest users
        if ($this->context->customer->is_guest) {
            return;
        }

        $invoice_url = null;
        if ($params['objOrder'] && !empty($params['objOrder']->id)) {
            $invoice_url = $this->context->link->getPageLink(
                'pdf-invoice',
                true,
                $this->context->language->id,
                "id_order={$params['objOrder']->id}"
            );
        }
        $customer_email = null;
        if ($params['cart'] && !empty($params['cart']->id_customer)) {
            $customer = new Customer($params['cart']->id_customer);
            $customer_email = $customer->email;
        }
        $this->smarty->assign(array(
            'invoice_url' => $invoice_url,
            'customer_email' => $customer_email,
        ));
        return $this->display(__FILE__, 'confirmation.tpl');
    }

    // Payment hook for version < 1.7
    public function hookPayment($params)
    {
        $this->smarty->assign(array(
            'payrexx_url' => $this->context->link->getModuleLink($this->name, 'payrexx'),
            'image_path' => $this->_path,
            'title' => $this->displayName
        ));
        return $this->display(__FILE__, 'payrexx_payment.tpl');
    }

    // Payment hook for version >= 1.7
    public function hookPaymentOptions($params)
    {
//        $payIcons = unserialize(Configuration::get('PAYREXX_PAY_ICONS'));

        $payment_options = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $action_text = $this->l(Configuration::get('PAYREXX_LABEL'));
        $this->context->smarty->assign(array(
            'path' => $this->_path,
        ));
        $payment_options->setCallToActionText($action_text);
        $payment_options->setAction($this->context->link->getModuleLink($this->name, 'payrexx'));

        if (!empty($this->context->cookie->payrexx_gateway_url)) {
            $payment_options->setAdditionalInformation('
                <a id="payrexx-gateway-modal" style="display: none;" data-href="' . $this->context->cookie->payrexx_gateway_url . '"></a>
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
                <script src="https://media.payrexx.com/modal/v1/gateway.min.js"></script>
                <script>jQuery(\'#payrexx-gateway-modal\').payrexxModal().click();</script>
            ');
            $this->context->cookie->payrexx_gateway_url = null;
        }

        $payments_options = array(
            $payment_options,
        );

        return $payments_options;
    }
}
