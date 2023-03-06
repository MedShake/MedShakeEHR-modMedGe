<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
 * Bertrand Boutillier <b.boutillier@gmail.com>
 * http://www.medshake.net
 *
 * MedShakeEHR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * MedShakeEHR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Patient > ajax : calculer les honoraires de règlement
 * Module Médecine Générale
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

// infos patient
$patient = new msPeople();
$patient->setToID($_POST['patientID']);
$p['page']['patient']['administrativeDatas'] = $patient->getSimpleAdminDatasByName();
$p['page']['patient']['ages'] = $patient->getAgeFormats();

////// définitions pour le formulaire
$hono = new msModMedgeReglement();
$hono->setMode($_POST['mode']);
if ($_POST['regleSecteurHonoraires']) {
	$hono->setSecteurTarifaire($_POST['regleSecteurHonoraires']);
} else {
	$hono->setSecteurTarifaire($p['config']['administratifSecteurHonorairesCcam']);
}
if ($_POST['regleSecteurHonorairesNgap']) {
	$hono->setSecteurTarifaireNgap($_POST['regleSecteurHonorairesNgap']);
} else {
	$hono->setSecteurTarifaireNgap($p['config']['administratifSecteurHonorairesNgap']);
}
if ($_POST['regleSecteurGeoTarifaire']) {
	$hono->setSecteurTarifaireGeo($_POST['regleSecteurGeoTarifaire']);
} else {
	$hono->setSecteurTarifaireGeo($p['config']['administratifSecteurGeoTarifaire']);
}
$hono->setPatientAgeInMonths($p['page']['patient']['ages']['ageTotalMonths']);
$hono->setPatientSexe($p['page']['patient']['administrativeDatas']['administrativeGenderCode']);
$hono->setContexte($_POST['mcContexte']);
$hono->setPlageAge($_POST['mcAge']);
$hono->setSituation($_POST['mcSituation']);
$hono->setPeriode($_POST['mcPeriode']);
$hono->setIK($_POST['mcIK']);
if (isset($_POST['mcActesECG']) and $_POST['mcActesECG'] == 'true') $hono->addActeCcam('DEQP003');
if (isset($_POST['mcActesFrottis']) and $_POST['mcActesFrottis'] == 'true') $hono->addActeCcam('JKHD001');
if (isset($_POST['actesFaits'])) $hono->setActesFaits($_POST['actesFaits']);
$menus = $hono->getOptionsTagsForMenus();
$menus['details'] = $hono->getActes();
$menus['tarif'] = $hono->getTarifFinal();
$menus['ikHelpText'] = $hono->getIkHelpText();
$menus['messagesInfos'] = $hono->getMessagesInfos();
exit(json_encode($menus));
