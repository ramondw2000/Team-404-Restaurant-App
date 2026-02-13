
Een intern Engels-talige applicatie voor tafelmanagement, reserveringen, betalingen en inzicht in verkoop en omzet.

  

## Thema
  

Donkerblauw/Lichtblauw/Wit kleuren.

Donkerblauw: #                (Haal uit het logo)
Lichtblauw:     #                (Haal uit het logo)
Wit:                #FFFFFF

  

## Table Management Systeem

##### [[Voorbeeld Table Management]] 
  
aanpasbaar tafelmanagementsysteem waar de gebruiker tafels kan aanmaken en overzien voor een bepaald aantal gasten in het restaurant. De gebruiker moet kunnen zien of de tafel bezet, vrij of gereserveerd is met een begin- en eindtijd (maximaal 2 uur).  


## Authenticatie en Autorisatie

Rollen:
- Management (SuperAdmin)
- Personeel 
- Meer?

Management heeft toegang tot alle rechten.

##### Vragen hierover:
- Welke rollen moeten er bestaan? (type personeel)
- Welke rechten moet elke rol hebben?
- Wat moet rol X kunnen doen? (Waar X staat voor een rol hierboven)
## Reserveringen

Mensen kunnen bellen of langskomen bij het hotel om een reservering te maken voor het restaurant.

1.  Restaurant/Hotel personeel voert gegevens in van klant door middel van het aanpassen van een tafel die kan worden gereserveerd.
2. Als een reservering via het hotel komt moet dat tafelnummer gelinkt worden aan het kamernummer.
3. De ingevoerde gegevens wordt opgeslagen in de database.
4. Als de persoon heeft betaald wordt de tafel als vrij beschouwd.

###### Vragen hierover:
- Zijn er tafels die specifiek alleen gereserveerd kunnen worden?
	- bijvoorbeeld met een icoon met een sterretje?

## Ingrediënten

#### Ingrediënten moeten aanpasbaar zijn (bewerkknop)

1. De gebruiker maakt een lijst van Ingrediënten (indien deze ontbreken)
2. De gebruiker voegt de allergeneninformatie toe per ingrediënt (maakt zelf een notitie per allergeen)
3. Het Ingrediënt wordt opgeslagen in de database
4. Het Ingrediënt kan dan worden gebruikt in het maken van recepten

###### Vragen hierover:
- Welk personeel moeten er ingrediënten kunnen aanmaken?


## Recepten

##### Het recept moet aanpasbaar zijn (Edit knop)

1. De gebruiker maakt een lijst van recepten
2. De gebruiker voert een naam in voor het recept
3. De gebruiker voegt de prijs van het recept toe
4. De gebruiker kan ingrediënten toevoegen aan het recept (Wat voor opmaak?)
5. Allergenen informatie (via icoontjes?)

###### Vragen hierover:
- Welk personeel moeten er recepten kunnen aanmaken?
- Hoe moet de allergenen informatie worden weergegeven op de pagina?

## Bestellingen doorgeven

##### ==BELANGRIJK==: Dit systeem moet werken op kleine resoluties voor handheldapparaten

1. De gebruiker geeft een bestelling door via het systeem
2. Het systeem 

## Bestelling informatie

1. Het overzichtspagina toont aan de gebruiker als er een nieuwe bestelling is met een notificatie geluid. 
2. De bestelling wordt weergegeven aan de gebruikers die dit nodig hebben. 
3. De bestelling laat een tafelnummer, bestellingsnummer, de recepten, uitzonderingen zonder ingrediënt, extra van ingrediënt of belangrijke ingrediënt informatie zoals medium rare, medium, etc.)
4. Als de bestelling klaar is, kan een gebruiker deze markeren als klaar (recept afbeelding wordt grijs?)


###### Vragen hierover:
- Welk personeel moeten er bestellingen kunnen inzien en afvinken als klaar?

### Betalingen

1. De gebruiker vinkt af dat de tafel heeft betaald 
	-   Als de gebruiker een hotelkamer heeft, wordt dit toegevoegd aan de totaalprijs en wordt later betaald via het hotel.
2. Dit wordt verwerkt in de database en dat is terug te zien op het statistieken overzicht
3. 

###### Vragen hierover:
- 
- Wie hanteerd de betalingen?
## Statistieken

Inzicht betalingen, verkoop en omzet

- De gebruiker moet de omzet kunnen zien van het afgelopen jaar, maand, week, en dag.

###### Vragen hierover:
- Moeten mensen van het personeel toegang hebben tot deze statistieken?
	- Ja -> Wie moet er toegang hebben?
- Moeten betalingen apart worden weergegeven?
	- Ja -> Wat voor informatie is belangrijk per betaling.
- Wat wordt er precies bedoeld met inzicht verkoop?


### Room service bijhouden

###### Vragen hierover:
- Moet het restaurant iets doen met de roomservice?
	- Zo ja, wat moeten wij doen met de roomservice?
		- Hoe gaat dat in werking?