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

    public $address;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = 'payrexx';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Payrexx';
        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = array('min' => '1.6');
        $this->controllers = array('payment', 'validation');

        parent::__construct();

        $this->displayName = $this->l('Payrexx');
        $this->description = $this->l('Accept payments using Payrexx Payment gateway');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        // Install default
        if (!parent::install()) {
            return false;
        }
        // install DataBase
        if (!$this->installSQL()) {
            return false;
        }
        // Registration hook
        if (!$this->registrationHook()) {
            return false;
        }

        if (!Configuration::updateValue('PAYREXX_LABEL', '')
            || !Configuration::updateValue('PAYREXX_API_SECRET', '')
            || !Configuration::updateValue('PAYREXX_INSTANCE_NAME', '')
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
    private function installSQL()
    {
        return true;
    }

    /**
     * [registrationHook description]
     * @return [type] [description]
     */
    private function registrationHook()
    {
        if (_PS_VERSION_ >= '1.7' && !$this->registerHook('paymentOptions')) {
            return false;
        } elseif (_PS_VERSION_ < '1.7' && !$this->registerHook('payment')) {
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
            'PAYREXX_PAY_ICONS',
        );
        foreach ($config as $var) {
            Configuration::deleteByName($var);
        }

        //Uninstall DataBase
        if (!$this->uninstallSQL()) {
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
    private function uninstallSQL()
    {
        return true;
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
//            Configuration::updateValue('PAYREXX_PAY_ICONS', serialize(Tools::getValue('payrexx_pay_icons')));
        }
    }

    //Hook payment for version < 1.7
    public function hookPayment($params)
    {
        $this->smarty->assign(array(
            'payrexx_url' => $this->context->link->getModuleLink($this->name, 'payrexx'),
            'image_path' => $this->_path,
            'title' => $this->displayName
        ));
        return $this->display(__FILE__, 'payrexx_payment.tpl');
    }

    //payment hook for version >= 1.7
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
        $payments_options = array(
            $payment_options,
        );

        return $payments_options;
    }


    public function validateOrder(
        $id_cart,
        $id_order_state,
        $payment_method = 'Unknown',
        $message = null,
        $transaction = array(),
        $currency_special = null,
        $dont_touch_amount = false,
        $secure_key = false,
        Shop $shop = null
    ) {
        $cart = new Cart((int)$id_cart);
        $total_ps = (float)$cart->getOrderTotal(true, Cart::BOTH);

        parent::validateOrder(
            (int)$id_cart,
            (int)$id_order_state,
            (float)$total_ps,
            $payment_method,
            $message,
            $transaction,
            $currency_special,
            $dont_touch_amount,
            $secure_key,
            $shop
        );
    }
}
