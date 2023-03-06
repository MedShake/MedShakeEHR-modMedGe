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
 *
 * Données et calcules complémentaires :
 * - liés à la présence de typeID particuliers dans le tableau de tags
 * passé au modèle de courrier
 * - appelés en fonction du modèle (modeleID) du courrier
 * - appelés par défaut si existe par les methodes de la class msCourrier
 *
 * Module médecine générale
 *
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msModMedgeDataCourrier
{

	/**
	 * Extractions complémentaires générales pour getCrData() de msCourrier
	 * @param  array $d         tableau de tags
	 * @return void
	 */
	public static function getCrDataCompleteModule(&$d)
	{

		//atcd du patient (data du formulaire latéral)
		$atcd = new msCourrier();
		$atcd = $atcd->getExamenData($d['patientID'], 'medGeATCD', 0);
		if (is_array($atcd)) {
			foreach ($atcd as $k => $v) {
				if (!in_array($k, array_keys($d))) $d[$k] = $v;
			}
		}
		// résoudre le problème de l'IMC
		unset($d['imc']);
		if (isset($d['poids'], $d['taillePatient'])) $d['imc'] = msModMedgeCalcMed::imc($d['poids'], $d['taillePatient']);
	}

	/**
	 * Extraction complémentaire pour le modèle de courrier Résumé des vaccinations
	 * @param  array $d tableau des tags
	 * @return void
	 */
	public static function getCourrierDataCompleteModuleModele_modeleCourrierResumeVaccinations(&$d)
	{
		$obj = new msObjet;
		$obj->setToID($d['patientID']);
		if ($liste = $obj->getListObjetsIdFromName('medGeCsVaccination')) {
			foreach ($liste as $id => $date) {
				$obj2 = new msObjet;
				$obj2->setObjetID($id);
				$tab = $obj2->getObjetAndSons('name');
				$date = new DateTime($date);
				$rd[] = '<li>' . $date->format('d/m/Y') . ' : <strong>' . $tab['medGeCsDivVaccinationVaccin']['value'] . '</strong> (lot : ' . $tab['medGeCsDivVaccinationLot']['value'] . ')</li>';
			}

			$d['listeVaccinations'] = '<ul>' . implode("\n", $rd) . '</ul>';
		}
	}

	/**
	 * Extraction complémentaire pour le modèle de courrier résumé du dossier
	 * @param  array $d tableau des tags
	 * @return void
	 */
	public static function getCourrierDataCompleteModuleModele_medGeModeleCourrierResumeDossier(&$d)
	{
		global $p;

		// extraction des ATCD du formulaire lateral
		$atcd = new msCourrier();
		$atcd = $atcd->getExamenData($d['patientID'], 'medGeATCD', 0);
		if (is_array($atcd)) {
			$d = $d + $atcd;
		}

		// si LAP, extraction des donnéés structurées
		if ($p['config']['optionGeActiverLapInterne'] == 'true') {
			$patient = new msPeople;
			$patient->setToID($d['patientID']);
			foreach (explode(',', $p['config']['lapActiverAtcdStrucSur']) as $v) {
				$d['atcdStruc'][$v] = $patient->getAtcdStruc($v);
			}
			foreach (explode(',', $p['config']['lapActiverAllergiesStrucSur']) as $v) {
				$d['allergiesStruc'][$v] = $patient->getAllergies($v);
			}
			$d['ALD'] = $patient->getALD();
		}
	}
}
