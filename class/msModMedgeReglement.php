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
 * Calculs honoraires,
 * Module Médecine générale
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msModMedgeReglement extends msReglement
{
	private $_mode = 'calcCV';
	private $_patientAgeInMonths;
	private $_patientSexe;
	private $_menuContexte;
	private $_menuSutureContexte;
	private $_menuPlageAge;
	private $_menuSituation;
	private $_menuPeriode;
	private $_selectedContexte;
	private $_selectedSutureContexte;
	private $_selectedPlageAge;
	private $_selectedSituation;
	private $_selectedPeriode;
	private $_tarifFinal;
	private $_actesFaits;
	private $_actesListes = [];
	private $_actesFinaleListe = [];
	private $_messagesInfos = [];
	private $_ik = 0;
	private $_ikHelpText;
	private $_modifsCcamListe;

	private $_itemsContexteMenu = array(
		'cabinet' => ['label' => 'Cabinet', 'visibled' => true, 'disabled' => ''],
		'visite' => ['label' => 'Visite', 'visibled' => true, 'disabled' => '']
	);

	private $_itemsAgeMenu = array(
		'AgeAdulte' => ['label' => 'Patient de 6 ans et plus', 'visibled' => true, 'disabled' => '', 'c' => '', 'v' => ''],
		'AgeEnfant' => ['label' => '0 à 6 ans', 'visibled' => true, 'disabled' => '', 'c' => 'MEG', 'v' => 'MEG']
	);

	private $_itemsSituationAdulteMenu = array(
		"SituationV" => ['label' => 'Visite justifiée', 'visibled' => true, 'disabled' => '', 'c' => '', 'v' => ''],
		"SituationVNJ" => ['label' => 'Visite non justifiée', 'visibled' => true, 'disabled' => '', 'c' => '', 'v' => ''],
		"SituationMU" => ['label' => 'Visite urgente aux heures de consultation au cabinet', 'visibled' => true, 'disabled' => '', 'c' => '', 'v' => 'MU'],
		"SituationVL" => ['label' => 'Visite longue', 'visibled' => true, 'disabled' => '', 'c' => '', 'v' => 'VL'],

		"SituationC" => ['label' => 'Consultation', 'visibled' => true, 'disabled' => '', 'c' => '', 'v' => ''],
		"SituationGR" => ['label' => 'Garde régulée', 'visibled' => true, 'disabled' => '', 'c' => '', 'v' => ''],
		"SituationMCG" => ['label' => 'Patient hors résidence ou avis', 'visibled' => true, 'disabled' => '', 'c' => 'MCG', 'v' => ''],
		"SituationMRT" => ['label' => 'Vu à la demande du 15', 'visibled' => true, 'disabled' => '', 'c' => 'MRT', 'v' => 'MRT'],
		"SituationMUT" => ['label' => 'RDV sous 48h avec spécialiste', 'visibled' => true, 'disabled' => '', 'c' => 'MUT', 'v' => 'MUT'],
		"SituationAPC" => ['label' => 'Avis d\'expert demandé', 'visibled' => true, 'disabled' => '', 'c' => 'APC', 'v' => ''],
		"SituationMSH" => ['label' => 'Majoration suite hospitalisation', 'visibled' => true, 'disabled' => '', 'c' => 'MSH', 'v' => 'MSH'],
		"SituationMIC" => ['label' => 'Majoration insuffisance cardiaque', 'visibled' => true, 'disabled' => '', 'c' => 'MIC', 'v' => 'MIC'],
		"SituationMTX" => ['label' => 'Majoration consultation très complexe', 'visibled' => true, 'disabled' => '', 'c' => 'MTX', 'v' => ''],
	);

	private $_itemsSituationPediaMenu = array(
		"SituationCOE" => ['label' => 'Certificat pédiatrique obligatoire', 'visibled' => true, 'disabled' => '', 'c' => 'COE', 'v' => ''],
		"SituationCCP" => ['label' => '1re consultation contraception (F 15-18 ans)', 'visibled' => true, 'disabled' => '', 'c' => 'CCP', 'v' => ''],
		"SituationCSO" => ['label' => 'Consultation suivi obésité (3-12 ans)', 'visibled' => true, 'disabled' => '', 'c' => '', 'v' => 'CSO'],
	);

	private $_itemsPeriodeMenu = array(
		"PeriodeJ" => ['label' => 'Journée', 'visibled' => true, 'disabled' => ''],
		"PeriodeSa" => ['label' => 'Samedi après-midi', 'visibled' => true, 'disabled' => ''],
		"PeriodeF" => ['label' => 'Dimanche / Férié', 'visibled' => true, 'disabled' => ''],
		"PeriodeS" => ['label' => '20h-0h ou 6h-8h', 'visibled' => true, 'disabled' => ''],
		"PeriodeN" => ['label' => '0h-6h', 'visibled' => true, 'disabled' => ''],
	);

	/**
	 * Définir le mode de détermination des actes
	 * @param string $mode mode
	 */
	public function setMode($mode)
	{
		return $this->_mode = $mode;
	}

	/**
	 * Définir l'age du patient en mois
	 * @param int $ageInMonths âge du patient en mois
	 */
	public function setPatientAgeInMonths($ageInMonths)
	{
		return $this->_patientAgeInMonths = $ageInMonths;
	}

	/**
	 * Définir le sexe du patient
	 * @param string $patientSexe F/M
	 */
	public function setPatientSexe($patientSexe)
	{
		if (!in_array($patientSexe, ['F', 'M'])) {
			throw new Exception('Le sexe défini n\'est pas pris en compte');
		}
		return $this->_patientSexe = $patientSexe;
	}

	/**
	 * Définir la plage d'âges devant s'appliquer
	 * @param string $plageAge code de la plage d'âges
	 */
	public function setPlageAge($plageAge)
	{
		return $this->_menuPlageAge = $this->_selectedPlageAge = $plageAge;
	}

	/**
	 * Définir le contexte
	 * @param string $contexte code contexte
	 */
	public function setContexte($contexte)
	{
		if (!in_array($contexte, ['cabinet', 'visite'])) {
			throw new Exception('Le contexte défini n\'existe pas');
		}
		return $this->_menuContexte = $this->_selectedContexte = $contexte;
	}

	/**
	 * Définir la situation
	 * @param string $situation code situation
	 */
	public function setSituation($situation)
	{
		return $this->_menuSituation = $this->_selectedSituation = $situation;
	}

	/**
	 * Définir la période
	 * @param string $periode code periode
	 */
	public function setPeriode($periode)
	{
		return $this->_menuPeriode = $this->_selectedPeriode = $periode;
	}

	/**
	 * Définir les actes faits (sutures CCAM)
	 * @param array  $ar array des actes de sutures
	 */
	public function setActesFaits($ar)
	{
		return $this->_actesFaits = $ar;
	}

	/**
	 * Définir le nobre d'IK
	 * @param int $ik nombre d'IK
	 */
	public function setIK($ik)
	{
		$this->_ik = $ik;
	}

	/**
	 * Obtenir le texte d'aide sur le IK
	 * @return string texte de conversion IK => km
	 */
	public function getIkHelpText()
	{
		return $this->_ikHelpText;
	}

	/**
	 * Obtenir les messages d'info sur la facturation appliquée
	 * @return string html <li>...</li><li>...
	 */
	public function getMessagesInfos()
	{
		if ($this->_actesFaits['mcLesions'] == 'LM') {
			$this->_messagesInfos[] = "Le contexte \"Localisation multiple\" s'applique (textes officiels) \"pour les actes de chirurgie portant sur des membres différents, sur le tronc et un membre, sur la tête et un membre\".
      Assurez-vous que vos actes rentrent bien dans ce cadre !";
		} elseif ($this->_actesFaits['mcLesions'] == 'LTM') {
			$this->_messagesInfos[] = "Le contexte \"Lésions traumatiques multiples et récentes\" s'applique (textes officiels) \"pour les actes de chirurgie pour lésions traumatiques multiples et récentes\".
      Assurez-vous que vos actes rentrent bien dans ce cadre !";
		}
		if (!empty($this->_messagesInfos)) {
			return '<li>' . implode('</li><li>', $this->_messagesInfos) . '</li>';
		} else {
			return '';
		}
	}

	/**
	 * Ajouter un acte CCAM
	 * @param string $code code acte CCAM
	 */
	public function addActeCcam($code)
	{
		$this->_actesListes[] = $code;
	}

	/**
	 * Obtenir le tarif final
	 * @return float tarif final
	 */
	public function getTarifFinal()
	{
		return $this->_tarifFinal;
	}

	/**
	 * Appliquer les règles automatiques en fonction des paramètres du dossier patient
	 * @return void
	 */
	public function automaticRules()
	{

		// option préférentielle en fonction de l'age
		if ($this->_patientAgeInMonths < 60) {
			$this->_selectedPlageAge = 'AgeEnfant';
		} else {
			$this->_selectedPlageAge = 'AgeAdulte';
			$this->_setItemsDisabled($this->_itemsAgeMenu, ['AgeEnfant']);
		}
		// pré réglage période
		$H = date('H');
		$J = date('N');
		if ($H >= 0 and $H < 6) {
			$this->_selectedPeriode = 'PeriodeN';
		} elseif (($H >= 20 and $H <= 23) or ($H >= 6 and $H <= 7)) {
			$this->_selectedPeriode = 'PeriodeS';
		} elseif ($J == 6 and $H >= 13) {
			$this->_selectedPeriode = 'PeriodeSa';
		} elseif ($J == 7) {
			$this->_selectedPeriode = 'PeriodeF';
		} else {
			$this->_selectedPeriode = 'PeriodeJ';
		}
		//correction spécifique période pour actes sutures en cotation CCAM pure
		if ($this->_mode == 'calcSutures' and $this->_selectedSituation != 'SituationGR') {
			if ($H >= 0 and $H <= 7) {
				$this->_selectedPeriode = 'PeriodeN';
			} elseif ($H >= 20 and $H <= 23) {
				$this->_selectedPeriode = 'PeriodeS';
			}
		}

		if ($this->_selectedContexte == 'cabinet') {
			$this->_selectedSituation = 'SituationC';
		} elseif ($this->_selectedContexte == 'visite') {
			$this->_selectedSituation = 'SituationV';
		}
	}

	/**
	 * Sortir un acte pour ajout direct à la liste finale
	 * @param string $codeActe code de l'acte
	 * @return array tableau data acte
	 */
	private function addActe($codeActe)
	{
		return $this->_getActesDetails([$codeActe]);
	}

	/**
	 * Obtenir les actes pour tableau final
	 * @return array tableau des actes
	 */
	public function getActes()
	{
		global $p;

		if ($this->_mode == 'calcCV') {

			if ($this->_selectedContexte == 'cabinet') {
				$actes = $this->_getActesCabinet();
			} elseif ($this->_selectedContexte == 'visite') {
				$actes = $this->_getActesVisite();

				//ajout majoration ECG en visite
				if (in_array('DEQP003', $this->_actesListes)) $this->addActeCcam('YYYY490');
			}

			//ajout des actes CCAM indépendants
			if (!empty($this->_actesListes)) {
				$actes = array_merge($actes, $this->_actesListes);
			}

			$dataActes = $this->_getActesDetails($actes);
			$this->_actesFinaleListe =  array_merge(array_flip($actes), $dataActes);
		} elseif ($this->_mode == 'calcSutures') {
			$this->_actesFinaleListe = $this->_getActesSutures();

			if ($this->_selectedContexte == 'visite') $this->_actesFinaleListe['ID'] = $this->addActe('ID')['ID'];
		}

		//ik
		if ($this->_selectedContexte == 'visite' and $this->_ik > 0) {
			$this->_addIK();
		} else {
			$this->_ikHelpText = '';
		}

		if (!empty($this->_actesFinaleListe)) {
			$this->_tarifFinal = array_sum(array_column($this->_actesFinaleListe, 'total'));
			return $this->_actesFinaleListe;
		} else {
			return;
		}
	}

	/**
	 * Ajouter les IK à la facturation finale
	 */
	private function _addIK()
	{
		global $p;
		if ($p['config']['administratifSecteurIK'] == 'plaine') {
			$ik = 'IKp';
			$ab = 4;
		} elseif ($p['config']['administratifSecteurIK'] == 'montagne') {
			$ik = 'IKm';
			$ab = 2;
		}
		$ikValue = msSQL::sqlUniqueChamp("SELECT dataYaml from actes_base where code = :ik and codeProf = :secteurTarifaireNgap limit 1", ['ik' => $ik, 'secteurTarifaireNgap' => $this->_secteurTarifaireNgap]);
		$ikValue = msYAML::yamlYamlToArray($ikValue);
		$ikValue = $ikValue['tarifParZone'][$this->_secteurTarifaireGeo];
		$this->_actesFinaleListe['IK'] = array(
			'code' => 'IK',
			'ikNombre' => $this->_ik,
			'type' => 'NGAP',
			'base' => $ikValue,
			'pourcents' => '100',
			'depassement' => '0',
			'label' => 'indemnités kilométriques (' . $p['config']['administratifSecteurIK'] . ')',
			'tarif' => $this->_ik * $ikValue,
			'total' => $this->_ik * $ikValue
		);
		$this->_ikHelpText = 'soit ' . ($this->_ik + $ab) . 'km aller-retour (abat. ' . $ab . 'km)';
	}

	/**
	 * Obtenir les actes dans le mode calcSutures
	 * @return array liste des actes
	 */
	private function _getActesSutures()
	{
		global $p;

		$hono = new msReglement;
		$this->_modifsCcamListe = $hono->getModificateursCcam();

		$lccam = array();
		//plaie sourcil BACA008
		if ($this->_actesFaits['pSourcil'] == 'true') {
			$lccam['BACA008'] = '-';
		}
		//plaie nez GAJA002
		if ($this->_actesFaits['pNez'] == 'true') {
			$lccam['GAJA002'] = '-';
		}
		//plaie lèvre HAJA003 / HAJA006
		if ($this->_actesFaits['pLevre'] == 'true') {
			if ($this->_actesFaits['pLevreTrans'] == 'n') $lccam['HAJA003'] = '-';
			if ($this->_actesFaits['pLevreTrans'] == 'o') $lccam['HAJA006'] = '-';
		}
		//plaie auricule CAJA002
		if ($this->_actesFaits['pAuricule'] == 'true') {
			$lccam['CAJA002'] = '-';
		}
		//plaie superficielle de face QAJA013 / QAJA005 / QAJA002
		if (is_numeric($this->_actesFaits['pFaceSuper']) and $this->_actesFaits['pFaceSuper'] > 0) {
			if ($this->_actesFaits['pFaceSuper'] < 3) $lccam['QAJA013'] = '-';
			elseif ($this->_actesFaits['pFaceSuper'] < 10) $lccam['QAJA005'] = '-';
			elseif ($this->_actesFaits['pFaceSuper'] >= 10) $lccam['QAJA002'] = '-';
		}
		//plaie profonde de face QAJA004 / QAJA006 / QAJA012
		if (is_numeric($this->_actesFaits['pFacePro']) and $this->_actesFaits['pFacePro'] > 0) {
			if ($this->_actesFaits['pFacePro'] < 3) $lccam['QAJA004'] = '-';
			elseif ($this->_actesFaits['pFacePro'] < 10) $lccam['QAJA006'] = '-';
			elseif ($this->_actesFaits['pFacePro'] >= 10) $lccam['QAJA012'] = '-';
		}
		//plaie pulpoungueale QZJA022 / QZJA021
		if ($this->_actesFaits['pPulpoUngu'] == 'true') {
			if ($this->_actesFaits['pPulpoUnguNb'] == 'u') $lccam['QZJA022'] = '-';
			if ($this->_actesFaits['pPulpoUnguNb'] == 'm') $lccam['QZJA021'] = '-';
		}
		//plaie superficielle main QZJA002 / QZJA017 / QZJA015 !!!! idem autres zones mais Modificateur à ajouter
		if (is_numeric($this->_actesFaits['pMainSuper']) and $this->_actesFaits['pMainSuper'] > 0) {
			if ($this->_actesFaits['pMainSuper'] < 3) {
				$lccam['QZJA002'] = 'R';
			} elseif ($this->_actesFaits['pMainSuper'] < 10) {
				$lccam['QZJA017'] = 'R';
			} elseif ($this->_actesFaits['pMainSuper'] >= 10) {
				$lccam['QZJA015'] = 'R';
			}
		}

		//plaie main profonde QCJA001
		if ($this->_actesFaits['pMainPro'] == 'true') {
			$lccam['QCJA001'] = '-';
		}

		//plaie superficielle autres zone QZJA002 / QZJA017 / QZJA015
		if (is_numeric($this->_actesFaits['pAutreSuper']) and $this->_actesFaits['pAutreSuper'] > 0) {
			if ($this->_actesFaits['pAutreSuper'] < 3) $lccam['QZJA002'] = '-';
			elseif ($this->_actesFaits['pAutreSuper'] < 10) $lccam['QZJA017'] = '-';
			elseif ($this->_actesFaits['pAutreSuper'] >= 10) $lccam['QZJA015'] = '-';
		}

		//plaie profonde autres zone QZJA016 / QZJA012 / QZJA001
		if (is_numeric($this->_actesFaits['pAutrePro']) and $this->_actesFaits['pAutrePro'] > 0) {
			if ($this->_actesFaits['pAutrePro'] < 3) $lccam['QZJA016'] = '-';
			elseif ($this->_actesFaits['pAutrePro'] < 10) $lccam['QZJA012'] = '-';
			elseif ($this->_actesFaits['pAutrePro'] >= 10) $lccam['QZJA001'] = '-';
		}

		$dataActes = $this->_getActesDetails(array_keys($lccam), 'orderByTarifs');

		// sélection final des actes
		if (!empty($lccam)) {
			// ajouter les modificateur CCAM déjà déterminés à ce stade
			foreach ($lccam as $acte => $m) {
				if ($m != '-') $dataActes[$acte]['modifsCCAM'] = $dataActes[$acte]['modifsCCAM'] . $m;
			}

			// détermination des règles d'asso
			$this->_reglesAsso = $this->_getCcamRules();
			// conserver uniquement le nombre d'actes nécessaires et les traiter
			$this->_actesFinaleListe = array_slice($dataActes, 0, $this->_reglesAsso['nbActes'], TRUE);
			$i = 0;
			foreach ($this->_actesFinaleListe as $acte => $v) {
				$this->_traiterActeCcam($acte, $i);
				$i++;
			}
		}

		// si situation de visite urgente en journée
		if ($this->_selectedSituation == 'SituationMU') {
			$this->_actesFinaleListe['MU'] = $this->addActe('MU')['MU'];
		}

		return $this->_actesFinaleListe;
	}

	private function _getActesDetails($actes, $orderBy = '')
	{
		$dataActes = [];
		if (!empty($actes)) {
			$sqlImlode = msSQL::sqlGetTagsForWhereIn($actes, 'acte');
			if ($dataActes = msSQL::sql2tabKey("SELECT code, label, dataYaml, type from actes_base where code in (" . $sqlImlode['in'] . ") and type !='mCCAM'", 'code', '', $sqlImlode['execute'])) {
				foreach ($dataActes as $k => $v) {
					$modificateurCCAM = null;
					$dataYaml = msYAML::yamlYamlToArray($v['dataYaml']);
					if ($v['type'] == 'CCAM' and !empty($this->_secteurTarifaire)) {
						$tarif = $dataYaml['tarifParGrilleTarifaire']['CodeGrilleT' . $this->_secteurTarifaire];

						if (isset($dataYaml['modificateursParGrilleTarifaire']) and !empty($dataYaml['modificateursParGrilleTarifaire'])) {
							$modificateurCCAM = $dataYaml['modificateursParGrilleTarifaire']['CodeGrilleT' . $this->_secteurTarifaire];
						}
					} elseif ($v['type'] == 'mCCAM' and !empty($this->_secteurTarifaire)) {
						if ($v['tarifUnit'] == 'euro') {
							$tarif = $dataYaml['tarifParGrilleTarifaire']['CodeGrilleT' . $this->_secteurTarifaire]['forfait'];
						} else {
							$tarif = $dataYaml['tarifParGrilleTarifaire']['CodeGrilleT' . $this->_secteurTarifaire]['coef'];
						}
					} elseif ($v['type'] == 'NGAP') {
						if (isset($dataYaml['tarifParZone'][$this->_secteurTarifaireGeo])) {
							$tarif = $dataYaml['tarifParZone'][$this->_secteurTarifaireGeo];
						} else {
							$tarif = '';
						}
					} elseif ($v['type'] == 'Libre') {
						$tarif = $dataYaml['tarifBase'];
					} else {
						$tarif = '';
					}
					if (is_numeric($tarif)) $tarif = number_format($tarif, 2, '.', '');
					$dataActes[$k] = array(
						'code' => $k,
						'label' => $v['label'],
						'tarif' => $tarif,
						'total' => $tarif,
						'base' => $tarif,
						'pourcents' => 100,
						'depassement' => 0,
						'type' => $v['type'],
						'codeAsso' => '',
						'modifsCCAMpossibles' => $modificateurCCAM,
						'modifsCCAM' => ''
					);
				}
			}
		}
		if (!empty($dataActes) and $orderBy == 'orderByTarifs') {
			msTools::array_unatsort_by('tarif', $dataActes);
			$dataActes = array_reverse($dataActes);
		}
		return $dataActes;
	}


	/**
	 * Obtenir les actes pour consultation au cabinet
	 * @return array tableau actes
	 */
	private function _getActesCabinet()
	{
		//situations simplex : pédia cabinet
		if ($this->_selectedSituation == 'SituationCOE') return ['COE'];
		if ($this->_selectedSituation == 'SituationCCP') return ['CCP'];
		if ($this->_selectedSituation == 'SituationCSO') return ['CSX'];

		//situations simplex : adulte
		if ($this->_selectedSituation == 'SituationAPC') return ['APC'];

		//base
		$actes[] = 'G';

		// 0-6 ans
		if ($this->_selectedPlageAge == 'AgeEnfant') $actes[] = 'MEG';

		// situation
		if (in_array($this->_selectedSituation, array_keys($this->_itemsSituationPediaMenu))) {
			$actes[] = $this->_itemsSituationPediaMenu[$this->_selectedSituation]['c'];
		} else {
			$actes[] = $this->_itemsSituationAdulteMenu[$this->_selectedSituation]['c'];
		}

		// période
		if ($this->_selectedSituation == 'SituationC') {
			if ($this->_selectedPeriode == 'PeriodeSa') $actes[] = 'F';
			if ($this->_selectedPeriode == 'PeriodeF') $actes[] = 'F';
			if ($this->_selectedPeriode == 'PeriodeS') $actes[] = 'MN';
			if ($this->_selectedPeriode == 'PeriodeN') $actes[] = 'MM';
		} elseif ($this->_selectedSituation == 'SituationGR') {
			if ($this->_selectedPeriode == 'PeriodeSa') $actes[] = 'CRS';
			if ($this->_selectedPeriode == 'PeriodeF') $actes[] = 'CRD';
			if ($this->_selectedPeriode == 'PeriodeS') $actes[] = 'CRN';
			if ($this->_selectedPeriode == 'PeriodeN') $actes[] = 'CRM';
		}

		return array_filter($actes);
	}

	/**
	 * Obtenir les actes de consultation en visite
	 * @return array actes en visite
	 */
	private function _getActesVisite()
	{
		//situations simplex
		if ($this->_selectedSituation == 'SituationVL') return ['VL'];

		$actes[] = 'VG';

		// situation
		if (in_array($this->_selectedSituation, array_keys($this->_itemsSituationPediaMenu))) {
			$actes[] = $this->_itemsSituationPediaMenu[$this->_selectedSituation]['v'];
		} else {
			$actes[] = $this->_itemsSituationAdulteMenu[$this->_selectedSituation]['v'];
		}

		// période
		if ($this->_selectedSituation == 'SituationMU') {
			$actes[] = 'MU';
		} elseif ($this->_selectedSituation == 'SituationV') {
			if ($this->_selectedPeriode == 'PeriodeJ') $actes[] = 'MD';
			elseif ($this->_selectedPeriode == 'PeriodeSa') $actes[] = 'MDD';
			elseif ($this->_selectedPeriode == 'PeriodeF') $actes[] = 'MDD';
			elseif ($this->_selectedPeriode == 'PeriodeS') $actes[] = 'MDN';
			elseif ($this->_selectedPeriode == 'PeriodeN') $actes[] = 'MDI';
		} elseif ($this->_selectedSituation == 'SituationGR') {
			if ($this->_selectedPeriode == 'PeriodeJ') $actes[] = 'MD';
			elseif ($this->_selectedPeriode == 'PeriodeSa') $actes[] = 'VRS';
			elseif ($this->_selectedPeriode == 'PeriodeF') $actes[] = 'VRD';
			elseif ($this->_selectedPeriode == 'PeriodeS') $actes[] = 'VRN';
			elseif ($this->_selectedPeriode == 'PeriodeN') $actes[] = 'VRM';
		} elseif ($this->_selectedSituation == 'SituationVNJ') {
			if ($this->_selectedPeriode == 'PeriodeJ') $actes[] = '';
			elseif ($this->_selectedPeriode == 'PeriodeSa') $actes[] = 'F';
			elseif ($this->_selectedPeriode == 'PeriodeF') $actes[] = 'F';
			elseif ($this->_selectedPeriode == 'PeriodeS') $actes[] = 'MN';
			elseif ($this->_selectedPeriode == 'PeriodeN') $actes[] = 'MM';
		}

		// 0-6 ans
		if ($this->_selectedPlageAge == 'AgeEnfant') $actes[] = 'MEG';

		return array_filter($actes);
	}

	/**
	 * Obtenir les items des menus select
	 * @return string html des <option> des menu select
	 */
	public function getOptionsTagsForMenus()
	{
		$this->_applyRules();

		// contexte
		$stringContexte = '';
		foreach ($this->_itemsContexteMenu as $item => $v) {
			if ($v['visibled'] == true) {
				$stringContexte .= '<option value="' . $item . '"';
				if ($this->_selectedContexte == $item) $stringContexte .= ' selected="selected" ';
				if ($v['disabled'] == 'disabled') $stringContexte .= ' disabled="disabled" ';
				$stringContexte .= '>' . $v['label'] . '</option>';
			}
		}

		// age
		$stringAge = '';
		foreach ($this->_itemsAgeMenu as $item => $v) {
			if ($v['visibled'] == true) {
				$stringAge .= '<option value="' . $item . '"';
				if ($this->_selectedPlageAge == $item) $stringAge .= ' selected="selected" ';
				if ($v['disabled'] == 'disabled') $stringAge .= ' disabled="disabled" ';
				$stringAge .= '>' . $v['label'] . '</option>';
			}
		}

		// situation
		$tabSituationAdulte = [];
		foreach ($this->_itemsSituationAdulteMenu as $item => $v) {
			if ($v['visibled'] == true) {
				$stringSituation = '<option value="' . $item . '"';
				if ($this->_selectedSituation == $item) $stringSituation .= ' selected="selected" ';
				if ($v['disabled'] == 'disabled') $stringSituation .= ' disabled="disabled" ';
				$stringSituation .= '>' . $v['label'] . '</option>';
				$tabSituationAdulte[] = $stringSituation;
			}
		}
		$tabSituationPedia = [];
		foreach ($this->_itemsSituationPediaMenu as $item => $v) {
			if ($v['visibled'] == true) {
				$stringSituation = '<option value="' . $item . '"';
				if ($this->_selectedSituation == $item) $stringSituation .= ' selected="selected" ';
				if ($v['disabled'] == 'disabled') $stringSituation .= ' disabled="disabled" ';
				$stringSituation .= '>' . $v['label'] . '</option>';
				$tabSituationPedia[] = $stringSituation;
			}
		}
		if (!empty($tabSituationPedia)) {
			$stringSituation = '<optgroup label="Situations particulières chez enfants et adolescents">';
			$stringSituation .= implode("\n", $tabSituationPedia);
			$stringSituation .= '</optgroup>';
			$stringSituation .= '<optgroup label="Situations générales">';
			$stringSituation .= implode("\n", $tabSituationAdulte);
			$stringSituation .= '</optgroup>';
		} else {
			$stringSituation = implode("\n", $tabSituationAdulte);
		}

		// période
		$stringPeriode = '';
		foreach ($this->_itemsPeriodeMenu as $item => $v) {
			if ($v['visibled'] == true) {
				$stringPeriode .= '<option value="' . $item . '"';
				if ($this->_selectedPeriode == $item) $stringPeriode .= ' selected="selected" ';
				if ($v['disabled'] == 'disabled') $stringPeriode .= ' disabled="disabled" ';
				$stringPeriode .= '>' . $v['label'] . '</option>';
			}
		}

		return array(
			'mcContexte' => $stringContexte,
			'mcAge' => $stringAge,
			'mcSituation' => $stringSituation,
			'mcPeriode' => $stringPeriode,
		);
	}

	/**
	 * Appliquer les règles pour obtenir des menus select adéquats
	 * @return void
	 */
	private function _applyRules()
	{
		if ($this->_mode == 'calcCV') {
			//contexte
			if ($this->_selectedContexte == 'cabinet') {
				$this->_selectedContexte = 'cabinet';

				//situations impossibles
				$situationsImpossibles = ['SituationV', 'SituationVNJ', 'SituationMU', 'SituationVL'];
				$this->_setItemsInvisibled($this->_itemsSituationAdulteMenu, $situationsImpossibles);
				if (in_array($this->_selectedSituation, $situationsImpossibles)) {
					$this->_selectedSituation = 'SituationC';
				}
			} elseif ($this->_selectedContexte == 'visite') {

				$this->_selectedContexte = 'visite';

				//situations impossibles
				$situationsImpossibles = ['SituationC', 'SituationMCG', 'SituationAPC', 'SituationMTX', 'SituationCCP', 'SituationCOE', 'SituationCCP', 'SituationCSO'];
				$this->_setItemsInvisibled($this->_itemsSituationAdulteMenu, $situationsImpossibles);
				$this->_setItemsInvisibled($this->_itemsSituationPediaMenu, $situationsImpossibles);
				if (in_array($this->_selectedSituation, $situationsImpossibles)) {
					$this->_selectedSituation = 'SituationV';
				}
			}
			// retrait plageAge 0-6 ans si age > 6
			if ($this->_patientAgeInMonths >= 72) {
				$this->_setItemsInvisibled($this->_itemsAgeMenu, ['AgeEnfant']);
			}

			// certif pédia : désactivation si age > 25 mois
			if ($this->_patientAgeInMonths > 25) {
				$this->_setItemsInvisibled($this->_itemsSituationPediaMenu, ['SituationCOE']);
			}
			// consult contraception : désactivation si pas une fille entre 15 et 18 ans
			if ($this->_patientAgeInMonths < 180 or $this->_patientAgeInMonths >= 228 or $this->_patientSexe == 'M') {
				$this->_setItemsInvisibled($this->_itemsSituationPediaMenu, ['SituationCCP']);
			}

			// consult obésité : désactivation si pas entre 3 et 12 ans
			if ($this->_patientAgeInMonths < 36 or $this->_patientAgeInMonths >= 156) {
				$this->_setItemsInvisibled($this->_itemsSituationPediaMenu, ['SituationCSO']);
			}

			// correction pour certif pédia obligatoire
			if ($this->_selectedSituation == 'SituationCOE') {
				$this->_selectedContexte = 'cabinet';
				$this->_selectedPeriode = 'PeriodeJ';
			}

			// correction de période si situations incompatibles
			if (in_array($this->_selectedSituation, ['SituationMCG', 'SituationMRT', 'SituationMUT', 'SituationAPC', 'SituationMSH', 'SituationMIC', 'SituationMTX', 'SituationCOE', 'SituationCCP', 'SituationCSO', 'SituationVL'])) {
				$this->_selectedPeriode = 'PeriodeJ';
			}
		} elseif ($this->_mode == 'calcSutures') {
			// ajuster les plages horaires suivant qu'on parle en NGAP (garde reg) ou CCAM (autre)
			if ($this->_selectedSituation == "SituationGR") {
				$this->_itemsPeriodeMenu['PeriodeS']['label'] = '20h-0h ou 6h-8h';
				$this->_itemsPeriodeMenu['PeriodeN']['label'] = '0h-6h';
			} else {
				$this->_itemsPeriodeMenu['PeriodeS']['label'] = '20h-0h';
				$this->_itemsPeriodeMenu['PeriodeN']['label'] = '0h-8h';
			}

			//situations
			foreach ($this->_itemsSituationAdulteMenu as $kitem => $item) {
				if (in_array($kitem, ['SituationMU', 'SituationGR', 'SituationC', 'SituationV'])) {
					$this->_itemsSituationAdulteMenu[$kitem]['visibled'] = true;
				} else {
					$this->_itemsSituationAdulteMenu[$kitem]['visibled'] = false;
				}
			}
			foreach ($this->_itemsSituationPediaMenu as $kitem => $item) {
				$this->_itemsSituationPediaMenu[$kitem]['visibled'] = false;
			}

			//contexte
			if ($this->_selectedContexte == 'cabinet') {
				//situations impossibles
				$situationsImpossibles = ['SituationV', 'SituationMU'];
				$this->_setItemsInvisibled($this->_itemsSituationAdulteMenu, $situationsImpossibles);
				if (in_array($this->_selectedSituation, $situationsImpossibles)) {
					$this->_selectedSituation = 'SituationC';
				}
			} elseif ($this->_selectedContexte == 'visite') {
				//situations impossibles
				$situationsImpossibles = ['SituationC'];
				$this->_setItemsInvisibled($this->_itemsSituationAdulteMenu, $situationsImpossibles);
				if (in_array($this->_selectedSituation, $situationsImpossibles)) {
					$this->_selectedSituation = 'SituationV';
				}
			}

			// periode
			if ($this->_selectedPeriode != 'PeriodeJ') {
				$this->_setItemsInvisibled($this->_itemsSituationAdulteMenu, ['SituationMU']);
			}
		}
	}

	/**
	 * Rendre item(s) d'un menu select invisible
	 * @param array $tab tableau des items du menu
	 * @param array  $items tableau des items à enlever
	 */
	private function _setItemsInvisibled(&$tab, $items = [])
	{
		foreach ($items as $item) {
			$tab[$item]['visibled'] = false;
		}
	}

	/**
	 * Rendre disabled item(s) d'un menu select
	 * @param array $tab   tableau des items du menu
	 * @param array  $items items concernés à rendre disabled
	 */
	private function _setItemsDisabled(&$tab, $items = [])
	{
		foreach ($items as $item) {
			$tab[$item]['disabled'] = 'disabled';
		}
	}

	/**
	 * Réinitialiser les valeurs d'un tableau d'items de menu select
	 * @param array $tab tableau du menu concerné
	 */
	private function _setTabToInitalState(&$tab)
	{
		foreach ($tab as $item => $v) {
			$tab[$item]['visibled'] = true;
			$tab[$item]['disabled'] = '';
		}
	}

	/**
	 * Obtenir les règles d'association d'actes CCAM en fonction du contexte de sutures
	 * @return array règles
	 */
	private function _getCcamRules()
	{
		if ($this->_actesFaits['mcLesions'] == 'LM') {
			$regles = array(
				'nbActes' => 2,
				'codes' => array('1', '3'),
				'pourcents' => array('100', '75')
			);
		} elseif ($this->_actesFaits['mcLesions'] == 'LTM') {
			$regles = array(
				'nbActes' => 3,
				'codes' => array('1', '3', '2'),
				'pourcents' => array('100', '75', '50')
			);
		} else {
			$regles = array(
				'nbActes' => 2,
				'codes' => array('1', '2'),
				'pourcents' => array('100', '50')
			);
		}
		return $regles;
	}

	/**
	 * Traiter un acte CCAM en fonction de son rang de facturation et du contexte
	 * @param  string $acte       code acte
	 * @param  int $rang       rang de l'acte
	 * @return void
	 */
	private function _traiterActeCcam($acte, $rang)
	{
		$mCCAM = array();

		//si premier acte,
		if ($rang == 0) {
			//on passe les résidus NGAP si garde régulée
			if ($this->_selectedSituation == 'SituationGR') {
				if ($this->_selectedPeriode == 'PeriodeF' and $this->_selectedContexte == 'cabinet') {
					$addNGAP = 'CRD';
				} elseif ($this->_selectedPeriode == 'PeriodeSa' and $this->_selectedContexte == 'cabinet') {
					$addNGAP = 'CRS';
				} elseif ($this->_selectedPeriode == 'PeriodeS' and $this->_selectedContexte == 'cabinet') {
					$addNGAP = 'CRN';
				} elseif ($this->_selectedPeriode == 'PeriodeN' and $this->_selectedContexte == 'cabinet') {
					$addNGAP = 'CRM';
				} elseif ($this->_selectedPeriode == 'PeriodeSa' and $this->_selectedContexte == 'visite') {
					$addNGAP = 'VRS';
				} elseif ($this->_selectedPeriode == 'PeriodeF' and $this->_selectedContexte == 'visite') {
					$addNGAP = 'VRD';
				} elseif ($this->_selectedPeriode == 'PeriodeS' and $this->_selectedContexte == 'visite') {
					$addNGAP = 'VRN';
				} elseif ($this->_selectedPeriode == 'PeriodeN' and $this->_selectedContexte == 'visite') {
					$addNGAP = 'VRM';
				} else {
					$addNGAP = NULL;
				}
				if ($addNGAP) {
					$this->_actesFinaleListe[$addNGAP] = $this->addActe($addNGAP)[$addNGAP];
				}

				//sinon on passe les modifs CCAM si ils sont à true
			} else {
				if ($this->_selectedPeriode == 'PeriodeF' and in_array('F', $this->_actesFinaleListe[$acte]['modifsCCAMpossibles'])) {
					$mCCAM[] = 'F';
				} elseif ($this->_selectedPeriode == 'PeriodeSa' and in_array('F', $this->_actesFinaleListe[$acte]['modifsCCAMpossibles'])) {
					$mCCAM[] = 'F';
				} elseif ($this->_selectedPeriode == 'PeriodeS' and in_array('P', $this->_actesFinaleListe[$acte]['modifsCCAMpossibles'])) {
					$mCCAM[] = 'P';
				} elseif ($this->_selectedPeriode == 'PeriodeN' and in_array('S', $this->_actesFinaleListe[$acte]['modifsCCAMpossibles'])) {
					$mCCAM[] = 'S';
				} else {
					$addNGAP = NULL;
				}
			}
		}

		//ajout du M si besoin et si possible
		if ($this->_selectedContexte == 'cabinet' and in_array('M', $this->_actesFinaleListe[$acte]['modifsCCAMpossibles']) and $this->_selectedSituation != 'SituationMU') $mCCAM[] = 'M';

		// ajout modificateurs collectés
		if (!empty($mCCAM)) {
			$this->_actesFinaleListe[$acte]['modifsCCAM'] = $this->_actesFinaleListe[$acte]['modifsCCAM'] . implode('', $mCCAM);
		}

		// application des règles association
		if (count($this->_actesFinaleListe) > 1) $this->_actesFinaleListe[$acte]['codeAsso'] = $this->_reglesAsso['codes'][$rang];
		$this->_actesFinaleListe[$acte]['pourcents'] = $this->_reglesAsso['pourcents'][$rang];

		/////ajustements induits du tarif :

		//application des modificateur en %
		if (strlen($this->_actesFinaleListe[$acte]['modifsCCAM']) > 0) {
			foreach (str_split($this->_actesFinaleListe[$acte]['modifsCCAM']) as $mo) {
				if ($this->_modifsCcamListe[$mo]['tarifUnit'] == 'pourcent') {
					$this->_actesFinaleListe[$acte]['tarif'] = $this->_actesFinaleListe[$acte]['tarif'] + $this->_addValueModifCcam($mo, $this->_actesFinaleListe[$acte]['base']);
				}
			}
		}

		//application du % attribué par le code asso
		$this->_actesFinaleListe[$acte]['tarif'] = round(($this->_actesFinaleListe[$acte]['tarif'] * $this->_reglesAsso['pourcents'][$rang] / 100), 2);

		//application des modificateur en euro
		if (strlen($this->_actesFinaleListe[$acte]['modifsCCAM']) > 0) {
			foreach (str_split($this->_actesFinaleListe[$acte]['modifsCCAM']) as $mo) {
				if ($this->_modifsCcamListe[$mo]['tarifUnit'] == 'euro') {
					$this->_actesFinaleListe[$acte]['tarif'] = $this->_actesFinaleListe[$acte]['tarif'] + $this->_addValueModifCcam($mo, $this->_actesFinaleListe[$acte]['tarif']);
				}
			}
		}

		//tarif total ligne
		$this->_actesFinaleListe[$acte]['tarif'] = round($this->_actesFinaleListe[$acte]['tarif'], 2);
		$this->_actesFinaleListe[$acte]['total'] = $this->_actesFinaleListe[$acte]['tarif'] + $this->_actesFinaleListe[$acte]['depassement'];
	}

	/**
	 * Calculer le montant apporté par un modificateur CCAM sur un acte
	 * @param string $m     modificateur
	 * @param float $value tarif de base de l'acte (si modificateur en %)
	 */
	private function _addValueModifCcam($m, $value)
	{
		if (!isset($this->_modifsCcamListe)) {
			// liste et propriétés  des modificateurs CCAM
			$hono = new msReglement;
			$this->_modifsCcamListe = $hono->getModificateursCcam();
		}

		if ($this->_modifsCcamListe[$m]['tarifUnit'] == 'pourcent') {
			$rv = round(($this->_modifsCcamListe[$m]['dataYaml']['tarifParGrilleTarifaire']['CodeGrilleT' . $this->_secteurTarifaire]['coef'] * $value / 100), 2);
		} elseif ($this->_modifsCcamListe[$m]['tarifUnit'] == 'euro') {
			$rv = $this->_modifsCcamListe[$m]['dataYaml']['tarifParGrilleTarifaire']['CodeGrilleT' . $this->_secteurTarifaire]['forfait'];
		}
		return $rv;
	}
}
