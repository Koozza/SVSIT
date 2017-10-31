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

class FactuurAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    public $factuurTypes = array('inc' => 'Inclusief BTW');

    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'DESC',
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
        $collection->add('edit',  $this->getRouterIdParameter().'/show');
        $collection->add('pdf', $this->getRouterIdParameter().'/pdf');
        $collection->add('betaald', $this->getRouterIdParameter().'/betaald');
        $collection->add('annuleren', $this->getRouterIdParameter().'/annuleren');
        $collection->add('mail', $this->getRouterIdParameter().'/mail');

        $collection->clearExcept(array('list', 'show', 'export', 'create', 'edit', 'pdf', 'betaald', 'annuleren', 'mail'));
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_FACTUURDATUM'))
            $formMapper->add('factuurdatum');
        if($this->isGranted('FIELD_FACTUUR_VERLOOP_DATUM'))
            $formMapper->add('factuurVerloopDatum') ;
        if($this->isGranted('FIELD_REFERENTIE'))
            $formMapper->add('referentie') ;
        if($this->isGranted('FIELD_PRODUCT'))
            $formMapper->add('producten', 'sonata_type_collection',
                array('by_reference' => false),
                array('edit' => 'inline',
                    'inline' => 'table'
                ));
        if($this->isGranted('FIELD_CUSTOM_PRODUCT'))
            $formMapper->add('customProducten', 'sonata_type_collection',
                array('by_reference' => false),
                array('edit' => 'inline',
                    'inline' => 'table'
                ));
        if($this->isGranted('FIELD_GEBRUIKER'))
            $formMapper->add('gebruiker') ;
        if($this->isGranted('FIELD_NAAM'))
            $formMapper->add('naam') ;
        if($this->isGranted('FIELD_ADRES'))
            $formMapper->add('adres') ;
        if($this->isGranted('FIELD_POSTCODE'))
            $formMapper->add('postcode') ;
        if($this->isGranted('FIELD_WOONPLAATS'))
            $formMapper->add('woonplaats') ;
        if($this->isGranted('FIELD_EMAIL'))
            $formMapper->add('email') ;
        if($this->isGranted('FIELD_TYPE'))
            $formMapper->add('type', ChoiceType::class, array('choices' => $this->factuurTypes));
        if($this->isGranted('FIELD_CHARGE_PAYMENT_COST'))
            $formMapper->add('chargePaymentCost', null, array('label' => 'Transactie Kosten Doorberekenen'));
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_FACTUURNUMMER'))
            $datagridMapper->add('factuurnummer');
        if($this->isGranted('FIELD_REFERENTIE'))
            $datagridMapper->add('referentie') ;
        if($this->isGranted('FIELD_TYPE'))
            $datagridMapper->add('type');
        if($this->isGranted('FIELD_STATUS'))
            $datagridMapper->add('status') ;
        if($this->isGranted('FIELD_CHARGE_PAYMENT_COST'))
            $datagridMapper->add('chargePaymentCost', null, array('label' => 'Transactie Kosten Doorberekenen'));
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_FACTUURNUMMER'))
            $listMapper->addIdentifier('displayFactuurnummer');
        if($this->isGranted('FIELD_FACTUURDATUM'))
            $listMapper->add('factuurdatum');
        if($this->isGranted('FIELD_FACTUUR_VERLOOP_DATUM'))
            $listMapper->add('factuurVerloopDatum') ;
        if($this->isGranted('FIELD_GEBRUIKER') || $this->isGranted('FIELD_NAAM'))
            $listMapper->add('factuurnaam', null, array('mapped' => false)) ;
        if($this->isGranted('FIELD_TYPE'))
            $listMapper->add('type', 'choice', array('choices' => $this->factuurTypes));
        if($this->isGranted('FIELD_STATUS'))
            $listMapper->add('status') ;
        if($this->isGranted('FIELD_CHARGE_PAYMENT_COST'))
            $listMapper->add('chargePaymentCost', null, array('label' => 'Transactie Kosten Doorberekenen'));
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_FACTUURNUMMER'))
            $showMapper->add('displayFactuurnummer', null, array('label' => 'Factuurnummer'));
        if($this->isGranted('FIELD_FACTUURDATUM'))
            $showMapper->add('factuurdatum');
        if($this->isGranted('FIELD_FACTUUR_VERLOOP_DATUM'))
            $showMapper->add('factuurVerloopDatum') ;
        if($this->isGranted('FIELD_REFERENTIE'))
            $showMapper->add('referentie') ;
        if($this->isGranted('FIELD_PRODUCT'))
            $showMapper->add('producten');
        if($this->isGranted('FIELD_CUSTOM_PRODUCT'))
            $showMapper->add('customProducten');
        if($this->isGranted('FIELD_GEBRUIKER'))
            $showMapper->add('gebruiker') ;
        if($this->isGranted('FIELD_NAAM'))
            $showMapper->add('naam') ;
        if($this->isGranted('FIELD_ADRES'))
            $showMapper->add('adres') ;
        if($this->isGranted('FIELD_POSTCODE'))
            $showMapper->add('postcode') ;
        if($this->isGranted('FIELD_WOONPLAATS'))
            $showMapper->add('woonplaats') ;
        if($this->isGranted('FIELD_EMAIL'))
            $showMapper->add('email') ;
        if($this->isGranted('FIELD_TYPE'))
            $showMapper->add('type', 'choice', array('choices' => $this->factuurTypes));
        if($this->isGranted('FIELD_STATUS'))
            $showMapper->add('status') ;
        if($this->isGranted('FIELD_CHARGE_PAYMENT_COST'))
            $showMapper->add('chargePaymentCost', null, array('label' => 'Transactie Kosten Doorberekenen'));
    }

    public function prePersist($object)
    {
        $em = $this->getConfigurationPool()->getContainer()->get('Doctrine')->getEntityManager();

        if ($object instanceof Factuur) {
            $factuurnummer = 1;
            $laatsteFactuur = $em->getRepository('SITBundle\Entity\Factuur')->findOneBy(array(),
                array('id' => 'DESC')
            );

            if($laatsteFactuur != null)
                $factuurnummer = $laatsteFactuur->getFactuurnummer() + 1;

            $object->setFactuurnummer($factuurnummer);
            $object->setStatus("Open");
            $em->persist($object);

            //Producten copieren
            foreach($this->getForm()->get('producten')->getData() as $formfield) {
                $productCopy = new FactuurProduct();
                $productCopy->setBtw($formfield->getProductOriginal()->getBtw());
                $productCopy->setType($formfield->getProductOriginal()->getType());
                $productCopy->setNaam($formfield->getProductOriginal()->getNaam());
                $productCopy->setPrijs($formfield->getProductOriginal()->getIncPrijs());
                $productCopy->setFactuur($object);

                $em->persist($productCopy);
                $formfield->setProduct($productCopy);
                if($formfield->getMaatOriginal() != null)
                    $formfield->setMaat($formfield->getMaatOriginal()->getNaam());
                $em->persist($formfield);

                //Voorraad updaten
                $MaatProduct = $em->getRepository('SITBundle\Entity\MaatProduct')->findOneBy(array('product' => $formfield->getProductOriginal(), 'maat' =>$formfield->getMaatOriginal()));
                $MaatProduct->setVoorraad($MaatProduct->getVoorraad() - $formfield->getAantal());
                $em->persist($MaatProduct);
            }

            //Factuur zetten voor custom producten
            foreach($this->getForm()->get('customProducten')->getData() as $formfield) {
                $formfield->setFactuur($object);
                $em->persist($formfield);
            }

            //Betaallink Aanmaken
            $betaalLink = new BetaalLink();
            $betaalLink->setType("Factuur");
            $betaalLink->setNaam("Factuur ".$object->getDisplayFactuurnummer());
            $betaalLink->setAmount($object->getBetalenBedrag());
            $betaalLink->setMultiuser(false);
            $betaalLink->setVerloopDatum($object->getFactuurVerloopDatum());
            $betaalLink->setChargePaymentCost($object->getChargePaymentCost());
            $em->persist($betaalLink);

            $object->setBetaalLink($betaalLink);

            //PDF aanmaken
            $dompdf = new Dompdf();
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->loadHtml($this->getConfigurationPool()->getContainer()->get('templating')->render('factuur/factuur.html.twig', [
                'factuur' => $object,
                'incassoKosten' => $this->getConfigurationPool()->getContainer()->getParameter('IncassoKosten')
            ]));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->set_option('defaultFont', 'Open Sans');
            $dompdf->render();

            //Get mail info
            $voornaam = ($object->getGebruiker() == null ? $object->getNaam() : $object->getGebruiker()->getVoornaam());
            $email = ($object->getGebruiker() == null ? $object->getEmail() : $object->getGebruiker()->getEmailadres());

            //Mail versturen
            $attachment = Swift_Attachment::newInstance()
                ->setFilename('factuur-'.$object->getDisplayFactuurnummer().'.pdf')
                ->setContentType('application/pdf')
                ->setBody($dompdf->output());
            $message = \Swift_Message::newInstance()
                ->setSubject('Uw factuur: '.$object->getDisplayFactuurnummer())
                ->setFrom('no-reply@svsit.nl')
                ->setTo($email)
                ->setBody(
                    $this->getConfigurationPool()->getContainer()->get('templating')->render(
                        '_CONTENT_/mail/send_factuur.html.twig',
                        array('voornaam' => $voornaam,
                            'factuur' => $object)
                    ),
                    'text/html'
                )
                ->attach($attachment)
            ;
            $this->getConfigurationPool()->getContainer()->get('mailer')->send($message);
        }
    }

    public function validate( ErrorElement $errorElement, $object ) {
        $em = $this->getConfigurationPool()->getContainer()->get('Doctrine')->getEntityManager();

        if(count($object->getProducten()) == 0 && count($object->getCustomProducten()) == 0) {
            $errorElement->with('producten')->addViolation('Een factuur dient minimaal 1 product te bevatten');
        }else {

            foreach ($object->getProducten() as $productKoppeling) {
                if ($productKoppeling->getProductOriginal() != null) {

                    $MaatProduct = $em->getRepository('SITBundle\Entity\MaatProduct')->findOneBy(array('product' => $productKoppeling->getProductOriginal(), 'maat' =>$productKoppeling->getMaatOriginal()));
                    if($MaatProduct == null) {
                        if($productKoppeling->getMaatOriginal() != null)
                            $errorElement->with('producten')->addViolation('Geen voorraad gevonden voor product: '.$productKoppeling->getProductOriginal()->getNaam().', maat: '.$productKoppeling->getMaatOriginal()->getNaam());
                        else
                            $errorElement->with('producten')->addViolation('Geen voorraad gevonden voor product: '.$productKoppeling->getProductOriginal()->getNaam());
                    }else{
                        if (!$productKoppeling->getProductOriginal()->getVoorraadKanNegatief()) {
                            if ($MaatProduct->getVoorraad() - $productKoppeling->getAantal() < 0) {
                                $errorElement->with('producten')->addViolation('Er is niet genoeg voorraad voor: ' . $productKoppeling->getProductOriginal()->getNaam().' maat: '.$productKoppeling->getMaatOriginal()->getNaam())->end();
                            }
                        }
                    }
                }
            }

            if ($object->getGebruiker() == null && $errorElement->getErrors() == null) {
                $errorElement->with('naam')->addConstraint(new NotBlankConstraint())->end();
                $errorElement->with('adres')->addConstraint(new NotBlankConstraint())->end();
                $errorElement->with('postcode')->addConstraint(new NotBlankConstraint())->end();
                $errorElement->with('woonplaats')->addConstraint(new NotBlankConstraint())->end();
                $errorElement->with('email')->addConstraint(new NotBlankConstraint())->end();
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


        if($this->isGranted('FIELD_FACTUURNUMMER'))
            $exportFields["Factuur Nummer"] = 'displayFactuurnummer';
        if($this->isGranted('FIELD_FACTUURDATUM'))
            $exportFields["Factuur Datum"] = 'factuurdatum';
        if($this->isGranted('FIELD_FACTUUR_VERLOOP_DATUM'))
            $exportFields["Factuur Verloop Datum"] = 'factuurVerloopDatum';
        if($this->isGranted('FIELD_REFERENTIE'))
            $exportFields["Referentie"] = 'referentie';
        if($this->isGranted('FIELD_PRODUCT'))
            $exportFields["Producten"] = 'producten';
        if($this->isGranted('FIELD_GEBRUIKER'))
            $exportFields["Gebruiker"] = 'gebruiker';
        if($this->isGranted('FIELD_NAAM'))
            $exportFields["Naam"] = 'naam';
        if($this->isGranted('FIELD_ADRES'))
            $exportFields["Adres"] = 'adres';
        if($this->isGranted('FIELD_POSTCODE'))
            $exportFields["Postcode"] = 'postcode';
        if($this->isGranted('FIELD_WOONPLAATS'))
            $exportFields["Woonplaats"] = 'woonplaats';
        if($this->isGranted('FIELD_EMAIL'))
            $exportFields["Email"] = 'email';
        if($this->isGranted('FIELD_TYPE'))
            $exportFields["Type"] = 'type';
        if($this->isGranted('FIELD_STATUS'))
            $exportFields["Status"] = 'status';

        return $exportFields;
    }
}