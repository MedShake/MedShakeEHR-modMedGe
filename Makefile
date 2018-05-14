default:
	zip -r MedShakeEHR-modMedGe.zip . -x .git\* -x Makefile

clean:
	rm -f MedShakeEHR-modMedGe.zip
