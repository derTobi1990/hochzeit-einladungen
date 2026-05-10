# Hochzeit Einladungen Plugin
**Alina & Tobias – 5. September 2026**

## Installation

1. Den gesamten Ordner `hochzeit-einladungen` in das Verzeichnis
   `/wp-content/plugins/` auf deinem Server hochladen (via FTP oder Dateimanager).

2. Im WordPress-Backend unter **Plugins → Installierte Plugins** das Plugin
   **"Hochzeit Einladungen"** aktivieren.
   → Die Datenbanktabellen werden automatisch angelegt.

## Verwendung

### Backend
Im WordPress-Menü erscheint der Punkt **💍 Hochzeit** mit drei Unterseiten:
- **Dashboard** – Übersicht mit Statistiken (Eingeladen / Kommen / Kommen nicht / Ausstehend)
- **Einladungen** – Einladungen anlegen, bearbeiten, löschen
- **Rückmeldungen** – Rückmeldungen manuell eintragen, bearbeiten, löschen

### Frontend-Formular einbinden
Den Shortcode auf einer WordPress-Seite einfügen:

```
[hochzeit_rueckmeldung]
```

Das Formular erscheint dann auf der Seite in eurem Teal/Gold-Design.

## Logik beim Frontend-Formular

- Gibt jemand seinen Namen ein, sucht das Plugin automatisch nach einer
  passenden Einladung (Name-Suche).
- Wird eine eindeutige Einladung gefunden → Rückmeldung wird direkt zugeordnet.
- Findet das Plugin keine oder mehrere → Es wird automatisch ein neuer Eintrag
  angelegt (erkennbar an der Adresse „(über Website)") – so geht keine Meldung verloren.

## Deinstallation
Beim Deaktivieren und Löschen des Plugins über WordPress werden alle Tabellen
und Einstellungen automatisch entfernt.
