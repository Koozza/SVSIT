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

class MollieMandateAdmin extends AbstractAdmin
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
        die("Je kan geen mollie machtigingen aanmaken");
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('MOLLIE')) {
            $datagridMapper->add('mandateid');
            $datagridMapper->add('status');
        }
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if ($this->isGranted('MOLLIE')) {
            $listMapper->add('mandateid');
            $listMapper->add('status');
            $listMapper->add('method');
            $listMapper->add('createdDatetime');
            $listMapper->add('customerId');
            $listMapper->add('_action', null, array(
                'actions' => array(
                    'show' => array(
                        'template' => 'SITBundle:Admin:show_button.html.twig'
                    ),
                )
            ));
            ;
        }
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('MOLLIE')) {
            $showMapper->add('mandateid');
            $showMapper->add('status');
            $showMapper->add('method');
            $showMapper->add('customerId');
            $showMapper->add('createdDatetime');
            $showMapper->add('consumerName');
            $showMapper->add('consumerAccount');
            $showMapper->add('consumerBic');
            $showMapper->add('payments');
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
            $exportFields["Mandate ID"] = 'mandateid';
            $exportFields["Status"] = 'status';
            $exportFields["Method"] = 'method';
            $exportFields["Customer ID"] = 'customerId';
            $exportFields["Created"] = 'createdDatetime';
            $exportFields["Consumer Name"] = 'consumerName';
            $exportFields["Consumer Account"] = 'consumerAccount';
            $exportFields["Consumer BIC"] = 'consumerBic';
            $exportFields["Payments"] = 'payments';
        }

        return $exportFields;
    }
}