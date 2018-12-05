INSERT INTO `configuration` ( `name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ( 'administratifReglementFormulaires', 'module', '0', 'medge', 'Règlements', 'liste', '', 'medGeReglePorteurCalculateur');

update `data_types` set label='Règlement', description='Règlement assisté S1' WHERE `name` = 'medGeReglePorteurCalculateur';
update `data_types` set label='Règlement', description='Règlement conventionné S2' WHERE `name` = 'medGeReglePorteurS2';

-- upgrade n° de version
UPDATE `system` SET `value`='v1.1.0' WHERE `name`='medge';
