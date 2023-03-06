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
 * Générer le SQL pour export du module Medge
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


class msModMedgeSqlGenerate extends msSqlGenerate
{

	/**
	 * Obtenir les codes NGAP / CCAM nécessaires pour générer le dump SQL du module
	 * @return array array des codes
	 */
	protected function _getActesModuleSqlExtraction()
	{
		$listeCCAM = ['BACA008', 'GAJA002', 'HAJA003', 'HAJA006', 'CAJA002',  'QAJA013', 'QAJA005', 'QAJA002', 'QAJA004', 'QAJA006', 'QAJA012', 'QZJA022', 'QZJA021', 'QZJA002', 'QZJA017', 'QZJA015', 'QCJA001', 'QZJA002', 'QZJA017', 'QZJA015', 'QZJA016', 'QZJA012', 'QZJA001', 'DEQP003', 'JKHD001', 'YYYY490'];

		$listeNGAPmCCAM = msSQL::sql2tabSimple("select code from $this->_bdd.actes_base where type in ('NGAP', 'mCCAM')");

		return array_unique(array_merge($listeCCAM, $listeNGAPmCCAM));
	}
}
