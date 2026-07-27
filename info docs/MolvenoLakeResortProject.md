# Verslag Restaurantapplicatie Molveno Lake Resort

Een intern Engels-talige applicatie voor tafelmanagement, reserveringen, betalingen en inzicht in verkoop en inkomsten.

##### Vragen hierover: 
- Wat is precies het doel van de applicatie, wat moet er bereikt worden? 

## Thema
  
Donkerblauw/Lichtblauw/Wit kleuren.

##### Vragen hierover: 
- Vragen aan Maarten-Jan om het logo te delen aan het team.

## Table Management Systeem

##### [[Voorbeeld Table Management]] 
  
Aanpasbaar table management systeem waar de gebruiker tafels kan aanmaken en overzien voor een bepaald aantal gasten in het restaurant. De gebruiker moet kunnen zien of de tafel bezet, vrij of gereserveerd is met een begin- en eindtijd (maximaal 2 uur).  

##### Vragen hierover:
- Is er een vast aantal mensen die bij een bepaalde tafel kunnen zitten?

## Authenticatie en Autorisatie

Rollen:
- Management (SuperAdmin)
- Kokken
- Personeel 
(evt. meerdere rollen hieronder?)

Management heeft toegang tot alle rechten.

##### Vragen hierover:
- Welke rollen moeten er bestaan vanuit het restaurant?
- Wat moet elke rol moeten doen?
- Welke rechten moet elke rol hebben?

## Reserveringen

Mensen kunnen bellen of langskomen bij het hotel om een reservering te maken voor het restaurant.


- De gebruiker kan de specifieke informatie zien van elke reservering die opgeslagen is in de database. De volgende gegevens moeten als volgt zichtbaar zijn: 
    - De volledige naam van de gast.
    - De hoeveelheid gasten.
    - De aankomsttijd.
    - Het telefoonnummer en/of e-mailadres.
    - evt. bijzonderheden (bv. gehandicapten, allergenen).

    - Als benodigd is, moet de gebruiker de mogelijkheid hebben om de gegevens aan te passen voor elke reservering, zoals volgt: 
        - Het aanpassen van de persoonlijke informatie voor elke reservering.
        - Het annuleren van een reservering.

- De gebruiker moet een overzicht zien van alle reserveringen op een bepaalde dag. 
    - De reserveringen zijn gesorteerd in tijdvakken om de halfuur.
    - De gebruiker kan een latere datum uitkiezen om de reserveringen op die bepaalde dag in te kunnen zien.

###### Vragen hierover:
- Hoe verloopt het proces wanneer een gast zonder reservering binnenloopt?
- Zijn er tafels die specifiek alleen bestemd zijn voor mensen met een reservering?

### Recepten

##### Vragen hierover:
- Moeten er recepten toegevoegd kunnen worden?
	- Zo ja:
		Aanpak voorbeeld 1: Eerst individuele ingrediënten toevoegen en daarna uit een lijst van die ingrediënten een recept opbouwen.
		Aanpak voorbeeld 2: In een keer een recept toevoegen.

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

De gebruiker geeft een bestelling door via het systeem

## Bestelling informatie

1. Het overzichtspagina toont aan de gebruiker als er een nieuwe bestelling is. 
2. De bestelling wordt weergegeven aan de gebruikers die dit nodig hebben. 
3. De bestelling laat een tafelnummer, bestellingsnummer, speciale instructies en de gerechten zien.
4. Als de bestelling klaar is, kan een gebruiker deze markeren als klaar


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

Inzicht betalingen, verkoop en inkomsten

- De gebruiker moet de inkomsten kunnen zien van het afgelopen jaar, maand, week, en dag.

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


