-- upgrade n° de version
UPDATE `system` SET `value`='v1.4.0' WHERE `name`='medge';

-- ajustement form pour autosize
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- poids,plus={kg} ', '- poids,plus={kg},donotsaveempty ') where internalName in ('medGeConsultPedia2M', 'medGeConsultPedia4M', 'medGeConsultPedia9M', 'medGeConsultPedia24M', 'medGeConsultPedia3A', 'medGeConsultPedia4A', 'medGeConsultPedia6A', 'medGeConsultPedia8a9A', 'medGeConsultPedia11a13A', 'medGeConsultPedia15a16A', 'medGeConsultPedia');

UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- taillePatient,plus={cm} ', '- taillePatient,plus={cm},donotsaveempty ') where internalName in ('medGeConsultPedia2M', 'medGeConsultPedia4M', 'medGeConsultPedia9M', 'medGeConsultPedia24M', 'medGeConsultPedia3A', 'medGeConsultPedia4A', 'medGeConsultPedia6A', 'medGeConsultPedia8a9A', 'medGeConsultPedia11a13A', 'medGeConsultPedia15a16A', 'medGeConsultPedia');

UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- taSystolique,plus={mmHg},class=text-right ', '- taSystolique,plus={mmHg},class=text-right,donotsaveempty ') where internalName in ('medGeConsultAdulte', 'medGeConsultPedia4A', 'medGeConsultPedia6A', 'medGeConsultPedia8a9A', 'medGeConsultPedia11a13A', 'medGeConsultPedia15a16A', 'medGeConsultPedia');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- taDiastolique,plus={mmHg},class=text-right ', '- taDiastolique,plus={mmHg},class=text-right,donotsaveempty ') where internalName in ('medGeConsultAdulte', 'medGeConsultPedia4A', 'medGeConsultPedia6A', 'medGeConsultPedia8a9A', 'medGeConsultPedia11a13A', 'medGeConsultPedia15a16A', 'medGeConsultPedia');

UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- taSystolique,plus={mmHg} ', '- taSystolique,plus={mmHg},class=text-right,donotsaveempty ') where internalName in ('medGeConsultAdulte', 'medGeConsultPedia4A', 'medGeConsultPedia6A', 'medGeConsultPedia8a9A', 'medGeConsultPedia11a13A', 'medGeConsultPedia15a16A', 'medGeConsultPedia');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- taDiastolique,plus={mmHg} ', '- taDiastolique,plus={mmHg},class=text-right,donotsaveempty ') where internalName in ('medGeConsultAdulte', 'medGeConsultPedia4A', 'medGeConsultPedia6A', 'medGeConsultPedia8a9A', 'medGeConsultPedia11a13A', 'medGeConsultPedia15a16A', 'medGeConsultPedia');

UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- freqCardiaque,plus={bpm},class=text-right ', '- freqCardiaque,plus={bpm},class=text-right,donotsaveempty ') where internalName in ('medGeConsultAdulte', 'medGeConsultPedia');

-- correction class fontawesome
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '"fa ', '"fas ');
