<?php

namespace App\Controller;

use App\Entity\Prod;
use App\Form\ProdType;
use App\Repository\ProdRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class prodController extends AbstractController
{
    #[Route('/addProd', name: 'addProd', methods: ['GET', 'POST'])]
    public function addProd(ProdRepository $prodRepository, Request $request)
    {
        $prod = new Prod();
        //dd($prod);
        //Permet de créer le formulaire
        $prodForm = $this->createForm(ProdType::class, $prod);
        //permet de gérer les requêtes, récupérer get et post du formulaire
        $prodForm->handleRequest($request);
        //on contrôle 2 conditions préexistantes
        if($prodForm->isSubmitted() && $prodForm->isValid()){

            //récupérer une image
            $img = $prodForm->get('img')->getData();
            //créer une ID unique de l'image sous format de data
            $imgName = md5(uniqid()) . '.' . $img->guessExtension();
            //On met l'image' récupérer dans le dossier public/uploadDirectory grace au paramètre créé dans le services.yaml
            $img->move($this->getParameter('uploadDirectory'), $imgName);
            //ajouter l'id unique $imgName dans le $prod
            $prod->setImg($imgName);

            //on demande à ce que le champ active soit à 1 true, soit qu'on met l'active de l'utilisateur en true
            $prod->setActive(true);
            //on lance l'exécution de la mise en bdd
            $prodRepository->add($prod);
            //dd($prodForm, $prod);
            //cela permet de rediriger vers une fonction on appelle par son nom
            return $this->redirectToRoute('showProds');
        }
        //permet de créer la vue qui affichera notre formulaire
        return $this->render('prod/addProd.html.twig', [
            'prodForm' => $prodForm->createView()
        ]);
    }
        // la fonction showProd permet de revenir a la page html pour afficher les produits d'où les redirection dans les autrres fonctions
        #[Route('/prods', name: 'showProds', methods: ['GET', 'POST'])]
        public function showProds(ProdRepository $prodRepository)
        {
            $prodsActive = $prodRepository->findBy(['active'=>true]);
            $prodsUnactive = $prodRepository->findBy(['active'=>false]);

            return $this->render('prod/prods.html.twig', [
                    'prodsActive' => $prodsActive,
                    'prodsUnactive'=> $prodsUnactive
            ]);
        }

        //une fois l'ajout de ID dans ma ROUTE alors il est reconnu automatiquement ensuite dans le $ID comme variable
        #[Route('/changeActiveProd/{id}', name: 'changeActiveProd', methods: ['GET', 'POST'])]
        public function changeActiveProd( ProdRepository $prodRepository, $id){
                //on utilise la methode findOneBy pour récupérer (1 entité 1 id) l'entité correspondant à l'id
                $prod = $prodRepository->findOneBY(['id' => $id], ['id'=>'DESC']);

                if ($prod->getActive() == true){
                    //on met l'active en false
                    $prod->setActive(false);
                }else{$prod->setActive(true);
                }

                //met à jour la BDD
                $prodRepository->add($prod);
                //Renvoi à la fonction showProd
                return $this->redirectToRoute('showProds');
        }

        #[Route('/deleteUnactiveProd/{id}', name: 'deleteUnactiveProd', methods: ['GET', 'POST'])]
        public function deleteUnactiveProd(ProdRepository $prodRepository, $id)
        {
                $prod = $prodRepository->findOneBy([
                    'id' =>$id
                ]);
                if($prod->getActive() == false){
                $prodRepository->remove($prod);
                }
                return $this->redirectToRoute('showProds');
        }

        #[Route('/singleShowProd/{id}', name: 'singleShowProd', methods: ['GET', 'POST'])]
        public function singleShowProd(ProdRepository $prodRepository, $id)
        {
                //on SELECT 1 élément par son ID
                $prod = $prodRepository->findOneBy(['id' => $id]);

            //renvoi sur la page HTML nommée
                return  $this->render('Prod/singleShowProd.html.twig',[
                    'prod' => $prod
                    ]);
        }

        #[Route('/updateProd/{id}', name: 'updateProd', methods: ['GET', 'POST'])]
        public function updateProd(ProdRepository $prodRepository, $id, Request $request)
        {
            //On sélectionne notre élément ID
            $prod = $prodRepository->findOneBy(['id' => $id]);
            //je crée le patron du formulaire qui est en relation avec mon prof

            $editForm = $this->createForm(ProdType::class, $prod);
            //Préparation pour l'envoi, ça récupère le get ou le post, i l définit l'action
            $editForm->handleRequest($request);
            //si le formulaire est envoyé et valide alors
            if ($editForm->isSubmitted() && $editForm->isValid()) {
                //$prodRepository est ma requête je lui dde d'insérer mes données
                $prodRepository->add($prod);

                //cela permet de rediriger vers une fonction qui nous renvoi sur une page 'showProds'
                return $this->redirectToRoute('showProds');
            }
            //On retourne sur la page update
            return $this->render('prod/updateProd.html.twig', [
                //htm ne lis pas les variables, donc on doit lui donner un nom por qu'elle soit lu
                //createView pour créer la forme du formulaire
                'editForm' => $editForm->createView()
            ]);
        }
    }