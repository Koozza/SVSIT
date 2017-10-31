<?php
namespace SITBundle\Admin;

use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class MolliePaymentAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
        $collection->add('edit',  $this->getRouterIdParameter().'/show');
        $collection->add('intrekken', $this->getRouterIdParameter().'/intrekken');

        $collection->clearExcept(array('list', 'show', 'export', 'create', 'edit', 'intrekken'));
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        die("Je kan geen mollie betalingen aanmaken");
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('MOLLIE')) {
            $datagridMapper->add('paymentid');
            $datagridMapper->add('mode');
            $datagridMapper->add('status');
        }
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if ($this->isGranted('MOLLIE')) {
            $listMapper->add('paymentid');
            $listMapper->add('amount');
            $listMapper->add('status');
            $listMapper->add('createdDatetime');
            $listMapper->add('description');
            $listMapper->add('method');
            $listMapper->add('recurringType');
            $listMapper->add('mode');
            $listMapper->add('betaling');
            $listMapper->add('mandateId');
            $listMapper->add('customerId');
            $listMapper->add('_action', null, array(
                'actions' => array(
                    'show' => array(
                        'template' => 'SITBundle:Admin:show_button.html.twig'
                     ),
                    'intrekken' => array(
                        'template' => 'SITBundle:CRUD:list__action_betaling_intrekken.html.twig'
                    )
                )
            ));
            ;
        }
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('MOLLIE')) {
            $showMapper->add('UID');
            $showMapper->add('paymentid');
            $showMapper->add('mode');
            $showMapper->add('amount');
            $showMapper->add('amountRefunded');
            $showMapper->add('amountRemaining');
            $showMapper->add('description');
            $showMapper->add('method');
            $showMapper->add('status');
            $showMapper->add('expiryPeriod');
            $showMapper->add('createdDatetime');
            $showMapper->add('paidDatetime');
            $showMapper->add('expiredDatetime');
            $showMapper->add('cancelledDatetime');
            $showMapper->add('manuallyCancelledDatetime');
            $showMapper->add('profileId');
            $showMapper->add('customerId');
            $showMapper->add('recurringType');
            $showMapper->add('mandateId');
            $showMapper->add('subscriptionId');
            $showMapper->add('settlementId');
            $showMapper->add('locale');
            $showMapper->add('consumerName');
            $showMapper->add('consumerAccount');
            $showMapper->add('consumerBic');
            $showMapper->add('metadataString');
            $showMapper->add('betaling');
        }
    }

    public function getExportFormats()
    {
        return array(
            'json', 'csv', 'xls'
        );
    }


    public function getDataSourceIterator()
    {

        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();
        $fields=$this->getExportFields();
        $query = $datagrid->getQuery();


        $query->select('DISTINCT ' . $query->getRootAlias());
        $query->setFirstResult(null);
        $query->setMaxResults(null);



        if ($query instanceof ProxyQueryInterface) {
            $query->addOrderBy($query->getSortBy(), $query->getSortOrder());

            $query = $query->getQuery();
        }


        return new CustomDoctrineORMQuerySourceIterator($query, $fields,'d F y H:i:s');
    }

    public function getExportFields()
    {

        if($this->isGranted('MOLLIE')) {
            $exportFields["Payment ID"] = 'paymentid';
            $exportFields["UID"] = 'UID';
            $exportFields["Mode"] = 'mode';
            $exportFields["Amount"] = 'amount';
            $exportFields["Amount Refunded"] = 'amountRefunded';
            $exportFields["Amount Remaining"] = 'amountRemaining';
            $exportFields["Description"] = 'description';
            $exportFields["Method"] = 'method';
            $exportFields["Status"] = 'status';
            $exportFields["Expiry Period"] = 'expiryPeriod';
            $exportFields["Created"] = 'createdDatetime';
            $exportFields["Paid"] = 'paidDatetime';
            $exportFields["Expired"] = 'expiredDatetime';
            $exportFields["Cancelled"] = 'cancelledDatetime';
            $exportFields["Manually Cancelled"] = 'manuallyCancelledDatetime';
            $exportFields["Profile ID"] = 'profileId';
            $exportFields["Customer ID"] = 'customerId';
            $exportFields["Recurring Type"] = 'recurringType';
            $exportFields["Mandate ID"] = 'mandateId';
            $exportFields["Subscription ID"] = 'subscriptionId';
            $exportFields["Settlement ID"] = 'settlementId';
            $exportFields["Locale"] = 'locale';
            $exportFields["Consumer Name"] = 'consumerName';
            $exportFields["Consumer Account"] = 'consumerAccount';
            $exportFields["Consumer BIC"] = 'consumerBic';
            $exportFields["Metadata"] = 'metadataString';
            $exportFields["Betaling"] = 'betaling';
        }

        return $exportFields;
    }
}