default:
	rm -f MedShakeEHR-modMedGe.zip SHA256SUMS
	zip -r MedShakeEHR-modMedGe.zip . -x .git\* -x Makefile -x installer\*
	sha256sum -b MedShakeEHR-modMedGe.zip > preSHA256SUMS
	head -c 64 preSHA256SUMS > SHA256SUMS
	rm -f preSHA256SUMS

clean:
	rm -f MedShakeEHR-modMedGe.zip