###[DEF]###
[name = Transponderleser-Bestimmung]
[e#1 = Vorhaus (Leser 1)]
[e#2 = Garage (Leser 2)]
[e#3 = Programmierbox (Leser 3)]
[e#4 = Ferienwohnung Oben (Leser 4)]
[e#5 = Unter Terrasse (Leser 5)]
[e#6 = Büro (Leser 6)]
[a#1 = Vorhaus (Leser 1)]
[a#2 = Garage (Leser 2)]
[a#3 = Büro (Leser 3)]
[a#4 = Ferienwohnung Oben (Leser 4)]
[a#5 = Unter Terrasse (Leser 5)]
[a#6 = Programmierbox (Leser 6)]
[a#7 = Transpondernnummer]
###[/DEF]###



###[HELP]###
<b>Dieser LBS ist Teil eines größeren Projekts. Alle benötigten Dateien und ein Beispiel der Verschaltung ist auf <a href="https://github.com/dachsei/Arduino-RFID-Knx-dooropener">https://github.com/dachsei/Arduino-RFID-Knx-dooropener</a>.</b>

Markiert den letzten aktiven Leser mit einer 1, alle anderen mit einer 0. Wichtig: Die Eingänge sind direkt mit den Gruppenadressen der entsprechenden Leser zu verbinden, in welche die Transpondernummern geschrieben werden. Anschließend wird die aktuelle Transpondernummer am Ausgang 1 ausgegeben. Dieser Ausgang ist mit Eingang 2 (Transpondernummer) von allen (!) Transponderidentifikations-LBS´s (19000864) über ein internes KO zu verknüpfen. Die restlichen Ausgänge werden per internem KOs mit Eingang 1 (Aktiv/Inaktiv) der dazugehörigen Berechtigungs-LBS´s verknüpft.

<b style="color:red">Wichtiger Hinweis:</b> Dieser LBS darf maximal <b>einmal</b> verwendet werden, da intern verschiedene anderweitig wichtige Prozesse berücksichtigt werden!!! Bei mehrmaliger Verwendung kann es passieren, dass ihre KNX-Installation Sie sprichwörtlich im Regen stehen lässt, was auch wiederum dem WAF nicht dienlich sein sollte. Sollten mehrere Leser verwendet werden, einfach den LBS selbst anpassen oder Kontakt zum Autor aufnehmen.

E1: GA von Leser 1, auf welche die Transpondernummer gesendet wird
E2: GA von Leser 2, auf welche die Transpondernummer gesendet wird
E3: GA von Leser 3, auf welche die Transpondernummer gesendet wird
E4: GA von Leser 4, auf welche die Transpondernummer gesendet wird
E5: GA von Leser 5, auf welche die Transpondernummer gesendet wird
E6: GA von Leser 6, auf welche die Transpondernummer gesendet wird

A1: Gibt eine 1 aus, wenn E1 eine Transpondernummer empfangen hat
A2: Gibt eine 1 aus, wenn E2 eine Transpondernummer empfangen hat
A3: Gibt eine 1 aus, wenn E3 eine Transpondernummer empfangen hat
A4: Gibt eine 1 aus, wenn E4 eine Transpondernummer empfangen hat
A5: Gibt eine 1 aus, wenn E5 eine Transpondernummer empfangen hat
A6: Gibt eine 1 aus, wenn E6 eine Transpondernummer empfangen hat
A7: Gibt die aktuelle Transpondernummer aus


&copy; MH & PD
###[/HELP]###


###[LBS]###
<?
function LB_LBSID($id) {
	if ($E=getLogicEingangDataAll($id)) {
        for($i=1; $i <= 6; $i++){
            if($E[$i]['refresh']==1) {
                for($j=1; $j <= 6; $j++){
                    setLogicLinkAusgang($id, $j, 0);
                }
                setLogicLinkAusgang($id, $i, 1);
                setLogicLinkAusgang($id, 7, $E[$i]['value']);
            }
        }
    }
}
?>
###[/LBS]###
###[EXEC]### 
<?
?>
###[/EXEC]###