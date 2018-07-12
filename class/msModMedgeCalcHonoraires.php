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

class msModMedgeCalcHonoraires extends msReglement
{

  private $_patientAgeInMonths;
  private $_patientSexe;
  private $_menuContexte;
  private $_menuPlageAge;
  private $_menuSituation;
  private $_menuPeriode;
  private $_selectedContexte;
  private $_selectedPlageAge;
  private $_selectedSituation;
  private $_selectedPeriode;
  private $_tarifFinal;
  private $_actesListes = [];
  private $_ik=0;
  private $_ikHelpText;

  private $_itemsContexteMenu=array(
    'cabinet'=>['label'=>'Cabinet', 'visibled'=>true,'disabled'=>''],
    'visite'=>['label'=>'Visite', 'visibled'=>true, 'disabled'=>'']
  );

  private $_itemsAgeMenu=array(
    'AgeAdulte'=>['label'=>'Patient de 6 ans et plus', 'visibled'=>true,'disabled'=>'', 'c'=>'', 'v'=>''],
    'AgeEnfant'=>['label'=>'0 à 6 ans', 'visibled'=>true, 'disabled'=>'', 'c'=>'MEG', 'v'=>'MEG']
  );

  private $_itemsSituationAdulteMenu=array(
    "SituationV"=>['label'=>'Visite justifiée', 'visibled'=>true,'disabled'=>'', 'c'=>'', 'v'=>''],
    "SituationVNJ"=>['label'=>'Visite non justifiée', 'visibled'=>true,'disabled'=>'', 'c'=>'', 'v'=>''],
    "SituationMU"=>['label'=>'Visite urgente aux heures de consultation au cabinet', 'visibled'=>true,'disabled'=>'', 'c'=>'', 'v'=>'MU'],
    "SituationVL"=>['label'=>'Visite longue', 'visibled'=>true,'disabled'=>'', 'c'=>'', 'v'=>'VL'],

    "SituationC"=>['label'=>'Consultation', 'visibled'=>true,'disabled'=>'', 'c'=>'', 'v'=>''],
    "SituationGR"=>['label'=>'Garde régulée', 'visibled'=>true,'disabled'=>'', 'c'=>'', 'v'=>''],
    "SituationMCG"=>['label'=>'Patient hors résidence ou avis', 'visibled'=>true,'disabled'=>'', 'c'=>'MCG', 'v'=>''],
    "SituationMRT"=>['label'=>'Vu à la demande du 15', 'visibled'=>true,'disabled'=>'', 'c'=>'MRT', 'v'=>'MRT'],
    "SituationMUT"=>['label'=>'RDV sous 48h avec spécialiste', 'visibled'=>true,'disabled'=>'', 'c'=>'MUT', 'v'=>'MUT'],
    "SituationAPC"=>['label'=>'Avis d\'expert demandé', 'visibled'=>true,'disabled'=>'', 'c'=>'APC', 'v'=>''],
    "SituationMSH"=>['label'=>'Majoration suite hospitalisation', 'visibled'=>true,'disabled'=>'', 'c'=>'MSH', 'v'=>'MSH'],
    "SituationMIC"=>['label'=>'Majoration insuffisance cardiaque', 'visibled'=>true,'disabled'=>'', 'c'=>'MIC', 'v'=>'MIC'],
    "SituationMTX"=>['label'=>'Majoration consultation très complexe', 'visibled'=>true, 'disabled'=>'', 'c'=>'MTX', 'v'=>''],
  );

  private $_itemsSituationPediaMenu=array(
    "SituationCOE"=>['label'=>'Certificat pédiatrique obligatoire', 'visibled'=>true, 'disabled'=>'', 'c'=>'COE', 'v'=>''],
    "SituationCCP"=>['label'=>'1re consultation contraception (F 15-18 ans)', 'visibled'=>true, 'disabled'=>'', 'c'=>'CCP', 'v'=>''],
    "SituationCSO"=>['label'=>'Consultation suivi obésité (3-12 ans)', 'visibled'=>true,'disabled'=>'', 'c'=>'', 'v'=>'CSO'],
  );

