<?php

class QuestionGenerator
{
    public function __construct(
        private OpenAiQuestionClient $client,
        private string $promptPath
    ) {
    }

    /**
     * @return array<int, array{level:int,question:string,options:array<int,string>,correctIndex:int,explanation:string,difficulty:string,hint:string}>
     */
    public function generate(array $task): array
    {
        $prompt = is_file($this->promptPath) ? (file_get_contents($this->promptPath) ?: '') : '';
        $aiQuestions = $this->client->generate($task, $prompt);
        $questions = $this->normalizeQuestions($aiQuestions ?? []);

        if (count($questions) === 15) {
            return $questions;
        }

        return $this->generateFallback($task);
    }

    private function normalizeQuestions(array $questions): array
    {
        $normalized = [];
        foreach ($questions as $index => $question) {
            if (!is_array($question) || count($normalized) >= 15) {
                continue;
            }

            $options = $question['options'] ?? [];
            $correctIndex = $question['correctIndex'] ?? null;
            if (!is_array($options) || count($options) !== 4 || !is_int($correctIndex) || $correctIndex < 0 || $correctIndex > 3) {
                continue;
            }

            $level = (int)($question['level'] ?? ($index + 1));
            $normalized[] = [
                'level' => $level,
                'question' => (string)($question['question'] ?? ''),
                'options' => array_values(array_map('strval', $options)),
                'correctIndex' => $correctIndex,
                'explanation' => (string)($question['explanation'] ?? ''),
                'difficulty' => $this->difficultyForLevel($level),
                'hint' => (string)($question['hint'] ?? 'Mõtle, milline vastus seostub ülesande nõuete ja lahenduse tegeliku loogikaga.'),
            ];
        }

        return $normalized;
    }

