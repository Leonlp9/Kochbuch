# Kochbuch

## Inhaltsverzeichnis
1. [Über das Projekt](#über-das-projekt)
2. [Technologien](#technologien)
3. [Installation](#installation)
4. [API-Endpunkte](#api-endpunkte)
5. [Stilrichtlinien](#stilrichtlinien)
6. [Autoren](#autoren)
7. [Lizenz](#lizenz)
8. [Konfigurationen](#konfigurationen)
   - [Erforderliche Apache2-Module](#erforderliche-apache2-module)
   - [Apache2-Konfiguration](#apache2-konfiguration)
   - [Git-Konfiguration](#git-konfiguration)
   - [Berechtigungen](#berechtigungen)

---

## Über das Projekt
Kochbuch ist eine Webanwendung, die es Benutzern ermöglicht, Rezepte zu durchsuchen, zu speichern und zu teilen. Die Anwendung bietet eine benutzerfreundliche Oberfläche und verschiedene Kategorien, um Rezepte einfach zu finden.

---

## Technologien
- **PHP**: Backend-Logik und Datenbankinteraktionen
- **JavaScript**: Frontend-Interaktivität
- **jQuery**: AJAX-Anfragen und DOM-Manipulation
- **QuillJS**: Rich-Text-Editor für Rezeptbeschreibungen
- **HTML/CSS**: Struktur und Styling der Anwendung

---

## Installation
1. **Repository klonen**:
    ```bash
    cd /var/www/home/
    git clone https://github.com/Leonlp9/kochbuch.git
    cd kochbuch
    ```

2. **Abhängigkeiten installieren**:
    ```bash
    sudo apt update
    sudo apt install apache2 php mysql-server
    ```

3. **Datenbank konfigurieren**:
    - `config.ini`-Datei erstellen:
      ```ini
      [database]
      username = root
      password =
      base_url = http://localhost/Kochbuch/
      ```

4. **Erforderliche Konfigurationen**:
    - [Erforderliche Apache2-Module](#erforderliche-apache2-module) aktivieren
    - [Apache2-Konfiguration](#apache2-konfiguration) anpassen
    - [Git-Konfiguration](#git-konfiguration) einrichten
    - [Berechtigungen](#berechtigungen) setzen

---

## API-Endpunkte
| **Endpunkt**                   | **Beschreibung**                     | **Parameter**                                                                                   |
|--------------------------------|-------------------------------------|-------------------------------------------------------------------------------------------------|
| `GET /api.php?task=getImages`  | Gibt alle Bilder eines Rezepts zurück | `rezept_id` (erforderlich)                                                                      |
| `GET /api.php?task=deleteImage`| Löscht ein Bild eines Rezepts       | `rezept_id`, `image` (erforderlich)                                                            |
| `GET /api.php?task=deleteRezept`| Löscht ein Rezept                   | `id` (erforderlich)                                                                             |
| `GET /api.php?task=getZutaten` | Gibt Zutaten zurück                 | `name`, `limit`, `id` (optional)                                                               |
| `GET /api.php?task=getRezept`  | Gibt ein Rezept zurück              | `id` (erforderlich), `zutaten` (optional)                                                      |
| `GET /api.php?task=addEvaluation` | Fügt eine Bewertung hinzu         | `rezept`, `rating`, `name`, `text` (alle erforderlich)                                         |
| `GET /api.php?task=editEvaluation`| Bearbeitet eine Bewertung         | `rezept`, `rating`, `name`, `text` (alle erforderlich)                                         |
| `GET /api.php?task=deleteEvaluation` | Löscht eine Bewertung         | `id` (erforderlich)                                                                             |
| `GET /api.php?task=search`     | Sucht nach Rezepten                 | `search` (erforderlich), `order`, `zeit`, `kategorie`, `random`, `neueste` (optional)           |
| `GET /api.php?task=getKategorien` | Gibt alle Kategorien zurück       | `includeCount` (optional)                                                                      |

**Weitere Endpunkte** können im vollständigen Dokument eingesehen werden.

---

## Stilrichtlinien
- **CSS**:
  - Verwenden von flexbox für Layouts
  - Responsive Design mit Media Queries
- **JavaScript**:
  - jQuery für DOM-Manipulation und AJAX-Anfragen
  - Funktionen klar benennen und dokumentieren

---

## Autoren
- **Leonlp9**: Hauptentwickler

---

## Lizenz
Dieses Projekt ist unter der MIT-Lizenz lizenziert.

---

## Konfigurationen

### Erforderliche Apache2-Module
- Aktivieren Sie das `rewrite`-Modul und die `gd2`-Erweiterung:
    ```bash
    sudo a2enmod rewrite
    sudo systemctl restart apache2
    sudo nano /etc/php/7.4/apache2/php.ini
    ```
    Entfernen Sie das `;` vor `;extension=gd2`.

---

### Apache2-Konfiguration
- Aktivieren Sie das `rewrite`-Modul:
    ```bash
    sudo a2enmod rewrite
    sudo systemctl restart apache2
    ```

---

### Git-Konfiguration
- Fügen Sie das Repository zu sicheren Verzeichnissen hinzu:
    ```bash
    sudo git config --system --add safe.directory /var/www/home/Kochbuch
    git config --global core.filemode false
    ```

---

### Berechtigungen
- Setzen Sie die Berechtigungen und Eigentümer:
    ```bash
    sudo chmod -R 775 /var/www/home/Kochbuch/
    sudo chown -R www-data:www-data /var/www/home/Kochbuch/
    ```