  private $_itemsPeriodeMenu=array(
    "PeriodeJ"=>['label'=>'Journée', 'visibled'=>true, 'disabled'=>''],
    "PeriodeF"=>['label'=>'Samedi AM / Dimanche / Férié', 'visibled'=>true, 'disabled'=>''],
    "PeriodeS"=>['label'=>'20h-0h ou 6h-8h', 'visibled'=>true, 'disabled'=>''],
    "PeriodeN"=>['label'=>'0h-6h', 'visibled'=>true, 'disabled'=>''],
  );

/**
 * Définir l'age du patient en mois
 * @param int $ageInMonths âge du patient en mois
 */
  public function setPatientAgeInMonths($ageInMonths) {
    return $this->_patientAgeInMonths=$ageInMonths;
  }

/**
 * Définir le sexe du patient
 * @param string $patientSexe F/M
 */
  public function setPatientSexe($patientSexe) {
    if (!in_array($patientSexe, ['F', 'M'])) {
        throw new Exception('Le sexe défini n\'est pas pris en compte');
    }
    return $this->_patientSexe=$patientSexe;
  }

/**
 * Définir la plage d'âges devant s'appliquer
 * @param string $plageAge code de la plage d'âges
 */
  public function setPlageAge($plageAge) {
    return $this->_menuPlageAge=$this->_selectedPlageAge=$plageAge;
  }

/**
 * Définir le contexte
 * @param string $contexte code contexte
 */
  public function setContexte($contexte) {
    if (!in_array($contexte, ['cabinet', 'visite', 'ccam', 'sutures'])) {
        throw new Exception('Le contexte défini n\'existe pas');
    }
    return $this->_menuContexte = $this->_selectedContexte = $contexte;
  }

/**
 * Définir la situation
 * @param string $situation code situation
 */
  public function setSituation($situation) {
    return $this->_menuSituation=$this->_selectedSituation=$situation;
  }

/**
 * Définir la période
 * @param string $periode code periode
 */
  public function setPeriode($periode) {
    return $this->_menuPeriode=$this->_selectedPeriode=$periode;
  }

/**
 * Obtenir le tarif final
 * @return float tarif final
 */
  public function getTarifFinal() {
    return $this->_tarifFinal;
  }

/**
 * Appliquer les règles automatiques
 * @return void
 */
  public function automaticRules() {

    // option préférentielle en fonction de l'age
    if($this->_patientAgeInMonths < 60 ) {
      $this->_selectedPlageAge = 'AgeEnfant';
    } else {
      $this->_selectedPlageAge = 'AgeAdulte';
      $this->_setItemsDisabled($this->_itemsAgeMenu, ['AgeEnfant']);
    }
    // pré réglage période
    $H=date('H');
    $J=date('N');
    if($H>=0 and $H<6) {
      $this->_selectedPeriode = 'PeriodeN';
    } elseif(($H>=20 and $H<=23) or ($H>=6 and $H<=7)) {
      $this->_selectedPeriode = 'PeriodeS';
    } elseif(($J==6 and $H>=13) or $J==7 ) {
      $this->_selectedPeriode = 'PeriodeF';
    } else {
      $this->_selectedPeriode = 'PeriodeJ';
    }
    if($this->_selectedContexte == 'cabinet') {
      $this->_selectedSituation='SituationC';
    } elseif($this->_selectedContexte == 'visite') {
      $this->_selectedSituation='SituationV';
    }
  }

