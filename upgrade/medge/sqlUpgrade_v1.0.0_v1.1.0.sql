INSERT INTO `configuration` ( `name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ( 'administratifReglementFormulaires', 'module', '0', 'medge', 'Règlements', 'liste', '', 'medGeReglePorteurCalculateur');

update `data_types` set label='Règlement', description='Règlement assisté S1' WHERE `name` = 'medGeReglePorteurCalculateur';
update `data_types` set label='Règlement', description='Règlement conventionné S2' WHERE `name` = 'medGeReglePorteurS2';

-- mise en base du JS des forms 
update `forms` set `javascript`= '//echos IG : observation nombre foetus\r\n$(\'body\').on(\"keyup, change\", \'#id_igNbFoetus_id\', function() {\r\n  afficherFxNbFoetus();\r\n});\r\n\r\n//issue grossesse : calcul terme à l\'acc\r\n$(\'body\').on(\"focusout\", \'#id_igDate_id\', function() {\r\n  terme = termeAccCalc($(\'#id_igDate_id\').val(), $(\'#id_DDR_id\').val(), $(\'#id_ddgReel_id\').val());\r\n  if (terme != null) {\r\n    $(\'#id_igTermeFA_id, #id_igTermeFB_id, #id_igTermeFC_id\').val(terme);\r\n  }\r\n});'
where `internalName`='medGeIssueGrossesse';

-- upgrade n° de version
UPDATE `system` SET `value`='v1.1.0' WHERE `name`='medge';
