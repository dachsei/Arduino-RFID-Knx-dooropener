//KEY A to use on Tags, use random numbers and keep private for maximum security
MFRC522::MIFARE_Key key = { 0x01, 0x02, 0x03, 0x04, 0x05, 0x06 };
//Key Tags have as default
MFRC522::MIFARE_Key keyDefault = { 0xff, 0xff, 0xff, 0xff, 0xff, 0xff };