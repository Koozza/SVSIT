<?php
namespace SITBundle\Admin;

use Dompdf\Dompdf;
use Knp\Menu\ItemInterface;
use SITBundle\Controller\DefaultController;
use SITBundle\Entity\BetaalLink;
use SITBundle\Entity\Factuur;
use SITBundle\Entity\FactuurProduct;
use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use SITBundle\Form\Type\PlainTextType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Swift_Attachment;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank as NotBlankConstraint;

class SITMediaAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $link_parameters = array();

        if ($this->hasParentFieldDescription()) {
            $link_parameters = $this->getParentFieldDescription()->getOption('link_parameters', array());
        }

        if ($this->hasRequest()) {
            $context = $this->getRequest()->get('context', null);

            if (null !== $context) {
                $link_parameters['context'] = $context;
            }
        }

        $formMapper

            ->add('media', 'sonata_type_model_list', array('required' => false), array(
                'link_parameters' => $link_parameters
            ))


        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $datagridMapper->add('id');
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $listMapper->addIdentifier('id');
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $showMapper->add('id');
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
        if($this->isGranted('FIELD_BESTUURSLEDEN'))
            $exportFields["Bestuursleden"] = 'bestuursleden';
        if($this->isGranted('FIELD_ISHUIDIGEBESTUUR'))
            $exportFields["Is Huidige Bestuur"] = 'isHuidigeBestuur';

        return $exportFields;
    }
}