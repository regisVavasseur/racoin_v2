<?php
require 'vendor/autoload.php';

use App\controller\GetCategorieController;
use App\controller\GetDepartmentController;
use App\controller\indexController;
use App\controller\ItemController;
use App\model\Photo;
use db\connection;
use App\model\Annonce;
use App\model\Annonceur;
use App\model\Categorie;
use App\model\Departement;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


connection::createConn();

// Initialisation de Slim
$app = new App([
    'settings' => [
        'displayErrorDetails' => true,
    ],
]);

// Initialisation de Twig
$loader = new FilesystemLoader(__DIR__ . '/template');
$twig   = new Environment($loader);

// Ajout d'un middleware pour le trailing slash
$app->add(function (Request $request, Response $response, $next) {
    $uri  = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && str_ends_with($path, '/')) {
        $uri = $uri->withPath(substr($path, 0, -1));
        if ($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        } else {
            return $next($request->withUri($uri), $response);
        }
    }
    return $next($request, $response);
});


if (!isset($_SESSION)) {
    session_start();
    $_SESSION['formStarted'] = true;
}

if (!isset($_SESSION['token'])) {
    $token                  = md5(uniqid(rand(), TRUE));
    $_SESSION['token']      = $token;
    $_SESSION['token_time'] = time();
} else {
    $token = $_SESSION['token'];
}

$menu = [
    [
        'href' => './index.php',
        'text' => 'Accueil'
    ]
];

$chemin = dirname($_SERVER['SCRIPT_NAME']);

$annonce = new Annonce();
$annonceur = new Annonceur();
$categorie = new Categorie();
$photo = new Photo();

$cat = new GetCategorieController($annonce, $annonceur, $categorie, $photo);
$dpt = new GetDepartmentController();

$app->get('/', function () use ($twig, $menu, $chemin, $cat) {
    $annonceModel = new \App\model\Annonce();
    $annonceurModel = new \App\model\Annonceur();
    $photoModel = new \App\model\Photo();
    $index = new \App\controller\indexController($annonceModel, $annonceurModel, $photoModel);
    $index->displayAllAnnonce($twig, $menu, $chemin, $cat->getCategories());
});

