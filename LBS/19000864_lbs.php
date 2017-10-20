###[DEF]###
[name = Transponder-Identifikation]
[e#1 IMPORTANT = Chipnummer]
[e#2 IMPORTANT = Remanente Transpondernummer]
[e#3 IMPORTANT = Transpondernnummer]
[e#4 IMPORTANT = Schreiben erfolgreich]
[e#5 = Länge neue Transpondernummer #init=12]
[a#1 = Chipnummer]
[a#2 = Remantente Transpondernummer]
[a#3 = Seriennummer schreiben]
[v#1 = ] New ID
###[/DEF]###



###[HELP]###
<b>Dieser LBS ist Teil eines größeren Projekts. Alle benötigten Dateien und ein Beispiel der Verschaltung ist auf <a href="https://github.com/dachsei/Arduino-RFID-Knx-dooropener">https://github.com/dachsei/Arduino-RFID-Knx-dooropener</a>.</b>

Dieser LBS wandelt eine sich ständig ändernde Transpondernummer eines RFID-Chips in eine feste Chipnummer (diese kann auch Buchstaben enthalten) um, d.h. die Chipnummer identifiziert jeden Chip eindeutig, wohingegen sich die Transpondernummer bei jedem Lesevorgang ändert. Z.B. befindet sich der Chip mit der Chipnummer 1 an meinem Schlüsselbund. Dessen Transpondernummer sei 123456abcdef. Wird dieser Chip nun an einen beliebigen Leser gehalten, ändert dieser LBS dessen Transpondernummer, z.B. zu 987654zyxwvu, gleichzeitig hat sich der LBS jedoch die alte Transpondernummer "gemerkt", sodass er als Chipnummer wieder "1" ausgibt. Sollte es also jemandem gelungen sein, einen Chip zu kopieren, so ist dieser kopierte Chip nicht mehr berechtigt, nachdem der "originale" Chip mindestens ein mal nach dem Kopieren ganz normal verwendet wurde.

E1: Hier wird die gewünschte Chipnummer dieses Transponders eingetragen, welche bspw. auch im Berechtigungs-LBS verwendet wird. Diese Chipnummer identifiziert von nun an eindeutig einen bestimmten Transponder.
E2: Dieser Eingang ist mit A2 zu verbinden. <b>Wichtig:</b> Es muss sich hierbei um ein <b>remanentes</b> KO handeln, welches über eine Ausgangsbox mit dem Ausgang 2 dieses LBS´s verbunden ist.
E3: Hier werden die aktuellen Transpondernummern eingelesen. Der Eingang ist also mit A7 des Transponderleser-Bestimmungs-LBS´s (19000863) zu verbinden.
E4: Dieser Eingang ist mit dem Status-KO der Leser zu verbinden, welches ausgibt, ob die neue Transpondernummer erfolgreich geschrieben wurde.
E5: Hier ist die gewünschte Transpondernummernlänge (# Stellen) einzutragen. Hinweis: Optimal sind 4 bis 8 Stellen, maximal jedoch 14.

A1: Gibt nach positiv abgeschlossenem Prozesere die in E1 eingetragene Chipnummer aus. Ab diesem Zeitpunkt ist ausschließlich diese Chipnummer zu verwenden, die Transpondernummer wird nun nicht mehr benötigt. Dieser Ausgang wird über ein gemeinsames internes KO an den Eingang 2 <b>aller</b> Berechtigungs-LBS´s (19000865) verknüpft.
A2: Dieser Ausgang ist über ein <b>remanentes</b>, internes KO mit dem Eingang 2 zu verbinden.
A3: Dieser Ausgang gibt die neue Transpondernummer aus, die auf den Transponder geschrieben werden soll.


&copy; MH & PD 
###[/HELP]###


###[LBS]###
<?
function RandomString($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < $length; $i++) {
        $randstring .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randstring;
}

function LB_LBSID($id) {
	if ($E=getLogicEingangDataAll($id)) {
        if($E[3]['refresh'] == 1) {
            if($E[3]['value'] == $E[2]['value']) {
                $newId = RandomString($E[5]['value']);
                logic_setVar($id, 1, $newId);
                logic_setOutput($id, 3, $newId);
            }
        }
        

        if($E[4]['refresh'] == 1 && $E[4]['value'] == 1) {
            if($E[3]['value'] == $E[2]['value']) {
                $newId = logic_getVar($id, 1);
                logic_setOutput($id, 2, $newId);
                logic_setOutput($id, 1, $E[1]['value']);
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