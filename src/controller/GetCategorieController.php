<?php

namespace App\controller;

use App\model\Annonce;
use App\model\Annonceur;
use App\model\Categorie;
use App\model\Photo;

class GetCategorieController
{
    private Annonce $annonceModel;
    private Annonceur $annonceurModel;
    private Categorie $categorieModel;
    private Photo $photoModel;

    public function __construct(Annonce $annonceModel, Annonceur $annonceurModel, Categorie $categorieModel, Photo $photoModel)
    {
        $this->annonceModel = $annonceModel;
        $this->annonceurModel = $annonceurModel;
        $this->categorieModel = $categorieModel;
        $this->photoModel = $photoModel;
    }

    public function getCategories()
    {
        return $this->categorieModel->orderBy('nom_categorie')->get()->toArray();
    }

    public function getCategorieContent($chemin, $n): array
    {
        $tmp = $this->annonceModel->with("Annonceur")->orderBy('id_annonce','desc')->where('id_categorie', "=", $n)->get();
        $annonce = [];
        foreach($tmp as $t) {
            $t->nb_photo = $this->photoModel->where("id_annonce", "=", $t->id_annonce)->count();
            if($t->nb_photo > 0){
                $t->url_photo = $this->photoModel->select("url_photo")
                    ->where("id_annonce", "=", $t->id_annonce)
                    ->first()->url_photo;
            }else{
                $t->url_photo = $chemin.'/img/noimg.png';
            }
            $t->nom_annonceur = $this->annonceurModel->select("nom_annonceur")
                ->where("id_annonceur", "=", $t->id_annonceur)
                ->first()->nom_annonceur;
            array_push($annonce, $t);
        }
        return $annonce;
    }

    public function displayCategorie($twig, $menu, $chemin, $cat, $n): void
    {
        $template = $twig->load("index.html.twig");
        $menu = array(
            array('href' => $chemin,
                'text' => 'Acceuil'),
            array('href' => $chemin."/cat/".$n,
                'text' => $this->categorieModel->find($n)->nom_categorie)
        );

        $annonces = $this->getCategorieContent($chemin, $n);
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "categories" => $cat,
            "annonces" => $annonces));
    }
}