$app->get('/item/{n}', function ($request, $response, $arg) use ($twig, $menu, $chemin, $cat) {
    $n     = $arg['n'];
    $item = new ItemController();
    $item->afficherItem($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/add', function () use ($twig, $app, $menu, $chemin, $cat, $dpt) {
    $ajout = new \App\controller\AddItemController();
    $ajout->addItemView($twig, $menu, $chemin, $cat->getCategories(), $dpt->getAllDepartments());
});

$app->post('/add', function ($request) use ($twig, $app, $menu, $chemin) {
    $allPostVars = $request->getParsedBody();
    $ajout = new \App\controller\AddItemController();
    $ajout->addNewItem($twig, $menu, $chemin, $allPostVars);
});

$app->get('/item/{id}/edit', function ($request, $response, $arg) use ($twig, $menu, $chemin) {
    $id   = $arg['id'];
    $item = new ItemController();
    $item->modifyGet($twig, $menu, $chemin, $id);
});
$app->post('/item/{id}/edit', function ($request, $response, $arg) use ($twig, $app, $menu, $chemin, $cat, $dpt) {
    $id          = $arg['id'];
    $allPostVars = $request->getParsedBody();
    $item        = new ItemController();
    $item->modifyPost($twig, $menu, $chemin, $id, $allPostVars, $cat->getCategories(), $dpt->getAllDepartments());
});

$app->map(['GET, POST'], '/item/{id}/confirm', function ($request, $response, $arg) use ($twig, $app, $menu, $chemin) {
    $id   = $arg['id'];
    $allPostVars = $request->getParsedBody();
    $item        = new ItemController();
    $item->edit($twig, $menu, $chemin, $id, $allPostVars);
});

$app->get('/search', function () use ($twig, $menu, $chemin, $cat) {
    $s = new \App\controller\SearchController();
    $s->show($twig, $menu, $chemin, $cat->getCategories());
});


$app->post('/search', function ($request, $response) use ($app, $twig, $menu, $chemin, $cat) {
    $array = $request->getParsedBody();
    $s     = new \App\controller\SearchController();
    $s->research($array, $twig, $menu, $chemin, $cat->getCategories());

});

$app->get('/annonceur/{n}', function ($request, $response, $arg) use ($twig, $menu, $chemin, $cat) {
    $n         = $arg['n'];
    $annonceur = new \App\controller\ViewAnnonceurController();
    $annonceur->afficherAnnonceur($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/del/{n}', function ($request, $response, $arg) use ($twig, $menu, $chemin) {
    $n    = $arg['n'];
    $item = new \App\controller\ItemController();
    $item->supprimerItemGet($twig, $menu, $chemin, $n);
});

$app->post('/del/{n}', function ($request, $response, $arg) use ($twig, $menu, $chemin, $cat) {
    $n    = $arg['n'];
    $item = new \App\controller\ItemController();
    $item->supprimerItemPost($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/cat/{n}', function ($request, $response, $arg) use ($twig, $menu, $chemin, $cat) {
    $n = $arg['n'];
    $annonceModel = new Annonce();
    $annonceurModel = new Annonceur();
    $categorieModel = new Categorie();
    $photoModel = new Photo();
    $categorie = new \App\controller\GetCategorieController($annonceModel, $annonceurModel, $categorieModel, $photoModel);
    $categorie->displayCategorie($twig, $menu, $chemin, $cat->getCategories(), $n);
});

$app->get('/api(/)', function () use ($twig, $menu, $chemin, $cat) {
    $template = $twig->load('api.html.twig');
    $menu     = array(
        array(
            'href' => $chemin,
            'text' => 'Acceuil'
        ),
        array(
            'href' => $chemin . '/api',
            'text' => 'Api'
        )
    );
    echo $template->render(array('breadcrumb' => $menu, 'chemin' => $chemin));
});

$app->group('/api', function () use ($app, $twig, $menu, $chemin, $cat) {

    $app->group('/annonce', function () use ($app) {

        $app->get('/{id}', function ($request, $response, $arg) use ($app) {
            $id          = $arg['id'];
            $annonceList = ['id_annonce', 'id_categorie as categorie', 'id_annonceur as annonceur', 'id_departement as departement', 'prix', 'date', 'titre', 'description', 'ville'];
            $return      = Annonce::select($annonceList)->find($id);

            if (isset($return)) {
                $response->headers->set('Content-Type', 'application/json');
                $return->categorie     = Categorie::find($return->categorie);
                $return->annonceur     = Annonceur::select('email', 'nom_annonceur', 'telephone')
                    ->find($return->annonceur);
                $return->departement   = Departement::select('id_departement', 'nom_departement')->find($return->departement);
                $links                 = [];
                $links['self']['href'] = '/api/annonce/' . $return->id_annonce;
                $return->links         = $links;
                echo $return->toJson();
            } else {
                $app->notFound();
            }
        });
    });

    $app->group('/annonces(/)', function () use ($app) {

        $app->get('/', function ($request, $response) use ($app) {
            $annonceList = ['id_annonce', 'prix', 'titre', 'ville'];
            $response->headers->set('Content-Type', 'application/json');
            $a     = Annonce::all($annonceList);
            $links = [];
            foreach ($a as $ann) {
                $links['self']['href'] = '/api/annonce/' . $ann->id_annonce;
                $ann->links            = $links;
            }
            $links['self']['href'] = '/api/annonces/';
            $a->links              = $links;
            echo $a->toJson();
        });
    });


    $app->group('/categorie', function () use ($app) {

        $app->get('/{id}', function ($request, $response, $arg) use ($app) {
            $id = $arg['id'];
            $response->headers->set('Content-Type', 'application/json');
            $a     = Annonce::select('id_annonce', 'prix', 'titre', 'ville')
                ->where('id_categorie', '=', $id)
                ->get();
            $links = [];

            foreach ($a as $ann) {
                $links['self']['href'] = '/api/annonce/' . $ann->id_annonce;
                $ann->links            = $links;
            }

            $c                     = Categorie::find($id);
            $links['self']['href'] = '/api/categorie/' . $id;
            $c->links              = $links;
            $c->annonces           = $a;
            echo $c->toJson();
        });
    });

    $app->group('/categories(/)', function () use ($app) {
        $app->get('/', function ($request, $response, $arg) use ($app) {
            $response->headers->set('Content-Type', 'application/json');
            $c     = Categorie::get();
            $links = [];
            foreach ($c as $cat) {
                $links['self']['href'] = '/api/categorie/' . $cat->id_categorie;
                $cat->links            = $links;
            }
            $links['self']['href'] = '/api/categories/';
            $c->links              = $links;
            echo $c->toJson();
        });
    });

    $app->get('/key', function () use ($app, $twig, $menu, $chemin, $cat) {
        $kg = new App\controller\KeyGenerator();
        $kg->show($twig, $menu, $chemin, $cat->getCategories());
    });

    $app->post('/key', function () use ($app, $twig, $menu, $chemin, $cat) {
        $nom = $_POST['nom'];

        $kg = new App\controller\KeyGenerator();
        $kg->generateKey($twig, $menu, $chemin, $cat->getCategories(), $nom);
    });
});


$app->run();
