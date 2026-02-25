# Verslag Restaurantapplicatie Molveno Lake Resort

Een intern Engels-talige applicatie voor tafelmanagement, reserveringen, betalingen en inzicht in verkoop en omzet.

## Thema
  
Donkerblauw/Lichtblauw/Wit kleuren.

##### Vragen hierover: 
- Vragen aan Maarten-Jan om het logo te delen aan het team.

## Table Management Systeem

##### [[Voorbeeld Table Management]] 
  
Aanpasbaar tafelmanagementsysteem waar de gebruiker tafels kan aanmaken en overzien voor een bepaald aantal gasten in het restaurant. De gebruiker moet kunnen zien of de tafel bezet, vrij of gereserveerd is met een begin- en eindtijd (maximaal 2 uur).  

##### Vragen hierover:
- Is er een vast aantal mensen die bij een bepaalde tafel kunnen zitten?

## Authenticatie en Autorisatie

Rollen:
- Management (SuperAdmin)
- Kokken (?)
- Personeel 
(evt. meerdere rollen hieronder?)

Management heeft toegang tot alle rechten.

##### Vragen hierover:
- Welke rollen moeten er bestaan vanuit het restaurant?
- Wat moet elke rol moeten doen?
- Welke rechten moet elke rol hebben?

## Reserveringen

Mensen kunnen bellen of langskomen bij het hotel om een reservering te maken voor het restaurant.

1. Restaurant/Hotel personeel voert gegevens in van de gast door middel van het aanpassen van een tafel die kan worden gereserveerd.
2. Als een reservering via het hotel komt moet dat tafelnummer gelinkt worden aan het kamernummer.
3. De ingevoerde gegevens worden opgeslagen in de database.
4. Als de gast heeft betaald wordt de tafel als vrij beschouwd.

###### Vragen hierover:
- Hoe verloopt het proces wanneer een gast zonder reservering binnenloopt?
- Zijn er tafels die specifiek alleen bestemd zijn voor mensen met een reservering?

## Ingrediënten

#### Ingrediënten moeten aanpasbaar zijn (bewerkknop)

1. De gebruiker maakt een lijst van ingrediënten (indien deze ontbreken)
2. De gebruiker voegt de allergeneninformatie toe per ingrediënt (maakt zelf een notitie per allergeen)
3. Het ingrediënt wordt opgeslagen in de database
4. Het ingrediënt kan dan worden gebruikt in het maken van gerechten

###### Vragen hierover:
- Welk personeel moeten er ingrediënten kunnen aanmaken?

## Gerechten

##### Het gerecht moet aanpasbaar zijn (Edit knop)

1. De gebruiker maakt een lijst van gerechten
2. De gebruiker voert een naam in voor het gerecht
3. De gebruiker voegt de prijs van het gerecht toe
4. De gebruiker kan ingrediënten toevoegen aan het gerecht (Wat voor opmaak?)
5. Allergenen informatie (via icoontjes?)

###### Vragen hierover:
- Welk personeel moeten er gerechten kunnen aanmaken?
- Hoe moet de allergenen informatie worden weergegeven op de pagina?

## Bestellingen doorgeven

##### ==BELANGRIJK VOOR MAARTEN-JAN==: Dit systeem moet werken op kleine resoluties voor handheldapparaten

1. De gebruiker geeft een bestelling door via het systeem

## Bestelling informatie

1. Het overzichtspagina toont aan de gebruiker als er een nieuwe bestelling is. 
2. De bestelling wordt weergegeven aan de gebruikers die dit nodig hebben. 
3. De bestelling laat een tafelnummer, bestellingsnummen en de gerechten zien.
4. Als de bestelling klaar is, kan een gebruiker deze markeren als klaar (gerecht afbeelding wordt grijs?)


###### Vragen hierover:
- Welk personeel moeten er bestellingen kunnen inzien?
- Wanneer wordt een bestelling als "klaar" gezien?
- Welk personeel kunnen de bestellingen afvinken? 

### Betalingen

1. De gebruiker vinkt af dat de tafel heeft betaald 
	-   Als de gebruiker een hotelkamer heeft, wordt dit toegevoegd aan de totaalprijs en dat wordt later betaald via het hotel.
2. Dit wordt verwerkt in de database en dat is terug te zien op het statistieken overzicht

###### Vragen hierover:
- Welke rol hanteert de betalingen?

## Statistieken

Inzicht betalingen, verkoop en omzet

- De gebruiker moet de omzet kunnen zien van het afgelopen jaar, maand, week, en dag.

###### Vragen hierover:
- Is het van belang om de statistieken te zien binnen in de applicatie?
- Moeten mensen van het personeel toegang hebben tot deze statistieken?
	- Ja -> Wie moet er toegang hebben?
- Moeten betalingen apart worden weergegeven?
	- Ja -> Wat voor informatie is belangrijk per betaling.
- Wat wordt er precies bedoeld met inzicht verkoop?

### Room service bijhouden

###### Vragen hierover:
- Moet het restaurant iets doen met de room service?
	- Zo ja, wat moeten wij doen met de room service?
		- Hoe gaat dat in werking?


