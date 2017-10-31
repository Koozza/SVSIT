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

class ProductVerzoekAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
        $collection->add('edit',  $this->getRouterIdParameter().'/show');

        $collection->clearExcept(array('list', 'show', 'export', 'create', 'edit'));
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        die("Je kan geen product verzoeken aanmaken");
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if ($this->isGranted('FIELD_PRODUCT'))
            $listMapper->add('maatproduct', null, array('label' => 'Product'));
        if ($this->isGranted('FIELD_GEBRUIKER'))
            $listMapper->add('gebruiker');
        if ($this->isGranted('FIELD_DATUM'))
            $listMapper->add('datum');
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if ($this->isGranted('FIELD_PRODUCT'))
            $showMapper->add('maatproduct', null, array('label' => 'Product'));
        if ($this->isGranted('FIELD_GEBRUIKER'))
            $showMapper->add('gebruiker');
        if ($this->isGranted('FIELD_DATUM'))
            $showMapper->add('datum');
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

        if ($this->isGranted('FIELD_PRODUCT'))
            $exportFields["Product"] = 'maatproduct';
        if ($this->isGranted('FIELD_GEBRUIKER'))
            $exportFields["Gebruiker"] = 'gebruiker';
        if ($this->isGranted('FIELD_DATUM'))
            $exportFields["Datum"] = 'datum';

        return $exportFields;
    }
}