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
 * Gérer la preview des éléments de l'historique patient - module medge
 *
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 */

class msModMedgeObjetPreview extends msModBaseObjetPreview
{

	/**
	 * Obtenir la preview d'une Vaccination
	 * @return string HTML
	 */
	public function getPreviewMedGeCsVaccination()
	{
		global $p;

		$data = new msObjet();
		$data->setObjetID($this->_objetID);
		$p['page']['dataVac'] = $data->getObjetAndSons('name');

		$html = new msGetHtml;
		$html->set_template('inc-ajax-medGeCsVaccination.html.twig');
		$html = $html->genererHtmlVar($p);
		return $html;
	}
}
