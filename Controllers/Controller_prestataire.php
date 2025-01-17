<?php

class Controller_prestataire extends Controller
{
    /**
     * @inheritDoc
     */
    public function action_default()
    {
        $this->action_dashboard();
    }

    /**
     * Renvoie le tableau de bord du prestataire avec les variables adéquates
     * @return void
     */
    public function action_dashboard()
    {
        sessionstart();
        if (isset($_SESSION['role'])) {
            unset($_SESSION['role']);
        }
        $_SESSION['role'] = 'prestataire';

        if (isset($_SESSION['id'])) {
            $bd = Model::getModel();
            $data = [
                'menu' => $this->action_get_navbar(), 
                'bdlLink' => '?controller=prestataire&action=mission_bdl', 
                'header' => [
                    'Société', 
                    'Composante', 
                    'Nom Mission', 
                    'Bon de livraison'
                ], 
                'dashboard' => $bd->getDashboardPrestataire($_SESSION['id'])
            ];
            return $this->render('prestataire_missions', $data);
        } else {
            // TODO Réaliser un render de l'erreur
            echo 'Une erreur est survenue lors du chargement du tableau de bord';
        }
    }


    /**
     * Action qui retourne les éléments du menu pour le prestataire
     * @return array[]
     */
    public function action_get_navbar()
    {
        return [
            ['link' => '?controller=prestataire&action=dashboard', 'name' => 'Missions'],
            ['link' => '?controller=prestataire&action=liste_bdl', 'name' => 'Bons de livraison']];
    }

    /**
     * Renvoie la vue qui montre les informations de l'utilisateur connecté
     * @return void
     */
    public function action_infos()
    {
        sessionstart();
        $this->render('infos', ['menu' => $this->action_get_navbar()]);
    }

    // TEST

    /**
     * Ajoute dans la base de données la date à laquelle le prestataire est absent
     * @return void
     */
    public function action_prestataire_creer_absences()
    {
        $bd = Model::getModel();
        if (
            isset($_POST['prenom']) && 
            isset($_POST['nom']) && 
            isset($_POST['email']) && 
            isset($_POST['Date']) && 
            isset($_POST['motif'])
        ) {
            // FIXME Fonction non déclaré dans le modèle
            $bd->addAbsenceForPrestataire($_POST['prenom'], $_POST['nom'], $_POST['email'], $_POST['Date'], $_POST['motif']);
        } else {
            $this->action_error("données incomplètes");
        }
    }

    /**
     * Renvoie la vue qui lui permet de remplir son bon de livraion avec le bon type
     * @return void
     */
    public function action_afficher_bdl()
    {
        $bd = Model::getModel();
        sessionstart();
        if (isset($_GET['id'])) {
            $typeBdl = $bd->getBdlTypeAndMonth($_GET['id']);
            if ($typeBdl['type_bdl'] == 'Heure') {
                $infosBdl = $bd->getAllNbHeureActivite($_GET['id']);
            } elseif ($typeBdl['type_bdl'] == 'Journée') {
                $infosBdl = $bd->getAllJourActivite($_GET['id']);
            } elseif ($typeBdl['type_bdl'] == 'Demi-journée') {
                $infosBdl = $bd->getAllDemiJourActivite($_GET['id']);
            }
            $data = [
                'menu' => $this->action_get_navbar(), 
                'bdl' => $typeBdl, 
                'infosBdl' => $infosBdl
            ];
            $this->render("activite", $data,'prestataire');
        } else {
            // TODO Réaliser un render de l'erreur
            echo 'Une erreur est survenue lors du chargement de ce bon de livraison';
        }
    }

    /**
     * Vérifie d'avoir les informations nécessaire pour renvoyer la vue liste avec les bonnes variables pour afficher la liste des bons de livraisons du prestataire en fonction de la mission
     * @return void
     */
    public function action_mission_bdl()
    {
        $bd = Model::getModel();
        sessionstart();
        if (isset($_GET['id'])) {
            $data = [
                'title' => 'Bons de livraison', 
                'buttonLink' => '?controller=prestataire&action=ajout_bdl_form', 
                'cardLink' =>  '?controller=prestataire&action=afficher_bdl', 
                'menu' => $this->action_get_navbar(), 
                'person' => $bd->getBdlsOfPrestataireByIdMission($_GET['id'], $_SESSION['id'])
            ];
            $this->render('liste', $data);
        }
    }

