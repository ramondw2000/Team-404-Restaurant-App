# Product Backlog – Restaurant Managementsysteem

Dit document bevat alle gebruikersverhalen (user stories) voor het restaurantmanagementsysteem. De verhalen zijn gebaseerd op de projectvereisten uit de README en zijn georganiseerd per rol.

Bestaande GitHub Issues zijn aangegeven met hun issuenummer `(#nr)`. Nieuwe user stories die nog als issue aangemaakt moeten worden, zijn gemarkeerd met `[NIEUW]`. Issues die bijgewerkt moeten worden, zijn aangegeven met `[UPDATE]`.

> **Opmerking:** De user stories gemarkeerd met `[NIEUW]` worden automatisch aangemaakt als GitHub Issues via de workflow `.github/workflows/create-product-backlog-issues.yml` wanneer dit naar de `development` branch gepushed wordt. De bestaande issues worden ook bijgewerkt en toegevoegd aan het "Team 404 Scrumboard Test" project board.

---

## Eigenaar

| # | User Story | Status |
|---|-----------|--------|
| [NIEUW] | Als eigenaar wil ik toegang hebben tot alle onderdelen van de applicatie, zodat ik volledig beheer heb over het systeem. | Nieuw |

---

## Receptionist

| # | User Story | Status |
|---|-----------|--------|
| [#3](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/3) | Als receptionist wil ik reserveringen toevoegen, zodat ik het reserveringsproces kan afhandelen. | Open |
| [#4](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/4) | Als receptionist wil ik reserveringen kunnen verwijderen, zodat ik het proces van een annulering kan afhandelen. | Open |
| [#5](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/5) | Als receptionist wil ik reserveringen kunnen bewerken, zodat ik de opgegeven informatie kan bijwerken. | Open |
| [#6](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/6) | Als receptionist wil ik een overzicht zien van alle reserveringen, zodat ik kan zien welke tafels beschikbaar zijn. | Open |
| [NIEUW] | Als receptionist wil ik reserveringen gesorteerd zien per tijdvak van een halfuur, zodat ik snel een overzicht heb van de bezetting op een dag. | Nieuw |
| [NIEUW] | Als receptionist wil ik een specifieke datum kunnen selecteren om reserveringen op die dag te bekijken, zodat ik de bezetting vooruit kan inplannen. | Nieuw |
| [NIEUW] | Als receptionist wil ik onderhoudsopdrachten kunnen toevoegen voor de onderhoudsploeg, zodat ik technische problemen of taken kan melden. | Nieuw |

---

## Bedienend personeel

| # | User Story | Status |
|---|-----------|--------|
| [#7](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/7) | Als bedienend personeel wil ik bestellingen kunnen invoeren, zodat ik de keuken kan laten weten wat elke tafel heeft besteld. | Open |
| [#8](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/8) | Als bedienend personeel wil ik bestellingen kunnen aanpassen, zodat ik fouten kan verwijderen of veranderingen kan maken aan de bestelling. | Open |
| [#9](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/9) | Als bedienend personeel wil ik bestellingen kunnen annuleren, zodat ik verkeerde bestellingen kan verwijderen uit het systeem. | Open |
| [#10](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/10) | Als bedienend personeel wil ik kunnen inzien wat elke tafel heeft besteld, zodat ik zorgvuldig betalingen kan afhandelen per tafel. | Open |
| [#12](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/12) | Als bedienend personeel wil ik kunnen inzien wanneer een bestelling gereed is, zodat ik het kan serveren aan de tafel. | Open |
| [#18](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/18) | Als bedienend personeel wil ik opmerkingen kunnen toevoegen aan bestellingen, zodat ik speciale wensen of andere belangrijke informatie kan vermelden aan de keuken. | Open |
| [NIEUW] | Als bedienend personeel wil ik notities kunnen doorgeven aan de bar samen met een bestelling, zodat ik speciale wensen of instructies kan meesturen. | Nieuw |

---

## Tafelbeheer (Table Management)

| # | User Story | Status |
|---|-----------|--------|
| [NIEUW] | Als bedienend personeel wil ik een overzicht zien van alle tafels met hun huidige status (vrij, bezet, net vertrokken, gereserveerd), zodat ik snel kan zien welke tafels beschikbaar zijn. | Nieuw |
| [NIEUW] | Als bedienend personeel wil ik per tafel het aantal gasten en de resterende verblijfstijd kunnen zien, zodat ik de tafelroulatie goed kan beheren. | Nieuw |
| [NIEUW] | Als bedienend personeel wil ik een specifieke tafel kunnen selecteren en de geplaatste bestellingen en totaalprijs inzien, zodat ik de betaling correct kan afhandelen. | Nieuw |

---

## Betalingen

| # | User Story | Status |
|---|-----------|--------|
| [NIEUW] | Als bedienend personeel wil ik een betaling kunnen verwerken voor een geselecteerde tafel, zodat ik de rekening van de gasten kan afsluiten. | Nieuw |

---

## Koks

| # | User Story | Status |
|---|-----------|--------|
| [#11](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/11) | Als kok wil ik een overzicht zien van alle bestellingen, zodat ik kan weten wat elke tafel heeft besteld. | Open |
| [#13](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/13) | Als kok wil ik een bestelling kunnen markeren als klaar, zodat ik het bedienend personeel kan laten weten dat een bestelling klaar is. | Open |
| [#19](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/19) | Als kok wil ik gerechten kunnen toevoegen aan het menu, zodat ik nieuwe gerechten beschikbaar kan stellen. | Open |
| [#20](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/20) | Als kok wil ik recepten kunnen aanpassen, zodat ik veranderingen aan ingrediënten, instructies en allergenen kan maken. | Open |
| [NIEUW] | Als kok wil ik een overzicht zien van alle gerechten in het menu (naam, afbeelding, ingrediënten, instructies, allergenen), zodat ik het menu volledig kan beheren. | Nieuw |
| [NIEUW] | Als kok wil ik gerechten kunnen verwijderen uit het menu, zodat ik verouderde of niet-beschikbare gerechten kan verwijderen. | Nieuw |
| [NIEUW] | Als kok wil ik in het bestellingsoverzicht kunnen zien welke bestellingen voor room service zijn (inclusief kamernummer), zodat ik weet welke bestellingen op een kamer afgeleverd moeten worden. | Nieuw |

---

## Bar

| # | User Story | Status |
|---|-----------|--------|
| [#25](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/25) | Als barmedewerker wil ik een overzicht zien van alle bestellingen, zodat ik weet wat voor drank elke tafel heeft besteld. | Open |
| [#26](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/26) | Als barmedewerker wil ik een bestelling kunnen markeren als klaar, zodat ik het bedienend personeel kan laten weten dat een bestelling klaar is. | Open |
| [NIEUW] | Als barmedewerker wil ik ook bestellingen van gasten aan de bar kunnen bekijken en verwerken, zodat ik bargasten goed kan bedienen. | Nieuw |

---

## Manager

| # | User Story | Status |
|---|-----------|--------|
| [#14](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/14) | Als manager wil ik een overzicht hebben van alle verkoopstatistieken per dag, week, maand en jaar, zodat ik een goed beeld heb van de omzet over een bepaalde periode. | Open |
| [NIEUW] | Als manager wil ik een overzicht zien van de meest en minst verkochte gerechten, zodat ik het menu kan optimaliseren op basis van populariteit. | Nieuw |
| [NIEUW] | Als manager wil ik barmedewerkers kunnen toevoegen, verwijderen en beheren, zodat ik het barpersoneel en hun toegangsrechten efficiënt kan beheren. | Nieuw |
| [NIEUW] | Als manager wil ik de prestaties van barmedewerkers kunnen bekijken (aantal verwerkte bestellingen), zodat ik inzicht heb in de productiviteit van het barpersoneel. | Nieuw |

---

## Onderhoudspersoneel

| # | User Story | Status |
|---|-----------|--------|
| [#15](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/15) | Als onderhoudspersoneel wil ik een overzicht zien van alle onderhoudsopdrachten, zodat ik weet welke opdrachten er momenteel gedaan moeten worden. | Open |
| [#16](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/16) | Als onderhoudspersoneel wil ik een onderhoudsopdracht kunnen afvinken, zodat ik de opdracht kan melden als klaar. | Open |
| [#17](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/17) [UPDATE] | Als onderhoudspersoneel wil ik opmerkingen kunnen toevoegen aan onderhoudsopdrachten, zodat ik relevante informatie kan doorgeven aan collega's. | Open – bijwerken |

---

## Mobiele gebruiker / UX

| # | User Story | Status |
|---|-----------|--------|
| [#32](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/32) | Als mobiele gebruiker wil ik een overzichtelijke navigatiebar kunnen gebruiken (hamburgermenu), zodat ik makkelijk kan navigeren op smallere schermen. | Open |
| [#50](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/50) | Als mobiele gebruiker wil ik afbeeldingen zien van gerechten in het bestellingoverzicht, zodat ik snel gerechten kan herkennen. | Open |

---

## Technisch / Onboarding

| # | User Story | Status |
|---|-----------|--------|
| [#36](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/36) | Als ontwikkelaar wil ik via een bash-bestand kunnen onboarden, zodat ik direct aan de slag kan met de ontwikkelomgeving. | Open |

---

## Samenvatting nieuwe user stories

De volgende user stories zijn nieuw en worden aangemaakt via de GitHub Actions workflow:

| Rol | User Story |
|-----|-----------|
| Eigenaar | Volledige toegang tot de applicatie |
| Receptionist | Reserveringen gesorteerd per halfuur |
| Receptionist | Specifieke datum selecteren voor reserveringen |
| Receptionist | Onderhoudsopdrachten toevoegen |
| Bedienend personeel | Notities doorgeven aan bar |
| Tafelbeheer | Overzicht alle tafels met status |
| Tafelbeheer | Gasten en resterende tijd per tafel |
| Tafelbeheer | Tafel selecteren voor bestellingoverzicht en betaling |
| Betalingen | Betaling verwerken voor een tafel |
| Koks | Menuoverzicht bekijken |
| Koks | Gerecht verwijderen uit menu |
| Koks | Room service bestellingen herkennen |
| Bar | Bestellingen van bargasten verwerken |
| Manager | Meest/minst verkochte gerechten |
| Manager | Barmedewerkers beheren |
| Manager | Prestaties barmedewerkers bekijken |

## Aan te passen bestaande issues

| Issue | Probleem | Aanpassing |
|-------|---------|------------------------|
| [#17](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/issues/17) | Titel eindigt onvolledig op "zodat ik ." | Aangepast naar: *"Als onderhoudspersoneel wil ik opmerkingen kunnen toevoegen aan onderhoudsopdrachten, zodat ik relevante informatie kan doorgeven aan collega's."* |
