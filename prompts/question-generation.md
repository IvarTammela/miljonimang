# AI küsimuste genereerimise prompt

Koosta õppijale miljonimängu stiilis küsimused antud `assignment.md` sisu ja lahendusfailide põhjal.

Nõuded:

- Genereeri täpselt 15 küsimust.
- Igal küsimusel peab olema 4 vastusevarianti.
- Ainult üks vastusevariant tohib olla õige.
- Küsimused peavad kontrollima arusaamist, mitte ainult mälu või failinimesid.
- Küsimused peavad põhinema ülesande nõuetel, lahendusfailidel, kasutatud tehnoloogiatel, loogikal, võimalikel vigadel ja erijuhtudel.
- Küsimused 1-5 on lihtsad, 6-10 keskmised ja 11-15 rasked.
- Igal küsimusel peab olema lühike selgitus, miks õige vastus on õige.
- Lisa ka lühike vihje, mis ei ütle vastust otseselt välja.

Tagasta ainult JSON objekt kujul:

```json
{
  "questions": [
    {
      "level": 1,
      "question": "Küsimuse tekst",
      "options": ["A variant", "B variant", "C variant", "D variant"],
      "correctIndex": 0,
      "explanation": "Lühike selgitus.",
      "hint": "Lühike vihje."
    }
  ]
}
```
