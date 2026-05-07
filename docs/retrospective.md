# Tagasivaade

## Mis õnnestus

- Rakendus töötab ühe Miljonimänguna ja valib iga mängu alguses küsimused suuremast küsimustepangast.
- Staatiline GitHub Pages versioon võimaldab projekti tasuta hostida.
- PHP versioonis on eraldi OpenAI ühenduse koht, nii et projekti saab hiljem päris AI-ga edasi arendada.
- Dokumentatsioon kirjeldab nii tehnilist lahendust kui ka arendusprotsessi.

## Mis oli keeruline

- Algne PHP lahendus sobis hästi failide lugemiseks ja AI ühenduseks, kuid ei sobinud GitHub Pages tasuta hostimiseks.
- Seetõttu tuli lisada eraldi staatiline versioon, kus küsimused on eelgenereeritud JSON failis.
- Ülesandevalik nägi kasutajale alguses välja nagu mitu mängu, mistõttu muudeti avaleht ühe mängu vaateks.

## Mida järgmises iteratsioonis parandada

- Lisada õpetaja tööriist küsimustepanga mugavamaks uuendamiseks.
- Salvestada edetabel serverisse või andmebaasi, kui hostimine seda võimaldab.
- Suurendada küsimustepanka veel rohkem ja siduda küsimused täpsemalt konkreetsete lahendusfailidega.
- Lisada automaatsed testid küsimustepanga struktuuri ja mänguloogika kontrollimiseks.
