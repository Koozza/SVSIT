<?php
namespace SITBundle\Admin;

use SITBundle\Entity\Evenement;
use SITBundle\Entity\Position;
use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class NieuwsbriefLedenAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'show', 'export', 'delete'));
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_EMAIL'))
            $formMapper->add('email');
        if($this->isGranted('FIELD_INSCHRIJFDATUM'))
            $formMapper->add('inschrijfdatum');
        if($this->isGranted('FIELD_ACTIEF'))
            $formMapper->add('active') ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_EMAIL'))
            $datagridMapper->add('email');
        if($this->isGranted('FIELD_INSCHRIJFDATUM'))
            $datagridMapper->add('inschrijfdatum');
        if($this->isGranted('FIELD_ACTIEF'))
            $datagridMapper->add('active') ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_EMAIL'))
            $listMapper->add('email', 'sonata_type_model_list');
        if($this->isGranted('FIELD_INSCHRIJFDATUM'))
            $listMapper->add('inschrijfdatum');
        if($this->isGranted('FIELD_ACTIEF'))
            $listMapper->add('active') ;
        $listMapper->add('_action', null, array(
            'actions' => array(
                'delete' => array(
                    'template' => 'SITBundle:Admin:delete_button.html.twig'
                )
            )
        ));
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_EMAIL'))
            $showMapper->add('email');
        if($this->isGranted('FIELD_INSCHRIJFDATUM'))
            $showMapper->add('inschrijfdatum');
        if($this->isGranted('FIELD_ACTIEF'))
            $showMapper->add('active') ;
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

        if($this->isGranted('FIELD_EMAIL'))
            $exportFields["E-mailadres"] = 'email';
        if($this->isGranted('FIELD_INSCHRIJFDATUM'))
            $exportFields["Inschrijfdatum"] = 'inschrijfdatum';
        if($this->isGranted('FIELD_ACTIEF'))
            $exportFields["Actief"] = 'active';

        return $exportFields;
    }
}