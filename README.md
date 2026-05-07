# Miljonimäng

Avalik repositoorium: https://github.com/IvarTammela/miljonimang

Avalik GitHub Pages demo: https://ivartammela.github.io/miljonimang/

Kanban-tabel GitHub Projectsis: https://github.com/users/IvarTammela/projects/2

## Projekti kirjeldus

Miljonimäng on AI-põhine ülesande valideerimise rakendus. Rakendus kontrollib, kas õppija mõistab valitud ülesande lahendust, esitades 15 valikvastustega küsimust ülesande kirjelduse ja lahendusfailide põhjal.

## Kasutatud tehnoloogiad

- Staatiline GitHub Pages versioon: HTML, CSS, JavaScript ja eelgenereeritud JSON küsimustepank
- PHP 8.4
- HTML
- CSS
- PHP sessioonid mänguseisu hoidmiseks
- JSON fail edetabeli salvestamiseks
- OpenAI API ühenduse koht koos fallback küsimuste generaatoriga

## Käivitamise juhend

### GitHub Pages / staatiline versioon

Kõige odavam hostimise variant on staatiline versioon. Selle jaoks on projekti juures failid:

- `index.html`
- `assets/app.js`
- `assets/static.css`
- `data/question-bank.json`

GitHub Pages saab need failid otse serveerida. Lokaalselt saab staatilist versiooni proovida käsuga:

```bash
php -S localhost:8010 -t .
```

Ava brauseris:

```text
http://localhost:8010
```

Staatilises versioonis on üks mäng, aga kasutaja saab valida, millise ülesande küsimustepangast mäng koostatakse. Küsimused on eelgenereeritud failis `data/question-bank.json`. Praegu on pangas 54 käsitsi koostatud põhiküsimust. Iga mäng valib valitud ülesande küsimustest 15 ehk 5 lihtsat, 5 keskmist ja 5 rasket. Küsimuste vastusevariandid on kirjutatud sarnase pikkuse ja usutava tehnilise sisuga, et õige vastus ei oleks lihtsalt pikkuse või absurdse valevariandi järgi ära arvatav. Edetabel salvestub kasutaja brauseri `localStorage`-isse.

### PHP / AI versioon

Käivita projektijuures:

```bash
php -S localhost:8000 -t public
```

Ava brauseris:

```text
http://localhost:8000
```

Päris AI kasutamiseks määra enne käivitamist:

```bash
export OPENAI_API_KEY="sinu-api-voti"
```

Soovi korral saab mudeli määrata muutujaga:

```bash
export OPENAI_MODEL="gpt-4o-mini"
```

Kui API võtit ei ole, kasutab rakendus simuleeritud küsimuste generaatorit. Simulaatori backendis on rohkem kui 15 küsimust: küsimused on jagatud lihtsateks, keskmisteks ja rasketeks ning iga uus mäng valib neist juhuslikult 15 küsimust.

## Input-kausta struktuur

```text
input/
  001/
    assignment.md
    index.html
    style.css
    script.js
  002/
    assignment.md
    data.json
    app.js
```

Iga ülesanne peab olema numbrilises alamkaustas ja sisaldama faili `assignment.md`.

## AI küsimuste genereerimise loogika

AI prompt asub failis `prompts/question-generation.md`. Prompt nõuab 15 küsimust, 4 vastusevarianti, ühte õiget vastust, raskusastmeid ja lühikest selgitust. Rakendus valideerib AI vastuse kuju. Kui AI vastust ei saa kasutada, loob `QuestionGenerator` fallback küsimused suuremast küsimustepangast.

Fallback generaatoris on backendis rohkem küsimusi kui mängus korraga kasutatakse. Iga mängu alguses segatakse lihtsate, keskmiste ja raskete küsimuste pangad ning valitakse 5 lihtsat, 5 keskmist ja 5 rasket küsimust. Seetõttu on mängus alati 15 küsimust, kuid uue mängu alustamisel võivad küsimused ja nende järjekord muutuda.

GitHub Pages versioon kasutab sama põhimõtet, aga ilma serveri ja AI API-ta: küsimustepank on JSON failis ning JavaScript valib iga mängu alguses juhusliku komplekti.

## Mängu reeglid

- Rakenduses on üks Miljonimäng, mida saab mängida erinevate `input/` ülesannete põhjal.
- Mängus on 15 küsimust.
- Igal küsimusel on 4 vastusevarianti.
- Vale vastus lõpetab mängu.
- Turvatasemed on 1 000, 32 000 ja 1 000 000 punkti.
- Kasutaja saab mängu pooleli jätta.
- Õlekõrred on `50:50`, `AI vihje` ja `Publik`.
- Lõppenud mängu tulemus salvestatakse edetabelisse.

## Arendusprotsess

Projekt on jaotatud kasutajalugudeks failis `docs/backlog.md`. Esimese iteratsiooni kirjeldus asub failis `docs/sprint-1.md`. Definition of Done on failis `docs/definition-of-done.md`. Tehniline plaan on failis `docs/plan.md`.

Esimeses versioonis valmisid ülesannete valik, failide lugemine, küsimuste genereerimine, mänguloogika, õlekõrred, tulemuse kuvamine ja lihtne edetabel. Hilisemaks jäid kasutajate süsteem, õpetaja vaade ja veebiliidesest ülesannete lisamine.

Kanban-tabel asub GitHub Projects keskkonnas: https://github.com/users/IvarTammela/projects/2

Nõuete täitmise ülevaade on failis `docs/requirements-coverage.md`. Projekti tagasivaade on failis `docs/retrospective.md`.

## Teadaolevad piirangud

- Staatiline GitHub Pages versioon ei loe automaatselt `input/` kausta faile, sest GitHub Pages ei käivita serveripoolset koodi.
- Staatiline versioon kasutab eelgenereeritud küsimustepanka, mitte päris AI API-t.
- PHP fallback küsimused on üldisemad kui päris AI loodud küsimused.
- Tulemused salvestatakse lihtsasse JSON faili, mitte andmebaasi.
- Kasutajate süsteemi ei ole.
- Markdown ja kood kuvatakse MVP-s lihtsa tekstipõhise kontekstina, mitte eraldi eelvaatena.

## Edasiarenduse võimalused

- Tulemuste salvestamine SQLite andmebaasi.
- Mänguajalugu ja kasutajate süsteem.
- Õpetaja vaade uute ülesannete lisamiseks.
- Küsimuste cache, et API kulusid vähendada.
- Markdowni ja koodi ilus kuvamine süntaksivärvimisega.
- Täpsem AI prompt eri tehnoloogiate jaoks.
