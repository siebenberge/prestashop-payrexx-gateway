<?php
/**
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Payrexx <integration@payrexx.com>
 * @copyright  2023 Payrexx
 * @license MIT License
 */

 include_once _PS_MODULE_DIR_ . 'payrexx/models/PayrexxPaymentMethods.php';

use Payrexx\PayrexxPaymentGateway\Util\ConfigurationUtil;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPayrexxPaymentMethodsController extends ModuleAdminController
{
    public function __construct()
    {
        // Call of the parent constructor method
        parent::__construct();

        // Set variables
        $this->table = 'payrexx_payment_methods';
        $this->className = 'PayrexxPaymentMethods';
        $this->fields_list = [
            'position' => [
                'title' => $this->l('Position'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-md',
            ],
            'title' => [
                'width' => 'auto',
                'orderby' => false,
                'title' => $this->l('Title'),
                'type' => 'text',
                'search' => false,
             ],
            'active' => [
                'active' => 'status',
                'width' => 'auto',
                'orderby' => false,
                'title' => $this->l('Active'),
                'type' => 'bool',
                'search' => false,
            ],
        ];
        // Enable bootstrap
        $this->bootstrap = true;
        $this->_orderBy = 'id';
        $this->_defaultOrderWay = 'DESC';
        $this->_defaultOrderBy = 'position';
        $this->identifier = 'id';
        $this->position_identifier = 'position';

        $this->id_lang = $this->context->language->id;
        $this->default_form_language = $this->context->language->id;

        // Read & update record
        $this->addRowAction('details');
        $this->addRowAction('edit');

        $paymentMethod = $this->loadObject(true);
        $this->fields_value['country[]'] = unserialize($paymentMethod->country);
        $this->fields_value['currency[]'] = unserialize($paymentMethod->currency);
        $this->fields_value['customer_group[]'] = unserialize($paymentMethod->customer_group);

        foreach (ConfigurationUtil::getPaymentMethods() as $pmKey => $paymentMethod) {
            $paymentMethods[] = [
                'id_pm' => $pmKey, 
                'name' => $paymentMethod
            ];
        }

        $this->fields_form = [
        'legend' => [
            'title' => 'Payment Method',
            'icon' => 'icon-list-ul',
        ],
        'input' => [
            [
                'type' => 'select',
                'label' => 'Payment Method',
                'name' => 'pm',
                'required' => true,
                'options' => [
                    'query' => $paymentMethods,
                    'id' => 'id_pm',
                    'name' => 'name',
                ],
            ],
            [
                'type' => 'select',
                'label' => ('Active'),
                'name' => 'active',
                'required' => true,
                'options' => [
                    'query' => [
                        [
                            'idevents' => 1,
                            'name' => 'Active',
                        ],
                        [
                            'idevents' => 0,
                            'name' => 'Inactive',
                        ],
                    ],
                    'id' => 'idevents',
                    'name' => 'name',
                ],
            ],
            
            [
                'name' => 'title',
                'type' => 'text',
                'label' => 'Title',
                'required' => true
            ],
            [
                'type' => 'select',
                'label' => 'Accept payments from country',
                'name' => 'country[]',
                'multiple' => true,
                'class' => 'chosen',
                'desc' => 'Leave empty accepts payment from all countries',
                'options' => [
                    'query' => Country::getCountries($this->context->language->id),
                    'id' => 'id_country',
                    'name' => 'name',
                ]
            ],
            [
                'type' => 'select',
                'label' => 'Currency',
                'name' => 'currency[]',
                'multiple' => true,
                'class' => 'chosen',
                'desc' => 'Leave empty accepts payment for all currency',
                'options' => [
                    'query' => Currency::getCurrencies(false, false),
                    'id' => 'id',
                    'name' => 'name',
                ]
            ],
            [
                'type' => 'select',
                'label' => 'Customer Group',
                'name' => 'customer_group[]',
                'multiple' => true,
                'class' => 'chosen',
                'desc' => 'Leave empty accepts payment for all customer groups',
                'options' => [
                    'query' => Group::getGroups($this->context->language->id),
                    'id' => 'id_group',
                    'name' => 'name',
                ]
            ],
            [
                'name' => 'position',
                'type' => 'text',
                'label' => 'Sorting'
            ],
        ],
        'submit' => [
            'title' => $this->trans('Save', [], 'Admin.Actions'),
            'name' => 'submit_form_pm',
        ],
        ];
    }

    public function display()
    {
        // show saved messages
        if ($this->context->cookie->__isset('redirect_errors') &&
            $this->context->cookie->__get('redirect_errors') != ''){
                
            $this->errors = array_merge([$this->context->cookie->__get('redirect_errors')], $this->errors);
            // delete old messages
            $this->context->cookie->__unset('redirect_errors');
        }
        parent::display();
    }

    public function postProcess()
    {
        if (!Tools::isSubmit('submit_form_pm')) {
            parent::postProcess();
            return;
        }

        // Country
        $postCountry = !isset($_POST['country'])? [] : $_POST['country'];
        $_POST['country'] = serialize($postCountry);

        // currency
        $postCurrency = !isset($_POST['currency'])? [] : $_POST['currency'];
        $_POST['currency'] = serialize($postCurrency);

        $postCustomerGroup = !isset($_POST['customer_group'])? [] : $_POST['customer_group'];
        $_POST['customer_group'] = serialize($postCustomerGroup);

        parent::postProcess();
    }

    public function renderList()
    {
        parent::renderList();
    }

    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        // On Successful Endpoint creation, update, delete
        // Redirect to the Module page
        if (Tools::getValue('conf') == 3 || Tools::getValue('conf') == 1 || Tools::getValue('conf') == 4) {
            Tools::redirect(Context::getContext()->link->getAdminLink('AdminModules', true) . '&configure=payrexx&conf=' . Tools::getValue('conf'));
        }
        parent::initContent();
    }
}
