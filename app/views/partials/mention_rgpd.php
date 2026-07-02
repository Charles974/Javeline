<?php
/**
 * Mention d'information RGPD affichee sous les formulaires d'inscription.
 * Variable attendue : $typeFiche = 'membre' ou 'externe'.
 */
$estMembre = ($typeFiche ?? 'membre') === 'membre';
?>
<details class="mention-rgpd" hidden>
    <summary>Informations sur le traitement de vos données personnelles</summary>
    <div class="mention-rgpd-contenu">
        <p>
            Les données collectées dans ce formulaire sont traitées par l'association Javeline
            <?php if ($estMembre): ?>
                pour la gestion de votre adhésion et votre inscription aux challenges de tir organisés par le club
                (identification, contact, suivi de la licence et du certificat médical).
            <?php else: ?>
                pour votre inscription en tant que tireur invité aux challenges de tir organisés par le club
                (identification et contact).
            <?php endif; ?>
        </p>
        <p>
            <strong>Base légale :</strong>
            <?= $estMembre
                ? "exécution de votre contrat d'adhésion à l'association et intérêt légitime de l'association à organiser ses activités."
                : "intérêt légitime de l'association à organiser le challenge auquel vous participez." ?>
        </p>
        <p>
            <strong>Durée de conservation :</strong>
            <?= $estMembre
                ? "vos données sont conservées pendant la durée de votre adhésion, puis archivées ou supprimées 3 ans après votre dernier renouvellement."
                : "vos données sont conservées 3 ans après votre dernière participation à un challenge de l'association." ?>
        </p>
        <p>
            <strong>Destinataires :</strong> les données sont accessibles uniquement aux membres du bureau de
            l'association en charge de l'organisation des challenges. Elles ne sont ni cédées ni vendues à des tiers.
        </p>
        <p>
            <strong>Vos droits :</strong> conformément au Règlement Général sur la Protection des Données (RGPD),
            vous disposez d'un droit d'accès, de rectification, d'effacement et de limitation du traitement de vos
            données, ainsi que du droit d'introduire une réclamation auprès de la CNIL. Pour exercer ces droits,
            contactez l'association Javeline à l'adresse : <a href="mailto:[email-a-completer]">[email-a-completer]</a>.
        </p>
    </div>
</details>
