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

class BetalingAdmin extends AbstractAdmin
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
        $lidQuery = $this->modelManager
            ->getEntityManager('SITBundle\Entity\Gebruiker')
            ->createQuery(
                'SELECT s
             FROM SITBundle:Gebruiker s
             ORDER BY s.id DESC'
            );

        $periodeQuery = $this->modelManager
            ->getEntityManager('SITBundle\Entity\Periode')
            ->createQuery(
                'SELECT s
             FROM SITBundle:Periode s
             ORDER BY s.begindatum ASC'
            );

        if($this->isGranted('FIELD_GEBRUIKER'))
            $formMapper->add('gebruiker', 'sonata_type_model', array('class' => 'SITBundle\Entity\Gebruiker', 'query' => $lidQuery, 'required' => true, 'expanded' => false, 'multiple' => false, 'label' => 'Lid'));
        if($this->isGranted('FIELD_IS_LIFETIME'))
            $formMapper->add('isLifetime');
        if($this->isGranted('FIELD_PERIODES'))
            $formMapper->add('periodes', 'sonata_type_model', array('class' => 'SITBundle\Entity\Periode', 'query' => $periodeQuery, 'required' => false, 'expanded' => false, 'multiple' => true, 'label' => 'Periodes'));

            // ...
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_GEBRUIKER'))
            $datagridMapper->add('gebruiker');
        if($this->isGranted('FIELD_IS_LIFETIME'))
            $datagridMapper->add('isLifetime');
        if($this->isGranted('FIELD_PERIODES'))
            $datagridMapper->add('periodes');
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_GEBRUIKER'))
            $listMapper->addIdentifier('gebruiker');
        if($this->isGranted('FIELD_PERIODES'))
            $listMapper->add('periodes');
        if($this->isGranted('FIELD_MOLLIE_PAYMENT'))
            $listMapper->add('molliePayment');
        if($this->isGranted('FIELD_MOLLIE_STATUS'))
            $listMapper->add('molliePayment.status');
        if($this->isGranted('FIELD_IS_LIFETIME'))
            $listMapper->add('isLifetime');
        $listMapper->add('_action', null, array(
                'actions' => array(
                    'edit' => array(),
                )
            ));
        ;
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_GEBRUIKER'))
            $showMapper->add('gebruiker');
        if($this->isGranted('FIELD_PERIODES'))
            $showMapper->add('periodes');
        if($this->isGranted('FIELD_MOLLIE_PAYMENT'))
            $showMapper->add('molliePayment');
        if($this->isGranted('FIELD_MOLLIE_STATUS'))
            $showMapper->add('molliePayment.status');
        if($this->isGranted('FIELD_IS_LIFETIME'))
            $showMapper->add('isLifetime');
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

        if($this->isGranted('FIELD_DATUM'))
            $exportFields["Datum"] = 'datum';
        if($this->isGranted('FIELD_GEBRUIKER'))
            $exportFields["Gebruiker"] = 'gebruiker';
        if($this->isGranted('FIELD_PERIODES'))
            $exportFields["Periodes"] = 'periodes';
        if($this->isGranted('FIELD_MOLLIE_PAYMENT'))
            $exportFields["Mollie Betaling"] = 'molliePayment';
        if($this->isGranted('FIELD_MOLLIE_STATUS'))
            $exportFields["Mollie Betaling Status"] = 'molliePayment.status';
        if($this->isGranted('FIELD_IS_LIFETIME'))
            $exportFields["Is Levenslang"] = 'isLifetime';

        return $exportFields;
    }
}