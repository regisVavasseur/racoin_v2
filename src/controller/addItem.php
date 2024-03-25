<?php

namespace App\controller;
use App\model\Annonce;
use App\model\Annonceur;

class addItem
{

    function addItemView($twig, $menu, $chemin, $cat, $dpt): void
    {
        $template = $twig->load("add.html.twig");
        echo $template->render(array(
                "breadcrumb" => $menu,
                "chemin" => $chemin,
                "categories" => $cat,
                "departements" => $dpt
            )
        );

    }

    public function addNewItem($twig, $menu, $chemin, $allPostVars): void
    {
        date_default_timezone_set('Europe/Paris');
        $formData = $this->sanitizeFormData($_POST);
        $errors = $this->validateFormData($formData);

        if (!empty($errors)) {
            $this->displayErrors($twig, $menu, $chemin, $errors);
        } else {
            $this->saveFormData($allPostVars);
            $this->displaySuccess($twig, $menu, $chemin);
        }
    }

    private function sanitizeFormData($data): array
    {
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            $sanitizedData[$key] = trim($value);
        }
        return $sanitizedData;
    }

    private function displaySuccess($twig, $menu, $chemin): void
    {
        $template = $twig->load("add-confirm.html.twig");
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin
        ));
    }

    private function validateFormData($data): array
    {
        $errors = array();
        $errors['nameAdvertiser'] = empty($data['nom']) ? 'Veuillez entrer votre nom' : '';
        $errors['emailAdvertiser'] = !$this->isEmail($data['email']) ? 'Veuillez entrer une adresse mail correcte' : '';
        $errors['phoneAdvertiser'] = (empty($data['phone']) || !is_numeric($data['phone'])) ? 'Veuillez entrer votre numéro de téléphone' : '';
        $errors['villeAdvertiser'] = empty($data['ville']) ? 'Veuillez entrer votre ville' : '';
        $errors['departmentAdvertiser'] = !is_numeric($data['departement']) ? 'Veuillez choisir un département' : '';
        $errors['categorieAdvertiser'] = !is_numeric($data['categorie']) ? 'Veuillez choisir une catégorie' : '';
        $errors['titleAdvertiser'] = empty($data['title']) ? 'Veuillez entrer un titre' : '';
        $errors['descriptionAdvertiser'] = empty($data['description']) ? 'Veuillez entrer une description' : '';
        $errors['priceAdvertiser'] = (empty($data['price']) || !is_numeric($data['price'])) ? 'Veuillez entrer un prix' : '';
        $errors['passwordAdvertiser'] = (empty($data['psw']) || empty($data['confirm-psw']) || $data['psw'] != $data['confirm-psw']) ? 'Les mots de passes ne sont pas identiques' : '';


        // Filtrer pour ne garder que les erreurs non vides
        return array_filter($errors, function ($value) {
            return !empty($value);
        });
    }

    private function isEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private function displayErrors($twig, $menu, $chemin, $errors): void
    {
        $template = $twig->load("add-error.html.twig");
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "errors" => $errors
        ));
    }

    private function saveFormData($allPostVars): void
    {
        $annonce   = new Annonce();
        $annonceur = new Annonceur();

        $annonceur->email         = htmlentities($allPostVars['email']);
        $annonceur->nom_annonceur = htmlentities($allPostVars['nom']);
        $annonceur->telephone     = htmlentities($allPostVars['phone']);

        $annonce->ville          = htmlentities($allPostVars['ville']);
        $annonce->id_departement = $allPostVars['departement'];
        $annonce->prix           = htmlentities($allPostVars['price']);
        $annonce->mdp            = password_hash($allPostVars['psw'], PASSWORD_DEFAULT);
        $annonce->titre          = htmlentities($allPostVars['title']);
        $annonce->description    = htmlentities($allPostVars['description']);
        $annonce->id_categorie   = $allPostVars['categorie'];
        $annonce->date           = date('Y-m-d');


        $annonceur->save();
        $annonceur->annonce()->save($annonce);
    }

}

