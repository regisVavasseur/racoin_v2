<?php

namespace App\controller;

use App\model\Annonce;
use App\model\Annonceur;
use App\model\Photo;

class IndexController
{
    private $annonceModel;
    private $annonceurModel;
    private $photoModel;

    public function __construct(Annonce $annonceModel, Annonceur $annonceurModel, Photo $photoModel)
    {
        $this->annonceModel = $annonceModel;
        $this->annonceurModel = $annonceurModel;
        $this->photoModel = $photoModel;
    }

    public function displayAllAnnonce($twig, $menu, $chemin, $cat)
    {
        $template = $twig->load("index.html.twig");
        $menu = [
            [
                'href' => $chemin,
                'text' => 'Acceuil'
            ],
        ];

        $annonces = $this->getAllAnnonces($chemin);

        echo $template->render([
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "categories" => $cat,
            "annonces"   => $annonces
        ]);
    }

    private function getAllAnnonces($chemin)
    {
        $annonces = $this->annonceModel->with("Annonceur")->orderBy('id_annonce', 'desc')->take(12)->get();

        return $annonces->map(function ($annonce) {
            $annonce->nb_photo = $this->photoModel->where("id_annonce", "=", $annonce->id_annonce)->count();
            $annonce->url_photo = $annonce->nb_photo > 0 ? $this->photoModel->select("url_photo")->where("id_annonce", "=", $annonce->id_annonce)->first()->url_photo : '/img/noimg.png';
            $annonce->nom_annonceur = $this->annonceurModel->select("nom_annonceur")->where("id_annonceur", "=", $annonce->id_annonceur)->first()->nom_annonceur;
            return $annonce;
        });
    }
}