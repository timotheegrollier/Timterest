<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home" ,methods="GET")
     */
    public function index(PinRepository $pinRepository): Response
    {

        // FOR REMEMBER ME FEATURE
        
        if (isset($_COOKIE['rem'])) {
            if ($_COOKIE['rem'] == 1) {
                session_start();
                session_abort();
                session_set_cookie_params(26280028);
                unset($_COOKIE['rem']);
                setcookie('rem', '', time() - 3600, '/');
            }
        }

        // HOME DISPLAY
        
        $pins = $pinRepository->findBy([], ['createdAt' => 'DESC']);
        return $this->render('pins/index.html.twig', compact("pins"));
    }


    /**
     * @Route("/pins/create", name="app_pins_create",methods={"GET","POST"})
     */
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {

        $pin = new Pin;
        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            // $johnDoe = $userRepository->findOneBy(['email' => 'john@example.com']);
            $pin->setUser($this->getUser());
            $em->persist($pin);
            $em->flush();
            $this->addFlash('success', 'Pin successfully created !');


            return $this->redirectToRoute('app_home');
        }
        return $this->render('pins/create.html.twig', ['form' => $form->createView()]);
    }





    /**
     * @Route("/pins/{id<[0-9]+>}", name="app_pins_show" ,methods="GET")
     */

    public function show(Pin $pin): Response
    {
        return $this->render('pins/show.html.twig', compact('pin'));
    }





    /**
     * @Route("/pins/{id<[0-9]+>}/edit", name="app_pins_edit" ,methods={"GET","PUT"})
     */
    public function edit(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {

        $form = $this->createFormBuilder($pin, ["method" => "PUT"])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image (JPG or PNG file)',
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'imagine_pattern' => 'squared_thumbnail_small'
            ])
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->getForm();

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();
            $this->addFlash('success', 'Pin successfully updated !');

            return $this->redirectToRoute('app_home');
        }


        return $this->render('pins/edit.html.twig', ['pin' => $pin, 'form' => $form->createView()]);
    }



    /**
     * @Route("/pins/{id<[0-9]+>}", name="app_pins_delete", methods="DELETE")
     */


    public function delete(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {

        // dd($request->request->all());
        if ($this->isCsrfTokenValid('pin_deletion_' . $pin->getId(), $request->request->get('csrf_token'))) {
            $em->remove($pin);
            $em->flush();

            $this->addFlash('info', 'Pin successfully deleted !');
        }

        return $this->redirectToRoute('app_home');
    }
}