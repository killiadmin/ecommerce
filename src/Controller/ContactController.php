<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Handles the contact form submission and processing.
     *
     * @param Request $request The HTTP request object.
     * @return Response The HTTP response object, either rendering the contact form or redirecting on success.
     */
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request): Response
    {
        $contact = new Contact();
        $contactForm = $this->createForm(ContactType::class, $contact);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $email = $contactForm->get('email_contact')->getData();

            if ($email) {
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                $contact->setExistingAccount($user !== null);
            }

            $contact->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_product');
        }

        return $this->render('contact/contact.html.twig', [
            'contactForm' => $contactForm->createView(),
        ]);
    }
}
