# Kochbuch

## Inhaltsverzeichnis
- [Über das Projekt](#über-das-projekt)
- [Technologien](#technologien)
- [Installation](#installation)
- [Verwendung](#verwendung)
- [Projektstruktur](#projektstruktur)
- [Datenbankstruktur](#datenbankstruktur)
- [API-Endpunkte](#api-endpunkte)
- [Stilrichtlinien](#stilrichtlinien)
- [Autoren](#autoren)
- [Lizenz](#lizenz)

## Über das Projekt
Kochbuch ist eine Webanwendung, die es Benutzern ermöglicht, Rezepte zu durchsuchen, zu speichern und zu teilen. Die Anwendung bietet eine benutzerfreundliche Oberfläche und verschiedene Kategorien, um Rezepte einfach zu finden.

## Technologien
- **PHP**: Backend-Logik und Datenbankinteraktionen
- **JavaScript**: Frontend-Interaktivität
- **jQuery**: AJAX-Anfragen und DOM-Manipulation
- **QuillJS**: Rich-Text-Editor für Rezeptbeschreibungen
- **HTML/CSS**: Struktur und Styling der Anwendung

## Installation
1. **Repository klonen**:
    ```bash
    git clone https://github.com/Leonlp9/kochbuch.git
    cd kochbuch
    ```

2. **Abhängigkeiten installieren**:
    - PHP installieren
    - Datenbank (z.B. MySQL) einrichten

3. **Datenbank konfigurieren**:
    - `config.ini`-Datei erstellen und die Datenbankinformationen eintragen:
        ```ini
      [database]
      username = root
      password =
      base_url = http://localhost/Kochbuch/
      ```

4. **Datenbanktabellen erstellen**:
    - SQL-Skripte ausführen, um die erforderlichen Tabellen zu erstellen (siehe [Datenbankstruktur](#datenbankstruktur)).

## API-Endpunkte
<details>
  <summary>Klicken, um die API-Endpunkte anzuzeigen</summary>

  - **`api.php`**:
    - **GET** `/api.php?task=getImages`:
        - Gibt alle Bilder eines Rezepts zurück
        - **Parameter**:
            - `rezept_id` (erforderlich): ID des Rezepts
    - **GET** `/api.php?task=deleteImage`:
        - Löscht ein Bild eines Rezepts
        - **Parameter**:
            - `rezept_id` (erforderlich): ID des Rezepts
            - `image` (erforderlich): Name des Bildes
    - **GET** `/api.php?task=deleteRezept`:
        - Löscht ein Rezept
        - **Parameter**:
            - `id` (erforderlich): ID des Rezepts
    - **GET** `/api.php?task=getZutaten`:
        - Gibt Zutaten zurück
        - **Parameter**:
            - `name` (optional): Name der Zutat
            - `limit` (optional): Limit der Zutaten
            - `id` (optional): ID der Zutat
    - **GET** `/api.php?task=getRezept`:
        - Gibt ein Rezept zurück
        - **Parameter**:
            - `id` (erforderlich): ID des Rezepts
            - `zutaten` (optional): Zutaten des Rezepts
    - **GET** `/api.php?task=addEvaluation`:
        - Fügt eine Bewertung hinzu
        - **Parameter**:
            - `rezept` (erforderlich): ID des Rezepts
            - `rating` (erforderlich): Bewertung
            - `name` (erforderlich): Name des Bewerters
            - `text` (erforderlich): Text der Bewertung
    - **GET** `/api.php?task=editEvaluation`:
        - Bearbeitet eine Bewertung
        - **Parameter**:
            - `rezept` (erforderlich): ID des Rezepts
            - `rating` (erforderlich): Bewertung
            - `name` (erforderlich): Name des Bewerters
            - `text` (erforderlich): Text der Bewertung
    - **GET** `/api.php?task=deleteEvaluation`:
        - Löscht eine Bewertung
        - **Parameter**:
            - `id` (erforderlich): ID der Bewertung
    - **GET** `/api.php?task=search`:
        - Sucht nach Rezepten
        - **Parameter**:
            - `search` (erforderlich): Suchbegriff
            - `order` (optional): Sortierung
            - `zeit` (optional): Zeit
            - `kategorie` (optional): Kategorie
            - `random` (optional): Zufällige Rezepte
            - `neueste` (optional): Neueste Rezepte
    - **GET** `/api.php?task=getKategorien`:
        - Gibt alle Kategorien zurück
        - **Parameter**:
            - `includeCount` (optional): Anzahl der Rezepte in jeder Kategorie
    - **GET** `/api.php?task=getFilterprofile`:
        - Gibt alle Filterprofile zurück
    - **GET** `/api.php?task=getAnmerkungen`:
        - Gibt alle Anmerkungen eines Rezepts zurück
        - **Parameter**:
            - `rezept` (erforderlich): ID des Rezepts
    - **GET** `/api.php?task=addZutat`:
        - Fügt eine Zutat hinzu
        - **Parameter**:
            - `name` (erforderlich): Name der Zutat
            - `unit` (erforderlich): Einheit der Zutat
    - **GET** `/api.php?task=anmerkung`:
        - Fügt eine Anmerkung zu einem Rezept hinzu
        - **Parameter**:
            - `rezept` (erforderlich): ID des Rezepts
            - `text` (erforderlich): Text der Anmerkung
    - **GET** `/api.php?task=getKalender`:
        - Gibt alle Kalendereinträge zurück
        - **Parameter**:
            - `showPast` (optional): Vergangene Einträge anzeigen
    - **GET** `/api.php?task=addKalender`:
        - Fügt einen Eintrag zum Kalender hinzu
        - **Parameter**:
            - `date` (erforderlich): Datum des Eintrags
            - `rezept` (optional): ID des Rezepts
            - `info` (erforderlich): Info des Eintrags
    - **GET** `/api.php?task=deleteKalender`:
        - Löscht einen Eintrag aus dem Kalender
        - **Parameter**:
            - `id` (erforderlich): ID des Eintrags
    - **GET** `/api.php?task=updateKalender`:
        - Aktualisiert einen Kalendereintrag
        - **Parameter**:
            - `id` (erforderlich): ID des Eintrags
            - `text` (erforderlich): Text des Eintrags
    - **GET** `/api.php?task=getEinkaufsliste`:
        - Gibt die Einkaufsliste zurück
    - **POST** `/api.php?task=addEinkaufsliste`:
        - Fügt ein Element zur Einkaufsliste hinzu
        - **Parameter**:
            - `zutat` (erforderlich): ID der Zutat
            - `menge` (erforderlich): Menge der Zutat
            - `einheit` (erforderlich): Einheit der Zutat
    - **POST** `/api.php?task=deleteEinkaufsliste`:
        - Löscht ein Element von der Einkaufsliste
        - **Parameter**:
            - `id` (erforderlich): ID des Elements
    - **GET** `/api.php?task=export_db`:
        - Exportiert die Datenbank
    - **POST** `/api.php?task=addRezept`:
        - Fügt ein Rezept hinzu
        - **Parameter**:
            - `name` (erforderlich): Name des Rezepts
            - `kategorie` (erforderlich): Kategorie des Rezepts
            - `dauer` (erforderlich): Dauer des Rezepts
            - `portionen` (erforderlich): Portionen des Rezepts
            - `anleitung` (erforderlich): Anleitung des Rezepts
            - `zutaten` (erforderlich): Zutaten des Rezepts
            - `extraCustomInfos` (erforderlich): Zusätzliche Informationen
            - `bilder` (erforderlich): Bilder des Rezepts

</details>

## Stilrichtlinien
- **CSS**:
    - Verwenden von flexbox für Layouts
    - Responsive Design mit Media Queries
- **JavaScript**:
    - jQuery für DOM-Manipulation und AJAX-Anfragen
    - Funktionen klar benennen und dokumentieren

## Autoren
- **Leonlp9**: Hauptentwickler

## Lizenz
Dieses Projekt ist unter der MIT-Lizenz lizenziert.


## Required Apache2 Modules:
To enable the `gd2` extension in `php.ini`, open the file with:
```bash
sudo nano /etc/php/7.4/apache2/php.ini
```
Find the line `;extension=gd2`, remove the `;`, and restart Apache2:
```bash
sudo systemctl restart apache2
```

## Apache2 Configuration:
Enable the `rewrite` module and restart Apache2:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## Git Configuration:
Add the repository to the list of safe directories:
```bash
sudo git config --system --add safe.directory /var/www/home/Data-PullShow-Server/test/Kochbuch
```
Disable file mode changes:
```bash
git config --global core.filemode false
```

## Permissions:
Set the correct permissions and ownership for the project directory:
```bash
sudo chmod -R 775 /var/www/home/Data-PullShow-Server/test/Kochbuch/
sudo chown -R www-data:www-data /var/www/home/Data-PullShow-Server/test/Kochbuch/
```