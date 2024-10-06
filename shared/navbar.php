<?php

$currentPage = basename($_SERVER['PHP_SELF'], ".php");

?>

<style>
    nav.navDesktop {
        display: flex;
        flex-direction: column;
        gap: 10px;
        background-color: var(--secondaryBackground);

        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 200px;

        z-index: 100;

    }

    nav.navDesktop a {
        padding: 10px;
        color: var(--darkerColor);
        transition: margin-right 0.2s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-radius 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-left 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-right 0.2s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    nav.navDesktop a.active {
        background-color: var(--nonSelected);
        border-left: 2px solid var(--secondaryBackground);
        border-right: 2px solid var(--secondaryBackground);
        border-radius: 0 10px 10px 0;
        color: var(--color);
    }

    .nav-grid {
        display: grid;
        grid-template-columns: 200px calc(100% - 200px);
        grid-template-areas: "nav container";
        width: 100%;
    }

    @media (max-width: 768px) {
        .nav-grid {
            grid-template-columns: 100%;
            grid-template-areas: "container" "nav";
        }

        nav.navDesktop {
            flex-direction: row;
            justify-content: space-around;
            border-top: 2px solid var(--nonSelected);

            height: 50px;
            width: 100%;
            top: auto;
            bottom: 0;
            left: 0;
        }

        .navDesktop span {
            display: none;
        }

        .navDesktop a {
            font-size: 25px;
            text-decoration: none;
            text-shadow: 2px 2px 0 var(--nonSelected);
            height: calc(100% - 4px);
            width: calc(100% / 5);
            display: flex;
            justify-content: center;
            align-items: center;
            -webkit-tap-highlight-color:  rgba(255, 255, 255, 0);
            position: relative;
        }

        nav.navDesktop a.active {
            background-color: var(--nonSelected);
            border-left: 2px solid var(--secondaryBackground);
            border-right: 2px solid var(--secondaryBackground);
            border-radius: 0 0 10px 10px;
        }

        nav.navDesktop a.desktop {
            display: none;
        }
    }

    @media (min-width: 768px) {
        nav.navDesktop a:hover {
            background-color: var(--nonSelected);
            border-bottom-right-radius: 10px;
            border-top-right-radius: 10px;
            margin-right: 10px;
        }

    }

    @media print {
        nav.navDesktop {
            display: none;
        }
    }
</style>

<div style="grid-area: nav; height: 50px"></div>
<nav class="navDesktop no-print">

    <div class="desktop">
        <img src="logo.svg" alt="Logo" style="width: 40%; padding: 10px; filter: drop-shadow(3px 3px 0 var(--nonSelected));">
    </div>

    <a href="index.php" <?php if ($currentPage == "index") echo "class='active'" ?>>
        <i class="fas fa-home"></i> <span>Kochbuch</span>
    </a>
    <a href="search.php" <?php if ($currentPage == "search") echo "class='active'" ?>>
        <i class="fas fa-search"></i> <span>Suche</span>
    </a>
    <a href="cart.php" <?php if ($currentPage == "cart") echo "class='active'" ?>>
        <i class="fas fa-shopping-cart"></i> <span>Einkaufsliste</span>
    </a>
    <a href="calendar.php" <?php if ($currentPage == "calendar") echo "class='active'" ?>>
        <i class="fas fa-calendar-alt"></i> <span>Kalender</span>
    </a>
    <a id="addRezept" href="addRezept.php" <?php if ($currentPage == "addRezept") echo "class='active'" ?>>
        <i class="fas fa-plus"></i> <span>Rezept hinzufügen</span>
    </a>
    <a href="settings.php" <?php if ($currentPage == "settings"){ echo "class='active desktop'"; } else { echo "class='desktop'"; } ?>>
        <i class="fas fa-cog"></i> <span>Einstellungen</span>
    </a>
</nav>