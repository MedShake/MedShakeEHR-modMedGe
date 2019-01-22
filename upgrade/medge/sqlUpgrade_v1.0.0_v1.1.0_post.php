<?php
@unlink($p['homepath'].'class/msModMedgeCalcHonoraires.php');

// retrait des anciens fichiers JS formulaire
@unlink($p['config']['webDirectory'].'/js/module/formsScripts/medGeIssueGrossesse.js');
