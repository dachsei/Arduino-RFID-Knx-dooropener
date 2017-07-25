# Arduino-RFID-Knx-dooropener
RFID Reader/Writer for Knx using a mfrc522 chip


IDs are 14 Byte Text Datatypes.

When it detects a Programmed Card it sends the ID to ID_GA. When an ID is sent to PROGRAM_GA and in the next 5000 milliseconds (can be changed in programmTime) a Tag is held in front of the Reader it gets programmed with the specified ID.
If DEBUG_GA is defined any Debug Output is sent to this GA.