<?php
/**
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Payrexx <integration@payrexx.com>
 * @copyright  2022 Payrexx
 * @license MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use \Payrexx\PayrexxPaymentGateway\Util\ConfigurationUtil;
use \Payrexx\PayrexxPaymentGateway\Service\PayrexxApiService;
use \PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class Payrexx extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'payrexx';
        $this->tab = 'payments_gateways';
        $this->module_key = '0c4dbfccbd85dd948fd9a13d5a4add90';
        $this->version = '1.3.2';
        $this->author = 'Payrexx';
        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = array('min' => '1.6');
        $this->controllers = array('payment', 'validation', 'gateway');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Payrexx';
        $this->description = 'Accept payments using Payrexx Payment gateway';
        $this->confirmUninstall = 'Are you sure you want to uninstall?';
    }

    public function install()
    {
        // Install default
        if (!$this->installDb() ||
            !$this->registrationHook() ||
            !$this->installTab()
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
        include dirname(__FILE__) . '/sql/install.php';
        return parent::install();
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

    private function installTab()
    {
        // Create new admin tab
        $tab = new Tab();
        $tab->id_parent = -1;
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Payment Methods';
        }
        $tab->class_name = 'AdminPayrexxPaymentMethods';
        $tab->module = $this->name;
        $tab->active = 1;

        return $tab->add();
    }

    public function uninstall()
    {
        $config = ConfigurationUtil::getConfigKeys();
        foreach ($config as $var) {
            Configuration::deleteByName($var);
        }

        //Uninstall DataBase
        if (!$this->uninstallDb()) {
            return false;
        }

        // uninstall Tab
        if (!$this->uninstallTab()) {
            return false;
        }
        return true;
    }

    public function uninstallTab()
    {
        $tabId = Tab::getIdFromClassName('AdminPayrexxPaymentMethods');
        if (!$tabId) {
            return true;
        }
        $tab = new Tab($tabId);
        if (!$tab->delete()) {
            return false;
        }
        return true;
    }

    /**
     * Uninstall DataBase table
     * @return boolean if uninstall was successfull
     */
    private function uninstallDb()
    {
        include dirname(__FILE__) . '/sql/uninstall.php';
        return parent::uninstall();
    }

    public function getContent()
    {
        $this->postProcess();

        $paymentMethods = array(
            array('id_option' => 'masterpass', 'name' => 'Masterpass',),
            array('id_option' => 'mastercard', 'name' => 'Mastercard',),
            array('id_option' => 'visa', 'name' => 'Visa',),
            array('id_option' => 'apple_pay', 'name' => 'Apple Pay',),
            array('id_option' => 'maestro', 'name' => 'Maestro',),
            array('id_option' => 'jcb', 'name' => 'JCB',),
            array('id_option' => 'american_express', 'name' => 'American Express',),
            array('id_option' => 'wirpay', 'name' => 'WIRpay',),
            array('id_option' => 'paypal', 'name' => 'PayPal',),
            array('id_option' => 'bitcoin', 'name' => 'Bitcoin',),
            array('id_option' => 'sofortueberweisung_de', 'name' => 'Sofort Ueberweisung',),
            array('id_option' => 'airplus', 'name' => 'Airplus',),
            array('id_option' => 'billpay', 'name' => 'Billpay',),
            array('id_option' => 'bonuscard', 'name' => 'Bonus card',),
            array('id_option' => 'cashu', 'name' => 'CashU',),
            array('id_option' => 'cb', 'name' => 'Carte Bleue',),
            array('id_option' => 'diners_club', 'name' => 'Diners Club',),
            array('id_option' => 'direct_debit', 'name' => 'Direct Debit',),
            array('id_option' => 'discover', 'name' => 'Discover',),
            array('id_option' => 'elv', 'name' => 'ELV',),
            array('id_option' => 'ideal', 'name' => 'iDEAL',),
            array('id_option' => 'invoice', 'name' => 'Invoice',),
            array('id_option' => 'myone', 'name' => 'My One',),
            array('id_option' => 'paysafecard', 'name' => 'Paysafe Card',),
            array('id_option' => 'postfinance_card', 'name' => 'PostFinance Card',),
            array('id_option' => 'postfinance_efinance', 'name' => 'PostFinance E-Finance',),
            array('id_option' => 'swissbilling', 'name' => 'SwissBilling',),
            array('id_option' => 'twint', 'name' => 'TWINT'),
            array('id_option' => 'barzahlen', 'name' => 'Barzahlen/Viacash'),
            array('id_option' => 'bancontact', 'name' => 'Bancontact'),
            array('id_option' => 'giropay', 'name' => 'GiroPay'),
            array('id_option' => 'eps', 'name' => 'EPS'),
            array('id_option' => 'google_pay', 'name' => 'Google Pay'),
            array('id_option' => 'wechat_pay', 'name' => 'WeChat Pay'),
            array('id_option' => 'alipay', 'name' => 'Alipay'),
        );

        foreach (ConfigurationUtil::getPlatforms() as $url => $platformName) {
            $platforms[] = [
                'url' => $url,
                'name' => $platformName,
            ];
        }

        $fields_form = [];
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => 'Settings',
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => 'Payment Platform',
                    'name' => 'payrexx_platform',
                    'desc' => 'Choose the platform provider from the list',
                    'multiple' => false,
                    'options' => [
                        'query' => $platforms,
                        'id' => 'url',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => 'API Secret',
                    'name' => 'payrexx_api_secret',
                    'desc' => 'Paste here your API key from the Integrations page of your Payrexx merchant backend.',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => 'INSTANCE NAME',
                    'name' => 'payrexx_instance_name',
                    'desc' => 'INSTANCE NAME is a part of the url where you access your payrexx installation.
                    https://INSTANCE.payrexx.com',
                    'required' => true
                ],
                [
                    'type' => 'select',
                    'label' => 'Payment Icons',
                    'name' => 'payrexx_pay_icons',
                    'multiple' => true,
                    'options' => [
                        'query' => $paymentMethods,
                        'id' => 'id_option',
                        'name' => 'name',
                    ]
                ],
                [
                    'type' => 'text',
                    'label' => 'Look and Feel Profile Id',
                    'name' => 'payrexx_look_and_feel_id',
                    'desc' => 'Enter a profile ID if you wish to use a specific Look&Feel profile.',
                ],
            ],
            'submit' => [
                'title' => 'Save',
                'class' => 'btn btn-default pull-right'
            ],
        ];
        foreach (ConfigurationUtil::getConfigKeys() as $configKey) {
            if (in_array($configKey, ['PAYREXX_PAY_ICONS'])) {
                $fieldsValue[strtolower($configKey) . '[]'] = unserialize(Configuration::get($configKey));
                continue;
            }
            $fieldsValue[strtolower($configKey)] = Configuration::get($configKey);
        }
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
            'fields_value' => $fieldsValue,
            'id_language' => $this->context->language->id,
            'back_url' => $this->context->link->getAdminLink('AdminModules')
                . '&configure=' . $this->name
                . '&tab_module=' . $this->tab
                . '&module_name=' . $this->name
                . '#paypal_params'
        );
        $form = $helper->generateForm($fields_form) . $this->renderAdditionalPaymentMethodsList();

        return $form;
    }

    /**
     * Rendered payment method list.
     */
    protected function renderAdditionalPaymentMethodsList()
    {
        $this->fieldsList = [
            'active' => [
                'title' => 'Status',
                'active' => 'status',
                'type' => 'bool',
                'search' => false,
                'orderby' => false,
            ],
            'title' => [
                'width' => 'auto',
                'orderby' => false,
                'title' => 'Title',
                'type' => 'text',
                'search' => false,
            ],
            'pm' => [
                'title' => 'Payment Method',
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ],
            'position' => [
                'title' => 'Sorting',
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ],
        ];

        $adminLinkController = Context::getContext()->link->getAdminLink('AdminPayrexxPaymentMethods', false);
        $token = Tools::getAdminTokenLite('AdminPayrexxPaymentMethods');
        $helperList = new HelperList();
        $helperList->table = 'payrexx_payment_methods';
        $helperList->shopLinkType = '';
        $helperList->position_identifier = 'position';
        $helperList->simple_header = false;
        $helperList->identifier = 'id';
        $helperList->actions = ['edit', 'delete'];
        $helperList->show_toolbar = false;
        $helperList->toolbar_btn['new'] = [
           'href' => $adminLinkController . '&addpayrexx_payment_methods&token=' . $token,
           'desc' => 'Add new',
        ];
        $helperList->title = 'Payment Methods';
        $helperList->currentIndex = $adminLinkController;
        $helperList->token = $token;

        $content = $this->getPaymentMethodsList(false);
        $helperList->listTotal = count($content);

        return $helperList->generateList($content, $this->fieldsList);
    }

    /**
     * Get payment methods list.
     *
     * @param bool $filterActive
     * @return array
     */
    public function getPaymentMethodsList($filterActive = false): array
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('payrexx_payment_methods');
        if ($filterActive) {
            $sql->where('active = 1');
        }
        $sql->orderBy('position');
        return Db::getInstance()->ExecuteS($sql);
    }

    private function postProcess()
    {
        if (!Tools::isSubmit('payrexx_config')) {
            return;
        }
        $payrexxApiService = new PayrexxApiService();
        $signatureCheck = $payrexxApiService->validateSignature(
            Tools::getValue('payrexx_instance_name'),
            Tools::getValue('payrexx_api_secret'),
            Tools::getValue('payrexx_platform')
        );
        if (!$signatureCheck) {
            $this->context->controller->errors[] = 'Please enter valid credentials! Try again.';
            return false;
        }
        foreach (ConfigurationUtil::getConfigKeys() as $configKey) {
            $configValue = Tools::getValue(strtolower($configKey));
            if (in_array($configKey, ['PAYREXX_PAY_ICONS'])) {
                $configValue = serialize($configValue);
            }
            Configuration::updateValue($configKey, $configValue);
        }
        $this->context->controller->confirmations[] = 'Settings are successfully updated.';
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
        $action_text = $this->l('Payrexx payment method title');
        $this->smarty->assign(array(
            'payrexx_url' => $this->context->link->getModuleLink($this->name, 'payrexx'),
            'image_path' => $this->_path,
            'title' => $action_text,
        ));
        return $this->display(__FILE__, 'payrexx_payment.tpl');
    }

    /**
     * Return payment options available for PS 1.7+
     *
     * @param array Hook parameters
     *
     * @return array
     */
    public function hookPaymentOptions($params)
    {
        $payIconSource = unserialize(Configuration::get('PAYREXX_PAY_ICONS'));

        $paymentOption = new PaymentOption();
        $actionText = $this->l('Payrexx payment method title');
        $this->context->smarty->assign(array(
            'path' => $this->_path,
        ));
        $action = $this->context->link->getModuleLink($this->name, 'payrexx');
        $paymentOption->setCallToActionText($actionText);
        $paymentOption->setAction($action);

        $payIcons = '';
        if ($payIconSource) {
            foreach ((array)$payIconSource as $iconSource) {
                $payIcons .=
                    '<img style="width: 50px" src="' . $this->_path . 'views/img/cardicons/card_' . $iconSource . '.svg" />';
            }

            $payIcons = '<div class="payrexxPayIcons">' . $payIcons . '</div>';
        }

        $paymentOption->setAdditionalInformation($this->l('Payrexx payment method description') . $payIcons);
        $paymentMethods[] = $paymentOption;

        // Additional payment methods
        foreach ($this->getPaymentMethodsList(true) as $paymentMethod) {
            if (!$this->allowedPaymentMethodToPay($paymentMethod)) {
                continue;
            }
            $paymentOption = new PaymentOption();
            $paymentOption->setCallToActionText($paymentMethod['title']);
            $paymentOption->setAction($action);

            $paymentOption->setInputs([
                'payment_methods' => [
                    'name' =>'payrexx_pm',
                    'type' =>'hidden',
                    'value' => $paymentMethod['pm'],
                ]
            ]);

            $paymentOption->setAdditionalInformation($paymentMethod['description']);
            $paymentMethods[] = $paymentOption;
        }
        return $paymentMethods;
    }

    /**
     * Allowed Payment Method to pay
     *
     * @param array $paymentMethod
     * @return true|false
     */
    public function allowedPaymentMethodToPay(array $paymentMethod): bool
    {
        $allowedCountries = unserialize($paymentMethod['country']);
        $allowedCurrencies = unserialize($paymentMethod['currency']);
        $allowedCustomerGroups = unserialize($paymentMethod['customer_group']);
        if (!empty($allowedCountries) && !in_array($this->context->country->id, $allowedCountries)) {
            return false;
        }
        if (!empty($allowedCurrencies) && !in_array($this->context->currency->id, $allowedCurrencies)) {
            return false;
        }
        if (!empty($allowedCustomerGroups) &&
            empty(array_intersect(
                $this->context->customer->getGroups(),
                $allowedCustomerGroups
            ))
        ) {
            return false;
        }
        return true;
    }
}