  private function _applyRules() {

    //contexte
    if($this->_selectedContexte == 'cabinet') {
      $this->_selectedContexte = 'cabinet';

      //situations impossibles
      $situationsImpossibles=['SituationV', 'SituationVNJ', 'SituationMU', 'SituationVL'];
      $this->_setItemsInvisibled($this->_itemsSituationAdulteMenu, $situationsImpossibles);
      if(in_array($this->_selectedSituation, $situationsImpossibles)) {
        $this->_selectedSituation = 'SituationC';
      }

    } elseif($this->_selectedContexte == 'visite') {

      $this->_selectedContexte = 'visite';

      //situations impossibles
      $situationsImpossibles=['SituationC', 'SituationMCG', 'SituationAPC', 'SituationMTX', 'SituationCCP','SituationCOE', 'SituationCCP','SituationCSO'];
      $this->_setItemsInvisibled($this->_itemsSituationAdulteMenu, $situationsImpossibles);
      $this->_setItemsInvisibled($this->_itemsSituationPediaMenu, $situationsImpossibles);
      if(in_array($this->_selectedSituation, $situationsImpossibles)) {
        $this->_selectedSituation = 'SituationV';
      }
    }
    // retrait plageAge 0-6 ans si age > 6
    if($this->_patientAgeInMonths >= 72 ) {
      $this->_setItemsInvisibled($this->_itemsAgeMenu, ['AgeEnfant']);
    }

    // certif pédia : désactivation si age > 25 mois
    if($this->_patientAgeInMonths > 25 ) {
      $this->_setItemsInvisibled($this->_itemsSituationPediaMenu, ['SituationCOE']);
    }
    // consult contraception : désactivation si pas une fille entre 15 et 18 ans
    if($this->_patientAgeInMonths < 180 or $this->_patientAgeInMonths >= 228 or $this->_patientSexe=='M' ) {
      $this->_setItemsInvisibled($this->_itemsSituationPediaMenu, ['SituationCCP']);
    }

    // consult obésité : désactivation si pas entre 3 et 12 ans
    if($this->_patientAgeInMonths < 36 or $this->_patientAgeInMonths >= 156) {
      $this->_setItemsInvisibled($this->_itemsSituationPediaMenu, ['SituationCSO']);
    }

    // correction pour certif pédia obligatoire
    if($this->_selectedSituation == 'SituationCOE') {
      $this->_selectedContexte = 'cabinet';
      $this->_selectedPeriode = 'PeriodeJ';
    }

    // correction de période si situations incompatibles
    if(in_array($this->_selectedSituation, ['SituationMCG', 'SituationMRT', 'SituationMUT', 'SituationAPC', 'SituationMSH', 'SituationMIC', 'SituationMTX', 'SituationCOE', 'SituationCCP', 'SituationCSO', 'SituationVL' ])) {
      $this->_selectedPeriode ='PeriodeJ';

    }

  }

  public function setIK($ik) {
    $this->_ik=$ik;
  }

  public function getIkHelpText() {
    return $this->_ikHelpText;
  }

  public function addActeCcam($code) {
    $this->_actesListes[]=$code;
  }

  public function getActes() {
    global $p;

    if($this->_selectedContexte == 'cabinet') {
      $actes = $this->_getActesCabinet();
    }
    elseif($this->_selectedContexte == 'visite') {
      $actes = $this->_getActesVisite();

      //ajout majoration ECG en visite
      if(in_array('DEQP003', $this->_actesListes)) $this->addActeCcam('YYYY490');
    }
    //ajout des actes CCAM indépendants
    if(!empty($this->_actesListes)) {
      $actes = array_merge($actes, $this->_actesListes);
    }

    if(!empty($actes)) {
      if($p['config']['administratifSecteurHonoraires']=='1') $tarif='tarifs1'; else $tarif='tarifs2';
      $dataActes=msSQL::sql2tabKey("select code, label,".$tarif." as tarif,".$tarif." as total,".$tarif." as base, '100' as pourcents, '0' as depassement, type, '' as codeAsso, '' as modifsCCAM from actes_base where code in ('".implode("','", $actes)."')", 'code');
    }

    //ik
    if($this->_selectedContexte == 'visite' and $this->_ik > 0) {
      if($p['config']['administratifSecteurIK'] == 'plaine') {
        $ik = 'IKp';
        $ab = 4;
      } elseif($p['config']['administratifSecteurIK'] == 'montagne') {
        $ik = 'IKm';
        $ab =2;
      }
      $ikValue=msSQL::sqlUniqueChamp("select ".$tarif." from actes_base where code = '".$ik."' limit 1");
      $dataActes['IK']=array(
        'code'=>'IK',
        'ikNombre'=>$this->_ik,
        'type'=>'NGAP',
        'base'=>$ikValue,
        'pourcents'=>'100',
        'depassement'=>'0',
        'label'=>'indemnités kilométriques ('.$p['config']['administratifSecteurIK'].')',
        'tarif'=> $this->_ik * $ikValue,
        'total'=> $this->_ik * $ikValue
      );
      $this->_ikHelpText='soit '.($this->_ik + $ab).'km aller-retour (abat. '.$ab.'km)';
    } else {
      $this->_ikHelpText='';
    }

    $this->_tarifFinal=array_sum(array_column($dataActes, 'total'));

    return array_merge(array_flip($actes), $dataActes);

  }

