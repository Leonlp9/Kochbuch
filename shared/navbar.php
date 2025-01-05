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
        width: 50px;

        z-index: 100;

        transition: width 0.5s cubic-bezier(0.77, 0, 0.175, 1);

        overflow: hidden;

        user-select: none;
    }

    .nav-grid-content {
        display: grid;
        grid-template-columns: 50px calc(100% - 50px);
        grid-template-areas: "nav container";
        width: 100%;
    }

    .logo {
        width: 100%;
        height: 75px;
        object-fit: contain;
        padding: 10px;
        filter: drop-shadow(3px 3px 0 var(--nonSelected));
        transition: height 0.5s cubic-bezier(0.77, 0, 0.175, 1);
    }

    .nav-grid-content nav span {
        font-size: 20px;
        width: 100%;
    }

    nav.navDesktop a {
        font-size: 25px;
        text-decoration: none;
        text-shadow: 2px 2px 0 var(--nonSelected);
        height: 50px;
        width: 100%;
        display: grid;
        grid-template-columns: 50px 1fr;
        justify-items: center;
        align-items: center;
        -webkit-tap-highlight-color:  rgba(255, 255, 255, 0);
        position: relative;
        color: var(--color);
    }

    @media (max-width: 768px) {
        .nav-grid-content {
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
            font-size: 22px;
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

        nav.navDesktop a{
            grid-template-columns: 1fr;
        }

        nav.navDesktop a:hover {
            background-color: var(--nonSelected);
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .desktop {
            display: none;
        }
    }

    @media (min-width: 768px) {
        nav.navDesktop {
            filter: drop-shadow(3px 3px 0 var(--nonSelected));
        }

        nav.navDesktop a {
            transition: width 0.5s cubic-bezier(0.77, 0, 0.175, 1), background-color 0.25s cubic-bezier(0.77, 0, 0.175, 1);
            border-bottom-right-radius: 10px;
            border-top-right-radius: 10px;
        }

        nav.navDesktop a:hover {
            background-color: var(--nonSelected);
            width: calc(100% - 5px);
        }

        nav.navDesktop:hover {
            transition-delay: 0.05s;
            width: 200px;
        }

        nav.navDesktop:hover span {
            display: inline-block;
        }

        nav.navDesktop a.active {
            background-color: var(--nonSelected);
            border-radius: 0;
            transition: background-color 0.25s cubic-bezier(0.77, 0, 0.175, 1), border-radius 0.25s cubic-bezier(0.77, 0, 0.175, 1), width 0.5s cubic-bezier(0.77, 0, 0.175, 1);
        }

        nav.navDesktop a.active:hover {
            border-radius: 0 10px 10px 0;
        }

    }

    @media print {
        nav.navDesktop {
            display: none;
        }

        .nav-grid-content {
            grid-template-columns: 1fr;
            grid-template-areas: "container";
        }
    }
</style>

<div style="grid-area: nav; height: 50px"></div>
<nav class="navDesktop no-print">

    <div class="desktop">
        <img src="icons/logo.svg" alt="Logo" class="logo">
    </div>

    <a href="./" <?php if ($currentPage == "index") echo "class='active'" ?>>
        <i class="fas fa-home"></i> <span>Kochbuch</span>
    </a>
    <a href="search" <?php if ($currentPage == "search") echo "class='active'" ?>>
        <i class="fas fa-search"></i> <span>Suche</span>
    </a>
    <a href="cart.php" <?php if ($currentPage == "cart") echo "class='active'" ?>>
        <i class="fas fa-shopping-cart"></i> <span>Einkaufsliste</span>
    </a>
    <a href="calendar.php" <?php if ($currentPage == "calendar") echo "class='active'" ?>>
        <i class="fas fa-calendar-alt"></i> <span>Kalender</span>
    </a>
    <a id="addRezept" href="new" <?php if ($currentPage == "addRezept") echo "class='active'" ?>>
        <i class="fas fa-plus"></i> <span>Rezept hinzufügen</span>
    </a>
    <div style="margin-top: auto;">
        <a href="settings.php" <?php if ($currentPage == "settings"){ echo "class='active desktop'"; } else { echo "class='desktop'"; } ?>>
            <i class="fas fa-cog"></i> <span>Einstellungen</span>
        </a>
    </div>
</nav>