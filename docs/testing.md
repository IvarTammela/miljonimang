# Testimise kokkuvõte

## Käsitsi testitavad stsenaariumid

- Ava avaleht ja kontrolli, et `001` ning `002` on ülesannete nimekirjas.
- Vali `001` ja alusta mängu.
- Kontrolli, et kuvatakse küsimus, neli vastusevarianti, punktiredel ja õlekõrred.
- Kasuta `50:50` õlekõrt ja kontrolli, et kaks valet varianti keelatakse.
- Kasuta `AI vihje` õlekõrt ja kontrolli, et vihje ei ütle otsest vastust.
- Kasuta `Publik` õlekõrt ja kontrolli, et protsendid kuvatakse.
- Vasta õigesti ja kontrolli, et mäng liigub järgmisele küsimusele.
- Vasta valesti ja kontrolli, et mäng lõpeb ning tulemus langeb turvatasemele.
- Alusta uus mäng ilma `OPENAI_API_KEY` muutujata ja kontrolli, et fallback küsimused töötavad.

## Tehtud kontroll

- PHP süntaks kontrollitud failides `public/index.php`, `src/*.php` ja `views/*.php`.
- Avaleht avanes aadressil `http://localhost:8000`.
- Ülesanded `001 - JavaScripti kalkulaator` ja `002 - JSON-andmete kuvamine` ilmusid nimekirja.
- Ülesande `001` mäng käivitus fallback generaatoriga.
- Esimene küsimus kuvati koos 4 vastusevariandi, punktiredeli ja õlekõrtega.
- `50:50` õlekõrs muutis kaks valikut mitteaktiivseks.
- Õige vastus viis mängu teisele küsimusele ja kuvas selgituse.
- Fallback generaatorit käivitati mitu korda; iga kord tagastati 15 küsimust ja esimene küsimus vahetus.
- Lõppenud mängu tulemus salvestatakse edetabelisse ja avalehel kuvatakse parimad tulemused.
- Staatilise GitHub Pages versiooni `index.html` ja `data/question-bank.json` laevad lokaalsest serverist.
- Staatilises küsimustepangas on 2 ülesannet ja kokku 60 küsimust.

## Tulemus

Testimine tuleb enne esitamist läbi teha käivituskäsuga `php -S localhost:8000 -t public`.
