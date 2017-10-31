<?php
namespace SITBundle\Admin;

use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class PeriodeAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'SITBundle:AdminDatetimeFix:edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $formMapper->add('naam');
        if($this->isGranted('FIELD_BEGINDATUM'))
            $formMapper->add('begindatum', null, array('years' => range(2015, date('Y') + 10), 'format' => 'dd MM yyyy'));
        if($this->isGranted('FIELD_EINDDATUM'))
            $formMapper->add('einddatum', null, array('years' => range(2015, date('Y') + 10), 'format' => 'dd MM yyyy'));
        if($this->isGranted('FIELD_VOLGENDE_PERIODE'))
            $formMapper->add('volgendePeriode');
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $datagridMapper->add('naam');
        if($this->isGranted('FIELD_BEGINDATUM'))
            $datagridMapper->add('begindatum');
        if($this->isGranted('FIELD_EINDDATUM'))
            $datagridMapper->add('einddatum');
        if($this->isGranted('FIELD_VOLGENDE_PERIODE'))
            $datagridMapper->add('volgendePeriode');
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $listMapper->addIdentifier('naam');
        if($this->isGranted('FIELD_BEGINDATUM'))
            $listMapper->add('begindatum');
        if($this->isGranted('FIELD_EINDDATUM'))
            $listMapper->add('einddatum');
        if($this->isGranted('FIELD_VOLGENDE_PERIODE'))
            $listMapper->add('volgendePeriode');
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $showMapper->add('naam');
        if($this->isGranted('FIELD_BEGINDATUM'))
            $showMapper->add('begindatum');
        if($this->isGranted('FIELD_EINDDATUM'))
            $showMapper->add('einddatum');
        if($this->isGranted('FIELD_VOLGENDE_PERIODE'))
            $showMapper->add('volgendePeriode');
        if($this->isGranted('FIELD_BETALINGEN'))
            $showMapper->add('betalingen');
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
        if($this->isGranted('FIELD_BEGINDATUM'))
            $exportFields["Begindatum"] = 'begindatum';
        if($this->isGranted('FIELD_EINDDATUM'))
            $exportFields["Einddatum"] = 'einddatum';
        if($this->isGranted('FIELD_VOLGENDE_PERIODE'))
            $exportFields["Volgende Periode"] = 'volgendePeriode';
        if($this->isGranted('FIELD_BETALINGEN'))
            $exportFields["Betalingen"] = 'betalingen';

        return $exportFields;
    }
}