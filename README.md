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
    - PHP und Composer installieren
    - Datenbank (z.B. MySQL) einrichten

3. **Datenbank konfigurieren**:
    - `config.php` Datei erstellen und Datenbankverbindungsdetails hinzufügen:
    ```php
    <?php
    $host = 'localhost';
    $db = 'kochbuch';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
    ```

4. **Datenbanktabellen erstellen**:
    - SQL-Skripte ausführen, um die erforderlichen Tabellen zu erstellen (siehe [Datenbankstruktur](#datenbankstruktur)).

## Verwendung
- **Starten der Anwendung**:
    - Lokalen Server starten (z.B. mit XAMPP oder MAMP)
    - Im Browser `http://localhost/kochbuch` aufrufen

## Projektstruktur
kochbuch/<br>
├── icons/<br>
│ ├── apple-touch-icon.png<br>
│ ├── favicon-32x32.png<br>
│ ├── favicon-16x16.png<br>
│ ├── site.webmanifest<br>
│ ├── safari-pinned-tab.svg<br>
│ └── favicon.ico <br>
├── shared/<br>
│ └── navbar.php<br>
├── style.css<br>
├── script.js<br>
├── index.php<br>
├── search.php<br>
└── config.php

## Datenbankstruktur
- **Tabellen**:
    - `kategorien`:
        ```sql
        CREATE TABLE kategorien (
            ID INT AUTO_INCREMENT PRIMARY KEY,
            Name VARCHAR(255) NOT NULL,
            ColorHex VARCHAR(7) NOT NULL
        );
        ```

## API-Endpunkte
- **`search.php`**:
    - **POST**: Sucht nach Rezepten basierend auf den übergebenen Parametern
    - **Parameter**:
        - `search`: Suchbegriff
        - `random`: Zufällige Rezepte
        - `neueste`: Neueste Rezepte

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

;extension=gd
extension=gd2

sudo a2enmod rewrite
sudo systemctl restart apache2

sudo git config --system --add safe.directory /var/www/home/Data-PullShow-Server/test/Kochbuch

sudo chmod -R 775 /var/www/home/Data-PullShow-Server/test/Kochbuch/
sudo chown -R www-data:www-data /var/www/home/Data-PullShow-Server/test/Kochbuch/

git config --global core.filemode false

test