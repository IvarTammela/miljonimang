# Miljonimängu tehniline plaan

## Eesmärk

Luua PHP veebirakendus, mis aitab kontrollida, kas õppija mõistab valitud ülesande lahendust. Rakendus loeb `input/` kaustast ülesanded, kogub `assignment.md` ja lahendusfailide sisu ning koostab nende põhjal 15 küsimusega miljonimängu.

## Arhitektuur

- `index.html`, `assets/app.js`, `assets/static.css` ja `data/question-bank.json` moodustavad staatilise GitHub Pages versiooni.
- `public/index.php` on rakenduse avalik sisenemispunkt ja lihtne router.
- `src/TaskRepository.php` vastutab ülesannete nimekirja ja failisisu lugemise eest.
- `src/QuestionGenerator.php` vastutab küsimuste normaliseerimise ja fallback küsimuste eest.
- `src/OpenAiQuestionClient.php` sisaldab OpenAI API ühenduse kohta.
- `src/Game.php` hoiab mängureegleid, punktiredelit, turvatasemeid ja õlekõrsi.
- `views/` sisaldab server-renderdatud kasutajaliidest.

## AI ja fallback

Rakendus proovib kasutada OpenAI API-t, kui keskkonnamuutuja `OPENAI_API_KEY` on määratud. Kui võtit ei ole või API vastus ei sobi, kasutatakse simuleeritud generaatorit. Mõlemad variandid tagastavad sama küsimuste struktuuri, et mänguloogika ei sõltuks AI teenusest.

Simuleeritud generaatoris on backendis rohkem kui 15 küsimust. Iga mängu alguses valitakse suuremast pangast juhuslikult 5 lihtsat, 5 keskmist ja 5 rasket küsimust. Mängus kasutatakse alati 15 küsimust, aga uuesti alustades võivad küsimused ja nende järjekord erineda.

GitHub Pages versioon ei kasuta serveripoolset AI ühendust. Selle asemel on küsimused eelgenereeritud JSON faili. Praegu sisaldab staatiline küsimustepank 60 küsimust.

## Mängureeglid

Rakenduses on üks Miljonimäng, mida saab mängida mitme erineva `input/` ülesande põhjal. Mängus on 15 küsimust, igal 4 vastusevarianti ja üks õige vastus. Vale vastuse korral mäng lõpeb ning tulemus langeb viimasele saavutatud turvatasemele. Kasutaja saab mängu pooleli jätta. Õlekõrred on `50:50`, `AI vihje` ja `Publik`.

Lõppenud mängu tulemus salvestatakse faili `data/leaderboard.json`. Edetabelis kuvatakse mängija nimi, ülesanne ja punktisumma.

Staatilises versioonis salvestatakse edetabel brauseri `localStorage`-isse, sest GitHub Pages ei saa serverisse faile kirjutada.

## Testimine

Testida tuleb ülesannete lugemist, küsimuste genereerimist, fallback käitumist, vastuste kontrolli, vale vastuse lõppu, pooleli jätmist, turvatasemeid ja õlekõrsi.
