# Nõuete täitmise ülevaade

## Täidetud

- Repositoorium sisaldab kogu lähtekoodi, README faili ja näidisülesandeid `input/` kaustas.
- README kirjeldab projekti eesmärki, tehnoloogiaid, käivitamist, AI loogikat ja arendusprotsessi.
- AI prompt on nähtav failis `prompts/question-generation.md`.
- Product backlog on failis `docs/backlog.md` ning sisaldab kasutajalugusid ja vastuvõtutingimusi.
- Töövoog on kirjeldatud kujul Backlog, Todo, In progress, Review/Test ja Done.
- Definition of Done on failis `docs/definition-of-done.md`.
- Testimise kokkuvõte on failis `docs/testing.md`.
- Lõppdemo kirjeldus on failis `docs/demo.md`.
- Staatiline GitHub Pages versioon töötab ilma serverita ja kasutab eelgenereeritud küsimustepanka.
- PHP versioon sisaldab OpenAI API ühenduse kohta ning fallback generaatorit.
- Küsimuste genereerimine, failide lugemine, mänguloogika ja kasutajaliides on eraldatud.
- Mängus on 15 küsimust, 4 vastusevarianti, punktiredel, turvatasemed, õlekõrred ja edetabel.

## Osaliselt täidetud

- Päris AI API ühendus on PHP versioonis olemas, kuid GitHub Pages versioon kasutab eelgenereeritud küsimustepanka, sest GitHub Pages ei käivita serveripoolset koodi.
- Edetabel on olemas, kuid staatilises versioonis salvestub see ainult kasutaja brauseri `localStorage`-isse.

## Tegemata ja põhjus

- Kasutajate süsteemi ei lisatud, sest MVP eesmärk oli hoida lahendus lihtne ja tasuta hostitav.
- Õpetaja vaadet ülesannete lisamiseks ei lisatud, sest GitHub Pages ei saa serverisse uusi faile kirjutada.
- Andmebaasi ei lisatud, sest staatiline hostimine oli prioriteet.
