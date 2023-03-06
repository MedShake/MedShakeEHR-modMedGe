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
 * Patient > ajax : générer le fichier DICOM worklist pour Orthanc
 * Module médecine générale
 *
 * Inclusion avant l'envoi au template pour extraction de data spécifique à la spé
 * dans le fichier worklist : ici data sur grossesse
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!is_numeric($_POST['patientID'])) die;

//chercher une grossesse en cours
$typeCsCla = new msData;
$name2typeID = $typeCsCla->getTypeIDsFromName(['groFermetureSuivi', 'nouvelleGrossesse', 'ddgReel', 'DDR']);

$marqueurs = [
	'groFermetureSuivi' => $name2typeID['groFermetureSuivi'],
	'nouvelleGrossesse' => $name2typeID['nouvelleGrossesse'],
	'patientID' => $_POST['patientID']
];

if ($findGro = msSQL::sqlUnique("SELECT pd.id as idGro, eg.id as idFin
   from objets_data as pd
   left join objets_data as eg on pd.id=eg.instance and eg.typeID = :groFermetureSuivi and eg.outdated='' and eg.deleted=''
   where pd.toID = :patientID and pd.typeID = :nouvelleGrossesse and pd.outdated='' and pd.deleted='' order by pd.creationDate desc
   limit 1", $marqueurs)) {
	if (!$findGro['idFin']) {
		$p['page']['grossesseEnCours']['id'] = $findGro['idGro'];

		// générer le formulaire grossesse tête de page.
		$formSyntheseGrossesse = new msForm();
		$formSyntheseGrossesse->setFormIDbyName('medGeSyntheseObs');
		$formSyntheseGrossesse->setInstance($p['page']['grossesseEnCours']['id']);
		$p['page']['dataGrossesse'] = $formSyntheseGrossesse->getPrevaluesForPatient($_POST['patientID']);

		if (isset($p['page']['dataGrossesse'][$name2typeID['ddgReel']]) and strlen($p['page']['dataGrossesse'][$name2typeID['ddgReel']]) == 10) {
			$p['page']['patient']['dicomDDR'] = msTools::readableDate2Reverse(msModMedgeCalcMed::ddg2ddr($p['page']['dataGrossesse'][$name2typeID['ddgReel']]));
		} elseif (isset($p['page']['dataGrossesse'][$name2typeID['DDR']]) and strlen($p['page']['dataGrossesse'][$name2typeID['DDR']]) == 10) {
			$p['page']['patient']['dicomDDR'] = msTools::readableDate2Reverse($p['page']['dataGrossesse'][$name2typeID['DDR']]);
		}
	}
}
