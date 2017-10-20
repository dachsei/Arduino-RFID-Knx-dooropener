# Arduino-RFID-Knx-dooropener
Erweiterbares Zutrittsmanagement mit [EDOMI](http://www.edomi.de/) und RFID-Lesern.

Das zugrundeliegende Konzept ist dabei so gestaltet, dass es grundsätzlich keine Limits bezüglich der Anzahl der verwendeten Transponder bzw. Türstellen gibt, d.h. es können beliebig viele Transponder beliebig viele Türen/Tore/etc. bedienen. Dabei wurde auf eine bequeme Zuordnung der Berechtigungen (natürlich für jede Türe individuell), auch über eine Visualisierung, großen Wert gelegt. Darüber hinaus können neben ständigen Berechtigungen auch Temporäre (z.B. für eine Putzfrau) vergeben werden.

## Features
* skalierbar (keine Limitierung der RFID-Transponder bzw. Türstellen)
* bequeme Administrierung (auch über Visu)
* sehr schnell (vom Hinhalten des Transponders bis zur Öffnung ca. 200 ms)
* vergleichsweise sicher (einseits wird ausschließlich der passwortgeschützte Bereich des Transponders genutzt, andererseits wird bei jeder Behrührung mit einem Leser eine neue Seriennummer geschrieben, die nur ein einziges mal gültig ist)
* verschiedene Arten von Berechtigungen (ständige & temporäre), die völlig frei verwaltet werden können

## Hardwareaufbau
Wegen der geringen Größe eignet sich am besten ein Arduino Pro mini, es können aber auch andere Boards verwendet werden, solange man die Pinbelegung anpasst. Außerdem wird ein Busankoppler (Siemens 5WG1117-2AB12) und ein RFID Modul (MFRC-522) benötigt. Besonders platzsparend kann ein [RFID Modul mit separater Antenne](http://www.ebay.de/itm/RC522-RFID-Read-Write-Card-Module-IC-RF-Card-Inductive-13-56MHz-Separate-/322198553776?hash=item4b0487c4b0:g:5-QAAOSwRgJXj07I) verbaut werden.

| Busankoppler Pin | Arduino Pin |
| -----------------|-------------|
| GND (1)          | GND         |
| RxD (2)          | TX0         |
| TxD (4)          | RX0         |
| VCC (5)          | 5V          |

Der RST Pin wurde geändert, damit man die linke Pinleiste weglassen kann.

| Arduino Pin | MFRC-522 Pin |
| ------------|--------------|
| A1          | RST          |
| 10          | SS           |
| 11          | MOSI         |
| 12          | MISO         |
| 13          | SCK          |

## Konfiguration Arduino
Für maximale Sicherheit sollte man den key in keys.h auf ein eigenes Passwort ändern.
Die Gruppenadressen und Hardwareadresse müssen im Arduino Sketch angepasst werden. Die Hardwareadresse muss eindeutig sein, weil sich sonst Fehler ergeben, wenn 2 Leser gleichzeitig benutzt werden. SYNC_GA (DPT 1.002), WRITE_OK_GA (DPT 1.002) und NEWID_GA (DPT 16.000) sind für alle Leser gleich. READTAG_GA (DPT 16.000) sollte pro Tür eindeutig sein (Bei 2 Lesern für dieselbe Tür kann man auch die selbe GA verwenden). Es ist empfehlenswert, in der ETS pro Leser eine entsprechende Dummy-Applikation einzufügen.
Beispiel für 4 Leser:

| Leser             | SYNC_GA | WRITE_OK_GA | NEWID_GA | READTAG_GA |
| ------------------|---------|-------------|----------|------------|
| Haustüre          | 0/0/255 | 0/0/254     | 0/0/253  | 0/0/1      |
| Nebeneingang      | 0/0/255 | 0/0/254     | 0/0/253  | 0/0/2      |
| Garage links      | 0/0/255 | 0/0/254     | 0/0/253  | 0/0/3      |
| Garage rechts     | 0/0/255 | 0/0/254     | 0/0/253  | 0/0/3      |

## Allg. Ablauf/Verschaltung in Edomi
Ein Transponder wird an einen Leser gehalten. Der Arduino liest seine Transpondernummer aus und gibt sie an den Bus weiter, sodass sie schließlich in Edomi landet. Dabei ist die Gruppenadresse am LBS 19000863 mit z.B. Eingang 1 verbunden. Der Ausgang 1 dieses LBS´s gibt dann eine 1 aus, A2 bis A6 eine 0. Dieser Ausgang ist (geg. per internem KO, z.B. "Leser 1 aktiv") mit Eingang 1 des entsprechenden LBS (19000865, z.B. Berechtigungen Leser 1: Haustüre) zu verbinden. A7 reicht die Transpondernummer einfach durch. Dabei werden die Transpondernummer aller Leser auf ein gemeinsames internes KO zusammengefasst. Dieses ist mit **allen** Transponder-Identifikation-LBS´s (19000964, Eingang 3) zu verknüpfen. Dieser Baustein ist **maximal einmal** zu verwenden.

Der zweite LBS (19000864) ist pro verwendetem Transponder einmal zu verwenden. Die in Eingang 1 eingetragene Nummer identifiziert den jeweilige Chip eindeutig mit der eingetragenen Nummer (-> Chipnummer). Dies ist notwendig, da dem Transponder bei jedem Auslesen der Transpondernummer eine neue Transpondernummer zugewiesen wird. Damit wird verhindert, dass bei einer unrechtmäßig angefertigten Kopie kein Einlass gewährt wird, sobald der Originalchip mindestens einmal danach wieder an den Leser gehalten wurde, da dieser dann schon eine andere Transpondernummerhat und die Alte keine Gültigkeit mehr besitzt. Die Vergabe der Berechtigungen bezieht sich somit immer auf die jeweiligen Chipnummern. An Eingang 2 wird der Ausgang 7 von LBS 19000863 gelegt. Eingang 3 ist mit Ausgang 2 **des selben** LBS´s über ein **remanentes** internes KO zu verbinden. Das remanente KO ist deshalb sehr wichtig, um bei einem Neustart/Projektaktivierung von Edomi der LBS wieder auf die alten Transpondernummern zugreifen kann. Eingang 4 ist mit der Gruppenaddresse zu verbinden, auf welche der Arduino sendet, ob der Schreibvorgang erfolgreich abgeschlossen wurde, denn nur dann wird die neue Transpondernummer als "geschrieben" gesetzt. Mit Eingang 5 wird die gewünschte Länge der Transpondernummer gesetzt. Diese darf 14 Zeichen **nicht** überschreiten. Ausgang 1 gibt die Chipnummer aus, wenn der entsprechende Chip erkannt wurde und erfolgreich eine neue Transpondernummer geschrieben wurde. Dieser Ausgang ist dann über ein internes KO mit Eingang 2 von LBS 19000965 zu verbinden. Ausgang 2 wird mit Eingang 2 des selben LBS´s verbunden (siehe oben). Der Ausgang 3 ist mit einer Gruppenadresse zu verbinden (für alle 19000864er LBS´s die gleiche), die auch mit dem Arduino verbunden ist, womit diesem die neue Transpondernummer mitgeteilt wird, die er auf den Transponder schreiben soll.

Der dritte LBS (19000865) ist pro Tür/Tor/etc. einmal zu verwenden. Der Eingang 1 ist mit dem entsprechenden Ausgang (1 bis 6) von LBS 19000863 zu verbinden. Eingang 2 mit dem internem KO, in das alle Transponder-Identifikations-LBS´s ihre Chipnummer schreiben. In Eingang 3 werden alle ständigen Berechtigungen eingetragen, in die restlichen Eingänge die temporären Berechtigungen. Der einzige Ausgang gibt schließlich den Impuls zur Öffnung der Tür/Tor/etc.