  private function _getActesCabinet() {
    //situations simplex : pédia cabinet
    if($this->_selectedSituation == 'SituationCOE') return ['COE'];
    if($this->_selectedSituation == 'SituationCCP') return ['CCP'];
    if($this->_selectedSituation == 'SituationCSO') return ['CSX'];

    //situations simplex : adulte
    if($this->_selectedSituation == 'SituationAPC') return ['APC'];

    //base
    $actes[]='G';

    // 0-6 ans
    if($this->_selectedPlageAge == 'AgeEnfant') $actes[]='MEG';

    // situation
    if(in_array($this->_selectedSituation, array_keys($this->_itemsSituationPediaMenu))) {
      $actes[]=$this->_itemsSituationPediaMenu[$this->_selectedSituation]['c'];
    } else {
      $actes[]=$this->_itemsSituationAdulteMenu[$this->_selectedSituation]['c'];
    }

    // période
    if($this->_selectedSituation == 'SituationC') {
      if($this->_selectedPeriode == 'PeriodeF') $actes[]='F';
      if($this->_selectedPeriode == 'PeriodeS') $actes[]='MN';
      if($this->_selectedPeriode == 'PeriodeN') $actes[]='MM';
    } elseif($this->_selectedSituation == 'SituationGR') {
      if($this->_selectedPeriode == 'PeriodeF') $actes[]='CRD';
      if($this->_selectedPeriode == 'PeriodeS') $actes[]='CRN';
      if($this->_selectedPeriode == 'PeriodeN') $actes[]='CRM';
    }

    return array_filter($actes);
  }

  private function _getActesVisite() {
    //situations simplex
    if($this->_selectedSituation == 'SituationVL') return ['VL'];

    $actes[]='VG';

    // situation
    if(in_array($this->_selectedSituation, array_keys($this->_itemsSituationPediaMenu))) {
      $actes[]=$this->_itemsSituationPediaMenu[$this->_selectedSituation]['v'];
    } else {
      $actes[]=$this->_itemsSituationAdulteMenu[$this->_selectedSituation]['v'];
    }

    // période
    if($this->_selectedSituation == 'SituationMU') {
      $actes[]='MU';
    } elseif($this->_selectedSituation == 'SituationV') {
      if($this->_selectedPeriode == 'PeriodeJ') $actes[]='MD';
      elseif($this->_selectedPeriode == 'PeriodeF') $actes[]='MDD';
      elseif($this->_selectedPeriode == 'PeriodeS') $actes[]='MDN';
      elseif($this->_selectedPeriode == 'PeriodeN') $actes[]='MDI';
    } elseif($this->_selectedSituation == 'SituationGR') {
      if($this->_selectedPeriode == 'PeriodeJ') $actes[]='MD';
      elseif($this->_selectedPeriode == 'PeriodeF') $actes[]='VRD';
      elseif($this->_selectedPeriode == 'PeriodeS') $actes[]='VRN';
      elseif($this->_selectedPeriode == 'PeriodeN') $actes[]='VRM';
    } elseif($this->_selectedSituation == 'SituationVNJ') {
      if($this->_selectedPeriode == 'PeriodeJ') $actes[]='';
      elseif($this->_selectedPeriode == 'PeriodeF') $actes[]='F';
      elseif($this->_selectedPeriode == 'PeriodeS') $actes[]='MN';
      elseif($this->_selectedPeriode == 'PeriodeN') $actes[]='MM';
    }

    // 0-6 ans
    if($this->_selectedPlageAge == 'AgeEnfant') $actes[]='MEG';

    return array_filter($actes);
  }

