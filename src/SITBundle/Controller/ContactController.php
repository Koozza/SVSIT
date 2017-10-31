<?php

namespace SITBundle\Controller;

use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank as NotBlankConstraint;

class ContactController extends Controller
{

    /**
     * Create response for Contact page.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/contact/", name="contact")
     */
    public function contactAction(Request $request)
    {
        //Create contact form
        $form = $this->createFormBuilder()
            ->add('naam', TextType::class, array('attr' => array('placeholder'=>'John Doe', 'required' => true)))
            ->add('emailadres', TextType::class, array('attr' => array('placeholder'=>'johndoe@gmail.com', 'required' => true)))
            ->add('onderwerp', TextType::class, array('required' => true))
            ->add('bericht', TextareaType::class, array('required' => true))
            ->add('recaptcha', EWZRecaptchaType::class, array(
                'mapped' => false,
                'constraints' => array(
                    new RecaptchaTrue()
                )
            ))
            ->add('versturen', SubmitType::class, array(
                'attr' => array('value' => 'Versturen'),
            ))
            ->getForm();

        //Handle POST request
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if(count($this->get('validator')->validateValue($form->get('naam')->getData(), new NotBlankConstraint())) != 0)
                $form->get('naam')->addError(new FormError('Naam is een verplicht veld.'));

            if(count($this->get('validator')->validateValue($form->get('onderwerp')->getData(), new NotBlankConstraint())) != 0)
                $form->get('onderwerp')->addError(new FormError('Onderwerp is een verplicht veld.'));

            if(count($this->get('validator')->validateValue($form->get('bericht')->getData(), new NotBlankConstraint())) != 0)
                $form->get('bericht')->addError(new FormError('Bericht is een verplicht veld.'));

            if(count($this->get('validator')->validateValue($form->get('emailadres')->getData(), new NotBlankConstraint())) != 0)
                $form->get('emailadres')->addError(new FormError('E-mailadres is een verplicht veld.'));

            if(count($this->get('validator')->validateValue($form->get('emailadres')->getData(), new EmailConstraint())) != 0)
                $form->get('emailadres')->addError(new FormError('Dit is geen geldig E-mailadres.'));

            //Check if form POST is valid and send mail to bestuur.
            if($form->isValid()) {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Contactformulier '.$form->get('naam')->getData().': '.$form->get('onderwerp')->getData())
                    ->setFrom('no-reply@svsit.nl')
                    ->setReplyTo($form->get('emailadres')->getData())
                    ->setTo('bestuur@svsit.nl')
                    ->setBody('From: '.strip_tags($form->get('emailadres')->getData()).'<br><br>'.nl2br(strip_tags($form->get('bericht')->getData())),
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);
            }
        }

        return $this->render('default/contact.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'form' => $form->createView(),
            'formSucces' => $form->isValid()
        ]));
    }
}