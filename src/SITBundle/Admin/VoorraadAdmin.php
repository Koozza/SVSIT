<?php
namespace SITBundle\Admin;

use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Swift_Message;

class VoorraadAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_PRODUCT'))
            $formMapper->add('product');
        if($this->isGranted('FIELD_MAAT'))
            $formMapper->add('maat');
        if($this->isGranted('FIELD_VOORRAAD'))
            $formMapper->add('voorraad');
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_PRODUCT'))
            $datagridMapper->add('product');
        if($this->isGranted('FIELD_MAAT'))
            $datagridMapper->add('maat');
        if($this->isGranted('FIELD_VOORRAAD'))
            $datagridMapper->add('voorraad');
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_PRODUCT'))
            $listMapper->addIdentifier('product');
        if($this->isGranted('FIELD_MAAT'))
            $listMapper->add('maat');
        if($this->isGranted('FIELD_VOORRAAD'))
            $listMapper->add('voorraad');
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_PRODUCT'))
            $showMapper->add('product');
        if($this->isGranted('FIELD_MAAT'))
            $showMapper->add('maat');
        if($this->isGranted('FIELD_VOORRAAD'))
            $showMapper->add('voorraad');
    }

    public function postUpdate($object)
    {
        if($object->getVoorraad() > 0) {
            $em = $this->getConfigurationPool()->getContainer()->get('Doctrine')->getEntityManager();

            //Mails versturen naar product verzoeken
            foreach ($object->getProductverzoeken() as $productVerzoek) {
                //Mails versturen naar mensen die opzoek zijn naar dit product.
                $message = Swift_Message::newInstance()
                    ->setSubject('SIT Merchandise weer op voorraad!')
                    ->setFrom('no-reply@svsit.nl')
                    ->setTo($productVerzoek->getGebruiker()->getEmailadres())
                    ->setBody(
                        $this->getConfigurationPool()->getContainer()->get('templating')->render(
                            '_CONTENT_/mail/productverzoek.html.twig',
                            array('voornaam' => $productVerzoek->getGebruiker()->getVoornaam(),
                                'mp' => $object)
                        ),
                        'text/html'
                    );
                $this->getConfigurationPool()->getContainer()->get('mailer')->send($message);

                $em->remove($productVerzoek);
            }
            $em->flush();
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

        if($this->isGranted('FIELD_PRODUCT'))
            $exportFields["Product"] = 'product';
        if($this->isGranted('FIELD_MAAT'))
            $exportFields["Maat"] = 'maat';
        if($this->isGranted('FIELD_VOORRAAD'))
            $exportFields["Voorraad"] = 'voorraad';

        return $exportFields;
    }
}