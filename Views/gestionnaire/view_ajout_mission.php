<!-- Forumulaire permettant de créer une nouvelle mission -->

<?php
require 'view_begin.php';
require 'view_header.php';
?>
    <div class="add-container">
        <div class="form-abs">
            <h1>Ajout Mission</h1>
            <!-- Yavait une erreur corrigé ou yavait un > en trop et fallait echo le controller-->
            
            <form action="?controller=<?= $_GET['controller']?>&action=ajout_mission" method="post">
                <h2>Informations mission</h2>
                <input type="text" placeholder="Nom de la mission" name='mission' class="input-case">
                <input type="text" placeholder="Société" id='sté' name='client' class="input-case">



                <!-- FIXME il faut controller la saisie du composant sinon on a une erreur car composant est une clé étrangère
                    faut il afficher le nom de toutes les composantes dans un select option? mieux connaitre le projet pour fixer
                -->
                <input type="text" placeholder="Composante" name='composante' id='cpt' class="input-case">
                <div class="form-names">
                    <select name="type-bdl">
                        <option selected>Type de bon de livraison </option>
                        <option value="journee">Journée </option>
                        <option value="demi-journee">Demi-journée </option>
                        <option value="heure">Heure </option>
                    </select>
                    <input type="date" placeholder="Date de début" name="date-mission" class="input-case">
                </div>
                <div class="buttons" id="create">
                    <button type="submit">Créer</button>
                </div>
            </form>
        </div>
    </div>
<?php
require 'view_end.php';
?>