  public function getOptionsTagsForMenus() {

    $this->_applyRules();

    // contexte
    $stringContexte='';
    foreach($this->_itemsContexteMenu as $item=>$v) {
      if($v['visibled']==true) {
        $stringContexte.='<option value="'.$item.'"';
        if($this->_selectedContexte == $item) $stringContexte.=' selected="selected" ';
        if($v['disabled']=='disabled') $stringContexte.=' disabled="disabled" ';
        $stringContexte.= '>'.$v['label'].'</option>';
      }
    }

    // age
    $stringAge='';
    foreach($this->_itemsAgeMenu as $item=>$v) {
      if($v['visibled']==true) {
        $stringAge.='<option value="'.$item.'"';
        if($this->_selectedPlageAge == $item) $stringAge.=' selected="selected" ';
        if($v['disabled']=='disabled') $stringAge.=' disabled="disabled" ';
        $stringAge.= '>'.$v['label'].'</option>';
      }
    }

    // situation
    $tabSituationAdulte=[];
    foreach($this->_itemsSituationAdulteMenu as $item=>$v) {
      if($v['visibled']==true) {
        $stringSituation='<option value="'.$item.'"';
        if($this->_selectedSituation == $item) $stringSituation.=' selected="selected" ';
        if($v['disabled']=='disabled') $stringSituation.=' disabled="disabled" ';
        $stringSituation.= '>'.$v['label'].'</option>';
        $tabSituationAdulte[]=$stringSituation;
      }
    }
    $tabSituationPedia=[];
    foreach($this->_itemsSituationPediaMenu as $item=>$v) {
      if($v['visibled']==true) {
        $stringSituation='<option value="'.$item.'"';
        if($this->_selectedSituation == $item) $stringSituation.=' selected="selected" ';
        if($v['disabled']=='disabled') $stringSituation.=' disabled="disabled" ';
        $stringSituation.= '>'.$v['label'].'</option>';
        $tabSituationPedia[]=$stringSituation;
      }
    }
    if(!empty($tabSituationPedia)) {
      $stringSituation='<optgroup label="Situations particulières chez enfants et adolescents">';
      $stringSituation.= implode("\n",$tabSituationPedia);
      $stringSituation.='</optgroup>';
      $stringSituation.='<optgroup label="Situations générales">';
      $stringSituation.= implode("\n",$tabSituationAdulte);
      $stringSituation.='</optgroup>';
    } else {
      $stringSituation= implode("\n",$tabSituationAdulte);
    }

    // période
    $stringPeriode='';
    foreach($this->_itemsPeriodeMenu as $item=>$v) {
      if($v['visibled']==true) {
        $stringPeriode.='<option value="'.$item.'"';
        if($this->_selectedPeriode == $item) $stringPeriode.=' selected="selected" ';
        if($v['disabled']=='disabled') $stringPeriode.=' disabled="disabled" ';
        $stringPeriode.= '>'.$v['label'].'</option>';
      }
    }

    return array(
      'mcContexte'=>$stringContexte,
      'mcAge'=>$stringAge,
      'mcSituation'=>$stringSituation,
      'mcPeriode'=>$stringPeriode,
    );
  }

  private function _setItemsInvisibled(&$tab, $items=[]) {
    foreach($items as $item) {
        $tab[$item]['visibled']=false;
    }
  }

  private function _setItemsDisabled(&$tab, $items=[]) {
    foreach($items as $item) {
        $tab[$item]['disabled']='disabled';
    }
  }


  private function _setTabToInitalState(&$tab) {
    foreach($tab as $item=>$v) {
      $tab[$item]['visibled']=true;
      $tab[$item]['disabled']='';
    }
  }

}
