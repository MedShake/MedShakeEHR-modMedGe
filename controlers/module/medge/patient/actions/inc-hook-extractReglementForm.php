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
 * Patient > ajax : obtenir le formulaire de règlement pour la médecine générale
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 */


if ($hono->getReglementForm() == 'baseReglementS1' or $hono->getReglementForm() == 'baseReglementS2') {
	$delegate = TRUE;
} else {
	//template
	$template = "medGePatientReglementForm";

	$hono = new msModMedgeReglement();
	$hono->setPatientID($_POST['patientID']);

	if (!isset($_POST['objetID']) or $_POST['objetID'] === '') {
		$hono->setReglementForm($_POST['reglementForm']);
		$hono->setPorteur($_POST['porteur']);
		$hono->setUserID($userID = is_numeric($_POST['asUserID']) ? $_POST['asUserID'] : $p['user']['id']);
		$hono->setModule($_POST['module']);
	} else {
		$hono->setObjetID($_POST['objetID']);
	}

	//pour menu de choix de l'acte, par catégories
	$p['page']['menusActes'] = $hono->getFacturesTypesMenus();

	//edition : acte choisi :
	$p['page']['selectedFactureTypeID'] = $hono->getFactureTypeIDFromObjetID();

	// formulaire de base
	$form = new msForm();
	$form->setFormIDbyName('baseReglementS1');
	$form->setTypeForNameInForm('byName');
	if ($_POST['objetID'] > 0) {
		$prevalues = $hono->getPreValuesForReglementForm();
		$form->setPrevalues($prevalues);
	}
	$p['page']['form'] = $form->getForm();
	$form->addSubmitToForm($p['page']['form'], 'btn-warning btn-lg btn-block');
	$p['page']['formIN'] = 'baseReglementS1';

	// déterminer les secteurs tarifaires
	$hono->setSecteursTarifaires();

	// champ cachés
	$hono->setHiddenInputToReglementForm($p['page']['form']);

	// infos patient
	$patient = new msPeople();
	$patient->setToID($_POST['patientID']);
	$p['page']['patient']['administrativeDatas'] = $patient->getSimpleAdminDatasByName();
	$p['page']['patient']['ages'] = $patient->getAgeFormats();
	$p['page']['patient']['id'] = $_POST['patientID'];

	////// définitions pour le formulaire
	$hono->setContexte('cabinet');
	$hono->setPatientAgeInMonths($p['page']['patient']['ages']['ageTotalMonths']);
	$hono->setPatientSexe($p['page']['patient']['administrativeDatas']['administrativeGenderCode']);
	$hono->automaticRules();
	$p['page']['formReg']['itemsMenus'] = $hono->getOptionsTagsForMenus();
	$p['page']['formReg']['actes'] = $hono->getActes();

	// liste des modificateurs CCAM
	$p['page']['modifcateursCcam'] = $hono->getModificateursCcam();
}