    private function generateFallback(array $task): array
    {
        $title = $task['title'];
        $fileNames = array_column($task['files'], 'path');
        $mainFile = $fileNames[0] ?? 'lahendusfail';
        $usesJavaScript = $this->taskContains($task, 'addEventListener') || $this->taskContains($task, '.js');
        $usesJson = $this->taskContains($task, 'json');
        $usesLocalStorage = $this->taskContains($task, 'localStorage');

        $easy = [
            $this->q(1, "Mis on ülesande \"$title\" kontrollimise peamine eesmärk?", ['Kontrollida ainult failinimesid', 'Kontrollida, kas õppija mõistab lahenduse nõudeid ja loogikat', 'Muuta lahendus automaatselt teiseks keeleks', 'Kustutada kõik vigased vastused'], 1, 'Rakendus peab hindama arusaamist, mitte ainult failide olemasolu.'),
            $this->q(1, 'Millist faili kasutatakse ülesande püstituse ja hindamiskriteeriumite allikana?', ['assignment.md', 'style.css', 'package-lock.json', 'screenshot.png'], 0, 'Iga ülesande kaustas peab olema assignment.md.'),
            $this->q(1, "Mida annab AI-le lahendusfailide, näiteks \"$mainFile\", lugemine?", ['Võimaluse küsida sisulisi küsimusi tegeliku lahenduse kohta', 'Võimaluse muuta brauseri parooli', 'Võimaluse vältida ülesande teksti lugemist', 'Võimaluse luua ainult failinimede küsimusi'], 0, 'Lahendusfailide sisu aitab küsida kasutatud loogika ja tehnoloogiate kohta.'),
            $this->q(1, 'Miks peab igal küsimusel olema neli vastusevarianti?', ['See teeb mängu valikvastustega formaadi üheselt kontrollitavaks', 'See eemaldab vajaduse õige vastuse järele', 'See lubab mitu õiget vastust', 'See asendab assignment.md faili'], 0, 'Miljonimängu laadne vaade põhineb neljal variandil ja ühel õigel vastusel.'),
            $this->q(1, 'Mida tähendab turvatase 1 000 punkti juures?', ['Vale vastuse korral võib tulemus langeda sellele tasemele', 'Kõik küsimused muutuvad automaatselt lihtsaks', 'Kasutaja peab sisestama parooli', 'Mäng kustutab ülesande kausta'], 0, 'Turvatase hoiab osa tulemusest alles pärast hilisemat valet vastust.'),
            $this->q(1, 'Mida tähendab, et küsimus kontrollib arusaamist?', ['Kasutaja peab põhjendama lahenduse loogikat, mitte ainult detaile mäletama', 'Kasutaja peab teadma faili loomise kellaaega', 'Kasutaja peab ära arvama arvuti nime', 'Kasutaja peab valima kõige pikema vastuse'], 0, 'Arusaamise kontroll seob küsimuse ülesande nõuete ja lahenduse põhimõtetega.'),
            $this->q(1, 'Miks kuvatakse ülesanded numbriliste kaustade järgi?', ['Et üks rakendus saaks toetada mitut eraldi ülesannet', 'Et brauser ei avaks CSS faile', 'Et kasutaja ei saaks küsimustele vastata', 'Et assignment.md oleks alati tühi'], 0, 'Numbrilised kaustad annavad lihtsa viisi mitme ülesande eristamiseks.'),
            $this->q(1, 'Mida teeb mängu alustamine valitud ülesandega?', ['Loeb ülesande konteksti ja loob küsimused', 'Kustutab input kausta', 'Muudab PHP faili CSS failiks', 'Keelab kõik vastusevariandid'], 0, 'Mäng vajab valitud ülesande sisu, et küsimused oleksid selle ülesande kohta.'),
            $this->q(1, 'Miks on küsimusel vaja õige vastuse indeksit?', ['Et rakendus saaks kasutaja vastust kontrollida', 'Et küsimus kaoks ekraanilt enne lugemist', 'Et failide lugemine peatuks', 'Et punktiredel oleks alati tühi'], 0, 'Õige vastuse indeks seob variandi mänguloogika kontrolliga.'),
            $this->q(1, 'Mida näitab punktiredel kasutajale?', ['Praegust edenemist ja võimalikke järgmisi punktisummasid', 'Serveri kõvaketta suurust', 'Kõigi failide täpseid õigusi', 'Ainult CSS klasside arvu'], 0, 'Punktiredel aitab kasutajal aru saada, kus ta mängus asub.'),
            $this->q(1, 'Miks on ülesande pealkiri kasutajale kasulik?', ['See näitab, millise lahenduse kohta küsimused käivad', 'See asendab kõik vastused', 'See peidab mängureeglid', 'See keelab AI kasutamise'], 0, 'Pealkiri annab mängule konteksti ja aitab valikut kinnitada.'),
            $this->q(1, 'Mida tähendab, et ainult üks vastus on õige?', ['Mäng saab vastuse üheselt õigeks või valeks hinnata', 'Kasutaja võib valida kõik vastused korraga', 'Kõik variandid annavad alati punkte', 'Selgitust pole enam vaja'], 0, 'Üks õige vastus teeb punktiarvestuse ja selgituse selgeks.'),
            $this->q(1, 'Miks peab rakendus lugema vähemalt ühe lahendusfaili?', ['Et küsimused saaksid puudutada tegelikku lahendust', 'Et assignment.md jääks kasutamata', 'Et mängus oleks ainult 5 küsimust', 'Et õlekõrred ei töötaks'], 0, 'Lahendusfailide sisu on arusaamise kontrolli oluline sisend.'),
            $this->q(1, 'Mida teeb “Jäta pooleli” valik?', ['Lõpetab mängu ja jätab hetkel teenitud punktid alles', 'Annab automaatselt miljon punkti', 'Kustutab kõik küsimused backendist', 'Vahetab ülesande numbri juhuslikuks'], 0, 'Pooleli jätmine on osa miljonimängu loogikast.'),
            $this->q(1, 'Miks on selgitus pärast vastamist vajalik?', ['See aitab õppijal aru saada, miks vastus oli õige või vale', 'See muudab vale vastuse õigeks', 'See asendab ülesande valiku', 'See peidab punktisumma'], 0, 'Selgitus toetab õppimist, mitte ainult punktide kogumist.'),
            $this->q(1, 'Mida tähendab fallback generaator?', ['Varuplaan, mis loob küsimused ilma päris AI vastuseta', 'Fail, mis kustutab kõik variandid', 'CSS animatsioon punktiredelil', 'Ainult õpetajale nähtav parool'], 0, 'Fallback hoiab rakenduse kasutatavana ka ilma API võtmeta.'),
            $this->q(1, 'Miks ei pea õpetaja uue ülesande lisamiseks koodi muutma?', ['Rakendus leiab numbrilised kaustad automaatselt', 'Kõik küsimused on brauserisse lukustatud', 'PHP ei kasuta input kausta', 'Uued ülesanded on keelatud'], 0, 'Automaatne kaustade lugemine teeb lahenduse edasiarendatavaks.'),
        ];

        $medium = [
            $this->q(6, 'Miks ei tohiks küsimused olla ainult stiilis “mis faili nimi oli lahenduses”?', ['See kontrollib pigem mälu kui arusaamist', 'Sest failinimed on alati salajased', 'Sest PHP ei saa faile lugeda', 'Sest valikvastuseid ei saa failinimedega teha'], 0, 'Eesmärk on kontrollida kontseptsioone, loogikat ja nõuetest arusaamist.'),
            $this->q(6, $usesJavaScript ? 'Miks kasutatakse JavaScripti lahenduses sageli addEventListener meetodit?' : 'Miks on kasutaja tegevustele reageerimise loogika lahenduses oluline?', ['Serveri operatsioonisüsteemi muutmiseks', 'Kasutaja tegevustele, näiteks nupuvajutusele, reageerimiseks', 'CSS faili automaatseks tõlkimiseks', 'HTML faili kustutamiseks'], 1, 'Sündmuse kuulajad seovad kasutaja tegevuse rakenduse loogikaga.'),
            $this->q(6, $usesJson ? 'Milleks kasutatakse JSON-andmeid sellises lahenduses kõige tõenäolisemalt?' : 'Miks tuleb lahenduse andmete struktuuri mõista?', ['Andmete struktureeritud hoidmiseks või kuvamiseks', 'Brauseri sulgemiseks', 'CSS värvide krüpteerimiseks', 'Faililaiendite peitmiseks'], 0, 'Struktureeritud andmed aitavad loogikal väärtusi lugeda ja kuvada.'),
            $this->q(6, 'Mis on hea põhjus kasutaja sisendi valideerimiseks enne töötlemist?', ['See vähendab vigase või ootamatu sisendi põhjustatud probleeme', 'See muudab kõik vastused automaatselt õigeks', 'See keelab assignment.md lugemise', 'See teeb CSS-i mittevajalikuks'], 0, 'Valideerimine kaitseb loogikat vigaste väärtuste ja erijuhtude eest.'),
            $this->q(6, 'Mis võib juhtuda, kui kood eeldab HTML-elemendi olemasolu, aga elementi tegelikult pole?', ['Tekib viga, kui puuduva elemendi meetodit või omadust kasutatakse', 'Rakendus parandab HTML-i automaatselt', 'Kõik punktid antakse kohe kasutajale', 'Failid liiguvad input kaustast välja'], 0, 'Puuduva elemendi korral võib väärtus olla null ja sellele meetodi kutsumine põhjustab vea.'),
            $this->q(6, 'Miks on oluline võrrelda lahendust ülesande nõuetega?', ['Nii saab hinnata, kas lahendus täidab päriselt eesmärki', 'Nii saab vältida kõiki HTML faile', 'Nii muutub iga vale vastus õigeks', 'Nii ei pea kasutaja enam küsimusi lugema'], 0, 'Valideerimine peab kontrollima lahenduse vastavust lähteülesandele.'),
            $this->q(6, 'Miks peab lahenduse failide lugemisel mõnda kausta ignoreerima?', ['Et vältida suuri või kõrvalisi kaustu nagu node_modules ja vendor', 'Et assignment.md kustutada', 'Et küsimusi oleks alati null', 'Et punktiredel kaoks'], 0, 'Ignoreerimine hoiab AI konteksti asjakohase ja väldib liigseid faile.'),
            $this->q(6, 'Miks tuleb AI vastusest saadud küsimuste arvu kontrollida?', ['Mäng eeldab täpselt 15 küsimust', 'PHP ei suuda numbreid võrrelda', 'Kasutaja peab nägema alati null küsimust', 'CSS vajab täpselt 15 värvi'], 0, 'Kui küsimusi pole täpselt 15, võib mänguvoog katki minna.'),
            $this->q(6, 'Miks on hea, et OpenAI klient ja fallback generaator on eraldi?', ['API vea korral saab kasutada varugeneraatorit ilma mänguloogikat muutmata', 'See keelab küsimuste segamise', 'See teeb kõik vastused valeks', 'See eemaldab vajaduse assignment.md failile'], 0, 'Eraldus teeb AI osa vahetatavaks ja töökindlamaks.'),
            $this->q(6, 'Mida peaks rakendus tegema, kui OpenAI API ei vasta?', ['Kasutama fallback küsimusi ja jätkama mängu', 'Näidata ainult tühja valget lehte', 'Kustutada kasutaja sessiooni iga sekund', 'Muuta kõik failid binaarseks'], 0, 'API tõrge ei tohiks takistada MVP kasutamist.'),
            $this->q(6, 'Miks peab iga uus mäng küsimused uuesti valima?', ['Et õppija ei saaks lihtsalt vastuste järjekorda pähe õppida', 'Et assignment.md muutuks tühjaks', 'Et mängus oleks alati sama esimene küsimus', 'Et kõik õlekõrred kaoksid'], 0, 'Juhuslik valik muudab kordusmängud sisukamaks.'),
            $this->q(6, 'Miks jagatakse fallback küsimused raskusgruppidesse?', ['Et mäng läheks järjest keerulisemaks', 'Et kõik küsimused oleksid sama teemaga', 'Et mängus oleks ainult rasked küsimused', 'Et vastusevariantide arv muutuks suvaliseks'], 0, 'Raskusgrupid toetavad miljonimängu nõuet, et küsimused muutuvad keerulisemaks.'),
            $this->q(6, 'Miks peab 50:50 eemaldama ainult valesid vastuseid?', ['Muidu võib õige vastus kaduda ja mäng muutuks ebaõiglaseks', 'Sest õige vastus peab alati peidus olema', 'Sest küsimusi peab jääma 15 asemel 2', 'Sest punktiredel vajab kahte värvi'], 0, '50:50 peab jätma alles õige vastuse ja ühe vale variandi.'),
            $this->q(6, 'Miks publikuhääletus ei pea alati olema täiesti õige?', ['See on õlekõrs, mitte automaatne vastuse avaldamine', 'Sest publik ei tohi protsente näha', 'Sest õigel vastusel peab olema alati 0%', 'Sest mäng lõpeb enne küsimust'], 0, 'Publiku abi peaks aitama, kuid mitte garanteerima õiget vastust.'),
            $this->q(6, 'Miks peaks vihje vältima otsese vastuse ütlemist?', ['Vihje peab suunama mõtlemist, mitte mängu ära lahendama', 'Sest vihje ei tohi olla tekst', 'Sest ainult vale vastus võib vihjes olla', 'Sest vihje kustutab punktid'], 0, 'Õlekõrs peab toetama õppimist ja otsustamist.'),
            $this->q(6, 'Miks on PHP sessioon mänguseisu jaoks sobiv MVP lahendus?', ['See hoiab valitud küsimused ja punktid kasutaja mängu jooksul alles', 'See salvestab automaatselt Git commitid', 'See muudab HTML failid piltideks', 'See keelab mitme ülesande toe'], 0, 'Sessioon sobib lihtsaks ühe kasutaja mänguseisuks ilma andmebaasita.'),
            $this->q(6, 'Miks ei tohiks fallback küsimused olla ainult ühe konkreetse näidisülesande kohta?', ['Rakendus peab töötama erinevate input ülesannetega', 'Sest näidisülesandeid ei tohi olla', 'Sest mängus on ainult üks küsimus', 'Sest API võti kirjutatakse CSS-i'], 0, 'Üldisemad küsimused võimaldavad fallbackil töötada mitme ülesandetüübiga.'),
            $this->q(6, 'Mida näitab küsimuse raskusaste kasutajale või arendajale?', ['Millises mänguosas küsimus peaks paiknema', 'Mitu faili tuleb kustutada', 'Kas brauser toetab värve', 'Kas vastusevariant on peidetud'], 0, 'Raskusaste aitab küsimusi mängus õigesse järjekorda seada.'),
            $this->q(6, 'Miks on lahendusfailide sisu piiramine mõistlik?', ['See väldib liiga suurte failide saatmist AI konteksti', 'See teeb kõik küsimused raskeks', 'See eemaldab vajaduse ülesande pealkirja järele', 'See muudab punktid negatiivseks'], 0, 'Kontekst peab olema piisav, aga mitte liiga suur ega kõrvaline.'),
            $this->q(6, 'Miks peab vastusevariantide järjekorda segama?', ['Et õige vastus ei oleks alati samal kohal', 'Et küsimused muutuksid failideks', 'Et kasutaja ei näeks küsimust', 'Et mängus oleks vähem kui 4 varianti'], 0, 'Variantide segamine vähendab päheõppimist ja mustrite tekkimist.'),
        ];

        $hard = [
            $this->q(11, 'Milline küsimus kontrollib kõige paremini sügavamat arusaamist lahendusest?', ['Mis värvi oli nupp?', 'Miks võib innerHTML kasutamine kasutaja sisendiga olla riskantne?', 'Mis kell fail salvestati?', 'Mitu tähte on failinimes?'], 1, 'Turvariskide ja alternatiivide märkamine näitab sügavamat arusaamist.'),
            $this->q(11, $usesLocalStorage ? 'Mis on localStorage kasutamise piirang?' : 'Mis on brauseripõhise salvestuse üldine piirang?', ['Andmed on seotud konkreetse brauseri ja seadmega', 'Andmed muutuvad automaatselt SQL tabeliteks', 'See töötab ainult paberil', 'See teeb kõik API päringud võimatuks'], 0, 'Brauseri kohalik salvestus ei ole jagatud serveripoolne andmebaas.'),
            $this->q(11, 'Miks peaks failide lugemine ja mänguloogika olema eraldi osades?', ['Et koodi oleks lihtsam testida ja edasi arendada', 'Et kõik failid oleksid ühes väga pikas failis', 'Et küsimusi ei saaks enam genereerida', 'Et kasutaja ei näeks tulemust'], 0, 'Eraldatud vastutused teevad rakenduse hooldatavamaks.'),
            $this->q(11, 'Miks on AI vastuse JSON-kuju kasulik?', ['Rakendus saab küsimusi ühtse struktuurina kontrollida ja kuvada', 'JSON muudab vale vastuse alati õigeks', 'JSON eemaldab vajaduse mänguvaate järele', 'JSON on ainult piltide vorming'], 0, 'Struktureeritud vastust saab valideerida ja kasutada mänguloogikas.'),
            $this->q(11, 'Milline edasiarendus toetaks kõige paremini õpetaja korduvat kasutust?', ['Võimalus lisada uusi ülesandeid input kausta ilma rakendust ümber kirjutamata', 'Kõigi küsimuste muutmine samaks', 'assignment.md eemaldamine', 'Ainult ühe kõvakodeeritud ülesande lubamine'], 0, 'Mitme ülesande tugi teeb rakenduse taaskasutatavaks.'),
            $this->q(11, 'Mis on oht, kui AI vastust enne mängus kasutamist ei kontrollita?', ['Katkine või vale struktuur võib mänguvaate ja vastuste kontrolli rikkuda', 'Kõik küsimused muutuvad automaatselt CSS-iks', 'Kasutaja ei saa enam brauserit avada', 'PHP lõpetab failide lugemise igaveseks'], 0, 'AI väljund on välissisend ja seda tuleb enne kasutamist valideerida.'),
            $this->q(11, 'Kuidas parandaksid lahendust, kui küsimused muutuksid liiga korduvaks?', ['Lisaksid küsimuste panga, juhusliku valiku või päris AI genereerimise', 'Eemaldaksid kõik rasked küsimused', 'Lubaksid ainult ühe vastusevariandi', 'Paneksid kõik ülesanded samasse faili'], 0, 'Korduvust vähendavad suurem küsimuste pank, juhuslik valik ja AI genereerimine.'),
            $this->q(11, 'Mis võib juhtuda, kui AI tagastab kaks õiget vastust, aga mäng eeldab ühte?', ['Vastuse kontroll muutub ebaõiglaseks või eksitavaks', 'Mäng saab automaatselt rohkem ülesandeid', 'Punktiredel muutub Markdowniks', 'Kasutaja ei saa enam serverit käivitada'], 0, 'Valikvastustega mängu ausus sõltub ühest selgelt õigest vastusest.'),
            $this->q(11, 'Miks on suur küsimustepank parem kui ainult 15 küsimust?', ['See vähendab kordusi ja toetab sama ülesande kordusmänge', 'See teeb mängus korraga 100 küsimust', 'See kaotab vajaduse raskusastmete järele', 'See muudab kõik vastused samaks'], 0, 'Suurem pank võimaldab igal mängul valida erineva komplekti.'),
            $this->q(11, 'Milline probleem tekib, kui küsimuste järjekord segatakse täiesti ilma raskusastmeid arvestamata?', ['Raske küsimus võib sattuda esimeseks ja mängu tempo kannatab', 'Kõik küsimused muutuvad õigeks', 'input kaust kaob', 'AI prompti ei saa enam avada'], 0, 'Miljonimängu loogika eeldab kasvavat raskust.'),
            $this->q(11, 'Miks peab task ID valideerima numbriliseks?', ['Et vältida suvaliste failiteede küsimist kasutaja sisendist', 'Et CSS failid oleksid sinised', 'Et kõik kaustad muutuksid avalikuks', 'Et AI genereeriks ainult ühe küsimuse'], 0, 'Sisendi piiramine vähendab failitee kuritarvitamise riski.'),
            $this->q(11, 'Miks kontrollitakse, et ülesande tee jääb input kausta sisse?', ['Et kasutaja ei saaks küsida faile väljaspool lubatud ala', 'Et punktid oleksid alati 100', 'Et brauser ei näitaks nuppe', 'Et README kustutataks'], 0, 'Failisüsteemi piiramine on oluline turvakontroll.'),
            $this->q(11, 'Miks on mõistlik binaarfaile AI kontekstist välja jätta?', ['Need ei anna küsimuste koostamiseks kasulikku tekstilist infot ja võivad olla suured', 'Need parandavad alati küsimuste kvaliteeti', 'Need muudavad JSON vastuse kohustuslikuks', 'Need lubavad mitu õiget vastust'], 0, 'AI vajab peamiselt loetavat teksti ja lähtekoodi.'),
            $this->q(11, 'Mis oleks hea järgmine samm, kui tulemusi on vaja hiljem võrrelda?', ['Lisada andmebaas tulemuste ajaloo salvestamiseks', 'Eemaldada kõik sessioonid ja punktid', 'Lubada ainult üks ülesanne', 'Peita README fail'], 0, 'Tulemuste võrdlemiseks on vaja püsivat salvestust.'),
            $this->q(11, 'Miks on dokumenteeritud prompt hindamisel oluline?', ['Õpetaja näeb, kuidas AI-d juhendatakse küsimusi looma', 'Prompt asendab mängu käivitamise', 'Prompt teeb PHP süntaksikontrolli', 'Prompt kustutab valed vastused'], 0, 'AI osa peab olema nähtav ja seletatav.'),
            $this->q(11, 'Kuidas toetab eraldi README projekti hindamist?', ['See selgitab eesmärki, käivitamist, piiranguid ja edasiarendust', 'See annab automaatselt kõik punktid', 'See muudab API võtme avalikuks', 'See peidab backlogi'], 0, 'README aitab hindajal projekti kiiresti käivitada ja mõista.'),
            $this->q(11, 'Mis on risk, kui kogu rakendus oleks ühes pikas failis?', ['Edasiarendus ja vigade leidmine muutuks raskemaks', 'Mäng muutuks automaatselt kiiremaks', 'Kõik küsimused oleksid AI loodud', 'Ülesandeid saaks lisada ainult rohkem'], 0, 'Eraldatud vastutused teevad muudatused lihtsamaks.'),
            $this->q(11, 'Miks peaks AI loodud küsimused siduma lahenduses kasutatud tehnoloogiatega?', ['Nii kontrollitakse konkreetse lahenduse mõistmist', 'Nii saab vältida assignment.md lugemist', 'Nii muutuvad kõik küsimused üldteadmisteks', 'Nii ei pea vastuseid kontrollima'], 0, 'Rakenduse eesmärk on kontrollida just selle ülesande ja lahenduse arusaamist.'),
            $this->q(11, 'Milline käitumine näitab, et rakendus on edasiarendatav?', ['Uue ülesande lisamine input kausta ei nõua põhikoodi muutmist', 'Iga uus ülesanne vajab uut routerit', 'Kõik küsimused on HTML-is käsitsi sees', 'API võti on kõigile nähtav lehel'], 0, 'Automaatne avastamine ja eraldi komponendid toetavad edasiarendust.'),
            $this->q(11, 'Miks on aus dokumenteerida tegemata nõuded?', ['See näitab projekti piirangute ja järgmiste sammude mõistmist', 'See peidab vead hindaja eest', 'See muudab rakenduse ilma serverita töötavaks', 'See lisab automaatselt kasutajate süsteemi'], 0, 'Hindamiskriteeriumid ootavad ausat ülevaadet valmis ja tegemata osadest.'),
        ];

        shuffle($easy);
        shuffle($medium);
        shuffle($hard);

        $questions = [
            ...array_slice($easy, 0, 5),
            ...array_slice($medium, 0, 5),
            ...array_slice($hard, 0, 5),
        ];

        return array_map(
            fn (array $question, int $index): array => $this->shuffleOptions($this->withLevel($question, $index + 1)),
            $questions,
            array_keys($questions)
        );
    }

    private function taskContains(array $task, string $needle): bool
    {
        $haystack = mb_strtolower($task['assignment'] . "\n" . implode("\n", array_column($task['files'], 'content')));
        return str_contains($haystack, mb_strtolower($needle));
    }

    private function q(int $level, string $question, array $options, int $correctIndex, string $explanation): array
    {
        return [
            'level' => $level,
            'question' => $question,
            'options' => $options,
            'correctIndex' => $correctIndex,
            'explanation' => $explanation,
            'difficulty' => $this->difficultyForLevel($level),
            'hint' => 'Mõtle ülesande nõuete ja lahenduses kasutatud loogika seosele.',
        ];
    }

    private function withLevel(array $question, int $level): array
    {
        $question['level'] = $level;
        $question['difficulty'] = $this->difficultyForLevel($level);

        return $question;
    }

    private function shuffleOptions(array $question): array
    {
        $correct = $question['options'][$question['correctIndex']];
        shuffle($question['options']);
        $question['correctIndex'] = array_search($correct, $question['options'], true);

        return $question;
    }

    private function difficultyForLevel(int $level): string
    {
        if ($level <= 5) {
            return 'easy';
        }

        if ($level <= 10) {
            return 'medium';
        }

        return 'hard';
    }
}
