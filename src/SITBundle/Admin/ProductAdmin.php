<?php
namespace SITBundle\Admin;

use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProductAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    public $BTWTypes = array('21' => '21%', '6' => '6%', '0' => '0%');

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_TYPE'))
            $formMapper->add('type');
        if($this->isGranted('FIELD_NAAM'))
            $formMapper->add('naam');
        if($this->isGranted('FIELD_PRIJS'))
            $formMapper->add('prijs');
        if($this->isGranted('FIELD_BTW'))
            $formMapper->add('btw', ChoiceType::class, array('choices' => $this->BTWTypes));
        if($this->isGranted('FIELD_IS_INC_BTW'))
            $formMapper->add('isIncBtw') ;
        if($this->isGranted('FIELD_VOORRAAD_KAN_NEGATIEF'))
            $formMapper->add('voorraadKanNegatief') ;

        $formMapper->add('afbeeldingen', 'sonata_type_collection', array(
                'cascade_validation' => false,
                'type_options' => array('delete' => false),
            ), array(

                    'edit' => 'inline',
                    'inline' => 'table',
                    'link_parameters' => array('context' => 'webshop'),
                    'admin_code' => 'sonata.admin.footer_widgets_has_media' /*here provide service name for junction admin */
                )
            );
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $datagridMapper->add('naam');
        if($this->isGranted('FIELD_BTW'))
            $datagridMapper->add('btw') ;
        if($this->isGranted('FIELD_IS_INC_BTW'))
            $datagridMapper->add('isIncBtw') ;
        if($this->isGranted('FIELD_VOORRAAD_KAN_NEGATIEF'))
            $datagridMapper->add('voorraadKanNegatief') ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $listMapper->addIdentifier('naam');
        if($this->isGranted('FIELD_TYPE'))
            $listMapper->add('type');
        if($this->isGranted('FIELD_PRIJS'))
            $listMapper->add('prijs');
        if($this->isGranted('FIELD_BTW'))
            $listMapper->add('btw', 'choice', array('choices' => $this->BTWTypes));
        if($this->isGranted('FIELD_IS_INC_BTW'))
            $listMapper->add('isIncBtw') ;
        if($this->isGranted('FIELD_VOORRAAD_KAN_NEGATIEF'))
            $listMapper->add('voorraadKanNegatief') ;
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $showMapper->add('naam');
        if($this->isGranted('FIELD_TYPE'))
            $showMapper->add('type');
        if($this->isGranted('FIELD_PRIJS'))
            $showMapper->add('prijs');
        if($this->isGranted('FIELD_BTW'))
            $showMapper->add('btw', 'choice', array('choices' => $this->BTWTypes));
        if($this->isGranted('FIELD_IS_INC_BTW'))
            $showMapper->add('isIncBtw') ;
        if($this->isGranted('FIELD_VOORRAAD_KAN_NEGATIEF'))
            $showMapper->add('voorraadKanNegatief') ;
    }

    public function preUpdate($object)
    {
        foreach($object->getAfbeeldingen() as $afb) {
            if($afb->getMedia() == null) {
                $object->removeAfbeeldingen($afb);
            }
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
        if($this->isGranted('FIELD_PRIJS'))
            $exportFields["Prijs"] = 'prijs';
        if($this->isGranted('FIELD_BTW'))
            $exportFields["BTW"] = 'btw';
        if($this->isGranted('FIELD_IS_INC_BTW'))
            $exportFields["Is Inclusief BTW"] = 'isIncBtw';
        if($this->isGranted('FIELD_VOORRAAD_KAN_NEGATIEF'))
            $exportFields["Voorraad kan negatief"] = 'voorraadKanNegatief';

        return $exportFields;
    }
}