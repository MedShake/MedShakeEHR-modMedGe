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
 * Patient > ajax : générer la colonne atcd module médecine générale
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


// le formulaire latéral ATCD
$formLat = new msForm();
$formLat->setFormIDbyName('medGeATCD');
$formLat->getPrevaluesForPatient($p['page']['patient']['id']);
$p['page']['formLat'] = $formLat->getForm();
$p['page']['formJavascript']['medGeATCD'] = $formLat->getFormJavascript();

// si LAP activé : allergie et atcd structurés
if ($p['config']['optionGeActiverLapInterne'] == 'true') {

	// gestion atcd structurés
	if (!empty(trim($p['config']['lapActiverAtcdStrucSur']))) {
		$gethtml = new msGetHtml;
		$gethtml->set_template('inc-patientAtcdStruc');
		foreach (explode(',', $p['config']['lapActiverAtcdStrucSur']) as $v) {
			$p['page']['beforeVar'][$v] = $patient->getAtcdStruc($v);
			if (empty($p['page']['beforeVar'][$v])) $p['page']['beforeVar'][$v] = array('fake');
			$p['page']['formLat']['before'][$v] = $gethtml->genererHtmlVar($p['page']['beforeVar'][$v]);
		}
		unset($p['page']['beforeVar'], $gethtml);
	}

	// gestion allergies structurées
	if (!empty(trim($p['config']['lapActiverAllergiesStrucSur']))) {
		$gethtml = new msGetHtml;
		$gethtml->set_template('inc-patientAllergies');
		foreach (explode(',', $p['config']['lapActiverAllergiesStrucSur']) as $v) {
			$p['page']['beforeVar'][$v] = $patient->getAllergies($v);
			if (empty($p['page']['beforeVar'][$v])) $p['page']['beforeVar'][$v] = array('fake');
			$p['page']['formLat']['before'][$v] = $gethtml->genererHtmlVar($p['page']['beforeVar'][$v]);
		}
		unset($p['page']['beforeVar'], $gethtml);
	}
}