    /**
     * Renvoie la liste des bons de livraison du prestataire connecté
     * @return void
     */
    public function action_liste_bdl()
    {
        $bd = Model::getModel();
        sessionstart();
        if (isset($_SESSION['id'])) {
            $data = [
                'title' => 'Mes Bons de livraison', 
                'buttonLink' => '?controller=prestataire&action=ajout_bdl_form', 
                'cardLink' => '?controller=prestataire&action=afficher_bdl', 
                'menu' => $this->action_get_navbar(), 
                "person" => $bd->getAllBdlPrestataire($_SESSION['id'])
            ];
            $this->render("liste", $data);
        }
    }

    /**
     * Vérifie d'avoir les informations nécessaires pour créer un bon de livraison
     * @return void
     */
    public function action_prestataire_creer_bdl()
    {
        $bd = Model::getModel();
        sessionstart();
        if (isset($_SESSION['id']) && isset($_POST['mission'])) {
            // FIXME Fonction non déclaré dans le modèle 
            $bd->addBdlForPrestataire($_SESSION['id'], e($_POST['mission']));
        } else {
            // TODO Réaliser un render de l'erreur
            echo 'Une erreur est survenue lors de la création du bon de livraison';
        }
    }

    /**
     * Récupère le tableau renvoyé par le JavaScript et rempli les lignes du bon de livraison en fonction de son type
     * @return void
     */
    // TODO a tester
    public function action_completer_bdl()
    {
        $bd = Model::getModel();
        sessionstart();
        // Récupérer les données depuis la requête POST
        $data = json_decode(file_get_contents("php://input"), true);

        // Vérifier si les données sont présentes
        if ($data && is_array($data)) {
            // Parcourir chaque ligne du tableau
            foreach ($data as $row) {
                // Vérifier si l'activite existe avant de l'ajouter, sinon la modifier
                if ($bd->checkActiviteExiste($_GET['id'], $row[0])) {
                    $id_activite = $bd->getIdActivite($row[0], $_GET['id']);
                    if ($row[1] && $_GET['type'] == 'Heure') {
                        $bd->setNbHeure($id_activite, (int)$row[1]);
                    } elseif ($row[1] >= 0 && $row[1] <= 1 && $_GET['type'] == 'Journée') {
                        $bd->setJourneeJour($id_activite, (int)$row[1]);
                    } elseif ($row[1] >= 0 && $row[1] <= 2 && $_GET['type'] == 'Demi-journée') {
                        $bd->setDemiJournee($id_activite, (int)$row[1]);
                    }
                    if ($row[2]) {
                        $bd->setCommentaireActivite($id_activite, $row[2]);
                    }
                } elseif ($row[1]) {
                    if ($row[1] && $_GET['type'] == 'Heure') {
                        $bd->addNbHeureActivite($row[2], $_GET['id'], $_SESSION['id'], $row[0], (int)$row[1]);
                    } elseif ($row[1] >= 0 && $row[1] <= 1 && $_GET['type'] == 'Journée') {
                        $bd->addJourneeJour($row[2], $_GET['id'], $_SESSION['id'], $row[0], (int)$row[1]);
                    } elseif ($row[1] >= 0 && $row[1] <= 2 && $_GET['type'] == 'Demi-journée') {
                        $bd->addDemiJournee($row[2], $_GET['id'], $_SESSION['id'], $row[0], (int)$row[1]);
                    }
                }
            }
        }
        $this->render('dashboard');
    }

    /**
     * Renvoie le formulaire pour ajouter un bon de livraison
     * @return void
     */
    public function action_ajout_bdl_form()
    {
        $data = [
            'menu' => $this->action_get_navbar()
        ];
        $this->render('ajout_bdl', $data,'prestataire');
    }

    /**
     * Vérifie d'avoir les informations nécessaire pour ajouter un bon de livraison à une mission
     * @return void
     */
    public function action_ajout_bdl()
    {
        $bd = Model::getModel();
        sessionstart();
        if ($_POST['mission'] && $_POST['mois'] && $_POST['composante']) {
            $bd->addBdlInMission(e($_POST['mission']), e($_POST['composante']), e($_POST['mois']), $_SESSION['id']);
        }
        $this->action_ajout_bdl_form();
    }
}
