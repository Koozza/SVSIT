<?php
namespace SITBundle\Admin;

use SITBundle\Entity\BetaalLink;
use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class BetaalLinkAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('batch');
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $formMapper->add('naam');
        if($this->isGranted('FIELD_PRIJS'))
            $formMapper->add('amount');
        if($this->isGranted('FIELD_MULTIUSER'))
            $formMapper->add('multiuser');
        if($this->isGranted('FIELD_ALLEEN_INGELOGGED'))
            $formMapper->add('alleenIngelogged');
        if($this->isGranted('FIELD_ALLEEN_LEDEN'))
            $formMapper->add('alleenLeden', null, array('label' => 'Alleen Leden (Impliceert Ingelogged)'));
        if($this->isGranted('FIELD_CHARGE_PAYMENT_COST'))
            $formMapper->add('chargePaymentCost');
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $datagridMapper->add('naam');
        if($this->isGranted('FIELD_PRIJS'))
            $datagridMapper->add('amount');
        if($this->isGranted('FIELD_MULTIUSER'))
            $datagridMapper->add('multiuser');
        if($this->isGranted('FIELD_ALLEEN_INGELOGGED'))
            $datagridMapper->add('alleenIngelogged');
        if($this->isGranted('FIELD_ALLEEN_LEDEN'))
            $datagridMapper->add('alleenLeden');
        if($this->isGranted('FIELD_CHARGE_PAYMENT_COST'))
            $datagridMapper->add('chargePaymentCost');
        if($this->isGranted('FIELD_TYPE'))
            $datagridMapper->add('type');
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $listMapper->addIdentifier('naam');
        if($this->isGranted('FIELD_PRIJS'))
            $listMapper->add('amount');
        if($this->isGranted('FIELD_TYPE'))
            $listMapper->add('type');
        if($this->isGranted('FIELD_MULTIUSER'))
            $listMapper->add('multiuser');
        if($this->isGranted('FIELD_ALLEEN_INGELOGGED'))
            $listMapper->add('alleenIngelogged');
        if($this->isGranted('FIELD_ALLEEN_LEDEN'))
            $listMapper->add('alleenLeden');
        if($this->isGranted('FIELD_CHARGE_PAYMENT_COST'))
            $listMapper->add('chargePaymentCost');
        if($this->isGranted('FIELD_URL'))
            $listMapper->add('url', 'url', array(
                'label' => 'Betaallink',
                'template' => 'SITBundle:Admin:betaallink_list_url.html.twig'
            ));

        $listMapper->add('_action', null, array(
            'actions' => array(
                'show' => array(
                    'template' => 'SITBundle:Admin:show_button.html.twig'
                ),
                'delete' => array(
                    'template' => 'SITBundle:Admin:betaal_list__action_delete.html.twig'
                )
            )
        ));
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $showMapper->add('naam');
        if($this->isGranted('FIELD_MULTIUSER'))
            $showMapper->add('multiuser');
        if($this->isGranted('FIELD_ALLEEN_INGELOGGED'))
            $showMapper->add('alleenIngelogged');
        if($this->isGranted('FIELD_ALLEEN_LEDEN'))
            $showMapper->add('alleenLeden', null, array('label' => 'Alleen Leden (Impliceert Ingelogged)'));
        if($this->isGranted('FIELD_CHARGE_PAYMENT_COST'))
            $showMapper->add('chargePaymentCost');
        if($this->isGranted('FIELD_TYPE'))
            $showMapper->add('type');
        if($this->isGranted('FIELD_MOLLIE'))
            $showMapper->add('gebruikerMolliePayments', null, array('label' => 'Betalingen'));
    }

    public function prePersist($object)
    {
        $em = $this->getConfigurationPool()->getContainer()->get('Doctrine')->getEntityManager();

        if ($object instanceof BetaalLink) {
            $object->setType("Admin");
            $em->persist($object);
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


        return new CustomDoctrineORMQuerySourceIterator($query, $fields,'d F y');
    }

    public function getExportFields()
    {
        $exportFields = array();
        if($this->isGranted('FIELD_NAAM'))
            $exportFields["Naam"] = 'naam';
        if($this->isGranted('FIELD_MULTIUSER'))
            $exportFields["Multiuser"] = 'multiuser';
        if($this->isGranted('FIELD_ALLEEN_INGELOGGED'))
            $exportFields["Alleen Ingelogged"] = 'alleenIngelogged';
        if($this->isGranted('FIELD_ALLEEN_LEDEN'))
            $exportFields["Alleen Leden"] = 'alleenLeden';
        if($this->isGranted('FIELD_CHARGE_PAYMENT_COST'))
            $exportFields["Charge Payment Cost"] = 'chargePaymentCost';
        if($this->isGranted('FIELD_TYPE'))
            $exportFields["Type"] = 'type';
        if($this->isGranted('FIELD_MOLLIE'))
            $exportFields["Betalingen"] = 'gebruikerMolliePayments';

        return $exportFields;
    }
}