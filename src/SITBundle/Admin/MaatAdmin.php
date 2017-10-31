<?php
namespace SITBundle\Admin;

use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class MaatAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $formMapper->add('naam');
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $datagridMapper->add('naam');
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $listMapper->addIdentifier('naam');
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $showMapper->add('naam');
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

        return $exportFields;
    }
}