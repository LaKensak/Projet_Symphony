<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Form\PhotoOrderType;
use App\Form\PhotoUploadType;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/mon-compte')]
#[IsGranted('ROLE_USER')]
class GalleryController extends AbstractController
{
    #[Route('', name: 'app_gallery')]
    public function index(Request $request, PhotoRepository $photoRepository, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $photos = $photoRepository->findByUser($user);

        $uploadForm = $this->createForm(PhotoUploadType::class);
        $uploadForm->handleRequest($request);

        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $photoFile = $uploadForm->get('photo')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                $fileSize = $photoFile->getSize();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );

                    $photo = new Photo();
                    $photo->setFilename($newFilename);
                    $photo->setFilesize($fileSize);
                    $photo->setUser($user);

                    $em->persist($photo);
                    $em->flush();

                    $this->addFlash('success', 'Photo ajoutée avec succès.');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                }
            }

            return $this->redirectToRoute('app_gallery');
        }

        $orderForms = [];
        foreach ($photos as $photo) {
            $orderForms[$photo->getId()] = $this->createForm(PhotoOrderType::class, $photo, [
                'action' => $this->generateUrl('app_photo_order', ['id' => $photo->getId()])
            ])->createView();
        }

        return $this->render('gallery/index.html.twig', [
            'photos' => $photos,
            'uploadForm' => $uploadForm,
            'orderForms' => $orderForms,
        ]);
    }

    #[Route('/photo/{id}/order', name: 'app_photo_order', methods: ['POST'])]
    public function updateOrder(Photo $photo, Request $request, EntityManagerInterface $em): Response
    {
        if ($photo->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(PhotoOrderType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Ordre mis à jour.');
        }

        return $this->redirectToRoute('app_gallery');
    }

    #[Route('/photo/{id}/delete', name: 'app_photo_delete', methods: ['POST'])]
    public function delete(Photo $photo, Request $request, EntityManagerInterface $em): Response
    {
        if ($photo->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $photo->getId(), $request->request->get('_token'))) {
            $filename = $photo->getFilename();
            $filepath = $this->getParameter('photos_directory') . '/' . $filename;

            if (file_exists($filepath)) {
                unlink($filepath);
            }

            $em->remove($photo);
            $em->flush();

            $this->addFlash('success', 'Photo supprimée.');
        }

        return $this->redirectToRoute('app_gallery');
    }
}
