###[DEF]###
[name = Berechtigungen]
[e#1 IMPORTANT = Aktiv/Inaktiv]
[e#2 IMPORTANT = akt. Chipnummer]
[e#3 TRIGGER = ständige Berechtigungen]
[e#4 OPTION = temporäre Berechtigung 1]
[e#5 OPTION = temp. Ber. 1 aktiv? ]
[e#6 OPTION = temporäre Berechtigung 2]
[e#7 OPTION = temp. Ber. 2 aktiv? ]
[e#8 OPTION = temporäre Berechtigung 3]
[e#9 OPTION = temp. Ber. 3 aktiv? ]
[e#10 OPTION = temporäre Berechtigung 4]
[e#11 OPTION = temp. Ber. 4 aktiv? ]
[a#1 = Öffner]
###[/DEF]###



###[HELP]###
<b>Dieser LBS ist Teil eines größeren Projekts. Alle benötigten Dateien und ein Beispiel der Verschaltung ist auf <a href="https://github.com/dachsei/Arduino-RFID-Knx-dooropener">https://github.com/dachsei/Arduino-RFID-Knx-dooropener</a>.</b>

Dieser LBS gibt schließlich den Impuls zur Schalthandlung. Pro Tür/Tor/Garage/etc. muss ein Baustein eingesetzt werden.

E1: Wird mit dem korrespondierenden Ausgang des Leserbestimmungs-LBS (19000863) über ein internes KO oder direkt verbunden.
E2: Hier wird die aktuelle Chipnummer eingelesen. Wichtig: In dieses interne KO schreiben alle (!) Transponder-LBS´s (19000864), auch sind alle Berechtigungs-LBS´s an Eingang 2 mit immer jenem internem KO zu verbinden.
E3: Hier sind alle Chipnummern einzutragen, welche für diese Türe ständig berechtigt sind. <b>Hinweis:</b> Die Nummern sind einfach durch ein Leerzeichen getrennt zu übergeben (z.B.: 1 2 5 8 9 11). (Somit lassen sich die Berechtigungen auch einfach über die Visualisierung verwalten.)
E 4,6,8: Hier sind Chipnummern einzutragen, welche nur vorübergehend zum Eintritt berechtigt sind (z.B. Putzfrau). Wichtig: Pro Eingang kann nur <b>eine</b> Berechtigung vergeben werden.
E 5,7,9: Hier ist anzugeben, ob die darüberliegende Chipnummer berechtigt ist ("1") oder nicht ("0"). Man kann dies bspw. mit einer Zeitschaltuhr verbinden, sodass der entsprechende Transponder nur an bestimmten Tagen zu bestimmten Uhrzeiten berechtigt ist.

A1: Gibt bei positiver Überprüfung eine 1 aus, ansonsten eine 0. Dieser Ausgang löst dann schließlich die Schalthandlung aus.


&copy; MH & PD
###[/HELP]###


###[LBS]###
<?
function LB_LBSID($id) {
    if ($E=getLogicEingangDataAll($id)) {
        if($E[2]['refresh'] && $E[1]['value'] == 1) {
            $berechtigungen_standard = explode(" ", $E[3]['value']);
            foreach ($berechtigungen_standard as $it) {
                if($it == $E[2]['value']){
                    setLogicLinkAusgang($id,1,1);
                    return;
                }
            }

            if($E[4]['value'] == $E[2]['value'] && $E[5]['value'] == 1){
                setLogicLinkAusgang($id,1,1);
            }
            elseif($E[6]['value'] == $E[2]['value'] && $E[7]['value'] == 1){
                setLogicLinkAusgang($id,1,1);
            }
            elseif($E[8]['value'] == $E[2]['value'] && $E[9]['value'] == 1){
                setLogicLinkAusgang($id,1,1);
            }
            else{
                setLogicLinkAusgang($id,1,0);
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