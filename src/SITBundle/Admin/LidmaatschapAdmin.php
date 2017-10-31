<?php
namespace SITBundle\Admin;

use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class LidmaatschapAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_BESCHRIJVING')) {
            $formMapper->add('beschrijving');
            $formMapper->add('afkorting');
        }
        if($this->isGranted('FIELD_PRIJS'))
            $formMapper->add('prijs') ;
        if($this->isGranted('FIELD_AANTAL_PERIODES'))
            $formMapper->add('aantalPeriodes') ;
        if($this->isGranted('FIELD_ZICHTBAAR_OP_WEBSITE'))
            $formMapper->add('visibleOnWebsite', null, array('label' => 'Zichtbaar op website')) ;
        if($this->isGranted('FIELD_INCASSEERBAAR'))
            $formMapper->add('incasseerbaar') ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_BESCHRIJVING')) {
            $datagridMapper->add('beschrijving');
            $datagridMapper->add('afkorting');
        }
        if($this->isGranted('FIELD_PRIJS'))
            $datagridMapper->add('prijs') ;
        if($this->isGranted('FIELD_AANTAL_PERIODES'))
            $datagridMapper->add('aantalPeriodes') ;
        if($this->isGranted('FIELD_ZICHTBAAR_OP_WEBSITE'))
            $datagridMapper->add('visibleOnWebsite', null, array('label' => 'Zichtbaar op website')) ;
        if($this->isGranted('FIELD_INCASSEERBAAR'))
            $datagridMapper->add('incasseerbaar') ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_BESCHRIJVING')) {
            $listMapper->addIdentifier('beschrijving');
            $listMapper->addIdentifier('afkorting');
        }
        if($this->isGranted('FIELD_PRIJS'))
            $listMapper->add('prijs') ;
        if($this->isGranted('FIELD_AANTAL_PERIODES'))
            $listMapper->add('aantalPeriodes') ;
        if($this->isGranted('FIELD_ZICHTBAAR_OP_WEBSITE'))
            $listMapper->addIdentifier('visibleOnWebsite', null, array('label' => 'Zichtbaar op website')) ;
        if($this->isGranted('FIELD_INCASSEERBAAR'))
            $listMapper->add('incasseerbaar') ;
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_BESCHRIJVING')) {
            $showMapper->add('beschrijving');
            $showMapper->add('afkorting');
        }
        if($this->isGranted('FIELD_PRIJS'))
            $showMapper->add('prijs') ;
        if($this->isGranted('FIELD_AANTAL_PERIODES'))
            $showMapper->add('aantalPeriodes') ;
        if($this->isGranted('FIELD_ZICHTBAAR_OP_WEBSITE'))
            $showMapper->add('visibleOnWebsite', null, array('label' => 'Zichtbaar op website'));
        if($this->isGranted('FIELD_INCASSEERBAAR'))
            $showMapper->add('incasseerbaar') ;
        if($this->isGranted('FIELD_GEBRUIKERS'))
            $showMapper->add('gebruikers') ;
        if($this->isGranted('FIELD_STUDIERICHTINGEN'))
            $showMapper->add('studierichtingen') ;
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

        if($this->isGranted('FIELD_BESCHRIJVING')) {
            $exportFields["Naam"] = 'beschrijving';
            $exportFields["Afkorting"] = 'afkorting';
        }
        if($this->isGranted('FIELD_PRIJS'))
            $exportFields["Prijs"] = 'prijs';
        if($this->isGranted('FIELD_AANTAL_PERIODES'))
            $exportFields["Aantal Periodes"] = 'aantalPeriodes';
        if($this->isGranted('FIELD_ZICHTBAAR_OP_WEBSITE'))
            $exportFields["Zichtbaar op website"] = 'visibleOnWebsite';
        if($this->isGranted('FIELD_INCASSEERBAAR'))
            $exportFields["Incasseerbaar"] = 'incasseerbaar';
        if($this->isGranted('FIELD_GEBRUIKERS'))
            $exportFields["Gebruikers"] = 'gebruikers';
        if($this->isGranted('FIELD_STUDIERICHTINGEN'))
            $exportFields["Studierichtingen"] = 'studierichtingen';

        return $exportFields;
    }
}