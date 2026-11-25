-- Diese Tabelle speichert optionale Begruendungen, die bei Mehrfachkonto-Loeschungen wiederverwendet werden.
-- reasonid ist die Nummer, die das Formular als Auswahlwert zurueckschickt.
-- reason ist der lesbare Text, der im Adminformular angezeigt und an die Loeschfunktion uebergeben wird.
CREATE TABLE IF NOT EXISTS deluser_reasons (
    reasonid INT UNSIGNED NOT NULL AUTO_INCREMENT,
    reason VARCHAR(255) NOT NULL,
    PRIMARY KEY (reasonid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;