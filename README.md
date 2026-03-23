# Requirements

## Lijst aan rollen

- Eigenaar
    - Heeft beschikking tot de volledige applicatie.

- Receptionist
    - Reserveringen toevoegen, bijhouden en annuleren. Handmatig relevante informatie bij elke reservering kunnen zetten. Onderhoudsopdrachten toevoegen voor onderhoudsploeg.

- Bedienend personeel
    - Bestellingen kunnen toevoegen/weghalen voor elke tafel, opmerkingen toevoegen en afrekenen.

- Koks
    - Bestellingen ontvangen en uitdraaien, bestellingen gereed melden. Ook moet het menu kunnen worden aangepast, ingredienten worden aangepast, allergenen aangepast.

- Onderhoudsploeg
    - Moet onderhoudsopdrachten kunnen lezen en gereedmelden, opmerkingen daarbij kunnen plaatsen.

## Reserveringen

- De gebruiker moet de mogelijkheid hebben om reserveringen handmatig in te kunnen voeren. De gebruiker kan vervolgens de specifieke informatie zien van elke reservering die opgeslagen is in de database. De volgende gegevens die benodigd zijn als volgt: 
    - De volledige naam van de gast.
    - De hoeveelheid gasten.
    - De aankomsttijd.
    - Het telefoonnummer en/of e-mailadres.
    - Het tafelnummer die de gasten zijn toegewezen.
    - Het kamernummer van de gast (als het van toepassing is).
    - evt. bijzonderheden (bv. gehandicapten, allergenen).

    - Als benodigd is, moet de gebruiker de mogelijkheid hebben om de gegevens aan te passen voor elke reservering, zoals volgt: 
        - Het aanpassen van de persoonlijke informatie voor elke reservering.
        - Het annuleren van een reservering.  

- De gebruiker moet een overzicht zien van alle reserveringen op een bepaalde dag. 
    - De reserveringen zijn gesorteerd in tijdvakken om de halfuur.
    - De gebruiker kan een latere datum uitkiezen om de reserveringen op die bepaalde dag in te kunnen zien.

## Table Management

- De gebruiker moet een overzicht zien van alle tafels. Hieruit moet de gebruiker de volgende informatie moeten zien:
    - Het tafelnummer.

    - De huidige status van de tafel:
        - Vrij.
        - Bezet.
        - Net vertrokken.
        - Al bezet voor een reservering. 

    - Het aantal gasten die momenteel aan tafel zit. 
    - De resterende tijd die de gasten hebben om te blijven.

- De gebruiker kan een specifieke tafel selecteren om de volgende informatie te bekijken:
    - Een lijst van alle drank en gerechten die de tafel heeft besteld. 
    - De totaalprijs van alle drank en gerechten.
    - Een button betreft voor het afrekenen. 

### Betalingen

- Wanneer een tafel wilt betalen, kan de gebruiker de betreffende tafel selecteren en vanaf daar de betaling uitvoeren. De gebruiker drukt op de button om de betaling uit te voeren. 

## Bestellingen plaatsen

- Het bedienend personeel nemen de bestellingen op per tafel, daarna moeten ze deze op een apart apparaat waar de applicatie op draait kunnen invoeren. De volgende elementen moeten hierin zichtbaar zijn:
    - Een kader waar de gebruiker het betreffende tafelnummer in kan voeren.
    - Een lijst met alle drank en gerechten, waaronder de naam en de prijs van het gerecht aangetoond zijn.
        - Een nav-bar aanmaken met verschillende categorienamen.*
        - De gebruiker kan per gerecht nog extra commentaar bij toevoegen, om eventuele bijzonderheden te vermelden (bv. allergenen, substituties, gaarheid van vlees).

    - Een overzicht van alle gekozen gerechten, samen met de totaalprijs en eventuele kortingen die van toepassingen verrekenen.
        - Als de gebruiker een verkeerd gerecht gekozen heeft, moet er de mogelijkheid zijn om dat specifiek gerecht te kunnen verwijderen.

    - Een zoekbar om bepaalde gerechten sneller op te zoeken.*
    - Een verstuurbutton om de bestelling door te sturen naar de keuken als alles gereed is.

## Keuken overzicht 

- Alle bestellingen worden verstuurd en aangetoond aan het keukenpersoneel via een scherm in de keuken. Per bestelling is er een blokje met daarin de volgende informatie: 
- Alle gerechten en de hoeveelheid van elk gerecht. 
  - Onder het gerecht waarvan het van toepassing is, worden alle bijzonderheden weergeven die voor de koks van belang zijn. 
  - De tijd van wanneer de bestelling verstuurd werdt.
 - Het tafel- en bestellingsnummer.

Functionaliteiten van het bestellingsoverzicht:

- Mark Ready – Bestellingen als “gereed” markeren zodra ze volledig bereid of geserveerd zijn.

- Send Out – Bestellingen naar de volgende fase versturen zodat personeel weet dat ze onderweg zijn.

Wij hebben besloten het bestellingsoverzicht aan te passen, zodat bestellingen niet langer individueel met “Mark Ready” of “Send Out” kunnen worden afgevinkt, maar in één handeling per bestelling worden verwerkt.

### Room Service

- Gasten die voor room service hebben gevraagd komen ook in het overzichtsysteem te zien. Hiervoor is de kamernummer vermeld van de gast, naast alle andere benodigde informatie die vermeld is in de vorige paragraaf. 

## Menuoverzicht

- De gebruiker kan een overzicht zien van alle gerechten die momenteel te bestellen zijn. Hieronder is de volgende informatie te zien per gerecht: 
    - Naam van het gerecht.
    - Evt. afbeelding van het gerecht. 
    - De ingrediënten nodig voor het gerecht.
    - Instructies voor het gerecht.
    - Allergenen.

- De gebruiker kan vervolgens nieuwe gerechten kunnen toevoegen of verwijderen in het menu. De benodigde informatie hiervoor is al uitgelijst in de vorige paragraaf. 

## Statistieken

- De gebruiker kan de statistieken zien op een dagelijks, wekelijks, maandelijks en jaarlijks basis. Onder statistieken wordt het volgende weergeven:
    - Het totaalwinst van de gekozen periode.



## Bar overzicht



- Barmedewerkers moeten als aparte rol worden toegevoegd binnen het systeem, met bijbehorende rechten en toegangsbeheer.
- Er moet een bestellingsoverzicht worden gemaakt voor barmedewerkers, zodat zij bestellingen kunnen bekijken en verwerken voor:
    - gasten aan tafel
   - gasten aan de bar
- Manager kan het barsysteem en barmedewerkers beheren.
- Manager kan prestaties en aantal verwerkte bestelligen bekijken.
- Mogelijkheid om notities door te geven van bediening naar bar

    - Een lijst van de meest en minst verkochte gerechten.

*Nog bespreken met de anderen

