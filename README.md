# Arduino-RFID-Knx-dooropener
Erweiterbares Zutrittsmanagement mit [EDOMI](http://www.edomi.de/) und RFID-Lesern.

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
