<?php
require '../Utils/configuration.php';

class Controller_interlocuteur_client extends Controller
{
    /**
     * @inheritDoc
     */
    public function action_default()
    {
        $this->action_dashboard();
    }

    public function action_dashboard()
    {
        if (isset($_SESSION['id'])) {
            $bd = Model::getModel();
            $data = ['dashboard' => $bd->getClientContactData()];
            return $this->render('interlocuteur_client', $data);
        } else {
            error_log('Une erreur est survenue lors du chargement du tableau de bord');
        }
    }

    public function action_envoyer_email()
    {
        $bd = Model::getModel();
        $destinatairesEmails = '';
        foreach ($bd->getComponentCommercial($_SESSION['id']) as $v){
            $destinatairesEmails .= $v['email'] . ', ';
        }

        $emetteur = $bd->getEmailById($_SESSION['id']);
        $objet = $_POST['objet'];
        $message = e($_POST['message']);

        //header pour l'envoie du mail
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <' . $emetteur . '>' . "\r\n";

        mail($destinatairesEmails, $objet, $message, $headers);
    }

    public function telecharger_bdl()
    {
        $cheminFichier = $cheminBdl . $_GET['id'];

        // Vérifiez si le fichier existe
        if (file_exists($cheminFichier)) {
            // Définir les en-têtes HTTP pour le téléchargement
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($cheminFichier) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($cheminFichier));

            // Lire et renvoyer le contenu du fichier
            readfile($cheminFichier);
            exit;
        } else {
            // Le fichier n'existe pas
            echo "Le fichier n'existe pas.";
        }
    }

    public function action_bdl()
    {
        if (isset($_GET['idBdl'])) {
            $bd = Model::getModel();
            $data = ['bdl' => $bd->getBdlInfos($_GET['idBdl'])];
            $this->render('bdl', $data);
        }
    }
}
