<?php

namespace SITBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SITBundle\Entity\Gebruiker;
use SITBundle\Entity\PasswordReset;
use SITBundle\Entity\Studierichting;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RegisterController extends Controller
{

    /**
     * @Route("/resetpassword/", name="resetpassword")
     */
    public function resetPasswordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
            ->add('username', TextType::class, array('attr' => array('placeholder'=>'john@doe.nl'), 'mapped' => false))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $account = $em->getRepository('SITBundle\Entity\Gebruiker')->findOneBy(array('emailadres' => $form->get('username')->getData()));

            if($account != null) {
                $reset = new PasswordReset();
                $reset->setGebruiker($account);
                $em->persist($reset);
                $em->flush();

                $message = \Swift_Message::newInstance()
                    ->setSubject('SIT nieuw wachtwoord')
                    ->setFrom('no-reply@svsit.nl')
                    ->setTo($account->getEmailadres())
                    ->setBody(
                        $this->renderView(
                            '_CONTENT_/mail/reset_password.html.twig',
                            array('gebruiker' => $account, 'resetcode' => $reset)
                        ),
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);
            }

            return $this->redirectToRoute('resetpassword_complete');
        }

        return $this->render('user/wachtwoord_vergeten.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'form' => $form->createView(),
        ]));
    }

    /**
     * @Route("/resetpassword/complete", name="resetpassword_complete")
     */
    public function resetPasswordCompleteAction(Request $request)
    {
        return $this->render('user/wachtwoord_aangevraagd.html.twig', DefaultController::generateBaseVars($this->getDoctrine()->getManager()));
    }

    /**
     * @Route("/resetpassword/{id}", name="resetpassword_link")
     */
    public function resetPasswordLinkAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $activatieCode = $em->getRepository('SITBundle\Entity\PasswordReset')->findOneBy(array('code' => $id));
        if($activatieCode != null) {
            if(new \DateTime() > $activatieCode->getExpirationdate()) {
                throw new \Exception('Code is verlopen');
            }
        }else{
            throw new \Exception('Something went wrong!');
        }

        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class)
            ->add('herhaalPassword', PasswordType::class, array('mapped' => false, 'required' => true))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //Check of wachtwoorden matchen
            if($form->get('password')->getData() != $form->get('herhaalPassword')->getData())
                $form->get('herhaalPassword')->addError(new FormError('Wachtwoorden komen niet overeen.'));

            if(strlen($form->get('password')->getData()) < 8)
                $form->get('herhaalPassword')->addError(new FormError('Wachtwoord moet 8 tekens of langer zijn.'));

            if($form->isValid()) {
                $gebruiker = $activatieCode->getGebruiker();
                $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

                $encoder_service = $this->get('security.encoder_factory');
                $encoder = $encoder_service->getEncoder($gebruiker);
                $encoded_pass = $encoder->encodePassword($form->get('password')->getData(), $salt);

                $gebruiker->setPassword($encoded_pass);
                $gebruiker->setSalt($salt);

                $em->persist($gebruiker);
                $em->remove($activatieCode);
                $em->flush();

                return $this->render('user/wachtwoord_aangepast.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
                    'form' => $form->createView(),
                ]));
            }
        }

        return $this->render('user/wachtwoord_herstellen.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'form' => $form->createView(),
        ]));
    }

    /**
     * @Route("/register/", name="register")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $gebruiker = new Gebruiker();

        $session = $request->getSession();

        $form = $this->createFormBuilder($gebruiker)
            ->add('voornaam', TextType::class, array('attr' => array('placeholder'=>'John')))
            ->add('tussenvoegsel', TextType::class, array('required' => false))
            ->add('achternaam', TextType::class, array('attr' => array('placeholder'=>'Doe')))
            ->add('geslacht', ChoiceType::class, array('choices' => array('Man' => 'Man', 'Vrouw' => 'Vrouw')))
            ->add('geboortedatum', BirthdayType::class, array('years' => array_reverse(range(1920, date('Y') - 16)),'format' => 'd MMMM yyyy', 'empty_value' => array('year' => 'Jaar', 'month' => 'Maand', 'day' => 'Dag')) )

            ->add('adres', TextType::class, array('attr' => array('placeholder'=>'Lange Laan 15')))
            ->add('postcode', TextType::class, array('attr' => array('placeholder'=>'1234 AB')))
            ->add('woonplaats', TextType::class, array('attr' => array('placeholder'=>'Amsterdam')))
            ->add('telefoonnummer', TextType::class, array('attr' => array('placeholder'=>'0612345678')))
            ->add('emailadres', EmailType::class, array('attr' => array('placeholder'=>'john@doe.nl')))
            ->add('herhaalEmailadres', EmailType::class, array('mapped' => false, 'required' => true, 'attr' => array('placeholder'=>'john@doe.nl')))

            ->add('studentnummer', NumberType::class, array('attr' => array('placeholder'=>'500123456'), 'required' => false))
            ->add('startjaar', ChoiceType::class, array('choices' => $this->getJaartallen(), 'empty_value' => ''))
            ->add('studierichting', 'entity', array(
                'class' => 'SITBundle:Studierichting',
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('p')
                        ->orderBy('p.studierichting');
                },
                'empty_value' => '',
                'property' => 'studierichting',
                'multiple' => false,
            ))
            ->add('aanmelden', SubmitType::class, array(
                'attr' => array('value' => 'Aanmelden'),
            ))

            ->add('password', PasswordType::class)
            ->add('herhaalPassword', PasswordType::class, array('mapped' => false, 'required' => true))
            ->add('akkoord', CheckboxType::class, array('mapped' => false))
            ->add('incasso', CheckboxType::class, array('mapped' => false))
            ->add('recaptcha', EWZRecaptchaType::class, array(
                'mapped' => false,
                'constraints' => array(
                    new RecaptchaTrue()
                )
            ));

        $formModifier = function (FormInterface $form, Studierichting $studierichting = null) {
            $positions = null === $studierichting ? array() : $studierichting->getLidmaatschappen();

            $form->add('lidmaatschap', EntityType::class, array(
                'class' => 'SITBundle:Lidmaatschap',
                'choices'     => $positions,
            ));
        };

        $form->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getStudierichting());
            }
        );

        $form->get('studierichting')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $studierichting = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $studierichting);
            }
        );

        $form = $form->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($em->getRepository('SITBundle\Entity\Gebruiker')->findOneBy(array('emailadres' => $form->get('emailadres')->getData())) != null)
                $form->get('emailadres')->addError(new FormError('Dit e-mailadres is al bij ons bekend.'));

            /*if($em->getRepository('SITBundle\Entity\Gebruiker')->findOneBy(array('username' => $form->get('username')->getData())) != null)
                $form->get('username')->addError(new FormError('Deze gebruikersnaam is al bij ons bekend.'));*/

            //Checken of NVT startjaar hier wel mag
            if($form->get('startjaar')->getData() == 0 && $form->get('studierichting')->getData()->getStartjaarRelevant() == true)
                $form->get('startjaar')->addError(new FormError('N.V.T. is geen geldige optie voor deze studierichting.'));

            //Checken of studentnummer verplicht is
            if(($form->get('studentnummer')->getData() == 0 || $form->get('studentnummer')->getData() == "")&& $form->get('studierichting')->getData()->getStartjaarRelevant() == true)
                $form->get('studentnummer')->addError(new FormError('Studentnummer is verplicht voor deze studierichting.'));

            //Check of emails matchen
            if($form->get('emailadres')->getData() != $form->get('herhaalEmailadres')->getData())
                $form->get('herhaalEmailadres')->addError(new FormError('Emailadressen komen niet overeen.'));

            //Check of wachtwoorden matchen
            if($form->get('password')->getData() != $form->get('herhaalPassword')->getData())
                $form->get('herhaalPassword')->addError(new FormError('Wachtwoorden komen niet overeen.'));

            if(strlen($form->get('password')->getData()) < 8)
                $form->get('herhaalPassword')->addError(new FormError('Wachtwoord moet 8 tekens of langer zijn.'));

            if(!$form->get('akkoord')->getData())
                $form->get('akkoord')->addError(new FormError('Je dient akkoord te gaan met de algemene voorwaarden.'));

            if(!$form->get('incasso')->getData())
                $form->get('incasso')->addError(new FormError('Je dient akkoord te gaan met het incasseren van je lidmaatschapskosten.'));

            $validLidmaatschap = false;
            foreach($form->get('studierichting')->getData()->getLidmaatschappen() as $lidmaatschap) {
                if($lidmaatschap->getId() == $form->get('lidmaatschap')->getData()->getId())
                    $validLidmaatschap = true;
            }

            if(!$validLidmaatschap) {
                $form->get('lidmaatschap')->addError(new FormError('Dit is geen geldig lidmaatschap voor deze studierichting.'));
            }

            if($form->isValid()) {
                $gebruiker->setHeeftBevestiging(false);
                $gebruiker->setInschrijfdatum(new \DateTime());
                $gebruiker->setActivatiecode(DefaultController::generateRandomString(25));

                $encoder_service = $this->get('security.encoder_factory');
                $encoder = $encoder_service->getEncoder($gebruiker);
                $encoded_pass = $encoder->encodePassword($form->get('password')->getData(), $gebruiker->getSalt());

                $gebruiker->setPassword($encoded_pass);

                if($gebruiker->getStartjaar() == 0)
                    $gebruiker->setStartjaar(null);

                $em->persist($gebruiker);
                $em->flush();


                $message = \Swift_Message::newInstance()
                    ->setSubject('Welkom bij SIT!')
                    ->setFrom('no-reply@svsit.nl')
                    ->setTo($gebruiker->getEmailadres())
                    ->setBody(
                        $this->renderView(
                            '_CONTENT_/mail/activatiemail.html.twig',
                            array('gebruiker' => $gebruiker)
                        ),
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);


                $message = \Swift_Message::newInstance()
                    ->setSubject('Nieuwe SIT inschrijving!')
                    ->setFrom('no-reply@svsit.nl')
                    ->setTo('bestuur@svsit.nl')
                    ->setBody('Er is een nieuwe inschrijving',
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);

                return $this->redirectToRoute('register_confirm');
            }
        }


        return $this->render('register/register.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
           'form' => $form->createView(),
        ]));
    }

    /**
     * @Route("/register/av/", name="register_av")
     */
    public function registerAlgemeneVoorwaardenAction(Request $request)
    {
        return $this->render('register/register_av.html.twig', DefaultController::generateBaseVars($this->getDoctrine()->getManager()));
    }

    /**
     * @Route("/register/confirm/", name="register_confirm")
     */
    public function registerConfirmAction(Request $request)
    {
        return $this->render('_CONTENT_/register/bedanktVoorHetAanmelden.html.twig', DefaultController::generateBaseVars($this->getDoctrine()->getManager()));
    }

    /**
     * @Route("/register/activate/{code}/", name="register_activate")
     */
    public function registerActivateAction(Request $request, $code)
    {
        if($code != null) {
            $em = $this->getDoctrine()->getManager();
            $gebruiker = $em->getRepository('SITBundle\Entity\Gebruiker')->findOneBy(array('activatiecode' => $code));

            if ($gebruiker != null) {

                $gebruiker->setActivatiecode(null);
                $em->persist($gebruiker);
                $em->flush();

                return $this->render('_CONTENT_/register/gebruikerGeactiveerd.html.twig', DefaultController::generateBaseVars($this->getDoctrine()->getManager()));
            }
        }
        throw new \Exception('Something went wrong!');
    }

    private function getJaartallen()
    {
        $array = array();
        foreach (range(date('Y'), 2010) as $jaar) {
            $array[$jaar] = $jaar;
        }
        $array[0] = 'N.V.T.';

        return $array;
    }
}